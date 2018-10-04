<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

class DJMediatoolsController extends JControllerLegacy
{
	protected $default_view = 'cpanel';
	
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/djmediatools.php';
		DJMediatoolsHelper::addSubmenu($view = JRequest::getCmd('view', 'cpanel'));
				
		parent::display();

		return $this;
	}
	
	public function getvideo() {
	
		$app = JFactory::getApplication();
		
		// decode passed video url
		$link = urldecode(JRequest::getVar('video'));
	
		// get video object
		$video = DJVideoHelper::getVideo($link);
		
		// clear the buffer from any output
		@ob_clean();
		
		// return the JSON representation of $video object
		echo json_encode($video);
	
		// exit application
		$app->close();
	}
	
	public function upload() {
		
		// todo: secure upload from injections
		$user = JFactory::getUser();
		if (!$user->authorise('core.create', 'com_djmediatools')){
			echo JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN');
			exit(0);
		}
		
		DJUploadHelper::upload();
		
		return true;
	}
	
	// hidden task
	public function moveurltovideo(){
		
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM #__djmt_items');
		$items = $db->loadObjectList();
		$moved = 0;
		
		foreach($items as $item) {
				
			$item->params = new JRegistry($item->params);
			$linktype = explode(';', $item->params->get('link_type',''));
				
			if($linktype[0] == 'url') {
		
				$video = DJVideoHelper::getVideo($item->params->get('link_url'));
				
				if(count($video->getErrors())) continue; // not a video link
				if(empty($video->embed)) continue;
				
				$db->setQuery('UPDATE #__djmt_items SET video='.$db->quote($video->embed).' WHERE id='.$item->id.' AND (video IS NULL OR video=\'\')');
				$db->query();
				
				$moved++;
			}
		}
		
		$this->setRedirect(JRoute::_('index.php?option=com_djmediatools', false), 'Url link parameter moved to Video link successfully.');
		
		return true;
	}
	
	public function getfolderlist(){
		
		$app = JFactory::getApplication();
		
		$folder = urldecode(JRequest::getVar('folder'));
		$root = urldecode(JRequest::getVar('root'));
		
		if(!empty($root) && strpos($folder, $root) !== 0) {
			$folder = $root;
		}
		
		// Set folder path
		$path = JPATH_ROOT . '/' . $folder;
		
		// Get a list of folders in the search path with the given filter.
		$folders = JFolder::folders($path);
		
		$html = array();
		
		// Build the options list from the list of folders.
		$html[] = '<ul>';
		if($folder != $root) {
			$html[] = '<li><a data-folder="'.dirname($folder).'"><i class="icon-undo"></i> [ .. ]</a></li>';
		}
		if (is_array($folders))
		{
			foreach ($folders as $fldr)
			{
				$html[] = '<li><a data-folder="'.$folder.'/'.$fldr.'"><i class="icon-folder"></i> '.$fldr.'</a></li>';
			}
		}
		$html[] = '</ul>';
		
		echo implode("\n", $html);
		
		$app->close();
	}
	
	public function createfolder(){
	
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		
		$folder = urldecode(JRequest::getVar('folder'));
		$root = urldecode(JRequest::getVar('root'));
		$name = urldecode(JRequest::getVar('name'));
		
		if(!empty($root) && strpos($folder, $root) !== 0) {
			$folder = $root;
		}
		
		$name = str_replace(' ', '_', $name);
		$name = $lang->transliterate($name);
		$name = JFile::makeSafe($name);
		
		if(empty($name)) {
			echo JText::_('COM_DJMEDIATOOLS_EMPTY_FOLDER_NAME');
			$app->close();
		}
		
		// Set folder path
		$path = JPATH_ROOT . '/' . $folder . '/' . $name;
		$path = str_replace('/', DS, $path);
		// check if the destination folder exists or create it
		
		if (JFile::exists($path) && is_dir($path)) {
			echo JText::_('COM_DJMEDIATOOLS_FOLDER_EXISTS');
		} else {
			if (!JFolder::create($path)) {
				echo JText::_('COM_DJMEDIATOOLS_CANT_CREATE_FOLDER');
			} else {
				echo 'success';
			}
		}
	
		$app->close();
	}
	
	public function fixvideo() {
		
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM #__djmt_items');
		$items = $db->loadObjectList();
		$fixed = 0;
		
		foreach($items as $item) {
				
			if(!empty($item->video) && strstr($item->video, 'http:') !== false) {
				
				$video = str_replace('http:', 'https:', $item->video);
				$app->enqueueMessage($item->video.' ===> '.$video);
				$db->setQuery('UPDATE #__djmt_items SET video='.$db->quote($video).' WHERE id='.$item->id);
				$db->query();
				
				$fixed++;
			}
		}
		
		$this->setRedirect(JRoute::_('index.php?option=com_djmediatools', false), 'The http protocol has been changed to https for '.$fixed.' video links.');
		
		return true;
	}
}
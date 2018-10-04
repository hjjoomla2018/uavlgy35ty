<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.application.component.controlleradmin');


class Djcatalog2ControllerThumbs extends JControllerLegacy
{
public function go() {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		if (!$user->authorise('core.admin', 'com_djcatalog2')){
			echo 'end';
			exit(0);
		}
		$id = $app->input->get('image_id',0,'int');
		$type = $app->input->get('type', null);
		
		$where_type = (in_array($type, array('item', 'category', 'producer'))) ? 'type="'.$type.'"' : null;
		
		$db = JFactory::getDbo();
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		
		$query = 'select count(*) from #__djc2_images';
		if ($where_type) {
			$query .= ' where '.$where_type;
		}
		$db->setQuery($query);
		$total = $db->loadResult();
		$query = 'select count(*) from #__djc2_images where id > '.$id;
		if ($where_type) {
			$query .= ' and '.$where_type;
		}
		$db->setQuery($query);
		$left = $db->loadResult();
		$query = 'select id, type, fullname, path from #__djc2_images where id > '.$id;
		if ($where_type) {
			$query .= ' and '.$where_type;
		}
		$query .= ' order by id asc limit 1';
		
		$db->setQuery($query);
		$image = $db->loadObject();
		if ($image) {
			$return = array();
			$return['id'] = $image->id;
			$return['type'] = $image->type;
			$return['name'] = $image->fullname;
			$return['total'] = $total;
			$return['left'] = $left;
			
			$path = (empty($image->path)) ? DJCATIMGFOLDER : DJCATIMGFOLDER.DS.str_replace('/', DS, $image->path);
			
			if (DJCatalog2ImageHelper::processImage($path, $image->fullname, $image->type, $params)){
				$document->setMimeEncoding('application/json');
				echo json_encode($return);
			} else {
				echo 'error';
			}
			
		} else {
			echo 'end';	
		}
		exit(0);
	}
	public function purge() {
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin', 'com_djcatalog2')){
			echo JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN');
			exit(0);
		}
		
		$db = JFactory::getDbo();
		$db->setQuery('select count(*) as path_count, path from #__djc2_images group by path');
		
		$paths = $db->loadObjectList();
        
        $root_dir = new stdClass();
        $root_dir->path_count = 1;
        $root_dir->path = '';
        $paths[] = $root_dir;
		
		foreach ($paths as $path) {
			if ($path->path_count == '0') {
				continue;
			}
			
			$dir = (empty($path)) ? DJCATIMGFOLDER : DJCATIMGFOLDER.DS.str_replace('/', DS, $path->path);
			
			if (!JFolder::exists($dir.DS.'custom')){
				continue;
			}
			$files = JFolder::files($dir.DS.'custom', '.', false, false, array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX'));
			$errors = array();
			if (is_array($files) && count($files) > 0) {
				foreach ($files as $file) {
					if (!JFile::delete($dir.DS.'custom'.DS.$file)){
						$errors[] = str_replace('\\', '/', str_replace(array(DJCATIMGFOLDER.'/', DJCATIMGFOLDER.'\\'), '', $dir.DS.'custom'.DS.$file));
					}
				}
			}	
		}
		
		if (count($errors) > 0) {
			$db->setQuery("DELETE FROM #__djc2_resmushit WHERE path LIKE ".$db->quote('%/custom/%')." AND path NOT IN (".implode(',', $errors).")");
			$db->query();
			echo JText::_('COM_DJCATALOG2_SOME_IMAGES_WERE_NOT_DELETED').': '.count($errors);
		} else {
			$db->setQuery("DELETE FROM #__djc2_resmushit WHERE path LIKE ".$db->quote('%/custom/%'));
			$db->query();
			echo JText::_('COM_DJCATALOG2_ALL_IMAGES_HAVE_BEEN_DELETED');
		}
	}
	public function moveToFolders() {
		die('NO NO NO!');
		$db = JFactory::getDbo();
		$errors=array();
		
		$db->setQuery('select * from #__djc2_images');
		$images = $db->loadObjectList();
		foreach ($images as $image) {
			if (!empty($image->path)) {
				continue;
			}
			$dest =  DJCatalog2ImageHelper::getDestinationFolder(DJCATIMGFOLDER, $image->item_id, $image->type);
			if (!JFolder::exists($dest)) {
				JFolder::create($dest);
			}
			if (JFile::copy(DJCATIMGFOLDER.DS.$image->fullname, $dest.DS.$image->fullname)) {
				$path = DJCatalog2ImageHelper::getDestinationPath($image->item_id, $image->type);
				$fullpath = $path.'/'.$image->fullname;
				$db->setQuery('update #__djc2_images set fullpath='.$db->quote($fullpath).', path='.$db->quote($path).' where id='.$image->id);
				if (!$db->query()) {
					$errors[] = array('DB', $image->fullname);
					continue;
				}
				JFile::delete(DJCATIMGFOLDER.DS.$image->fullname);
			} else {
				$errors['images'] = array('COPY', $image->fullname);
			}
		}
		
		$db->setQuery('select * from #__djc2_files');
		$files = $db->loadObjectList();
		foreach ($files as $file) {
			if (!empty($file->path)) {
				continue;
			}
			$dest =  DJCatalog2FileHelper::getDestinationFolder(DJCATATTFOLDER, $file->item_id, $file->type);
			if (!JFolder::exists($dest)) {
				JFolder::create($dest);
			}
			if (JFile::copy(DJCATATTFOLDER.DS.$file->fullname, $dest.DS.$file->fullname)) {
				$path = DJCatalog2ImageHelper::getDestinationPath($file->item_id, $file->type);
				$fullpath = $path.'/'.$file->fullname;
				$db->setQuery('update #__djc2_files set fullpath='.$db->quote($fullpath).', path='.$db->quote($path).' where id='.$file->id);
				if (!$db->query()) {
					$errors[] = array('DB', $file->fullname);
					continue;
				}
				JFile::delete(DJCATATTFOLDER.DS.$file->fullname);
			} else {
				$errors['files'] = array('COPY', $file->fullname);
			}
		}
		echo '<pre>';
		print_r($errors);
		echo '</pre>';
		die();
	}
	
	public function resmushit() {
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin', 'com_djcatalog2')){
			echo JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN');
			exit(0);
		}
		
		require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djcatalog2/lib/optimizer.php');
		
		$result = DJCatalog2Optimizer::resmushit();
		
		if(!is_array($result)) {
			
			echo $result;
			
		} else {
			
			$document->setMimeEncoding('application/json');
			echo json_encode($result);
		}
		
		$app->close();
	}
}
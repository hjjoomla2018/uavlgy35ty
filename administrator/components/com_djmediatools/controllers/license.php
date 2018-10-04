<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: https://dj-extensions.com
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
 
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
jimport( 'joomla.database.table' );


class DJMediatoolsControllerLicense extends JControllerLegacy {
	
	public function save(){
		$app	= JFactory::getApplication();
		$config = JFactory::getConfig();
		$db 	= JFactory::getDbo();
		
		$ch = curl_init();
		$ext = JRequest::getString('option', '');
		$license = JRequest::getVar('license');
		$r = JRequest::getString('release', '0');
		$name = JRequest::getVar('extension', '');

		curl_setopt($ch, CURLOPT_URL,'https://dj-extensions.com/index.php?option=com_djsubscriptions&view=registerLicense&license='.$license.'&ext='.$ext.'&r='.$r);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$u = JFactory::getURI();
		curl_setopt ($ch, CURLOPT_REFERER, $u->getHost());

		if(!curl_errno($ch))
		{
			$contents = curl_exec ($ch);
		}

		curl_close ($ch);
		$res= explode(';', $contents);
		
		$secret_file = JFile::makeSafe('license_'.$config->get('secret').'.txt');
		$license_file = JPath::clean(dirname(__FILE__).'/../'.$secret_file);
		
		if(strstr($res[0], 'E')){
			$query = "UPDATE #__update_sites SET extra_query='' WHERE name='DJ-".$name."' AND type='extension' ";
			$db->setQuery($query);
			$db->query();
			 
			echo self::renderAlert(end($res), 'error');
			die();
			
		}else if(strstr($res[0], 'R')){
			$query = "UPDATE #__update_sites SET extra_query='' WHERE name='DJ-".$name."' AND type='extension' ";
			$db->setQuery($query);
			$db->query();
			 
			JFile::delete($license_file);
			
		}else{
			$query = "SELECT manifest_cache FROM #__extensions WHERE element ='pkg_dj-".strtolower($name)."' AND type='package' ";
			$db->setQuery($query);
			$mc = json_decode($db->loadResult());
			$version = $mc->version;
			
			$extra_query = 'license='.$license.'&v='.$version.'&site='.JURI::root();
			$query = "UPDATE #__update_sites SET extra_query='".addslashes($extra_query)."' WHERE name='DJ-".$name."' AND type='extension' ";
			$db->setQuery($query);
			$db->query();
			
			JFile::write($license_file, $license);
		}
		
		echo self::renderAlert(end($res), 'success');
		die();
	}
	
	public static function renderAlert($msg, $type = '', $title = '') {
	
		if(!in_array($type, array('success', 'error', 'info', ''))) $type = 'info';
	
		$html = 	'<div class="alert alert-'.$type.'">'
				.		(!empty($title) ? '<h3>'.$title.'</h3>' : '')
				.		'<div class="alert-body">'.$msg.'</div>'
						.	'</div>';
	
		return $html;
	
	}
}

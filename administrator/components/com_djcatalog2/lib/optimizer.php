<?php
/**
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

abstract class DJCatalog2Optimizer {

	static function resmushit() {
		
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		
		$files = JFolder::files(JPATH_ROOT.DS.'media'.DS.'djcatalog2'.DS.'images', '.', true, true, array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX'));
		
		$path = null;
		$filepath = null;
		
		foreach ($files as $key => $file) {
			$filepath = $file;
			//$path = JPath::clean(str_replace(JPATH_ROOT, '', $file));
			$path = str_replace('\\', '/', str_replace(array(DJCATIMGFOLDER.'/', DJCATIMGFOLDER.'\\'), '', $file));
			$db->setQuery("SELECT * FROM #__djc2_resmushit WHERE path=".$db->quote($path));
			$obj = $db->loadObject();
			if(!$obj) break;
			if($key == count($files) - 1) {
				return 'end';
			}
		}
		
		if (function_exists('curl_file_create')) { // php 5.6+
			$file = curl_file_create($filepath);
		} else { //
			$file = '@' . realpath($filepath);
		}
		$post = array('files'=> $file);
		
		// Losslessly compressing with resmush.it
		$url = 'http://api.resmush.it/ws.php';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($data);
		
		if(isset($json->error)) {
			return "reSmush.it Webservice Error ".$json->error." - ".$json->error_long;
		}
		
		// download and write file only if image size is smaller
		if($json->src_size > $json->dest_size) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $json->dest);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$image = curl_exec($ch);
			curl_close($ch);
				
			JFile::write($filepath, $image);
				
			$json->percent = 100 * ($json->src_size - $json->dest_size) / $json->src_size;
		}
		
		if(!isset($json->percent)) {
			return "Can't download or write the optimized file";
		}
		
		$db->setQuery("INSERT INTO #__djc2_resmushit (md5, path, original_size, size, percent) VALUES (".$db->quote(md5($path)).", ".$db->quote($path).", ".$json->src_size.", ".$json->dest_size.", ".$json->percent.")");
		$db->query();
		
		$db->setQuery("select count(*) from #__djc2_resmushit");
		$resmushed = $db->loadResult();
		
		$return = array();
		$return['path'] = $path;
		$return['percent'] = $json->percent;
		$return['total'] = count($files);
		$return['optimized'] = $resmushed;
		
		return $return;
	}
	
	static function removeResmushit($paths) {
		if (empty($paths) || !is_array($paths)) {
			return;
		}
		
		$db = JFactory::getDbo();
		
		$wheres = array();
		foreach($paths as $fullpath) {
			$path = str_replace('\\', '/', str_replace(array(DJCATIMGFOLDER.'/', DJCATIMGFOLDER.'\\'), '', $fullpath));
			$wheres[] = 'path LIKE '.$db->quote($path);
		}
		
		$query = $db->getQuery(true);
		$query->delete('#__djc2_resmushit')->where('( '.implode(' OR ', $wheres).' )');
		$db->setQuery($query);
		return $db->execute();
	}
}
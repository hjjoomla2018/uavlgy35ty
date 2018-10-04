<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
class Com_DJMediatoolsInstallerScript
{
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
	
	function preflight( $type, $parent ) {
		$jversion = new JVersion();
	
		// Installing component manifest file version
		$this->release = $parent->get( "manifest" )->version;
		$this->oldrelease = $this->getParam('version');
		
	}
	
	function postflight( $type, $parent ) {
		
		$db = JFactory::getDBO();
		$config = JFactory::getConfig();
		
		if($type == 'install') {
			
			$db->setQuery("UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND (element='djmediatools' OR folder='djmediatools')");
			$db->query();
		}
		
		if($type == 'update') {
			
			defined('DS') or define('DS', DIRECTORY_SEPARATOR);
			
			// fix doubled single album view
			if ( version_compare( $this->oldrelease, '2.0.0' , 'lt' ) ) {				
				$path = JPATH_ROOT.DS.'components'.DS.'com_djmediatools'.DS.'views'.DS.'item'.DS.'tmpl'.DS.'default.xml';
				//JFactory::getApplication()->enqueueMessage($path);
				if(JFile::exists($path)) {
					@unlink($path);
				}				
			}
			
			// fix video column for updates from verion 1.3.4 to 1.4.beta2
			$fixvideo = false;
			if ( version_compare( $this->oldrelease, '1.3.4' , 'ge' ) && version_compare( $this->oldrelease, '1.4.beta3' , 'lt' ) ) {
				
				$app = JFactory::getApplication();
				
				$db->setQuery("SELECT count(column_name) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$config->get('dbprefix')."djmt_items' AND table_schema = '".$config->get('db')."' AND column_name = 'video'");
				$result = $db->loadResult();
				
				if(!$result) {
					
					$db->setQuery("ALTER TABLE `".$config->get('dbprefix')."djmt_items` ADD `video` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `image`");
					$db->query();
					$fixvideo = true;
				}				
			}
			// convert old video links into video field
			if ( $fixvideo || version_compare( $this->oldrelease, '1.3.4' , 'lt' ) ) {
				
				require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_djmediatools'.DS.'lib'.DS.'video.php');
				
				$db->setQuery('SELECT * FROM #__djmt_items');
				$items = $db->loadObjectList();
				
				foreach($items as $item) {
					
					$item->params = new JRegistry($item->params);
					$linktype = explode(';', $item->params->get('link_type',''));
					
					if($linktype[0] == 'video') {
						
						$video = DJVideoHelper::getVideo($item->params->get('video_link'));
						
						$db->setQuery('UPDATE #__djmt_items SET video='.$db->quote($video->embed).' WHERE id='.$item->id);
						$db->query();
					}
				}
			}
			
			$db->setQuery("SELECT count(column_name) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$config->get('dbprefix')."djmt_albums' AND table_schema = '".$config->get('db')."' AND column_name = 'folder'");
			$result = $db->loadResult();
			
			if(!$result) {
					
				$db->setQuery("ALTER TABLE `".$config->get('dbprefix')."djmt_albums` ADD `folder` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `alias`");
				$db->query();
			}
			
			$db->setQuery("SELECT count(column_name) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$config->get('dbprefix')."djmt_albums' AND table_schema = '".$config->get('db')."' AND column_name = 'visible'");
			$result = $db->loadResult();
			
			if(!$result) {
				$db->setQuery("ALTER TABLE `".$config->get('dbprefix')."djmt_albums` ADD `visible` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `published`, ADD INDEX (`visible`)");
				$db->query();
			}
			
			// move images from joomla root to proper folder
			if ( version_compare( $this->oldrelease, '2.3.1' , 'le' ) ) {
				
				$db->setQuery("SELECT i.id, i.image, a.folder, a.id as aid, a.alias FROM #__djmt_items i, #__djmt_albums a WHERE i.catid=a.id AND i.publish_up > '2014-12-10 00:00:00'");
				$items = $db->loadObjectList();
				
				foreach($items as $item) {
					
					if((strpos($item->image, 'images/')!==0 && strcasecmp(substr($item->image, 0, 4), 'http') != 0)
							|| strpos($item->image, 'images/djmediatools/0-/')===0) {
						
						if(empty($item->folder) || $item->folder == 'images/djmediatools/0-') {
							$item->folder = 'images/djmediatools/'.$item->aid.'-'.$item->alias;
						}
						$tmpPath = JPATH_ROOT . (strpos($item->image, '/')!==0 ? DS : '') . str_replace('/', DS, $item->image);
						$path = JPATH_ROOT . DS . str_replace('/', DS, $item->folder);
						JFolder::create($path);
						
						$filename = JFile::getName($item->image);
						$name = JFile::stripExt($filename);
						$ext = JFile::getExt($filename);
						
						// prevent overriding the existing file with the same name
						if (JFile::exists($path.DS.$filename)) {
							$iterator = 1;
							$newname = $name.'.'.$iterator.'.'.$ext;
							while (JFile::exists($path.DS.$newname)) {
								$iterator++;
								$newname = $name.'.'.$iterator.'.'.$ext;
							}
							$filename = $newname;
						}
							
						if(JFile::move($tmpPath, $path . DS . $filename)) {
							
							$db->setQuery('UPDATE #__djmt_items SET image='.$db->quote($item->folder.'/'.$filename).' WHERE id='.$item->id);
							$db->query();
						}
					}
				}
				
				$db->setQuery("SELECT id, image, folder, alias FROM #__djmt_albums");
				$items = $db->loadObjectList();
				
				foreach($items as $item) {
					
					if($item->folder == 'images/djmediatools/0-') {
						$item->folder = 'images/djmediatools/'.$item->id.'-'.$item->alias;
						$db->setQuery('UPDATE #__djmt_albums SET folder='.$db->quote($item->folder).' WHERE id='.$item->id);
						$db->query();
					}
					
					if(strpos($item->image, 'images/djmediatools/0-/')===0){
						$item->image = str_replace('images/djmediatools/0-', $item->folder , $item->image);
						$db->setQuery('UPDATE #__djmt_albums SET image='.$db->quote($item->image).' WHERE id='.$item->id);
						$db->query();
					}
					
					if(strpos($item->image, 'images/')!==0 && strcasecmp(substr($item->image, 0, 4), 'http') != 0) {						
						$db->setQuery('UPDATE #__djmt_albums SET image='.$db->quote($item->folder.$item->image).' WHERE id='.$item->id);
						$db->query();
					}
				}
			}
			
			// we need to handle the update server for package here
			require_once(JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djmediatools/lib/djlicense.php'));
			DJLicense::setUpdateServer('MediaTools');
		}
	
		// move shared code
		$src = JPath::clean(JPATH_ROOT.'/media/djmediatools/djextensions');
		$dst = JPath::clean(JPATH_ROOT.'/media/djextensions');
		
		JFolder::create($dst);
		
		$folders = JFolder::folders($src);
		
		foreach($folders as $folder) {
			JFolder::move($src.DIRECTORY_SEPARATOR.$folder, $dst.DIRECTORY_SEPARATOR.$folder);
		}
				
		@JFolder::delete($src);
	}
	
	
	function getParam( $name ) {
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_djmediatools"');
		$manifest = json_decode( $db->loadResult(), true );
		return $manifest[ $name ];
	}
 
	/*
	 * sets parameter values in the component's row of the extension table
	 */
	function setParams($param_array) {
		if ( count($param_array) > 0 ) {
			// read the existing component value(s)
			$db = JFactory::getDbo();
			$db->setQuery('SELECT params FROM #__extensions WHERE name = "com_djmediatools"');
			$params = json_decode( $db->loadResult(), true );
			// add the new variable(s) to the existing one(s)
			foreach ( $param_array as $name => $value ) {
				$params[ (string) $name ] = (string) $value;
			}
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode( $params );
			$db->setQuery('UPDATE #__extensions SET params = ' .
				$db->quote( $paramsString ) .
				' WHERE name = "com_djmediatools"' );
				$db->query();
		}
	}
}

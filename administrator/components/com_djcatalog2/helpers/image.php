<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once (dirname(__FILE__).DS.'file.php');

class DJCatalog2ImageHelper extends DJCatalog2FileHelper {

	static $images = null;

	public static function renderInput($itemtype, $itemid=null, $multiple_upload = false) {
		if (!$itemtype) {
			return false;
		}
		
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$document = JFactory::getDocument();
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		
		$count_limit = $app->isAdmin() ? -1 : (int)$params->get('fed_max_images', 6);
		$total_imgs = 0;
		
		// given in KB
		$size_limit = $app->isAdmin() ? 0 : (int)$params->get('fed_max_image_size', 2048);
		
		$whitelist = explode(',', $params->get('allowed_image_types', 'jpg,png,gif'));
		foreach($whitelist as $key => $extension) {
			if (!in_array(trim($extension), self::$blacklist)) {
				$whitelist[$key] = strtolower(trim($extension));
			}
		}
		
		$images = array();
		if ($itemid) {
			$db->setQuery('SELECT * '.
						' FROM #__djc2_images '.
						' WHERE item_id='.intval($itemid). 
						' 	AND type='.$db->quote( $itemtype).
						' ORDER BY ordering ASC, name ASC ');
			$images = $db->loadObjectList();
		}
		
		
		$record_type = 'image';
		
		return self::getUploader($record_type, $itemtype, $itemid, $count_limit, $size_limit, $whitelist, $images, $multiple_upload);
	}
	public static function getImages($itemtype, $itemid, $excluded = false) {
		if (!$itemtype || !$itemid) {
			return false;
		}
		$hash = $itemtype.'.'.$itemid;
		if (isset(self::$images[$hash])) {
			return self::$images[$hash];
		}
		$db = JFactory::getDbo();
		$images = array();
		
		$include_excluded = ($excluded) ? '' : ' AND exclude=0';
		
		$db->setQuery('SELECT * '.
						' FROM #__djc2_images '.
						' WHERE item_id='.intval($itemid). 
						' 	AND type='.$db->Quote($itemtype) . $include_excluded .
						' ORDER BY ordering ASC, name ASC ');
		$images = $db->loadObjectList();

		if (count($images)) {
			foreach ( $images as $key=>$image) {
				$images[$key]->original = self::getImageUrl($image->fullpath);
				$images[$key]->fullscreen = self::getImageUrl($image->fullpath,'fullscreen');
				//$images[$key]->frontpage = self::getImageUrl($image->fullpath,'frontpage');
				$images[$key]->large = self::getImageUrl($image->fullpath,'large');
				$images[$key]->medium = self::getImageUrl($image->fullpath,'medium');
				$images[$key]->small = self::getImageUrl($image->fullpath,'small');
				$images[$key]->thumb = self::getImageUrl($image->fullpath,'thumb');
			}
		}
		self::$images[$hash] = $images;

		return self::$images[$hash];
	}
	
	public static function getImageUrl($fullpath, $size = null) {
		$suffix = '';
		switch($size) {
			case 'fullscreen': $suffix = '_f'; break;
			//case 'frontpage': $suffix = '_fp'; break;
			case 'large': $suffix = '_l'; break;
			case 'medium': $suffix = '_m'; break;
			case 'small': $suffix = '_t'; break;
			case 'thumb': $suffix = '_s'; break;
			case 'original':
			default: $suffix = ''; break;
		}
		return DJCATIMGURLPATH.'/'.self::addSuffix($fullpath, $suffix);
	}
	
	public static function getImagePath($fullpath, $size = null) {
		$suffix = '';
		switch($size) {
			case 'fullscreen': $suffix = '_f'; break;
			//case 'frontpage': $suffix = '_fp'; break;
			case 'large': $suffix = '_l'; break;
			case 'medium': $suffix = '_m'; break;
			case 'small': $suffix = '_t'; break;
			case 'thumb': $suffix = '_s'; break;
			case 'original':
			default: $suffix = ''; break;
		}
		return JPath::clean(DJCATIMGFOLDER.'/'.self::addSuffix($fullpath, $suffix));
	}
	
	public static function deleteFiles($itemtype, $itemid) {
		return self::deleteImages($itemtype, $itemid);
	}
	
	public static function deleteImages($itemtype, $itemid) {
		if (!$itemtype || !$itemid) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djcatalog2/lib/optimizer.php');
		
		$db = JFactory::getDbo();
		$images = array();
		$db->setQuery('SELECT id, fullname, path, fullpath '.
						' FROM #__djc2_images '.
						' WHERE item_id='.intval($itemid). 
						' 	AND type='.$db->Quote($itemtype).
						' ORDER BY ordering ASC, name ASC ');
		$images = $db->loadObjectList();

		$images_to_remove = array();
		if (count($images)) {
			foreach ($images as $key=>$image) {
				$image_dir = DJCATIMGFOLDER.DS.str_replace('/', DS, $image->path);
				$image_path = $image_dir.DS.$image->fullname;
				
				if (JFile::exists($image_path)) {
					
					$optimizePaths = array();
					
					if (JFile::delete($image_path)) {
						$optimizePaths[] = $image_path;
						
						$images_to_remove[] = $image->id;
						if (JFile::exists($image_dir.DS.self::addSuffix($image->fullname, '_s'))) {
							JFile::delete($image_dir.DS.self::addSuffix($image->fullname, '_s'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($image->fullname, '_s');
						}
						if (JFile::exists($image_dir.DS.self::addSuffix($image->fullname, '_f'))) {
							JFile::delete($image_dir.DS.self::addSuffix($image->fullname, '_f'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($image->fullname, '_f');
						}
						if (JFile::exists($image_dir.DS.self::addSuffix($image->fullname, '_t'))) {
							JFile::delete($image_dir.DS.self::addSuffix($image->fullname, '_t'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($image->fullname, '_t');
						}
						if (JFile::exists($image_dir.DS.self::addSuffix($image->fullname, '_m'))) {
							JFile::delete($image_dir.DS.self::addSuffix($image->fullname, '_m'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($image->fullname, '_m');
						}
						if (JFile::exists($image_dir.DS.self::addSuffix($image->fullname, '_l'))) {
							JFile::delete($image_dir.DS.self::addSuffix($image->fullname, '_l'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($image->fullname, '_l');
						}
						
						DJCatalog2Optimizer::removeResmushit($optimizePaths);
					}
				}
			}
		}
		if (count($images_to_remove)) {
			JArrayHelper::toInteger($images_to_remove);
			$ids = implode(',',$images_to_remove);
			$db->setQuery('DELETE FROM #__djc2_images WHERE id IN ('.$ids.')');
			$db->query();
		}

		return true;

	}
	
	public static function saveFiles($itemtype, $item, &$params, $isNew) {
		return self::saveImages($itemtype, $item, $params, $isNew);
	}


	public static function saveImages($itemtype, $item, &$params, $isNew) {
		if (!$itemtype || !$item || empty($params)) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djcatalog2/lib/optimizer.php');

		$itemid = $item->id;
		if (!($itemid) > 0) {
			return false;
		}
		
		$prefix = $suffix = 'image_';//.$itemtype.'_';
		
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		
		$count_limit = $app->isAdmin() ? -1 : (int)$params->get('fed_max_images', 6);
		$total_imgs = 0;
		
		// given in KB
		$size_limit = $app->isAdmin() ? 0 : (int)$params->get('fed_max_image_size', 2048);
		
		// given in Bytes
		$size_limit *= 1024;
		
		$whitelist = explode(',', $params->get('allowed_image_types', 'jpg,png,gif'));
		foreach($whitelist as $key => $extension) {
			$whitelist[$key] = strtolower(trim($extension));
		}
		
		$ids = $app->input->get($prefix.'file_id', array(),'array');
		$names = $app->input->get($prefix.'file_name', array(),'array');
		$captions = $app->input->get($prefix.'caption', array(),'array');
		$excludes = $app->input->get($prefix.'exclude', array(),'array');
		
		$files_to_update = array();
		$files_to_save = array();
		
		$update_ids = array();
		$files = array();
		
		$destination = self::getDestinationFolder(DJCATIMGFOLDER, $itemid, $itemtype);
		$sub_path = self::getDestinationPath($itemid, $itemtype);
		if (!JFolder::exists($destination)) {
			$destExist = JFolder::create($destination, 0755);
		} else {
			$destExist = true;
		}
		
		$additional_ids = (count($ids) > 0) ? 'OR id IN ('.implode(',', $ids).')' : '';
		$db->setQuery('select * from #__djc2_images where type='.$db->quote($itemtype).' and (item_id='.(int)$itemid.' '.$additional_ids.')');

		$existing_files = $db->loadObjectList('id');
		$ordering = 1;

		if (!empty($ids)) {
			foreach($ids as $key => $id) {
				
				/*if ($count_limit >= 0 && $total_imgs >= $count_limit) {
					break;
				}*/
				
				$id = (int)$id;
				$file = new stdClass();
				
				if ($id > 0 && array_key_exists($id, $existing_files)) {
					$file = clone $existing_files[$id];
				} 
				
				$file->id = (int)$id;
				$file->item_id = $itemid;
				$file->type = $itemtype;
				
				if (!empty($names[$key]) && !isset($file->fullname)) {
					$file->fullname = $names[$key];
				} else if (!isset($file->fullname)) {
					$file->fullname = '';
				}
				
				$file->caption = (!empty($captions[$key])) ? $captions[$key] : $item->alias;
				$file->exclude = (!empty($excludes[$key])) ? $excludes[$key] : 0;
				$file->ordering = $ordering++;
				$file->_uploaded = 0;				
				
				if ($id > 0) {
					$update_ids[] = $id;
				} else {
					$file->fullname = (!empty($names[$key])) ? $names[$key] : '';
					$file->path = null;
					$file->fullpath = null;
					$file->name = null;
					$file->ext = null;
					//$file->_temp_name = JPATH_ROOT.DS.'tmp'.DS.'djc2upload'.DS.$file->fullname;
					//$file->_temp_name = JFactory::getConfig()->get('tmp_path') . DS . 'djc2upload' . DS . $file->fullname;
					$file->_temp_name = JPATH_ROOT . DS . 'media' . DS . 'djcatalog2' . DS . 'tmp' . DS . $file->fullname;
					$file->_uploaded = 1;
				}
				
				$files[] = $file;
				$total_imgs++;
			}
		}
		
		// fetch files from POST
		$post_files = $app->input->files->get($prefix.'file_upload', array());
		
		foreach ($post_files as $key => $post_file) {
			if (!empty($post_file['name']) && !empty($post_file['tmp_name']) && $post_file['error'] == 0 && $post_file['size'] > 0) {
				$file = new stdClass();
				
				$file->id = 0;
				$file->item_id = $itemid;
				$file->type = $itemtype;
				$file->fullname = $post_file['name'];
				$file->caption 	= $item->alias;//JFile::stripExt($post_file['name']);
				$file->ordering = $ordering++;
				$file->exclude = 0;
				
				$file->path = null;
				$file->fullpath = null;
				$file->name = null;
				$file->ext = null;
				
				$file->_temp_name = $post_file['tmp_name'];
				$file->_uploaded = -1;
				
				$files[] = $file;
				$total_imgs++;
			}
		}

		// delete files, unless saveToCopy action is performed
		if (!$isNew && $app->input->get('task') != 'import') {
			
			$condition = 'WHERE item_id='.(int)$itemid.' AND type='.$db->quote($itemtype);
			if (count($update_ids) > 0) {
				JArrayHelper::toInteger($update_ids);

				$condition .= ' AND id NOT IN ('.implode(',', $update_ids).')';
			}
			
			$db->setQuery('SELECT id, fullname, path, fullpath FROM #__djc2_images '.$condition);
			$files_to_delete = $db->loadObjectList();
			
			$optimizePaths = array();
			
			foreach ($files_to_delete as $row) {
				$dir = DJCATIMGFOLDER.DS.str_replace('/', DS, $row->path);
				$path = $dir.DS.$row->fullname;
			
				if (!JFile::delete($path)) {
					JLog::add(JText::_('COM_DJCATALOG2_IMAGE_FILE_DELETE_ERROR'), JLog::WARNING, 'jerror');
				} else {
					$optimizePaths[] = $path;
				}
				if (JFile::exists($dir.DS.self::addSuffix($row->fullname, '_s'))) {
					JFile::delete($dir.DS.self::addSuffix($row->fullname, '_s'));
					$optimizePaths[] = $dir.DS.self::addSuffix($row->fullname, '_s');
				}
				if (JFile::exists($dir.DS.self::addSuffix($row->fullname, '_f'))) {
					JFile::delete($dir.DS.self::addSuffix($row->fullname, '_f'));
					$optimizePaths[] = $dir.DS.self::addSuffix($row->fullname, '_f');
				}
				if (JFile::exists($dir.DS.self::addSuffix($row->fullname, '_t'))) {
					JFile::delete($dir.DS.self::addSuffix($row->fullname, '_t'));
					$optimizePaths[] = $dir.DS.self::addSuffix($row->fullname, '_t');
				}
				if (JFile::exists($dir.DS.self::addSuffix($row->fullname, '_m'))) {
					JFile::delete($dir.DS.self::addSuffix($row->fullname, '_m'));
					$optimizePaths[] = $dir.DS.self::addSuffix($row->fullname, '_m');
				}
				if (JFile::exists($dir.DS.self::addSuffix($row->fullname, '_l'))) {
					JFile::delete($dir.DS.self::addSuffix($row->fullname, '_l'));
					$optimizePaths[] = $dir.DS.self::addSuffix($row->fullname, '_l');
				}
			}
			
			$db->setQuery('DELETE FROM #__djc2_images '.$condition);
			$db->query();
			
			DJCatalog2Optimizer::removeResmushit($optimizePaths);
		}
		
		// update existing files and move new ones from temporary
		
		$gd_info = function_exists('gd_info') ? gd_info() : array();
		
		if (count($files)) {
			if ($count_limit >= 0) {
				$files = array_slice($files, 0, $count_limit);
			}
			
			foreach($files as $k => &$file) {
				$copy = (bool)($isNew && $file->id > 0);
				
				if ($copy) {
					$source = (empty($file->path)) ? DJCATIMGFOLDER : DJCATIMGFOLDER.DS.str_replace('/',DS,$file->path);
					$source .= DS.$file->fullname;

					$file->id = 0;
					$file->fullname = self::createFileName(JString::strtolower($file->fullname), $destination);
					
					if (!JFile::copy($source, $destination.DS.$file->fullname)) {
						JLog::add(JText::_('COM_DJCATALOG2_IMAGE_FILE_COPY_ERROR'), JLog::WARNING, 'jerror');
						unset($files[$k]);
						continue;
					}
					
					$file->name = self::stripExtension($file->fullname);
					
					if (empty($file->caption)) {
						$file->caption = $file->name;
					}
					
					$file->ext = self::getExtension($file->fullname);
					$file->path = $sub_path;
					$file->fullpath = $sub_path.'/'.$file->fullname;
					
				} else if (empty($file->id) && !$copy) {
					$tmp_name = $file->fullname;
					$realname = empty($captions[$k]) ? $tmp_name : $captions[$k];
					
					$source = $file->_temp_name;
					
					unset($file->_temp_name);
					
					$newname = JString::strtolower(JString::substr($item->alias, 0, 200).'.'.self::getExtension($file->fullname));
					$file->fullname = self::createFileName($newname, $destination);
					
					$imgAttrs = getimagesize($source);
					
					if (in_array('gif', $whitelist) && $imgAttrs[2] == 1 && array_key_exists('GIF Create Support',$gd_info) && $gd_info['GIF Create Support'] == 1) {
						$file->ext = 'gif';
					} else if (in_array('jpg', $whitelist) && $imgAttrs[2] == 2 && ((array_key_exists('JPEG Support',$gd_info) && $gd_info['JPEG Support'] == 1) || (array_key_exists('JPG Support',$gd_info) && $gd_info['JPG Support'] == 1))) {
						$file->ext = 'jpg';
					} else if (in_array('png', $whitelist) && $imgAttrs[2] == 3 && array_key_exists('PNG Support',$gd_info) && $gd_info['PNG Support'] == 1) {
						$file->ext = 'png';
					} else {
						$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_IMAGE_WRONG_TYPE', $realname), 'error');
						JLog::add(JText::_('COM_DJCATALOG2_IMAGE_FILE_WRONG_TYPE'), JLog::WARNING, 'jerror');
						continue;
					}
					
					if (filesize($source) > $size_limit && $size_limit) {
						$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_FILE_IS_TOO_BIG', $realname), 'error');
						unset($files[$k]);
						continue;
					}
					
					if ($file->_uploaded === 1) {
						if (!JFile::copy($source, $destination.DS.$file->fullname)) {
							JLog::add(JText::_('COM_DJCATALOG2_IMAGE_FILE_COPY_ERROR'), JLog::WARNING, 'jerror');
							unset($files[$k]);
							continue;
						}
					} else if ($file->_uploaded === -1) {
						if (!JFile::upload($source, $destination.DS.$file->fullname)) {
							JLog::add(JText::_('COM_DJCATALOG2_IMAGE_FILE_COPY_ERROR'), JLog::WARNING, 'jerror');
							unset($files[$k]);
							continue;
						}
					} else {
						unset($files[$k]);
						continue;
					}
					
					unset($file->_uploaded);
					
					$file->name = self::stripExtension($file->fullname);
					$file->ext = self::getExtension($file->fullname);
					$file->path = $sub_path;
					$file->fullpath = $sub_path.'/'.$file->fullname;
				}
			}
			unset($file);
		}
		
		// update DB & process
		foreach ($files as $k=>$v) {
			$ret = false;
			if ($v->id) {
				$ret = $db->updateObject( '#__djc2_images', $v, 'id', false);
			} else {
				$ret = $db->insertObject( '#__djc2_images', $v, 'id');
				if ($ret) {
					self::processImage($destination, $v->fullname, $itemtype, $params);
				}
			}
			if( !$ret ){
				unset($files[$k]);
				JLog::add(JText::_('COM_DJCATALOG2_IMAGE_STORE_ERROR').$db->getErrorMsg(), JLog::WARNING, 'jerror');
				continue;
			}
		}
		return true;
	}
	
	
	public static function saveImagesPlain($itemtype, $item, &$params, $isNew) {
	//public static function saveImages($itemtype, $item, &$params, $isNew) {
		if (!$itemtype || !$item || empty($params)) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djcatalog2/lib/optimizer.php');
		
		$itemid = $item->id;
		if (!($itemid) > 0) {
			return false;
		}
		
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		
		$count_limit = $app->isAdmin() ? -1 : (int)$params->get('fed_max_images', 6);
		$total_imgs = 0;
		
		// given in KB
		$size_limit = $app->isAdmin() ? 0 : (int)$params->get('fed_max_image_size', 2048);
		
		// given in Bytes
		$size_limit *= 1024;
		
		$whitelist = explode(',', $params->get('allowed_image_types', 'jpg,png,gif'));
		foreach($whitelist as $key => $extension) {
			if (!in_array(trim($extension), self::$blacklist)) {
				$whitelist[$key] = strtolower(trim($extension));
			}
		}
		
		$image_id = $app->input->get('image_id_'.$itemtype, array(),'array');
		$caption = $app->input->get('caption_'.$itemtype, array(),'array');
		$delete = $app->input->get('delete_'.$itemtype, array(),'array');
		$order = $app->input->get('order_'.$itemtype, array(),'array');
		$files = $app->input->files;
		
		$multiple_images = array();
		$multiple_upload_count = $app->input->get('multiuploader_'.$itemtype.'_count', 0, 'int');

		if ($multiple_upload_count > 0) {
			for ($mi = 0; $mi < $multiple_upload_count; $mi++) {
				$mi_row = array();
				if ($app->input->get('multiuploader_'.$itemtype.'_'.$mi.'_status', '', 'string') == 'done') {
					$mi_row['tmp_name'] = $app->input->get('multiuploader_'.$itemtype.'_'.$mi.'_tmpname', '', 'string');
					if (!empty($mi_row['tmp_name'])) {
						$mi_row['tmp_name'] = JPATH_ROOT.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'djcatalog2'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$mi_row['tmp_name'];
						$mi_row['name'] = $app->input->get('multiuploader_'.$itemtype.'_'.$mi.'_name', '', 'string');
						$mi_row['error'] = 0;
						$mi_row['size'] = filesize($mi_row['tmp_name']);
						$mi_row['multiupload'] = true;
						$multiple_images[] = $mi_row;
					}
				}
			}
		}
		
		$images_to_update = array();
		$images_to_save = array();
		$images_to_copy = array();

		$orderingCounter = 0;


		//delete files
		if (count($delete) && !$isNew) {
			$cids = implode(',', array_keys($delete));
			$db->setQuery('SELECT id, fullname, path, fullpath FROM #__djc2_images WHERE id IN ('.$cids.')');
			$images_to_delete = $db->loadObjectList();
			
			$optimizePaths = array();
			
			foreach ($images_to_delete as $row) {
				$image_dir = DJCATIMGFOLDER.DS.str_replace('/', DS, $row->path);
				$image_path = $image_dir.DS.$row->fullname;
				
				if (JFile::exists($image_path)) {
					if (!JFile::delete($image_path)) {
						JLog::add(JText::_('COM_DJCATALOG2_IMAGE_FILE_DELETE_ERROR'), JLog::WARNING, 'jerror');
						unset($delete[$row->id]);
					} else {
						$optimizePaths[] = $image_path;
						
						if (JFile::exists($image_dir.DS.self::addSuffix($row->fullname, '_s'))) {
							JFile::delete($image_dir.DS.self::addSuffix($row->fullname, '_s'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($row->fullname, '_s');
						}
						if (JFile::exists($image_dir.DS.self::addSuffix($row->fullname, '_f'))) {
							JFile::delete($image_dir.DS.self::addSuffix($row->fullname, '_f'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($row->fullname, '_f');
						}
						if (JFile::exists($image_dir.DS.self::addSuffix($row->fullname, '_t'))) {
							JFile::delete($image_dir.DS.self::addSuffix($row->fullname, '_t'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($row->fullname, '_t');
						}
						if (JFile::exists($image_dir.DS.self::addSuffix($row->fullname, '_m'))) {
							JFile::delete($image_dir.DS.self::addSuffix($row->fullname, '_m'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($row->fullname, '_m');
						}
						if (JFile::exists($image_dir.DS.self::addSuffix($row->fullname, '_l'))) {
							JFile::delete($image_dir.DS.self::addSuffix($row->fullname, '_l'));
							$optimizePaths[] = $image_dir.DS.self::addSuffix($row->fullname, '_l');
						}
					}
				}
			}
			$cids = implode(',', array_keys($delete));
			$db->setQuery('DELETE FROM #__djc2_images WHERE id IN ('.$cids.')');
			$db->query();
			foreach ($delete as $key => $value) {
				if ($value == 1) {
					$idx = array_search($key, $image_id);
					if (array_key_exists($idx, $image_id)) {
						unset($image_id[$idx]);
					}
				}
			}
			
			DJCatalog2Optimizer::removeResmushit($optimizePaths);
		}

		// fetch images that need to be updated/copied to the new item
		if (count($image_id)) {
			JArrayHelper::toInteger($image_id);
			$ids = implode(',', $image_id);
			$db->setQuery('SELECT * FROM #__djc2_images WHERE id IN ('.$ids.') ORDER BY ordering ASC, name ASC');
			$images = $db->loadObjectList();
			foreach ($image_id as $key) {
				foreach ($images as $image) {
					if ($image->id == $key && !array_key_exists($key, $delete)) {
						$obj = array();
						$obj['id'] = ($isNew) ? null:$key;
						if (isset($caption[$key])) {
							$obj['caption'] = $caption[$key];
						} else {
							$obj['caption'] = '';
						}
						if (isset($order[$key])) {
							$obj['ordering'] = intval($order[$key]);
						} else {
							$obj['ordering'] = $image->ordering;
						}
						$obj['name'] = $image->name;
						$obj['fullname'] = $image->fullname;
						$obj['ext'] = $image->ext;
						$obj['item_id'] = $itemid;
						$obj['type'] = $itemtype;
						$obj['path'] = $image->path;
						$obj['fullpath'] = $image->fullpath;

						if ($obj['id']) {
							$images_to_update[] = $obj;
							$total_imgs++;
						} else {
							$images_to_copy[] = $obj;
						}
					}
				}
			}
			usort($images_to_update, array('DJCatalog2ImageHelper', 'setOrdering'));
		}

		$destExist = false;
		$destination = self::getDestinationFolder(DJCATIMGFOLDER, $itemid, $itemtype);
		$sub_path = self::getDestinationPath($itemid, $itemtype);
		if (!JFolder::exists($destination)) {
			$destExist = JFolder::create($destination, 0755);
		} else {
			$destExist = true;
		}

		if ($destExist) {
			// copy images
			if (count($images_to_copy)) {
				foreach ($images_to_copy as $key => $copyme) {
					$source = (empty($copyme['path'])) ? DJCATIMGFOLDER : DJCATIMGFOLDER.DS.str_replace('/',DS,$copyme['path']);
					
					$new_file_name = self::createFileName($copyme['fullname'], $destination);
					if (!JFile::copy($source.DS.$copyme['fullname'], $destination.DS.$new_file_name)) {
						JLog::add(JText::_('COM_DJCATALOG2_IMAGE_FILE_COPY_ERROR'), JLog::WARNING, 'jerror');
						unset($images_to_copy[$key]);
					} else {
						$images_to_copy[$key]['fullname'] = $new_file_name;
						$images_to_copy[$key]['name'] = self::stripExtension($new_file_name);
						$images_to_copy[$key]['ext'] = self::getExtension($new_file_name);
						$images_to_copy[$key]['path'] = $sub_path;
						$images_to_copy[$key]['fullpath'] = $sub_path.'/'.$new_file_name;
					}
				}
			}
			
			// save uploaded files
			$file_caption = $app->input->get('file_caption_'.$itemtype,array(),'array');
			$file_arr = $files->get('file_'.$itemtype, array());
			$file_arr = array_merge($file_arr, $multiple_images);

			if(!empty($file_arr)) {
				$gd_info = gd_info();
				foreach ($file_arr as $key => $file) {
					if (!empty($file['name']) && !empty($file['tmp_name']) && $file['error'] == 0) {
						$name = $file['name'];
						$imgAttrs = getimagesize($file['tmp_name']);
						$obj = array();
						$obj['id'] = null;
						if (in_array('gif', $whitelist) && $imgAttrs[2] == 1 && array_key_exists('GIF Create Support',$gd_info) && $gd_info['GIF Create Support'] == 1) {
							$obj['ext'] = 'gif';
						} else if (in_array('jpg', $whitelist) && $imgAttrs[2] == 2 && ((array_key_exists('JPEG Support',$gd_info) && $gd_info['JPEG Support'] == 1) || (array_key_exists('JPG Support',$gd_info) && $gd_info['JPG Support'] == 1))) {
							$obj['ext'] = 'jpg';
						} else if (in_array('png', $whitelist) && $imgAttrs[2] == 3 && array_key_exists('PNG Support',$gd_info) && $gd_info['PNG Support'] == 1) {
							$obj['ext'] = 'png';
						} else {
							$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_IMAGE_WRONG_TYPE', $name), 'error');
							JLog::add(JText::_('COM_DJCATALOG2_IMAGE_FILE_WRONG_TYPE'), JLog::WARNING, 'jerror');
							continue;
						}
						
						if ($file['size'] > $size_limit && $size_limit) {
							$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_FILE_IS_TOO_BIG', $name), 'error');
							continue;
						}
						
						if ($count_limit >= 0 && $total_imgs >= $count_limit) {
							continue;
						}
						
						$newname = substr($item->alias, 0, 200).'.'.self::getExtension($name);
						$obj['fullname'] = self::createFileName($newname, $destination);
						$obj['ordering'] = 0;
						$obj['name'] = self::stripExtension($obj['fullname']);
						$obj['item_id'] = $itemid;
						$obj['type'] = $itemtype;
						$obj['path'] = $sub_path;
						$obj['fullpath'] = $sub_path.'/'.$obj['fullname'];
						if (isset($file_caption[$key]) && $file_caption[$key] != '') {
							$obj['caption'] = $file_caption[$key];
						} else {
							$obj['caption'] = $obj['name'];
						}
						
						if (isset($file['multiupload']) && $file['multiupload']) {
							JFile::move($file['tmp_name'], $destination.DS.$obj['fullname']);
							$images_to_save[] = $obj;
							$total_imgs++;
						}
						else if (JFile::upload($file['tmp_name'], $destination.DS.$obj['fullname'])) {
							$images_to_save[] = $obj;
							$total_imgs++;
						}
						else {
							JLog::add(JText::_('COM_DJCATALOG2_IMAGE_UPLOAD_ERROR'), JLog::WARNING, 'jerror');
						}
					}
				}
			}
		}

		// order images
		$ordering = 1;
		foreach ($images_to_update as $k=>$v) {
			$images_to_update[$k]['ordering'] = $ordering++;
			$obj = new stdClass();
			foreach ($images_to_update[$k] as $key=>$data) {
				$obj->$key = $data;
			}
			if ($isNew) {
				$ret = $db->insertObject( '#__djc2_images', $obj, 'id');
			} else {
				$ret = $db->updateObject( '#__djc2_images', $obj, 'id', false);
			}
			if( !$ret ){
				JLog::add(JText::_('COM_DJCATALOG2_IMAGE_STORE_ERROR').$db->getErrorMsg(), JLog::WARNING, 'jerror');
				continue;
			}
		}

		$images_to_process = array_merge($images_to_copy, $images_to_save);
		foreach ($images_to_process as $k=>$v) {
			$images_to_process[$k]['ordering'] = $ordering++;
			$obj = new stdClass();
			foreach ($images_to_process[$k] as $key=>$data) {
				$obj->$key = $data;
			}
			$ret = $db->insertObject( '#__djc2_images', $obj, 'id');
			if( !$ret ){
				unset($images_to_process[$k]);
				JLog::add(JText::_('COM_DJCATALOG2_IMAGE_STORE_ERROR').$db->getErrorMsg(), JLog::WARNING, 'jerror');
				continue;
			}
			self::processImage($destination, $v['fullname'], $itemtype, $params);
		}
		return true;
	}

	public static function createFileName($filename, $path, $ext = null) {
		$lang = JFactory::getLanguage();
		
		$hash = md5($filename);
		
		$namepart = self::stripExtension($filename);
		$extpart = ($ext) ? $ext : self::getExtension($filename);
		
		$namepart = $lang->transliterate($namepart);
		$namepart = strtolower($namepart);
		$namepart = JFile::makeSafe($namepart);
		$namepart = str_replace(' ', '_', $namepart);
		
		if ($namepart == '') {
			$namepart = $hash;
		}
		
		$filename = $namepart.'.'.$extpart;
		
		if (JFile::exists($path.DS.$filename)) {
			if (is_numeric(self::getExtension($namepart)) && count(explode(".", $namepart))>1) {
				$namepart = self::stripExtension($namepart);
			}
			$iterator = 1;
			$newname = $namepart.'.'.$iterator.'.'.$extpart;
			while (JFile::exists($path.DS.$newname)) {
				$iterator++;
				$newname = $namepart.'.'.$iterator.'.'.$extpart;
			}
			$filename = $newname;
		}

		return $filename;
	}

	public static function processImage($path, $filename, $itemtype, &$params) {
		require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djcatalog2/lib/optimizer.php');
		
		$resize = intval($params->get($itemtype.'_resize', $params->get('resize', 0)));

		$width = $params->get($itemtype.'_width', $params->get('width', 300));
		$height = $params->get($itemtype.'_height', $params->get('height', 300));

		//$fp_width = $params->get($itemtype.'_fp_width', $params->get('fp_width', 300));
		//$fp_height = $params->get($itemtype.'_fp_height', $params->get('fp_height', 300));

		$medium_width = $params->get($itemtype.'_th_width', $params->get('th_width', 120));
		$medium_height = $params->get($itemtype.'_th_height', $params->get('th_height', 120));

		$small_width = $params->get($itemtype.'_smallth_width', $params->get('smallth_width', 92));
		$small_height = $params->get($itemtype.'_smallth_height', $params->get('smallth_height', 92));
		
		$enlargeDefault = true;
		$keepRatioDefault = false;
		$watermarkDefault = $params->get($itemtype.'_watermark', $params->get('watermark', false));
		
		$optimizePaths = array();
		
		if (JFile::exists($path.DS.self::addSuffix($filename, '_s'))) {
			JFile::delete($path.DS.self::addSuffix($filename, '_s'));
			$optimizePaths[] = $path.DS.self::addSuffix($filename, '_s');
		}
		if (JFile::exists($path.DS.self::addSuffix($filename, '_f'))) {
			JFile::delete($path.DS.self::addSuffix($filename, '_f'));
			$optimizePaths[] = $path.DS.self::addSuffix($filename, '_f');
		}
		self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_s'), 75, 45, true, $enlargeDefault, $watermarkDefault);

		self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_f'), 1920, 1920, true, false, $watermarkDefault);

		if (JFile::exists($path.DS.self::addSuffix($filename, '_t'))) {
			JFile::delete($path.DS.self::addSuffix($filename, '_t'));
			$optimizePaths[] = $path.DS.self::addSuffix($filename, '_t');
		}
		if (JFile::exists($path.DS.self::addSuffix($filename, '_m'))) {
			JFile::delete($path.DS.self::addSuffix($filename, '_m'));
			$optimizePaths[] = $path.DS.self::addSuffix($filename, '_m');
		}
		if (JFile::exists($path.DS.self::addSuffix($filename, '_l'))) {
			JFile::delete($path.DS.self::addSuffix($filename, '_l'));
			$optimizePaths[] = $path.DS.self::addSuffix($filename, '_l');
		}
		/*if (JFile::exists($path.DS.self::addSuffix($filename, '_fp'))) {
			JFile::delete($path.DS.self::addSuffix($filename, '_fp'));
		}*/
		
		DJCatalog2Optimizer::removeResmushit($optimizePaths);

		switch ($resize) {
			case 1: {
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_l'), $width, 0, $keepRatioDefault, $enlargeDefault, $watermarkDefault);
				//self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_fp'), $fp_width, 0);
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_m'), $medium_width, 0, $keepRatioDefault, $enlargeDefault, $watermarkDefault);
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_t'), $small_width, 0, $keepRatioDefault, $enlargeDefault, $watermarkDefault);
				break;
			}

			case 2 : {
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_l'), 0, $height, $keepRatioDefault, $enlargeDefault, $watermarkDefault);
				//self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_fp'), 0, $fp_height);
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_m'), 0, $medium_height, $keepRatioDefault, $enlargeDefault, $watermarkDefault);
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_t'), 0, $small_height, $keepRatioDefault, $enlargeDefault, $watermarkDefault);
				break;
			}

			case 3 : {
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_l'), $width, $height, $keepRatioDefault, $enlargeDefault, $watermarkDefault);
				//self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_fp'), $fp_width, $fp_height);
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_m'), $medium_width, $medium_height, $keepRatioDefault, $enlargeDefault, $watermarkDefault);
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_t'), $small_width, $small_height, $keepRatioDefault, $enlargeDefault, $watermarkDefault);
				break;
			}

			case 0 :
			default: {
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_l'), $width, $height, true, $enlargeDefault, $watermarkDefault);
				//self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_fp'), $fp_width, $fp_height, true);
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_m'), $medium_width, $medium_height, true, $enlargeDefault, $watermarkDefault);
				self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_t'), $small_width, $small_height, true, $enlargeDefault, $watermarkDefault);

				break;
			}

			/*case 0:
			 default: {
			 JFile::copy($path.DS.$filename, $path.DS.self::addSuffix($filename, '_l'));
			 self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_m'), $medium_width, 0);
			 self::resizeImage($path.DS.$filename, $path.DS.self::addSuffix($filename, '_t'), $small_width, 0);
			 break;
			 }*/
		}

		return true;
	}

	public static function resizeImage($path, $newpath, $nw = 0, $nh = 0, $keep_ratio = false, $enlarge = true, $watermark = null) {

		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		
		if (!$path || !$newpath)
		return false;
		
		if (!JFile::exists($path)) {
			return false;
		}
		
		$w = $h = $type = $attr = null;
		if (! list ($w, $h, $type, $attr) = getimagesize($path)) {
			return false;
		}

		$OldImage = null;

		switch($type)
		{
			case 1:
				$OldImage = imagecreatefromgif($path);
				break;
			case 2:
				$OldImage = imagecreatefromjpeg($path);
				break;
			case 3:
				$OldImage = imagecreatefrompng($path);
				break;
			default:
				return  false;
				break;
		}

		if ($nw == 0 && $nh == 0) {
			$nw = 75;
			$nh = (int)(floor(($nw * $h) / $w));
		}
		elseif ($nw == 0) {
			$nw = (int)(floor(($nh * $w) / $h));
		}
		elseif ($nh == 0) {
			$nh = (int)(floor(($nw * $h) / $w));
		}
		if ($keep_ratio) {
			$x_ratio = $nw / $w;
			$y_ratio = $nh / $h;

			if (($x_ratio * $h) < $nh){
				$nh = floor($x_ratio * $h);
			}else{
				$nw = floor($y_ratio * $w);
			}
		}

		if ( ($nw > $w || $nh > $h) && !$enlarge) {
			$nw = $w;
			$nh = $h;
		}

		// check if ratios match
		$_ratio=array($w/$h, $nw/$nh);
		if ($_ratio[0] != $_ratio[1]) { // crop image

			// find the right scale to use
			$_scale=min((float)($w/$nw),(float)($h/$nh));

			// coords to crop
			$cropX=(float)($w-($_scale*$nw));
			$cropY=(float)($h-($_scale*$nh));

			// cropped image size
			$cropW=(int)($w-$cropX);
			$cropH=(int)($h-$cropY);

			$crop = null;
				
			/*if ($type == 2 || $type == 3) {
				$crop = imagecreatetruecolor($cropW,$cropH);
			} else {
				$crop = imagecreate($cropW,$cropH);
			}*/
			
			$crop = imagecreatetruecolor($cropW,$cropH);
				
			if ($type == 3 || $type == 1) {
				$bg = imagecolortransparent($crop, imagecolorallocatealpha($crop, 0, 0, 0, 127));
				imagealphablending($crop, false);
				imagefill($crop, 0, 0, $bg);
				imagesavealpha($crop, true);
			} /*else if ($type == 1) {
				$bg = imagecolortransparent($crop, imagecolorallocate($crop, 0, 0, 0));
				imagefill($crop, 0, 0, $bg);
			} */else {
				$bg = imagecolorallocate($crop, 255, 255, 255);
				imagefill($crop, 0, 0, $bg);
			}
				
				
			$cropCoeffsX = array('l' => 0, 'm' => 0.5, 'r' => 1);
			$cropCoeffsY = array('t' => 0, 'm' => 0.5, 'b' => 1);
				
			$cropAlignmentX = $params->get('crop_alignment_h', 'm');
			$cropAlignmentY = $params->get('crop_alignment_v', 'm');
				
			if (!array_key_exists($cropAlignmentX, $cropCoeffsX)) {
				$cropAlignmentX = 'm';
			}
				
			if (!array_key_exists($cropAlignmentY, $cropCoeffsY)) {
				$cropAlignmentY = 'm';
			}
			/*
			 imagecopyresampled(
				$crop,
				$OldImage,
				0,
				0,
				(int)($cropX * $cropCoeffsX[$cropAlignmentX]),
				(int)($cropY * $cropCoeffsY[$cropAlignmentY]),
				$cropW,
				$cropH,
				$cropW,
				$cropH
				);
				*/
				
			imagecopy($crop, $OldImage, 0, 0, (int)($cropX * $cropCoeffsX[$cropAlignmentX]), (int)($cropY * $cropCoeffsY[$cropAlignmentY]), $cropW, $cropH);
		}

		// do the thumbnail
		$NewThumb = imagecreatetruecolor($nw,$nh);

		if ($type == 3 || $type == 1) {
			$bg = imagecolortransparent($NewThumb, imagecolorallocatealpha($NewThumb, 0, 0, 0, 127));
			imagealphablending($NewThumb, false);
			imagefill($NewThumb, 0, 0, $bg);
			imagesavealpha($NewThumb, true);
		} /*else if ($type == 1) {
			$bg = imagecolortransparent($NewThumb, imagecolorallocate($NewThumb, 0, 0, 0));
			imagefill($NewThumb, 0, 0, $bg);
		} */else {
			$bg = imagecolorallocate($NewThumb, 255, 255, 255);
			imagefill($NewThumb, 0, 0, $bg);
		}

		if (isset($crop)) { // been cropped
			imagecopyresampled($NewThumb, $crop, 0, 0, 0, 0, $nw, $nh, $cropW, $cropH);
			imagedestroy($crop);
		} else { // ratio match, regular resize
			imagecopyresampled($NewThumb, $OldImage, 0, 0, 0, 0, $nw, $nh, $w, $h);
		}
		
		if (is_null($watermark)) {
			$watermark = $params->get('watermark', false);
		}
		$watermarkImg = $watermark ? $params->get('watermark_file', false) : false;
		$watermarkPath = JPath::clean(JPATH_ROOT.'/'.$watermarkImg);
		
		if($watermarkImg && JFile::exists($watermarkPath)){
			if (list ($w_w, $w_h, $w_type, $w_attr) = getimagesize($watermarkPath)) {
				$w_size = $params->get('watermark_size', '20');
					
				$nw_w = round($nw * $w_size/100);
				$nw_ratio = $nw_w / $w_w;
				$nw_h= round($w_h * $nw_ratio);
		
				if($nw_w > $w_w || $nw_h > $w_h ){
					$nw_w = $w_w;
					$nw_h = $w_h;
				}
		
				imagealphablending($NewThumb, true);
				imagesavealpha($NewThumb, true);
		
				$OldWatermark = imagecreatefrompng($watermarkPath);
				//imagealphablending($OldWatermark, true);
				//imagesavealpha($OldWatermark, true);
		
				$NewWatermark=ImageCreateTrueColor($nw_w,$nw_h);
				$bg = imagecolortransparent($NewWatermark, imagecolorallocatealpha($NewWatermark, 0, 0, 0,127));
				imagealphablending($NewWatermark, true);
				imagefill($NewWatermark, 0, 0, $bg);
				imagesavealpha($NewWatermark, true);
		
				ImageCopyResampled($NewWatermark,$OldWatermark,0,0,0,0,$nw_w,$nw_h,$w_w,$w_h);
		
				$im = $NewThumb;
		
				// Set the margins for the stamp and get the height/width of the stamp image
				$margin_v = 10;
				$margin_h = 10;
				$sx = imagesx($NewWatermark);
				$sy = imagesy($NewWatermark);
		
				if ($params->get('watermark_alignment_h', 'l') == 'r'){
					$pos_l = $nw - $nw_w - $margin_v;
				} else if ($params->get('watermark_alignment_h', 'l') == 'm'){
					$pos_l = round($nw/2) - round($nw_w/2);
				} else {//left
					$pos_l = $margin_v;
				}
		
				if($params->get('watermark_alignment_v', 'b') == 't'){
					$pos_t = $margin_h;
				} else if ($params->get('watermark_alignment_v', 'b') == 'm'){
					$pos_t = round($nh/2) - round($nw_h/2);
				} else {//bottom
					$pos_t = $nh - $nw_h - $margin_h;
				}
		
				//$pos_l = $nw - $nw_w - $marge_right;
				//$pos_t = $nh - $nw_h - $marge_bottom;
		
				ImageCopy($NewThumb, $NewWatermark, $pos_l , $pos_t , 0, 0, $nw_w, $nw_h);
		
				//header('Content-Type: image/png');imagepng($NewThumb);die();
		
				ImageDestroy($OldWatermark);
				ImageDestroy($NewWatermark);
			}
		}

		$thumb_path = $newpath;

		if (is_file($thumb_path)) {
			unlink($thumb_path);
		}
		
		$jpg_quality = (int)$params->get('image_jpg_quality', 85);
		$jpg_quality = min(array(100, max(array(1, $jpg_quality))));

		switch($type)
		{
			case 1:
				imageinterlace($NewThumb, 1);
				imagegif($NewThumb, $thumb_path);
				break;
			case 2:
				imageinterlace($NewThumb, 1);
				imagejpeg($NewThumb, $thumb_path, $jpg_quality);
				break;
			case 3:
				imageinterlace($NewThumb, 1);
				imagepng($NewThumb, $thumb_path);
				break;
		}

		imagedestroy($NewThumb);
		imagedestroy($OldImage);

		return true;
	}
	
	public static function getProcessedImage($fullname, $width=0, $height=0, $keep_ratio = true, $path = '', $enlarge = true, $watermark = null) {
		if (!($width > 0 || $height > 0)) {
			return false;
		}
		$suffix = '_'.(int)$width.'x'.(int)$height.'-'.(($keep_ratio) ? 'r' : 'c');
		
		$sub_path = $path != '' ? str_replace('/', DS, $path).DS : '';
		$path = $path != '' ? '/'.$path : '';
		$imgPath	= DJCATIMGFOLDER.DS.$sub_path.$fullname;
		$thPath		= DJCATIMGFOLDER.DS.$sub_path.'custom'.DS.self::addSuffix($fullname, $suffix);
		$thUrl		= DJCATIMGURLPATH.$path.'/custom/'.self::addSuffix($fullname, $suffix);
		
		if (!JFolder::exists( DJCATIMGFOLDER.DS.$sub_path.'custom')) {
			JFolder::create( DJCATIMGFOLDER.DS.$sub_path.'custom', 0755);
		}
		
		if (!JFile::exists($thPath) && JFile::exists($imgPath)) {
			$customFolderExist = false;
			if (!JFolder::exists( DJCATIMGFOLDER.DS.$sub_path.'custom')) {
				$customFolderExist = JFolder::create( DJCATIMGFOLDER.DS.$sub_path.'custom', 0755);
			} else {
				$customFolderExist = true;
			}
			if ($customFolderExist) {
				if (!self::resizeImage($imgPath, $thPath, $width, $height, $keep_ratio, $enlarge, $watermark)) {
					return false;
				}
			}
		}
		if (!JFile::exists($thPath)) {
			return false;
		}
		return $thUrl;
	}
	
	public static function getDefaultImage($itemtype = 'item', $size = null, $options = array()) {
		if ($itemtype != 'category' && $itemtype != 'producer' && $itemtype != 'item' ) {
			return false;
		}
		$sfx = $itemtype == 'item' ? '' : $itemtype.'_';
		$params = JComponentHelper::getParams('com_djcatalog2');
		$imagePath = $params->get($sfx.'image_default', false);
		
		if (!$imagePath) {
			return false;
		}
		
		$width = 0;
		$height = 0;
		$keep_ratio = $params->get($sfx.'resize', $params->get('resize', 0));
		
		if ($size) {
			switch ($size) {
				case 'original': {
					return JUri::root(true).'/'.$imagePath;
					break;
				}
				case 'fullscreen': {
					$width = 1920;
					$height = 1920;
					$keep_ratio = true;
					break;
				}
				case 'large': {
					$width = $params->get($itemtype.'_width', $params->get('width', 300));
					$height = $params->get($itemtype.'_height', $params->get('height', 300));
					break;
				}
				case 'medium': {
					$width = $params->get($itemtype.'_th_width', $params->get('th_width', 120));
					$height = $params->get($itemtype.'_th_height', $params->get('th_height', 120));
					break;
				}
				case 'small': {
					$width = $params->get($itemtype.'_smallth_width', $params->get('smallth_width', 92));
					$height = $params->get($itemtype.'_smallth_height', $params->get('smallth_height', 92));
					break;
				}
				case 'thumb': {
					$width = 75;
					$height = 45;
					$keep_ratio = true;
					break;
				}
			}
		}
		else if (is_array($options)){
			$width = isset($options['width']) ? $options['width'] : 0;
			$height = isset($options['height']) ? $options['height'] : 0;
			$keep_ratio = isset($options['keep_ratio']) ? $options['keep_ratio'] : $keep_ratio;
		}
		
		if (!($width > 0 || $height > 0)) {
			return false;
		}
		
		$suffix = '_'.(int)$width.'x'.(int)$height.'-'.(($keep_ratio) ? 'r' : 'c');
		
		$imgPath	= JPATH_ROOT.DS.$imagePath;
		$imgPathParts = explode('/', $imgPath);
		$fullname = $imgPathParts[count($imgPathParts)-1];
		$thPath		= DJCATIMGFOLDER.DS.'custom'.DS.self::addSuffix($fullname, $suffix);
		$thUrl		= DJCATIMGURLPATH.'/custom/'.self::addSuffix($fullname, $suffix);
		
		if (!JFolder::exists( DJCATIMGFOLDER.DS.'custom')) {
			JFolder::create( DJCATIMGFOLDER.DS.'custom', 0755);
		}
		
		if (!JFile::exists($thPath) && JFile::exists($imgPath)) {
			
			if (!self::resizeImage($imgPath, $thPath, $width, $height, $keep_ratio, false, null)) {
				return false;
			}
		}
		if (!JFile::exists($thPath)) {
			return false;
		}
		return $thUrl;
	}
	
	public static function getRemoteImage($url, $width=0, $height=0, $keep_ratio = true) {
		$filename = basename($url);
		$allowed_extensions = array('jpg', 'jpeg', 'png', 'bmp');
		$ext = self::getExtension($filename);
		$name = self::stripExtension($filename);
	
		if (!in_array(strtolower($ext), $allowed_extensions)) {
			return false;
		}
	
		$hash_name = md5('djc2remoteimage'.$name).'.'.$ext;
	
		if (JFile::exists(DJCATIMGFOLDER.DS.$hash_name) == false) {
			if ($tmpname = self::downloadImage($url)) {
				$config = JFactory::getConfig();
				if (JFile::move($config->get('tmp_path').DS.$tmpname, DJCATIMGFOLDER.DS.$hash_name) == false) {
					return false;
				}
			} else {
				return false;
			}
		}
	
		return self::getProcessedImage($hash_name, $width, $height, $keep_ratio);
	}
	
	/*
	 * Joomla 3
	 * 
	 */
	
	/*
	public static function downloadImage($url, $target = false)
	{
		$config = JFactory::getConfig();
	
		// Capture PHP errors
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);
	
		// Set user agent
		$version = new JVersion;
		ini_set('user_agent', $version->getUserAgent('Installer'));
	
		$http = JHttpFactory::getHttp();
	
		// Load installer plugins, and allow url and headers modification
		$headers = array();
	
		$response = $http->get($url, $headers);
	
		if (302 == $response->code && isset($response->headers['Location']))
		{
			return self::downloadPackage($response->headers['Location']);
		}
		elseif (200 != $response->code)
		{
			if (JDEBUG) {
				JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT', $response->code), JLog::WARNING, 'jerror');
			}
	
			return false;
		}
	
		if (isset($response->headers['Content-Disposition']))
		{
			$contentfilename = explode("\"", $response->headers['Content-Disposition']);
			$target = $contentfilename[1];
		}
	
		// Set the target path if not given
		if (!$target)
		{
			$target = $config->get('tmp_path') . '/' . self::getFilenameFromURL($url);
		}
		else
		{
			$target = $config->get('tmp_path') . '/' . basename($target);
		}
	
		// Write buffer to file
		JFile::write($target, $response->body);
	
		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);
	
		// Bump the max execution time because not using built in php zip libs are slow
		@set_time_limit(ini_get('max_execution_time'));
	
		// Return the name of the downloaded package
		return basename($target);
	}*/
	
	/*
	 * Joomla 2.5 legacy
	 */
	public static function downloadImage($url, $target = false)
	{
		$config = JFactory::getConfig();
	
		// Capture PHP errors
		$php_errormsg = 'Error Unknown';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);
	
		// Set user agent
		$version = new JVersion;
		ini_set('user_agent', $version->getUserAgent('Installer'));
	
		// Open the remote server socket for reading
		$inputHandle = @ fopen($url, "r");
		$error = strstr($php_errormsg, 'failed to open stream:');
		if (!$inputHandle)
		{
			if (JDEBUG) {
				JError::raiseWarning(42, JText::sprintf('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT', $error));
			}
			
			return false;
		}
	
		$meta_data = stream_get_meta_data($inputHandle);
		foreach ($meta_data['wrapper_data'] as $wrapper_data)
		{
			if (substr($wrapper_data, 0, strlen("Content-Disposition")) == "Content-Disposition")
			{
				$contentfilename = explode("\"", $wrapper_data);
				$target = $contentfilename[1];
			}
		}
	
		// Set the target path if not given
		if (!$target)
		{
			$target = $config->get('tmp_path') . '/' . self::getFilenameFromURL($url);
		}
		else
		{
			$target = $config->get('tmp_path') . '/' . basename($target);
		}
	
		// Initialise contents buffer
		$contents = null;
	
		while (!feof($inputHandle))
		{
			$contents .= fread($inputHandle, 4096);
			if ($contents === false)
			{
				if (JDEBUG) {
					JError::raiseWarning(44, JText::sprintf('JLIB_INSTALLER_ERROR_FAILED_READING_NETWORK_RESOURCES', $php_errormsg));
				}
				return false;
			}
		}
	
		// Write buffer to file
		JFile::write($target, $contents);
	
		// Close file pointer resource
		fclose($inputHandle);
	
		// Restore error tracking to what it was before
		ini_set('track_errors', $track_errors);
	
		// bump the max execution time because not using built in php zip libs are slow
		@set_time_limit(ini_get('max_execution_time'));
	
		// Return the name of the downloaded package
		return basename($target);
	}
	
	public static function getFilenameFromURL($url)
	{
		if (is_string($url))
		{
			$parts = explode('/', $url);
	
			return $parts[count($parts) - 1];
		}
	
		return false;
	}

	protected static function stripExtension($filename) {
		return parent::stripExtension($filename);
	}

	protected static function getExtension($filename) {
		return parent::getExtension($filename);
	}

	protected static function addSuffix($filename, $suffix) {
		return parent::addSuffix($filename, $suffix);
	}
	public static function setOrdering($file1, $file2){
		return parent::setOrdering($file1, $file2);
	}
	public static function getDestinationFolder($path, $itemid, $itemtype) {
		return parent::getDestinationFolder($path, $itemid, $itemtype);
	}
	public static function getDestinationPath($itemid, $itemtype){
		return parent::getDestinationPath($itemid, $itemtype);
	}
}
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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class DJMediatoolsModelCategory extends JModelAdmin
{
	public function getTable($type = 'Categories', $prefix = 'DJMediatoolsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		jimport('joomla.form.form');
		
		JForm::addFormPath(JPATH_ADMINISTRATOR.'/components/com_djmediatools/models/forms');
		JForm::addFieldPath(JPATH_ADMINISTRATOR.'/components/com_djmediatools/models/fields');
		
		// Get the form.
		$form = $this->loadForm('com_djmediatools.category', 'category', array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form)) {
			return false;
		}
		
		return $form;
	}
	
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_djmediatools.edit.category.data', array());

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState($this->getName() . '.id') == 0) {
				$app = JFactory::getApplication();
				$data->set('source', 'component');
				$data->set('parent_id', JRequest::getInt('parent_id', $app->getUserState('com_djmediatools.categories.filter.category')));
			}
		}
		
		return $data;
	}
	
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= JApplication::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->title);
		}
		/*
		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$query = 'SELECT MAX(ordering) FROM #__djmt_albums';
				if($table->parent_id) $query.= ' WHERE parent_id='. (int) $table->parent_id;
				$db->setQuery($query);
				$max = $db->loadResult();
				
				$table->ordering = $max+1;
			}
		}
		*/
	}
	
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'parent_id = '.(int) $table->parent_id;

		return $condition;
	}
	
	public function getPlgParams() {
		
		// Initialize variables.
		$config = array();
		
		$path = JPATH_SITE . DS . 'plugins' . DS . 'djmediatools';
		$folders = JFolder::folders($path);
		
		$data = $this->loadFormData();
		
		if (is_array($folders))
		{
			$lang = JFactory::getLanguage();
			foreach ($folders as $folder)
			{				
				$file = JPATH_SITE . DS . 'plugins' . DS . 'djmediatools' . DS . $folder . DS . $folder . '.xml';
				$form = JForm::getInstance('plgParams_'.$folder, $file, array('control' => 'jform'), true, 'config');
				
				$form->bind($data);
				
				$config[] = $form;
			}
		}
		
		return $config;
	}
/*
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		//$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError(JText::_($message));
			}

			return false;
		}

		return $data;
	}
*/
	public function save($data){

		$app = JFactory::getApplication();
		
		// this is not elegant, but we have to do this while joomla remove extra params from source album plugin
		$jform	= $app->input->get('jform', array(), 'array');
		if(isset($jform['params'])) $data['params'] = $jform['params'];
		
		//JFactory::getApplication()->enqueueMessage("<pre>".print_r($data, true)."</pre>");
		if($saved = parent::save($data)) {
			
			// set folder if needed
			if($data['id'] == 0 || empty($data['folder']) || strpos($data['folder'], 'images/djmediatools') !== 0) {
				$album = $this->getTable('Categories');
				$album->load($this->getState($this->getName() . '.id'));
				
				$album->folder = 'images/djmediatools/' . $album->id . '-' . $album->alias;
				$album->store();
				$data['folder'] = $album->folder;
			}
			//djdebug($this);
			$item = $this->getTable('Items');
			$date = JFactory::getDate();
			
			$ids = JRequest::getVar('item_id',array(),'post','array');
			$titles = JRequest::getVar('item_title',array(),'post','array');
			$descs = JRequest::getVar('item_desc',array(),'post','array');
			$images = JRequest::getVar('item_image',array(),'post','array');
			JArrayHelper::toInteger($ids);
			
			// first remove deleted images from the list
			if($data['source'] == 'component') {				
				$query = 'DELETE FROM #__djmt_items WHERE catid='.$this->getState($this->getName() . '.id');
				if(count($ids)) $query.= ' AND id NOT IN ('.implode(',', $ids).')';
				$this->_db->setQuery($query);
				$this->_db->query();
			} else if($data['source'] == 'folder') {
				
				if(empty($data['image'])) { // set the album cover if not specified	
					
					$folder = $data['params']['plg_folder_path'];
					$dir = opendir(JPath::clean(JPATH_ROOT.DS.$folder));
					
					if($dir !== FALSE) {
						while (false !== ($file = readdir($dir))) {
							if (preg_match('/.+\.(jpg|jpeg|gif|png)$/i', $file)) {
								// check with getimagesize() which attempts to return the image mime-type
								if(getimagesize(JPath::clean(JPATH_ROOT.DS.$folder.DS.$file)) !== FALSE) {
									$this->_db->setQuery('UPDATE #__djmt_albums SET image='.$this->_db->Quote($folder.'/'.$file).' WHERE id='.$this->getState($this->getName() . '.id'));
									$this->_db->query();
									break;
								}
							}
						}
						closedir($dir);
					}
				}
			}
			
			if(count($ids)) {
				foreach($ids as $order => $id) {
					
					if($order == 0) continue; // skip album item template
					
					$item->reset();
					if($id) {
						$item->load($id);
						// continue if no changes made
						if($item->title == $titles[$order] && $item->description == $descs[$order] && $item->ordering == $order) continue;
					} else {
						$item->id = 0;
						$item->image = $this->moveUploadedImage($images[$order], $data);
						if(is_null($item->image)) {
							// don't save if move uploaded image faild
							$app->enqueueMessage( JText::_('COM_DJMEDIATOOLS_ERROR_MOVE_UPLOADED_IMAGE'), 'error');
							continue;
						}
						$tmp = explode(';', $images[$order]);
						if(count($tmp) > 2) $item->video = $tmp[2];
					}
					
					$item->catid = $this->getState($this->getName() . '.id');
					$item->title = (empty($titles[$order]) ? JFile::getName($item->image) : $titles[$order]);
					if (empty($item->alias)) {
						$item->alias = JApplication::stringURLSafe($item->title);
					}
					$item->description = $descs[$order];
					if(!$item->id) {
						$item->published = 1;
					}
					// Set the publish date to now
					if($item->published == 1 && intval($item->publish_up) == 0) {
						$item->publish_up = $date->toSql();
					}
					$item->ordering = $order;
					
					if(!$item->store()) {
						$app->enqueueMessage($item->getError(), 'error');
					} elseif(empty($data['image'])) { // set the album cover if not specified
						$data['image'] = $item->image;
						$this->_db->setQuery('UPDATE #__djmt_albums SET image='.$this->_db->Quote($item->image).' WHERE id='.$this->getState($this->getName() . '.id'));
						$this->_db->query();
					}
				}
				
				//$item->reorder();
			}
			
		}
		
		return $saved;
	}
	
	public function delete(&$pks){
		
		$deleted = parent::delete($pks);
		
		if($deleted) {
			
			$query = 'DELETE FROM #__djmt_items WHERE catid IN ('.implode(',', $pks).')';
			$this->_db->setQuery($query);
			$this->_db->query();
			
		}
		
		return $deleted;
	}
	
	public function getItems() {
		
		$model = JModelLegacy::getInstance('Items', 'DJMediatoolsModel', array('ignore_request'=>true));
		
		$model->setState('filter.category', JRequest::getInt('id'));
		$model->setState('list.ordering','a.ordering');
		$model->setState('list.direction','asc');
		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		
		return $model->getItems();
	}
	
	private function moveUploadedImage($paths = null, $data = null) {
		
		$paths = explode(';', $paths);
		$lang = JFactory::getLanguage();
		$date = JFactory::getDate();
		
		if(count($paths) == 2) {
			
			$folder = $data['folder'] == 'images/djmediatools' ? $data['folder'] . '/' . $data['id'] . '-' . $data['alias'] : $data['folder'];
			
			$tmpPath = JPATH_ROOT . '/media/djmediatools/upload/' . $paths[0];
			$path = JPATH_ROOT . DS . str_replace('/', DS, $folder);
			JFolder::create($path);
			
			$filename = str_replace(' ', '_', $paths[1]);
			$filename = $lang->transliterate($filename);
			//$filename = strtolower($filename);
			$filename = JFile::makeSafe($filename);
			
			$name = JFile::stripExt(JFile::getName($filename));
			$ext = JFile::getExt($filename);
			
			if(empty($name)) {
				$name = $date->format('YmdHis');
				$filename = $name.'.'.$ext;
			}
			
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
				return $folder . '/' .$filename;
			} else {
				return null;
			}
			
		} else {
			
			return $paths[0];
		}
	}
}

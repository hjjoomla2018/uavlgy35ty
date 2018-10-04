<?php
use Joomla\Registry\Registry;

/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

//jimport('joomla.application.component.modeladmin');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'modeladmin.php');

class Djcatalog2ModelField extends DJCJModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		//$config['event_after_save'] = 'onFieldAfterSave';
		//$config['event_after_delete'] = 'onFieldAfterDelete';
		parent::__construct($config);
	}

	public function getTable($type = 'Fields', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.field', 'field', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.field.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function _prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
		
		if (empty($table->alias)) {
			$table->alias = JFilterOutput::stringURLSafe($table->name);
			$table->alias = trim(str_replace('-','_',$table->alias));
			if(trim(str_replace('_','',$table->alias)) == '') {
				$table->alias = JFactory::getDate()->format('Y_m_d_H_i_s');
			}
		}

		if (empty($table->id)) {
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__djc2_items_extra_fields');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
	}

	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'group_id = '.(int) $table->group_id;
		return $condition;
	}

	public function delete(&$cid) {
		if (count( $cid ))
		{
			$db = JFactory::getDbo();
			$cids = implode(',', $cid);
			try {
				$db->setQuery('DELETE FROM #__djc2_items_extra_fields_values_text WHERE field_id IN ('.$cids.') ');
				$db->execute();
			} 
			catch (Exception $e) {
				$this->setError($e->getMessage());
				return false;
			}
			
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djc2_items_extra_fields_options')->where('field_id IN ('.$cids.')');
			$db->setQuery($query);
			$deleteOptions = $db->loadObjectList();

			foreach($deleteOptions as $option) {
				if (empty($option->params)) {
					continue;
				}
				
				$params = new Registry($option->params);
				$fileName = trim($params->get('file_name', ''));
				
				if ($fileName) {
					$this->deleteColorFile($fileName);
				}
			}
			
			try {
				$db->setQuery('DELETE FROM #__djc2_items_extra_fields_values_int WHERE field_id IN ('.$cids.') ');
				$db->execute();
			} 
			catch (Exception $e) {
				$this->setError($e->getMessage());
				return false;
			}
			try {
				$db->setQuery('DELETE FROM #__djc2_items_extra_fields_values_date WHERE field_id IN ('.$cids.') ');
				$db->execute();
			}
			catch (Exception $e) {
				$this->setError($e->getMessage());
				return false;
			}
			
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djc2_items_combinations_fields')->where('field_id IN ('.$cids.')');
			$db->setQuery($query);
			$deleteCombinations = $db->loadObjectList();
			$comboIds = array();
			foreach($deleteCombinations as $combination) {
				$comboIds[] = $combination->combination_id;
			}
			if (count($comboIds) > 0) {
				$db->setQuery('DELETE FROM #__djc2_items_combinations_fields WHERE field_id IN ('.$cids.') ');
				if ($db->execute()) {
					$db->setQuery('DELETE FROM #__djc2_items_combinations WHERE id IN ('.implode(',', $comboIds).') ');
					$db->execute();
				}
			}
		}
		return parent::delete($cid);
	}
	
	public function saveOptions($values, &$table, $newField) {
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		
		$files = $app->input->files->get('fieldtype', array());
		
		if (!empty($values) && array_key_exists('id', $values) && array_key_exists('option', $values) && array_key_exists('position', $values)) {
			if ($table->type == 'select' || $table->type == 'checkbox' || $table->type == 'radio' || $table->type == 'color' || $table->type == 'multicolor'  || $table->type == 'multiselect') {
				
				$pks = array();
                $max = 1;
				
				foreach ($values['id'] as $key=>$id) {
					if ($values['option'][$key] != '') {
						$fo_table = JTable::getInstance('FieldOptions', 'Djcatalog2Table', array());
						$isNew = true;
						// Load the row if saving an existing record.
						if ($id > 0 && $newField === false) {
							$fo_table->load($id);
							$isNew = false;
							$fo_table->params = new Registry($fo_table->params);
						} else {
							$fo_table->params = new Registry();
						}
						
						$data = array();
						$data['id'] = $isNew ? null:$id;
						//$data['value'] = htmlspecialchars($values['option'][$key]);
						$data['value'] = ($values['option'][$key]);
						$data['ordering'] = ($values['position'][$key] > 0) ? $values['position'][$key] : 0;
						$data['field_id'] = $table->id;
						
						$params = clone $fo_table->params;
						
						if ($table->type == 'color' || $table->type == 'multicolor') {
							if (isset($values['hexcode'][$key])) {
								$params->set('hexcode', $values['hexcode'][$key]);
							}
							
							$fileName = trim($params->get('file_name', ''));
							$deleteOld = (bool)($values['file_name'][$key] == '' && $fileName != '');
							
							if ($deleteOld) {
								$this->deleteColorFile($fileName);
								$fileName = '';
							}
							
							if (isset($files['file']) && isset($files['file'][$key])) {
								$retVal = $this->saveColorFile($data, $key, $files);
								if (true !== $retVal) {
									$fileName = ($retVal) ? $retVal : '';
								}
							}
							$params->set('file_name', $fileName);
						}
							
						$data['params'] = $params->toString('JSON');
						
						// Bind the data.
						if (!$fo_table->bind($data)) {
							$this->setError($fo_table->getError());
							return false;
						}
						if (empty($fo_table->ordering) || !$fo_table->ordering) {
							$fo_table->ordering = $max;
						}
                        $max = $fo_table->ordering + 1;
						// Check the data.
						if (!$fo_table->check()) {
							$this->setError($fo_table->getError());
							return false;
						}
			
						// Store the data.
						if (!$fo_table->store()) {
							$this->setError($fo_table->getError());
							return false;
						}
						
						$pks[] = $fo_table->id;
					}
				}
				
				if (!empty($pks)) {
					$query = $db->getQuery(true);
					$query->select('*')->from('#__djc2_items_extra_fields_options')->where('field_id='.(int)$table->id)->where('id NOT IN ('.implode(',', $pks).')');
					$db->setQuery($query);
					$deleteOptions = $db->loadObjectList();
					
					$optionIds = array();
					foreach($deleteOptions as $option) {
						$optionIds[] = $option->id;
						
						if (empty($option->params)) {
							continue;
						}
						
						$params = new Registry($option->params);
						$fileName = trim($params->get('file_name', ''));
						
						if ($fileName) {
							$this->deleteColorFile($fileName);
						}
					}
					
					if (count($optionIds) > 0) {
						$query = $db->getQuery(true);
						$query->delete('#__djc2_items_extra_fields_options')->where('id IN ('.implode(',', $optionIds).')');
						$db->setQuery($query);
						$db->execute();
						
						$db->setQuery('SELECT * FROM #__djc2_items_combinations_fields WHERE field_id='.(int)$table->id.' AND value IN ('.implode(',', $optionIds).')');
						
						$deleteCombinations = $db->loadObjectList();
						
						$comboIds = array();
						foreach($deleteCombinations as $combination) {
							$comboIds[] = $combination->combination_id;
						}
						if (count($comboIds) > 0) {
							$db->setQuery('DELETE FROM #__djc2_items_combinations_fields WHERE field_id='.$table->id.' AND value IN ('.implode(',', $optionIds).')');
							if ($db->execute()) {
								$db->setQuery('DELETE FROM #__djc2_items_combinations WHERE id IN ('.implode(',', $comboIds).') ');
								$db->execute();
							}
						}
					}
				}
			}
		}
		return true;
	}
	public function deleteOptions(&$table) {
		$db = JFactory::getDbo();
		$db->setQuery('DELETE FROM #__djc2_items_extra_fields_options WHERE field_id='.(int)$table->id);
		if (!$db->query()){
			$this->setError($db->getError());
		}
		return true;
		
		
	}
	public function saveColorFile($data, $key, $files) {
		$postFile = $files['file'][$key];
		$path = DJCATIMGFOLDER.'/colors';
		
		$fileName = '';
		
		if ($postFile['name'] && $postFile['tmp_name']) {
			if ($postFile['error'] === 0 && $postFile['size'] > 0) {
				$lang = JFactory::getLanguage();
				
				$fileExt = JFile::getExt($postFile['name']);
				$fileBase = JFile::stripExt($postFile['name']);
				$fileName = JFile::makeSafe($lang->transliterate(JString::strtolower($data['field_id'].'-'.$data['value'] . '-'.$fileBase).'.'.$fileExt));
				$fileName = str_replace(' ', '-', $fileName);
				if (!JFolder::exists(JPath::clean($path))) {
					JFolder::create(JPath::clean($path));
				}
				
				if (!JFile::upload($postFile['tmp_name'], JPath::clean($path.'/'.$fileName))) {
					return false;
				}
				
				return $fileName;
			} else {
				return !($postFile['size'] > 0);
			}
		}
		
		return true;
	}
	
	public function deleteColorFile($fileName) {
		$path = DJCATIMGFOLDER.'/colors';
		
		if ($fileName) {
			$filePath = $path.'/'.$fileName;
			
			if (JFile::exists(JPath::clean($filePath))) {
				JFile::delete(JPath::clean($filePath));
			}
		}
		
		return true;
	}
}
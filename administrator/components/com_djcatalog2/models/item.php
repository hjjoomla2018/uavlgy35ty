<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.application.component.helper');

class Djcatalog2ModelItem extends JModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';
	
	protected $_cart_attributes = array();
	protected $_customisations = array();
	
	public function __construct($config = array()) {
		//$config['event_after_save'] = 'onItemAfterSave';
		//$config['event_after_delete'] = 'onItemAfterDelete';
		parent::__construct($config);
	}

	public function getTable($type = 'Items', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.item', 'item', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			$db = JFactory::getDbo();
			
			
			if ((!isset($item->categories) || !is_array($item->categories)) && isset($item->id)){
				$db->setQuery('SELECT category_id FROM #__djc2_items_categories WHERE item_id=\''.$item->id.'\'');
				$item->categories = $db->loadColumn();
			}
			
			if (!isset($item->location) || !is_array($item->location)) {
				$location = array('address' => null, 'city' => null, 'postcode' => null, 'country' => null, 'state' => null, 'latitude' => null, 'longitude' => null, 'phone' => null, 'fax' => null, 'mobile' => null, 'website'=> null, 'email' => null );
				foreach($location as $k=>$v) {
					if (isset($item->$k)) {
						$location[$k] = $item->$k;
					}
				}
				$item->location = $location;
			}
			
			if (!is_array($item->group_id)) {
				$query = $db->getQuery(true);
				
				/*$query->select ('distinct f.group_id');
				$query->from('#__djc2_items_extra_fields AS f');
				$query->join('LEFT','#__djc2_items_extra_fields_values_text AS vt ON f.id=vt.field_id AND vt.item_id='.(int)$item->id);
				$query->join('LEFT','#__djc2_items_extra_fields_values_int AS vi ON f.id=vi.field_id AND vi.item_id='.(int)$item->id);
				$query->join('LEFT','#__djc2_items_extra_fields_values_date AS vd ON f.id=vd.field_id AND vd.item_id='.(int)$item->id);
				$query->where('vt.value IS NOT NULL OR vi.value IS NOT NULL OR vd.value IS NOT NULL');
				*/
				$query->select('distinct group_id');
				$query->from('#__djc2_items_groups');
				$query->where('item_id='.(int)$item->id);
				
				$db->setQuery($query);
				$item->group_id = $db->loadColumn();
			}
			
			if (!isset($item->combinations) && $item->id) {
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from('#__djc2_items_combinations');
				$query->where('item_id = '.$item->id);
				$db->setQuery($query);
				
				$item->combinations = $db->loadObjectList('id');
				foreach ($item->combinations as &$combination) {
					$query = $db->getQuery(true);
					$query->select('cf.*, f.group_id');
					$query->from('#__djc2_items_combinations_fields AS cf');
					$query->join('left', '#__djc2_items_extra_fields AS f ON f.id=cf.field_id');
					$query->where('cf.combination_id=' . $combination->id);
					$db->setQuery($query);
					$combination->fields = $db->loadObjectList();
				}
				unset($combination);
			} else if (!$item->id) {
				$item->combinations = null;
			}
			
			if (!isset($item->price_tiers)) {
				$query = $db->getQuery(true);
				$query->select('*')->from('#__djc2_items_price_tiers')->where('item_id='.(int)$item->id)->order('quantity ASC');
				$db->setQuery($query);
				$item->price_tiers = $db->loadAssocList();
			}
			
			if (!isset($item->customisations) && $item->id) {
				$query = $db->getQuery(true);
				$query->select('ic.*, c.name');
				$query->from('#__djc2_items_customisations AS ic');
				$query->join('inner', '#__djc2_customisations AS c ON c.id = ic.customisation_id');
				$query->where('item_id = '.$item->id);
				$query->order('c.ordering');
				$db->setQuery($query);
				
				$item->customisations = $db->loadObjectList();
			} else if (!$item->id) {
				$item->customisations = null;
			}
			
			if ((!isset($item->labels) || !is_array($item->labels)) && isset($item->id)){
				$db->setQuery('SELECT label_id FROM #__djc2_labels_items WHERE item_id=\''.$item->id.'\'');
				$item->labels = $db->loadColumn();
			}
			
			return $item;
		} else {
			return false;
		}
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.item.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
		$table->alias		= JApplication::stringURLSafe($table->alias);
		
		// TODO - just temporary
		$table->group_id = 0;
		
		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->name);
		}
		if (empty($table->cat_id)) {
			$table->cat_id = 0;
		}

		if (empty($table->id)) {
			if (empty($table->ordering)) {
				$db->setQuery('SELECT MAX(ordering) FROM #__djc2_items WHERE cat_id = '.$table->cat_id);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		
		if ($app->input->getCmd('task') != 'import' && ($table->latitude == 0 || $table->longitude == 0)) {
			require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djcatalog2/lib/geocode.php');
			
			$address = array();
			
			if (!empty($table->address)) {
				$address[] = $table->address;
			}
			if (!empty($table->city)) {
				$address[] = $table->city;
			}
			if (!empty($table->postcode)) {
				$address[] = $table->postcode;
			}
			if (!empty($table->country)) {
				$db->setQuery('select country_name from #__djc2_countries where id='.(int)$table->country);
				$country = $db->loadResult();
				if ($country) {
					$address[] = $country;
				}
			}
			
			$address_str = implode(',', $address);
			if ($address_str) {
				if ($coords = DJCatalog2Geocode::getLocation($address_str)) {
					$table->latitude = (!empty($coords['lat'])) ? $coords['lat'] : null;
					$table->longitude = (!empty($coords['lng'])) ? $coords['lng'] : null;
				}
			}
		}
	}

	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'cat_id = '.(int) $table->cat_id;
		$condition[] = 'parent_id = '.(int) $table->parent_id;
		return $condition;
	}
	
	public function validateAttributes($data, &$table) {
		$db = JFactory::getDbo();
		
		//$db->setQuery('select * from #__djc2_items_extra_fields where required=1 AND (group_id=0 OR group_id='.(int)$table->group_id.')');
		
		$group_ids = array();
		if (!empty($table->group_id) && is_array($table->group_id)) {
			$group_ids = $table->group_id;
			JArrayHelper::toInteger($group_ids);
		}
		$group_ids[] = 0;
		$group_ids = array_unique($group_ids);
		
		$db->setQuery('select * from #__djc2_items_extra_fields where required=1 AND group_id IN ('.implode(',', $group_ids).')');
		
		$required_fields = $db->loadObjectList();
		
		if (count($required_fields) == 0) {
			return true;
		}
		
		$all_valid = true;
		
		foreach($required_fields as $field) {
			$field_id = $field->id;
			$valid = false;
			if (isset($data[$field_id])) {
				if (is_array($data[$field_id])) {
					foreach($data[$field_id] as $option) {
						if (!empty($option)) {
							$valid = true;
							break;
						}
					}
				} else {
					if (!empty($data[$field_id])) {
						$valid = true;
					}
				}
			}
			if (!$valid) {
				$all_valid = false;
				$message = JText::_($field->name);
				$message = JText::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $message);
				$this->setError($message);
			}
		}
		
		return $all_valid;
		
	}
	
	public function getFields() {
		$item = $this->getItem();
		
		$itemId = $item->id;
		
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('f.*, g.name as group_name');
		$query->from('#__djc2_items_extra_fields AS f');
		$query->select('CASE '
				.'WHEN (f.type=\'text\' OR f.type=\'textarea\' OR f.type=\'html\') '
				.'THEN vt.value '
				.'WHEN (f.type=\'calendar\') '
				.'THEN vd.value '
				.'WHEN (f.type=\'checkbox\' OR f.type=\'select\' OR f.type=\'multiselect\' OR f.type=\'radio\') '
				.'THEN GROUP_CONCAT(vi.value SEPARATOR \'|\')'
				.'ELSE "" END AS field_value');
		$query->join('LEFT','#__djc2_items_extra_fields_values_text AS vt ON f.id=vt.field_id AND vt.item_id='.(int)$itemId);
		$query->join('LEFT','#__djc2_items_extra_fields_values_int AS vi ON f.id=vi.field_id AND vi.item_id='.(int)$itemId);
		$query->join('LEFT','#__djc2_items_extra_fields_values_date AS vd ON f.id=vd.field_id AND vd.item_id='.(int)$itemId);
		$query->join('LEFT', '#__djc2_items_extra_fields_groups as g ON g.id = f.group_id');
		
		//$query->where('f.group_id='.(int)$this->groupId.' OR f.group_id=0');
		$query->group('f.id');
		$query->order('f.group_id asc, f.ordering asc');
		//echo str_replace('#_', 'jos', (string)$query);die();
		$db->setQuery($query);
		
		$fields = ($db->loadObjectList('id'));

		$groupped_fields = array();
		
		if (count($fields)) {
			$fieldIds = array_keys($fields);
			$db->setQuery('SELECT * FROM #__djc2_items_extra_fields_options WHERE field_id IN ('.implode(',', $fieldIds).') ORDER BY field_id ASC, ordering ASC');
			$optionList = $db->loadObjectList();
		
			foreach($fields as $field_id => $field) {
				foreach ($optionList as $optionRow) {
					if ($optionRow->field_id == $field_id) {
						if (empty($field->optionlist)) {
							$fields[$field_id]->optionlist = array();
						}
						$fields[$field_id]->optionlist[] = $optionRow;
					}
				}
				
				if (array_key_exists($field->group_id, $groupped_fields) == false) {
					$groupped_fields[$field->group_id] 			= new stdClass();
					$groupped_fields[$field->group_id]->id 		= $field->group_id;
					$groupped_fields[$field->group_id]->name 	= ($field->group_id) > 0 ? $field->group_name : JText::_('COM_DJCATALOG2_FIELD_GROUP_COMMON');
					$groupped_fields[$field->group_id]->fields 	= array();
				}
				$groupped_fields[$field->group_id]->fields[$field_id] = $fields[$field_id];
			}
		}
		
		return $groupped_fields;
	}
	
	function getCartAttributes() {
		if (empty($this->_cart_attributes)) {
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query->select('f.*, g.name as group_name, g.label as group_label, g.id as fgroup_id');
			$query->from('#__djc2_items_extra_fields as f');
			$query->join('LEFT', '#__djc2_items_extra_fields_groups as g ON g.id=f.group_id');
			$query->where('f.cart_variant=1');
			$query->order('IFNULL(g.ordering,0) asc , g.ordering asc, f.ordering asc');
			$db->setQuery($query);
			
			$this->_cart_attributes = $db->loadObjectList('id');
			
			if (count($this->_cart_attributes)) {
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from('#__djc2_items_extra_fields_options');
				$query->where('field_id in ('.implode(',',array_keys($this->_cart_attributes)).')');
				$query->order('field_id asc, ordering asc');
				
				$db->setQuery($query);
				$optionslist = $db->loadObjectList();
				
				foreach ($this->_cart_attributes as $field_id => $field) {
					$field_options = array();
					$field_optionValues = array();
					$field_optionParams = array();
					
					foreach($optionslist as $k => $option) {
						if ($option->field_id == $field_id) {
							$field_options[] = $option->id;
							$field_optionValues[$option->id] = $option->value;
							
							$optionParam = $option->params;
							if (!empty($optionParam)) {
								$optionParam = new Registry($optionParam);
							}
							$field_optionParams[$option->id] = $optionParam;
							
						}
					}
					
					$this->_cart_attributes[$field_id]->options = $field_options;//implode('|', $field_options);
					$this->_cart_attributes[$field_id]->optionValues = $field_optionValues;//implode('|', $field_optionValues);
					$this->_cart_attributes[$field_id]->optionParams = $field_optionParams;
				}
			}
			
		}
		
		return $this->_cart_attributes;
	}
	
	function getCustomisations() {
		if (empty($this->_customisations)) {
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__djc2_customisations');
			$query->where('type='.$db->quote('i'));
			$query->order('ordering asc');
			
			$db->setQuery($query);
			
			$items = $db->loadObjectList();
			
			/*foreach($items as &$item) {
				$item->input_params = new Registry($item->input_params);
				$item->params = new Registry($item->params);
			}
			unset($item);*/
			
			$this->_customisations = $items;
		}
		
		return $this->_customisations;
	}

	public function saveAttributes($data, &$table) {
		$db = JFactory::getDbo();
		
		//if (!empty($data) ) {
			$non_empty_fields = array(0);

			if (!empty($data)) {
				foreach ($data as $k=>$v) {
					if (!empty($v) || trim($v) != '') {
						$non_empty_fields[] = (int)$k;
					}
				}
			}
			
			$app = JFactory::getApplication();
			$task = $app->input->getCmd('task');
			
			$non_empty_fields = array_unique($non_empty_fields);
			$non_empty_fields_ids = implode(',', $non_empty_fields);
			
			if ($task != 'import') {
				$query = $db->getQuery(true);
				$query->delete();
				$query->from('#__djc2_items_extra_fields_values_text');
				//$query->where('item_id ='.$table->id.' and field_id not in (select id from #__djc2_items_extra_fields where group_id = '.$table->group_id.' or group_id = 0)');
				
				$query->where('item_id ='.$table->id.' and field_id not in ('.$non_empty_fields_ids.')');
				
				$db->setQuery($query);
				$db->query();

				$query = $db->getQuery(true);
				$query->delete();
				$query->from('#__djc2_items_extra_fields_values_int');
				//$query->where('item_id ='.$table->id.' and field_id not in (select id from #__djc2_items_extra_fields where group_id = '.$table->group_id.' or group_id = 0)');
				
				$query->where('item_id ='.$table->id.' and field_id not in ('.$non_empty_fields_ids.')');
				
				$db->setQuery($query);
				$db->query();
			
			
				$query = $db->getQuery(true);
				$query->delete();
				$query->from('#__djc2_items_extra_fields_values_date');
				//$query->where('item_id ='.$table->id.' and field_id not in (select id from #__djc2_items_extra_fields where group_id = '.$table->group_id.' or group_id = 0)');
				
				$query->where('item_id ='.$table->id.' and field_id not in ('.$non_empty_fields_ids.')');
				
				$db->setQuery($query);
				$db->query();
			}
			
			if (empty($data)) {
				return true;
			}
			
			$query = $db->getQuery(true);
			$query->select('ef.*');
			$query->from('#__djc2_items_extra_fields as ef');
			//$query->where('ef.group_id='.$table->group_id.' OR ef.group_id=0');
			$query->where('ef.id in ('.$non_empty_fields_ids.')');
			$db->setQuery($query);

			$attribs = $db->loadObjectList();
			$itemId = $table->id;
			$rows = array();

			$text_types = array('text','textarea','html');
			$int_types = array('select', 'multiselect', 'checkbox','radio', 'color', 'multicolor');
			$date_types = array('calendar');
			/*
			foreach ($attribs as $k=>$v) {
				$fieldId = $v->id;
				$className =  DJCatalog2CustomField.ucfirst($v->type);
				if (class_exists($className) == false ){
					continue;
				}
				
				$field = new $className($fieldId, $itemId, $v->name, $v->required);
				
				if (array_key_exists($fieldId, $data) && !empty($data[$fieldId])) {
					$field->setValue($data[$fieldId]);
					$field->save();	
				} else {
					$field->delete();
				}
			}
			
			return true;*/
			
			foreach ($attribs as $k=>$v) {
				$fv_table = null;
				$type_table_name = null;
				$table_type = null;
				if (in_array($v->type, $text_types)) {
					$fv_table = JTable::getInstance('FieldValuesText', 'Djcatalog2Table', array());
					$type_table_name = '#__djc2_items_extra_fields_values_text';
					$table_type = 'text';
				} else if (in_array($v->type, $int_types)) {
					$fv_table = JTable::getInstance('FieldValuesInt', 'Djcatalog2Table', array());
					$type_table_name = '#__djc2_items_extra_fields_values_int';
					$table_type = 'int';
				} else if (in_array($v->type, $date_types)) {
					$fv_table = JTable::getInstance('FieldValuesDate', 'Djcatalog2Table', array());
					$type_table_name = '#__djc2_items_extra_fields_values_date';
					$table_type = 'date';
				} else {
					continue;
				}
				$fieldId = $v->id;
				if (array_key_exists($fieldId, $data) && isset($data[$fieldId])) {
					// add/alter data
					$value = null;
					$id = null;
						
					if (is_array($data[$fieldId])) {
						$db->setQuery('
									SELECT id 
									FROM '.$type_table_name.' 
									WHERE 
										item_id='.(int)$itemId.' 
										AND field_id='.$fieldId. ' order by id '
						);
						$values = $db->loadColumn();
						$count = (count($values) > count($data[$fieldId])) ? count($values) : count($data[$fieldId]);
						for ($i = 0; $i < $count; $i++) {
							if (isset($data[$fieldId][$i])) {
								$id = null;
								if (isset($values[$i])) {
									$id = $values[$i];
								}
								
								$rows[] = array(
											'id'=>$id, 
											'item_id'=>$itemId, 
											'field_id'=>$fieldId, 
											'value' => $data[$fieldId][$i],
											'type' => $table_type
								);
							} else {
								$db->setQuery('
								DELETE 
								FROM '.$type_table_name.' 
								WHERE id='.(int)$values[$i] 
								);
								$db->query();
							}
						}

					} else {
						if ($v->type == 'html') {
							$data[$fieldId] = JComponentHelper::filterText($data[$fieldId]);
							$data[$fieldId] = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/','&amp;',$data[$fieldId]);
						}
						if ($fv_table->load(array('item_id'=>$itemId,'field_id'=>$fieldId))) {
							$id = $fv_table->id;
						}
						$rows[] = array(
										'id'=>$id, 
										'item_id'=>$itemId, 
										'field_id'=>$fieldId, 
										'value' => $data[$fieldId],
										'type' => $table_type
						);
					}

				} else {
					// remove data
					$db->setQuery('
								DELETE 
								FROM '.$type_table_name.' 
								WHERE 
									field_id='.(int)$fieldId.' 
									AND item_id='.(int)$itemId
					);
					$db->query();
				}
			}

			foreach ($rows as $key=>$row) {
				$fv_table = null;
				if (isset($row['type'])) {
					if ($row['type'] == 'text' || $row['type'] == 'int' || $row['type'] == 'date') {
						$fv_table = JTable::getInstance('FieldValues'.ucfirst($row['type']), 'Djcatalog2Table', array());
						unset($row['type']);
					} else{
						continue;
					}
				} else {
					continue;
				}
				
				$isNew = true;
				// Load the row if saving an existing record.
				if ($row['id'] > 0) {
					$fv_table->load($row['id'], true);
					$isNew = false;
				}

				// Bind the data.
				if (!$fv_table->bind($row)) {
					$this->setError($fv_table->getError());
					return false;
				}
				// Check the data.
				if (!$fv_table->check()) {
					$this->setError($fv_table->getError());
					return false;
				}

				// Store the data.
				if (!$fv_table->store()) {
					$this->setError($fv_table->getError());
					return false;
				}

			}
		//}
		return true;
	}
	
	public function saveCombinations($data, &$table, $isNew) {
		$item 	= $this->getItem($table->id);
		$app = JFactory::getApplication();
		$task = $app->input->getCmd('task');
		
		$ids 		= (isset($data['id'])) ? (array)$data['id'] : array();
		$sku 		= (isset($data['sku'])) ? (array)$data['sku'] : array();
		$price 		= (isset($data['price'])) ? (array)$data['price'] : array();
		$stock 		= (isset($data['stock'])) ? (array)$data['stock'] : array();
		$attribute 	= (isset($data['attribute'])) ? (array)$data['attribute'] : array();
		
		$db = JFactory::getDbo();
		
		if (count($item->combinations)) {
			$toDelete = array();
			foreach($item->combinations as $key => $combination) {
				if (!in_array($combination->id, $ids)) {
					$toDelete[] = $combination->id;
				} /*else {
					$tmp = array();
					foreach($combination->fields as $field) {
						$tmp[] = array('field_id'=>(int)$field->field_id, 'value'=>(int)$field->value);
					}
					$existing[] = $tmp;
				}*/
			}
			
			if (count($toDelete)) {
				$query = $db->getQuery(true);
				$query->delete('#__djc2_items_combinations')->where('id IN ('.implode(',', $toDelete).')');
				$db->setQuery($query);
				$db->execute();
				
				$query = $db->getQuery(true);
				$query->delete('#__djc2_items_combinations_fields')->where('combination_id IN ('.implode(',', $toDelete).')');
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		$existing = array ();
		
		if (count($ids)) {
			foreach($ids as $key => $combination_id) {
				$newCombo = (int)$combination_id ? false : true;
				if ($task == 'save2copy') {
					$newCombo = true;
				}
				$combination = new stdClass();
				$combination->id = $newCombo ? 0 : (int)$combination_id;
				$combination->item_id = $table->id;
				$combination->sku = isset($sku[$key]) ? $sku[$key] : '';
				$combination->price = isset($price[$key]) ? floatval($price[$key]) : 0.0;
				$combination->stock = isset($stock[$key]) ? (int)$stock[$key] : 0;
				
				$attributes = array();
				$verify = array();
				foreach($attribute as $field_id => $combination_values) {
					foreach($combination_values as $key_idx => $field_value) {
						if ($key_idx != $key || empty($field_value)) {
							continue;
						}
						$field = new stdClass();
						$field->field_id = (int)$field_id;
						$field->value = (int)$field_value;
						
						$verify[] = array('field_id'=>$field->field_id, 'value'=>$field->value);
						
						$attributes[] = $field;
					}
				}
				// we should not create two combinations made from the exactly same set of attrbutes
				$skip = false;
				if (count($existing) < 1) {
					$existing[] = $verify;
				} else {
					foreach($existing as $already) {
						if (serialize($already) == serialize($verify)) {
							$skip = true;
							if (!$newCombo) {
								$query = $db->getQuery(true);
								$query->delete('#__djc2_items_combinations')->where('id='.$combination->id);
								$db->setQuery($query);
								$db->execute();
								
								$query = $db->getQuery(true);
								$query->delete('#__djc2_items_combinations_fields')->where('combination_id='.$combination->id);
								$db->setQuery($query);
								$db->execute();
							}
						}
					}
				}
				if ($skip) {
					continue;
				}
				$existing[] = $verify;
				
				$success = false;
				if ($newCombo) {
					$success = $db->insertObject('#__djc2_items_combinations', $combination, 'id');
				} else {
					$success = $db->updateObject('#__djc2_items_combinations', $combination, 'id', false);
				}
				
				if ($success) {
					$query = $db->getQuery(true);
					$query->delete('#__djc2_items_combinations_fields')->where('combination_id='.$combination->id);
					$db->setQuery($query);
					$db->execute();
					
					if (count($attributes) > 0) {
						$query = $db->getQuery(true);
						$query->insert('#__djc2_items_combinations_fields');
						$query->columns(array('combination_id,field_id,value'));
						foreach($attributes as $field) {
							$query->values($combination->id.','.$field->field_id.','.$field->value);
						}
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}
		
		return true;
	}
	
	public function saveCustomisations($data, &$table, $isNew) {
		$item 	= $this->getItem($table->id);
		$app = JFactory::getApplication();
		$task = $app->input->getCmd('task');
		
		$ids 		= (isset($data['customisation_id'])) ? (array)$data['customisation_id'] : array();
		$price 		= (isset($data['price'])) ? (array)$data['price'] : array();
		$min_quantity 		= (isset($data['min_quantity'])) ? (array)$data['min_quantity'] : array();
		$max_quantity 		= (isset($data['max_quantity'])) ? (array)$data['max_quantity'] : array();
		
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->delete('#__djc2_items_customisations');
		$query->where('item_id='.$table->id);
		$db->setQuery($query);
		$db->execute();
		
		$existing = array ();
		
		if (count($ids)) {
			foreach($ids as $key => $customisation_id) {
				
				if (empty($customisation_id)) {
					continue;
				}

				$customisation = new stdClass();
				$customisation->customisation_id = (int)$customisation_id;
				$customisation->item_id = $table->id;
				$customisation->price = isset($price[$key]) ? floatval($price[$key]) : 0.0;
				$customisation->min_quantity = isset($min_quantity[$key]) ? (int)$min_quantity[$key] : 0;
				$customisation->max_quantity = isset($max_quantity[$key]) ? (int)$max_quantity[$key] : 0;
				
				$success = $db->insertObject('#__djc2_items_customisations', $customisation);
			}
		}
		
		return true;
	}
	
	public function changeFeaturedState($pks, $value) {
		if (empty($pks)) {
			return false;
		}
		$ids = implode(',',$pks);
		$db = JFactory::getDbo();
		$db->setQuery('update #__djc2_items set featured='.(int)$value.' where id in ('.$ids.')');
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		return true;
	}
	
	public function changeAvailableState($pks, $value) {
		if (empty($pks)) {
			return false;
		}
		$ids = implode(',',$pks);
		$db = JFactory::getDbo();
		$db->setQuery('update #__djc2_items set available='.(int)$value.' where id in ('.$ids.')');
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		return true;
	}
	
	public function geocode($pks) {
		
		if (empty($pks)) {
			return false;
		}
		$ids = implode(',',$pks);
		$db = JFactory::getDbo();
		
		$app = JFactory::getApplication();
		
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djcatalog2/lib/geocode.php');
		
		$db->setQuery('select * from #__djc2_items where id IN ('.$ids.')');
		$items = $db->loadObjectList();
		
		foreach ($items as $item) {
			$address = array();
				
			if (!empty($item->address)) {
				$address[] = $item->address;
			}
			if (!empty($item->city)) {
				$address[] = $item->city;
			}
			if (!empty($item->postcode)) {
				$address[] = $item->postcode;
			}
			if (!empty($item->country)) {
				$db->setQuery('select country_name from #__djc2_countries where id='.(int)$item->country);
				$country = $db->loadResult();
				if ($country) {
					$address[] = $country;
				}
			}
			
			$address_str = implode(',', $address);
			if ($address_str) {
				if ($coords = DJCatalog2Geocode::getLocation($address_str)) {
					
					// bypassing Google Maps limits
					usleep(150000);
					
					$latitude = (!empty($coords['lat'])) ? $coords['lat'] : null;
					$longitude = (!empty($coords['lng'])) ? $coords['lng'] : null;
			
					$db->setQuery('UPDATE #__djc2_items SET latitude = '.$latitude.', longitude = '.$longitude.' WHERE id = '.(int)$item->id);
					
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
					$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_GEOLOCATION_OK', $item->id), 'message');
				} else {
					$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_ERROR_GEOLOCATION_NOT_FOUND', $item->id), 'notice');
				}
			} else {
				$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_ERROR_GEOLOCATION_EMPTY_ADDRESS', $item->id), 'notice');
			}
		}

		return true;
	}
	
	public function delete(&$cid) {
		$return = parent::delete($cid);
		$app = JFactory::getApplication();
	
		
		if ($return && count($cid)) {
			$cids = implode(',', $cid);
		
			$this->_db->setQuery("UPDATE #__djc2_items set published=-2, parent_id=0 WHERE parent_id IN ( ".$cids." )");
			$this->_db->query();
			$updCnt = $this->_db->getAffectedRows();
			if ($updCnt > 0) {
				$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_PARENTS_DELETED_NOTICE', $updCnt), 'notice');
			}
		}
		
		return $return;
	}
	
	public function batch($commands, $pks, $contexts) {
		$pks = array_unique($pks);
		$pks = ArrayHelper::toInteger($pks);
		
		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}
		
		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}
		
		$done = false;
		$db = JFactory::getDbo();
		
		if (!empty($commands['category_moveadd'])){
			$moveAdd = $commands['category_moveadd'];
			if (!empty($commands['category'])) {
				if ($moveAdd == 'm') {
					$query = $db->getQuery(true);
					$query->delete('#__djc2_items_categories');
					$query->where('item_id IN ('.implode(',', $pks).')');
					$db->setQuery($query);
					if ($db->execute()) {
						$query = $db->getQuery(true);
						$query->update('#__djc2_items')->set('cat_id='.(int)$commands['category'])->where('id IN ('.implode(',', $pks).')');
						$db->setQuery($query);
						
						if ($db->execute()) {
							$query = $db->getQuery(true);
							$query->insert('#__djc2_items_categories')->columns('item_id, category_id');
							foreach($pks as $id) {
								$query->values($id.', '.(int)$commands['category']);
							}
							$db->setQuery($query);
							$done = $db->execute();
						}
					}
				} else {
					$query = $db->getQuery(true);
					$query->delete('#__djc2_items_categories');
					$query->where('item_id IN ('.implode(',', $pks).') AND category_id='.$commands['category']);
					$db->setQuery($query);
					if ($db->execute()) {
						$query = $db->getQuery(true);
						$query->update('#__djc2_items')->set('cat_id='.(int)$commands['category'])->where('id IN ('.implode(',', $pks).') AND (cat_id IS NULL OR cat_id=0)');
						$db->setQuery($query);
						
						if ($db->execute()) {
							$query = $db->getQuery(true);
							$query->insert('#__djc2_items_categories')->columns('item_id, category_id');
							foreach($pks as $id) {
								$query->values($id.', '.(int)$commands['category']);
							}
							$db->setQuery($query);
							$done = $db->execute();
						}
					}
				}
			}
		}
		
		if (!empty($commands['producer'])) {
			$query = $db->getQuery(true);
			$commands['producer'] = $commands['producer'] == -1 ? 0 : (int)$commands['producer'];
			$query->update('#__djc2_items')->set('producer_id='.$commands['producer'])->where('id IN ('.implode(',', $pks).')');
			$db->setQuery($query);
			$done = $db->execute();
		}
		
		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			
			return false;
		}
		
		// Clear the cache
		$this->cleanCache();
		
		return true;
	}
}
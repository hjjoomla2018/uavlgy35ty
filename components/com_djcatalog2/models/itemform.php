<?php
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
jimport('joomla.application.component.helper');

class Djcatalog2ModelItemform extends DJCJModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		
		JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
		JForm::addRulePath(JPATH_COMPONENT_ADMINISTRATOR . '/models/rules');
		
		require_once(JPATH_COMPONENT.DS.'helpers'.DS.'itemevents.php');
		$dispatcher = JEventDispatcher::getInstance();
		$itemevent = new Djcatalog2Itemevents($dispatcher);
		
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
		$form = $this->loadForm('com_djcatalog2.itemform', 'itemform', array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form)) {
			return false;
		}

		return $form;
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'content') {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_djcatalog2');
		$user = JFactory::getUser();
		$allowed_categories = $params->get('fed_allowed_categories', array());
		JArrayHelper::toInteger($allowed_categories);
		if (!empty($allowed_categories)) {
			$form->setFieldAttribute('cat_id', 'allowed_categories', implode(',',$allowed_categories));
		}
		if ($params->get('fed_multiple_categories', '0') == '0') {
			$form->removeField('categories');
		} else { 
			if(!empty($allowed_categories)) {
				$form->setFieldAttribute('categories', 'allowed_categories', implode(',',$allowed_categories));
			}
			if ((int)$params->get('fed_multiple_categories_limit',3) > 0) {
				$form->setFieldAttribute('categories', 'limit', (int)$params->get('fed_multiple_categories_limit',3));
				if (!empty($data->cat_id)) {
					$form->setFieldAttribute('categories', 'ignored_values', $data->cat_id);
				}
			} else if ($params->get('fed_multiple_categories_limit',3) == '0') {
				$form->removeField('categories');
			}
		}
		
		if ($params->get('fed_multiple_groups', '0') == '0') {
			$form->setFieldAttribute('group_id', 'multiple', 'false');
		}
		
		if ($params->get('fed_producer', '0') == '0') {
			$form->removeField('producer_id');
		} else {
			if ($params->get('fed_producer', '0') == '2') {
				$form->setFieldAttribute('producer_id', 'required', 'required');
				$form->setFieldAttribute('producer_id', 'class', $form->getFieldAttribute('producer_id', 'class').' required');
			}
			if ($params->get('fed_producer_restrict', 0) == '1') {
				$form->setFieldAttribute('producer_id', 'validate', 'djcproducer');
				if (!empty($data->created_by) && $data->created_by > 0) {
					$form->setFieldAttribute('producer_id', 'validate_user', $data->created_by);
				}
			}
		}
		if ($params->get('fed_sku', '0') == '0') {
			$form->removeField('sku');
		} else if ($params->get('fed_sku', '0') == '2') {
			$form->setFieldAttribute('sku', 'required', 'required');
			$form->setFieldAttribute('sku', 'class', $form->getFieldAttribute('sku', 'class').' required');
		}
		
		if ($params->get('fed_price', '0') == '0') {
			$form->removeField('price');
			$form->removeField('special_price');
		} else if ($params->get('fed_price', '0') == '2') {
			$form->setFieldAttribute('price', 'required', 'required');
			$form->setFieldAttribute('price', 'class', $form->getFieldAttribute('price', 'class').' required');
		}
		
		if ($params->get('fed_featured', '0') == '0') {
			$form->removeField('featured');
		} else if ($params->get('fed_featured', '0') == '2') {
			$form->setFieldAttribute('featured', 'required', 'required');
			$form->setFieldAttribute('featured', 'class', $form->getFieldAttribute('featured', 'class').' required');
		}
		
		if ($params->get('fed_available', '0') == '0') {
			$form->removeField('available');
		} else if ($params->get('fed_available', '0') == '2') {
			$form->setFieldAttribute('available', 'required', 'required');
			$form->setFieldAttribute('available', 'class', $form->getFieldAttribute('available', 'class').' required');
		}
		
		if ($params->get('fed_group', '0') == '0') {
			$form->removeField('group_id');
		} else if ($params->get('fed_group', '0') == '2') {
			$form->setFieldAttribute('group_id', 'required', 'true');
			//$form->setFieldAttribute('group_id', 'class', $form->getFieldAttribute('group_id', 'class').' required');
		}
		
		if ($params->get('fed_meta', '0') == '0') {
			$form->removeField('metatitle');
			$form->removeField('metakey');
			$form->removeField('metadesc');
		}
		
		if ($params->get('fed_intro_description_editor', null)) {
			$form->setFieldAttribute('intro_desc', 'editor', $params->get('fed_intro_description_editor'));
		}
		
		if ($params->get('fed_editor_buttons', '0') == '1') {
			$form->setFieldAttribute('intro_desc', 'buttons', 'true');
			$form->setFieldAttribute('description', 'buttons', 'true');
		}
		
		if ($params->get('fed_intro_description', '0') == '0') {
			$form->removeField('intro_desc');
		} else if ($params->get('fed_intro_description', '0') == '2') {
			$form->setFieldAttribute('intro_desc', 'required', 'required');
			$form->setFieldAttribute('intro_desc', 'class', $form->getFieldAttribute('intro_desc', 'class').' required');
		}
		
		if ($params->get('fed_description_editor', null)) {
			$form->setFieldAttribute('description', 'editor', $params->get('fed_description_editor'));
		}
		
		if ($params->get('fed_description', '0') == '0') {
			$form->removeField('description');
		} else if ($params->get('fed_description', '0') == '2') {
			$form->setFieldAttribute('description', 'required', 'required');
			$form->setFieldAttribute('description', 'class', $form->getFieldAttribute('description', 'class').' required');
		}
		
		$location_fields = $form->getGroup('location');
		$show_location = (bool)($params->get('fed_location_details', '1') == '1');
		foreach($location_fields as $k=> $field) {
			$field_name = $field->__get('fieldname');
			if ($show_location) {
				$show_location_field = (int)$params->get('fed_location_'.$field_name, '1');
				if ($show_location_field == 0) {
					$form->removeField($field_name, 'location');
				} else if ($show_location_field == 2) {
					$form->setFieldAttribute($field_name, 'required', 'true', 'location');
					$form->setFieldAttribute($field_name, 'class', $form->getFieldAttribute($field_name, 'class', '', 'location').' required', 'location');
				}
			} else {
				$form->removeField($field_name, 'location');
			}
		}
		
		$default_state = $params->get('fed_default_state', '0');
		$form->setFieldAttribute('published', 'default', $default_state);

		$is_owner = ((empty($data->created_by) && !empty($data)) || (isset($data->created_by) && (int)JFactory::getUser()->id === (int)$data->created_by)) ? true : false;

		if (!(JFactory::getUser()->authorise('core.edit.state', 'com_djcatalog2') || (JFactory::getUser()->authorise('core.edit.state.own', 'com_djcatalog2') && $is_owner))) {
			if (isset($data->published)) {
				$form->removeField('published');
			}
		}
	}
	
	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			if ((!isset($item->categories) || !is_array($item->categories)) && isset($item->id)){
				$this->_db->setQuery('SELECT category_id FROM #__djc2_items_categories WHERE item_id=\''.$item->id.'\'');
				$item->categories = $this->_db->loadColumn();
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
				$query = $this->_db->getQuery(true);
				/*$query->select ('distinct f.group_id');
				$query->from('#__djc2_items_extra_fields AS f');
				$query->join('LEFT','#__djc2_items_extra_fields_values_text AS vt ON f.id=vt.field_id AND vt.item_id='.(int)$item->id);
				$query->join('LEFT','#__djc2_items_extra_fields_values_int AS vi ON f.id=vi.field_id AND vi.item_id='.(int)$item->id);
				$query->join('LEFT','#__djc2_items_extra_fields_values_date AS vd ON f.id=vd.field_id AND vd.item_id='.(int)$item->id);
				$query->where('vt.value IS NOT NULL OR vi.value IS NOT NULL OR vd.value IS NOT NULL');*/
				
				$query->select('distinct group_id');
				$query->from('#__djc2_items_groups');
				$query->where('item_id='.(int)$item->id);
				
				$this->_db->setQuery($query);
				$item->group_id = $this->_db->loadColumn();
			}
			
			if ((!isset($item->labels) || !is_array($item->labels)) && isset($item->id)){
				$this->_db->setQuery('SELECT label_id FROM #__djc2_labels_items WHERE item_id=\''.$item->id.'\'');
				$item->labels = $this->_db->loadColumn();
			}

			return $item;
		} else {
			return false;
		}
	}

	protected function loadFormData()
	{
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		//$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.itemform.data', array());
		
		$data = $this->getItem();
		
		/*if (!empty($existing_data)) {
			if (empty($data)) {
				$data = $existing_data;
				if ($params->get('fed_intro_description_editor', null) == 'none' && !empty($data->intro_desc)) {
					$data->intro_desc = str_replace(array('<br />', '<br/>', '<br>'), '', $data->intro_desc);
				}
				if ($params->get('fed_description_editor', null) == 'none' && !empty($data->description)) {
					$data->description = str_replace(array('<br />', '<br/>', '<br>'), '', $data->description);
				}
			} else {
				$data = JArrayHelper::toObject($data, 'stdClass');
			}
			
			if (!empty($existing_data->created_by)) {
				$data->created_by = $existing_data->created_by;
			} else {
				$data->created_by = JFactory::getUser()->id;
			}
		}*/
		
		/*if (is_array($data)) {
			$data = JArrayHelper::toObject($data, 'stdClass');
		}*/
		
		$this->preprocessData('com_djcatalog2.itemform', $data, 'content');
		
		return $data;
	}

	protected function _prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_djcatalog2');
		$db = JFactory::getDbo();
		
		
		if ($params->get('fed_intro_description_editor', null) == 'none' && !empty($table->intro_desc)) {
			$table->intro_desc = nl2br($table->intro_desc, true);
		}
		if ($params->get('fed_description_editor', null) == 'none' && !empty($table->description)) {
			$table->description = nl2br($table->description, true);
		}

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
		$table->alias		= JApplication::stringURLSafe($table->alias);
		
		// TODO - just temporary
		$table->group_id = 0;
		
		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->name);
		}

		if (empty($table->id)) {
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__djc2_items WHERE cat_id = '.$table->cat_id);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		
		if (!isset($table->group_id)) {
			$table->group_id = 0;
		}
		
		if ( (!isset($table->latitude) || !isset($table->longtitude)) || $table->latitude == 0 || $table->longtitude == 0) {
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
		return $condition;
	}
	
	public function validateAttributes($data, &$table) {
		$db = JFactory::getDbo();
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$group_ids = array();
		if (!empty($table->group_id) && is_array($table->group_id)) {
			$group_ids = $table->group_id;
			JArrayHelper::toInteger($group_ids);
		}
		$group_ids[] = 0;
		$group_ids = array_unique($group_ids);
		
		
		if ($params->get('fed_group', '0') == '0') {
			$db->setQuery('select * from #__djc2_items_extra_fields where required=1 AND (group_id=0)');
		} else {
			//$db->setQuery('select * from #__djc2_items_extra_fields where required=1 AND (group_id=0 OR group_id='.(int)$table->group_id.')');
			$db->setQuery('select * from #__djc2_items_extra_fields where required=1 AND group_id IN ('.implode(',', $group_ids).')');
		}
		
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

	public function saveAttributes($data, &$table) {
		$db = JFactory::getDbo();
		
		if (!isset($table->group_id) || !$table->group_id) {
			$table->group_id = 0;
		}
		
		//if (!empty($data) ) {
			
			$non_empty_fields = array(0);
			
			if (!empty($data)) {
				foreach ($data as $k=>$v) {
					if (!empty($v) || trim($v) != '') {
						$non_empty_fields[] = (int)$k;
					}
				}
			}
			
			$non_empty_fields = array_unique($non_empty_fields);
			$non_empty_fields_ids = implode(',', $non_empty_fields);
			
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
										AND field_id='.$fieldId
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
					$fv_table->load($row['id']);
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
	
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		$canEdit = $user->authorise('core.edit.state', $this->option);
		$canEditOwn = $user->authorise('core.edit.state.own', $this->option);
		
		return (($user->id == $record->created_by && $canEditOwn) || $canEdit) ? true : false;
	}
	
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		
		$canDelete = $user->authorise('core.delete', $this->option);
		$canDeleteOwn = $user->authorise('core.delete.own', $this->option);
		
		return (($user->id == $record->created_by && $canDeleteOwn) || $canDelete) ? true : false;
	}
	
	public function validate($form, $data, $group = null)
	{
		/*$data = parent::validate($form, $data, $group);
		if ($data == false) {
			return false;
		}*/
		
		$params = JComponentHelper::getParams('com_djcatalog2');
		$user = JFactory::getUser();
		
		$recordId	= (int) isset($data['id']) ? $data['id'] : 0;
		$ownerId = (int)$user->id;
		$default_state = (int)$params->get('fed_default_state', '0');
		$default_access = (int) $params->get('fed_access', JFactory::getConfig()->get('access'));
		
		if (!empty($data['cat_id'])) {
			$categories = Djc2Categories::getInstance();
			$category = $categories->get($data['cat_id']);
			if ($category) {
				$default_access = $category->access;
			}
		}
		
		$canEdit = $user->authorise('core.edit.state', $this->option);
		$canEditOwn = $user->authorise('core.edit.state.own', $this->option);
		
		$record = null;
		if ($recordId) {
			$record = $this->getItem($recordId);
		}
		
		if ($record) {
			if (!($canEdit || ($canEditOwn && $record->created_by == $ownerId))) {
				$data['published'] = $record->published;
				$data['access'] = $record->access;
			} else if (empty($data['published'])) {
				$data['published'] = $default_state;
				$data['access'] = $default_access;
			}
			
			if (!empty($record->created_by)) {
				$data['created_by'] = $record->created_by;
			} else {
				$data['created_by'] = $user->id;
			}
			
			$data['modified_by'] = $user->id;
		} else {
			if ((!$canEditOwn && !$canEdit) || empty($data['published'])) {
				$data['published'] = $default_state;
				$data['access'] = $default_access;
			}
			$data['created_by'] = $user->id;
		}
		
		$form->setValue('created_by', null, $data['created_by']);
		
		return parent::validate($form, $data, $group);
		//return $data;
	}

}
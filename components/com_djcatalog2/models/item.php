<?php
use Joomla\Registry\Registry;

/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modelform');

class DJCatalog2ModelItem extends JModelForm {
	
	protected $view_item = 'item';
	protected $_item = null;
	protected $_context = 'com_djcatalog2.item';
	protected $_related = array();
	protected $_attributes = array();
	protected $_cart_attributes = array();
	protected $_children = array();
	protected $_combinations = array();
	protected $_customisations = array();
	protected $_price_tiers = array();
	
	public $childrenModel = null;
	
	protected function populateState()
	{
		$app = JFactory::getApplication('site');
		
		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('item.id', $pk);
		
		$user = Djcatalog2Helper::getUserProfile($app->getUserState('com_djcatalog2.checkout.user_id', null));
		if (isset($user->customer_group_id)) {
			$this->setState('filter.customergroup', $user->customer_group_id);
		}
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_djcatalog2.contact', 'contact', array('control' => 'jform', 'load_data' => true));
		if (empty($form)) {
			return false;
		}
		
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$user = JFactory::getUser();
		if ($user->id > 0) {
			if ($form->getValue('contact_email') == '') {
				$form->setFieldAttribute('contact_email', 'default', $user->email);
			}
			if ($form->getValue('contact_name') == '') {
				$form->setFieldAttribute('contact_name', 'default', $user->name);
			}
		}
		
		$subject = @$this->getItem()->name;
		if ($subject && $form->getValue('contact_subject') == '') {
			$form->setFieldAttribute('contact_subject', 'default', $subject);
		}
		
		return $form;
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'content') {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_djcatalog2');
		$db = JFactory::getDbo();
		
		$user = JFactory::getUser();

		$switchable_fields = array('contact_phone', 'contact_street', 'contact_city', 'contact_zip', 'contact_country', 'contact_state', 'contact_company_name', 'contact_gdpr_policy', 'contact_gdpr_agreement', 'contact_email_copy');
		foreach($switchable_fields as $field_name) {
			if ($field_name == 'contact_gdpr_policy') {
				$policy_info = JText::sprintf('COM_DJCATALOG2_GDPR_AGREE', $app->get('sitename'));
				if (trim($params->get('contact_gdpr_policy_info_field')) != '') {
					$policy_info = $params->get('contact_gdpr_policy_info_field');
				}
				$form->setFieldAttribute($field_name, 'label', $policy_info);
			} else if ($field_name == 'contact_gdpr_agreement') {
				$agreement_info = JText::sprintf('COM_DJCATALOG2_GDPR_AGREE', $app->get('sitename'));
				if (trim($params->get('contact_gdpr_agreement_info_field')) != '') {
					$agreement_info = $params->get('contact_gdpr_agreement_info_field');
				}
				$form->setFieldAttribute($field_name, 'label', $agreement_info);
			}
			if ($params->get($field_name.'_field', '0') == '0'){
				$form->removeField($field_name);
			} else if ($params->get($field_name.'_field', '0') == '2') {
				$form->setFieldAttribute($field_name, 'required', 'true');
				$form->setFieldAttribute($field_name, 'class', $form->getFieldAttribute($field_name, 'class').' required');
			}
		}
		
		if ($params->get('contact_country_field') == '0' && (int)$params->get('contact_state_field') > 0) {
			$query = $db->getQuery(true);
			$query->select('id')->from('#__djc2_countries')->where('is_default=1');
			$db->setQuery($query);
			$default_country = $db->loadResult();
			if ($default_country > 0) {
				$form->setFieldAttribute('contact_state', 'country', $default_country);
			}
		}
		
		$plugin = JFactory::getApplication()->getParams()->get('contact_captcha', JFactory::getConfig()->get('captcha'));
		if ($user->id > 0 || ($plugin === 0 || $plugin === '0' || $plugin === '' || $plugin === null)) {
			$form->removeField('captcha');
		} else {
			JFactory::getApplication()->getParams()->set('captcha', $plugin);
		}
	}
	
	protected function loadFormData()
	{
		$data = (array)JFactory::getApplication()->getUserState('com_djcatalog2.contact.data', array());
		return $data;
	}
	
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('item.id');
		
		$price_group_filter = $this->getState('filter.customergroup', false);
		
		if ($this->_item === null) {
			$this->_item = array();
		}
		
		$bound = true;
		if (!isset($this->_item[$pk])) {
			try
			{
				$db = JFactory::getDbo();
				$query = $db -> getQuery(true);
				
				//$attributes = $this -> getAttributes();
				
				$query -> select('i.*, CASE WHEN (i.special_price > 0.0 AND i.special_price < i.price) THEN i.special_price ELSE i.price END as final_price');
				$query -> from('#__djc2_items as i');
				
				$query -> select('c.id as _category_id, c.name as category, c.published as publish_category, c.alias as category_alias');
				$query -> join('left', '#__djc2_categories AS c ON c.id = i.cat_id');
				
				$query -> select('p.id as _producer_id, p.name as producer, p.published as publish_producer, p.alias as producer_alias');
				$query -> join('left', '#__djc2_producers AS p ON p.id = i.producer_id');
				
				$query -> select('ua.name AS author, ua.email AS author_email');
				$query -> join('left', '#__users AS ua ON ua.id = i.created_by');
				
				$query -> select('gc.price as group_price');
				$query -> join('left', '#__djc2_prices AS gc ON gc.item_id = i.id AND gc.group_id='.(int)$price_group_filter);
				
				$query -> select('countries.country_name ');
				$query -> join('left', '#__djc2_countries AS countries ON countries.id = i.country');
				
				$nullDate = $db->quote($db->getNullDate());
				$date = JFactory::getDate();
				$nowDate = $db->quote($date->toSql());
				
				$query->where('i.id ='.(int)$pk);
				$query->where('(i.publish_up = ' . $nullDate . ' OR i.publish_up <= ' . $nowDate . ')');
				$query->where('(i.publish_down = ' . $nullDate . ' OR i.publish_down >= ' . $nowDate . ')');
				
				
				$query -> group('i.id');
				//echo str_replace('#_','jos',$query).'<br/>';die();
				$db -> setQuery($query);
				$item = $db -> loadObject();
				
				if (!empty($item)) {
					$item->slug = (empty($item->alias)) ? $item->id : $item->id.':'.$item->alias;
					$item->catslug = (empty($item->category_alias)) ? $item->cat_id : $item->cat_id.':'.$item->category_alias;
					$item->prodslug = (empty($item->producer_alias)) ? $item->producer_id : $item->producer_id.':'.$item->producer_alias;
					
					if ($item->group_price > 0) {
						if ($item->special_price > 0 && $item->special_price < $item->group_price ) {
							$item->price = $item->group_price;
							$item->final_price = $item->special_price;
						} else {
							$item->price = $item->final_price = $item->group_price;
						}
					}
				}
				$this->_item[$pk] = $item;
				$bound = false;
			}
			catch (JException $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
			
		}
		if ($this->_item[$pk] && !$bound)
		{
			$this->bindAttributes($pk);
		}
		return $this->_item[$pk];
		
	}
	
	function getRelatedItems($pk = null) {
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('item.id');
		
		if (empty($this->_related[$pk])) {
			$params = $this->getState('params',  Djcatalog2Helper::getParams());
			
			$filter_order       = $params->get('related_items_default_order', 'i.ordering');
			$filter_order_Dir   = $params->get('related_items_default_order_dir', 'asc');
			$filter_featured    = $params->get('related_featured_first', 0);
			
			if ($params->get('related_items_count', 2) == 0) {
				$this->_related[$pk] = array();
				return $this->_related[$pk];
			}
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('related_item');
			$query->from('#__djc2_items_related');
			$query->where('item_id='.(int)$pk);
			$db->setQuery($query);
			
			$ids = $db->loadColumn();
			
			if (empty($ids)) {
				$this->_related[$pk] = array();
				return $this->_related[$pk];
			}
			
			$model = JModelLegacy::getInstance('Items', 'Djcatalog2Model', array('ignore_request'=>true));
			$state = $model->getState();
			
			$model->setState('params', $params);
			
			$model->setState('list.start', 0);
			$model->setState('list.limit', $params->get('related_items_count', 2));
			
			$model->setState('filter.catalogue',false);
			$model->setState('list.ordering_featured',$filter_featured);
			$model->setState('list.ordering',$filter_order);
			$model->setState('list.direction',$filter_order_Dir);
			$model->setState('filter.item_ids', $ids);
			$model->setState('list.fields_visibility', '*');
			
			$items = $model->getItems();
			$this->_related[$pk] = array_values($items);
		}
		return $this->_related[$pk];
	}
	
	function getAttributes() {
		if (empty($this->_attributes)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('f.*, group_concat(fo.id separator \'|\') as options, g.name as group_name, g.label as group_label, g.id as fgroup_id');
			$query->from('#__djc2_items_extra_fields as f');
			$query->join('LEFT', '#__djc2_items_extra_fields_options as fo ON fo.field_id=f.id');
			$query->join('LEFT', '#__djc2_items_extra_fields_groups as g ON g.id=f.group_id');
			
			$query->where('(f.visibility = 1 or f.visibility = 3) and f.published = 1');
			$query->where('f.cart_variant = 0');
			$query->group('f.id');
			$query->order('IFNULL(g.ordering,0) asc , g.ordering asc, f.ordering asc');
			$db->setQuery($query);
			
			$this->_attributes = $db->loadObjectList();
		}
		
		return $this->_attributes;
	}
	
	function getCartAttributes() {
		if (empty($this->_cart_attributes)) {
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			//$query->select('f.*, group_concat(fo.id separator \'|\') as options, g.name as group_name, g.label as group_label, g.id as fgroup_id');
			$query->select('f.*, g.name as group_name, g.label as group_label, g.id as fgroup_id');
			$query->from('#__djc2_items_extra_fields as f');
			//$query->join('LEFT', '#__djc2_items_extra_fields_options as fo ON fo.field_id=f.id');
			$query->join('LEFT', '#__djc2_items_extra_fields_groups as g ON g.id=f.group_id');
			$query->where('f.cart_variant=1 and f.published = 1');
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
							$field_optionParams[$option->id] = new Registry($option->params);
							
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
	
	function getCartVariants() {
		$item = $this->getItem();
		$combinations = $this->getCombinations($item->id);
		$cart_attributes = $this->getCartAttributes();
		
		foreach($cart_attributes as $key => &$cartField) {
			$cartField->_variantData = null;
			
			$variantData = new stdClass();
			
			$variantData->options = $cartField->options;
			$variantData->optionValues = $cartField->optionValues;
			$variantData->optionParams = $cartField->optionParams;
			
			$variantData->availableOptions = array();
			$variantData->optionCombinations = array();
			
			$cartField->_combinations = array();
			
			$cartField->_variantData = $variantData;
			
		}
		unset($cartField);
		
		foreach($combinations as $combination_id => &$combination) {
			if (!isset($combination->fields)) continue;
			foreach($combination->fields as $field_id => $field) {
				if (!isset($cart_attributes[$field_id])) {
					continue;
				}
				
				$cart_attributes[$field_id]->_combinations[]= &$combination;
				
				if (!in_array($field->value, $cart_attributes[$field_id]->_variantData->availableOptions)) {
					$cart_attributes[$field_id]->_variantData->availableOptions[] = $field->value;
				}
				
				if (!isset($cart_attributes[$field_id]->_variantData->optionCombinations[$field->value])) {
					$cart_attributes[$field_id]->_variantData->optionCombinations[$field->value] = array();
				}
				$cart_attributes[$field_id]->_variantData->optionCombinations[$field->value][] = $combination->id;
			}
		}
		unset($combination);
		
		foreach($cart_attributes as $k => $v){
			if ( count($v->_variantData->availableOptions) < 1 ) {
				unset($cart_attributes[$k]);
			}
		}
		
		//echo '<pre>'.print_r($cart_attributes,true).'</pre>';
		/*echo '<br />-------<br />';
		 echo '<pre>'.print_r($item,true).'</pre>';
		 echo '<pre>'.print_r($children,true).'</pre>';
		 echo '<br />-------<br />';
		 die();
		 */
		
		return $cart_attributes;
	}
	
	function bindAttributes($id) {
		if (!empty($this->_item[$id])) {
			$db = JFactory::getDbo();
			
			$query_int = $db->getQuery(true);
			$query_text = $db->getQuery(true);
			$query_date = $db->getQuery(true);
			
			$query_int->select('fields.alias, fields.type, fields.ordering, fieldvalues.item_id, fieldvalues.field_id, fieldvalues.id as value_id, fieldoptions.id as option_id, fieldoptions.value, fieldoptions.params as option_params');
			$query_int->from('#__djc2_items_extra_fields_values_int as fieldvalues');
			$query_int->join('inner', '#__djc2_items as items on items.id=fieldvalues.item_id' );
			$query_int->join('inner','#__djc2_items_extra_fields as fields ON fields.id = fieldvalues.field_id');
			$query_int->join('left','#__djc2_items_extra_fields_options as fieldoptions ON fieldoptions.id = fieldvalues.value AND fieldoptions.field_id = fields.id');
			$query_int->where('fieldvalues.item_id='.$id.' AND (fields.visibility = 1 OR fields.visibility = 3) AND fields.published = 1');
			$query_int->order('fields.ordering asc, fieldoptions.ordering asc');
			
			$query_text->select('fields.alias, fields.type, fields.ordering, fieldvalues.item_id, fieldvalues.field_id, fieldvalues.id as value_id, 0 as option_id, fieldvalues.value');
			$query_text->from('#__djc2_items_extra_fields_values_text as fieldvalues');
			$query_text->join('inner', '#__djc2_items as items on items.id=fieldvalues.item_id' );
			$query_text->join('inner','#__djc2_items_extra_fields as fields ON fields.id = fieldvalues.field_id');
			$query_text->where('fieldvalues.item_id='.$id.' AND (fields.visibility = 1 OR fields.visibility = 3) AND fields.published = 1');
			
			$query_date->select('fields.alias, fields.type, fields.ordering, fieldvalues.item_id, fieldvalues.field_id, fieldvalues.id as value_id, 0 as option_id, fieldvalues.value');
			$query_date->from('#__djc2_items_extra_fields_values_date as fieldvalues');
			$query_date->join('inner', '#__djc2_items as items on items.id=fieldvalues.item_id' );
			$query_date->join('inner','#__djc2_items_extra_fields as fields ON fields.id = fieldvalues.field_id');
			$query_date->where('fieldvalues.item_id='.$id.' AND (fields.visibility = 1 OR fields.visibility = 3) AND fields.published = 1');
			$query_date->order('fields.ordering asc');
			
			$query_labels = $db->getQuery(true);
			$query_labels->select('l.*, li.item_id')->from('#__djc2_labels as l')->join('inner', '#__djc2_labels_items AS li ON li.label_id=l.id');
			$query_labels->where('li.item_id='.$id);
			$query_labels->order('l.ordering');
			
			$query_combos = $db->getQuery(true);
			$query_combos->select('COUNT(*)');
			$query_combos->from('#__djc2_items_combinations');
			$query_combos->where('item_id ='. $id);
			
			$db->setQuery($query_int);
			$int_attributes = $db->loadObjectList();
			$db->setQuery($query_text);
			$text_attributes = $db->loadObjectList();
			$db->setQuery($query_date);
			$date_attributes = $db->loadObjectList();
			
			$db->setQuery($query_labels);
			$labels = $db->loadObjectList();
			
			$db->setQuery($query_combos);
			$this->_item[$id]->combo_count = $db->loadResult();

			foreach($labels as $label) {
				if (!isset($this->_item[$label->item_id]->_labels)) {
					$this->_item[$label->item_id]->_labels = array();
				}
				$params = new Registry();
				$params->loadString($label->params);
				$label->params = $params;
				$this->_item[$label->item_id]->_labels[] = $label;
			}
			
			foreach ($text_attributes as $attribute) {
				if ($attribute->item_id == $id) {
					$field = '_ef_'.$attribute->alias;
					$this->_item[$id]->$field = $attribute->value;
				}
			}
			foreach ($date_attributes as $attribute) {
				if ($attribute->item_id == $id) {
					$field = '_ef_'.$attribute->alias;
					$this->_item[$id]->$field = $attribute->value;
				}
			}
			foreach ($int_attributes as $attribute) {
				if ($attribute->item_id == $id) {
					$field = '_ef_'.$attribute->alias;
					$param_field = '_efp_'.$attribute->alias;
					
					if (!isset($this->_item[$id]->$field) || !is_array($this->_item[$id]->$field)) {
						$this->_item[$id]->$field = array();
					}
					if (!in_array($attribute->value, $this->_item[$id]->$field)) {
						$tmp_arr = $this->_item[$id]->$field;
						$tmp_arr[$attribute->option_id] = $attribute->value;
						$this->_item[$id]->$field = $tmp_arr;
					}
					
					if (!isset($this->_item[$id]->$param_field) || !is_array($this->_item[$id]->$param_field)) {
						$this->_item[$id]->$param_field = array();
					}
					
					$tmp_arr = $this->_item[$id]->$param_field;
					$tmp_arr[$attribute->option_id] = new Registry($attribute->option_params);
					
					$this->_item[$id]->$param_field = $tmp_arr;
				}
			}
			
			$this->_item[$id]->_price_tiers = $this->getTierPrices($id);
		}
	}
	
	public function getNavigation($id, $catid = null, $params = null) {
		$db = JFactory::getDbo();
		$category_limit = ($catid) ? ' AND i.cat_id='.$catid : '';
		
		$orderby = 'c.ordering ASC, i.ordering ASC';
		
		if (!empty($params)) {
			$filter_order		= $params->get('items_default_order','i.ordering');
			$filter_order_Dir	= $params->get('items_default_order_dir','asc');
			$filter_featured	= $params->get('featured_first', 0);
			
			$sortables = array('i.ordering', 'i.name', 'i.created', 'i.price', 'category', 'c.name', 'producer', 'p.name', 'i.id', 'rand()');
			
			if (!in_array($filter_order, $sortables)) {
				$filter_order = 'i.ordering';
			}
			
			if ($filter_order_Dir != 'asc' && $filter_order_Dir != 'desc') {
				$filter_order_Dir = 'asc';
			}
			
			if ($filter_order == 'i.ordering'){
				if ($filter_featured) {
					//$orderby  = ' i.featured DESC, i.ordering '.$filter_order_Dir.', c.ordering '.$filter_order_Dir;
					//$orderby = 'i.featured DESC, c.parent_id asc, c.ordering asc, i.ordering '.$filter_order_Dir;
					if ($params->get('items_category_ordering', '1') != '1') {
						$orderby = ' i.featured DESC, i.ordering '.$filter_order_Dir.', c.ordering '.$filter_order_Dir;
					} else {
						$orderby = 'i.featured DESC, c.parent_id asc, c.ordering asc, i.ordering '.$filter_order_Dir;
					}
				} else {
					//$orderby  = ' i.ordering '.$filter_order_Dir.', c.ordering '.$filter_order_Dir;
					//$orderby = 'c.parent_id asc, c.ordering asc, i.ordering '.$filter_order_Dir;
					if ($params->get('items_category_ordering', '1') != '1') {
						$orderby = ' i.ordering '.$filter_order_Dir.', c.ordering '.$filter_order_Dir;
					} else {
						$orderby = 'c.parent_id asc, c.ordering asc, i.ordering '.$filter_order_Dir;
					}
				}
			} else {
				// older version compatibility
				switch ($filter_order) {
					case 'producer': {
						$filter_order = 'p.name';
						break;
					}
					case 'category': {
						$filter_order = 'c.name';
						break;
					}
					case 'i.price' : {
						$filter_order = 'final_price';
						break;
					}
				}
				if ($filter_featured) {
					$orderby 	= ' i.featured DESC, '.$filter_order.' '.$filter_order_Dir.' , i.ordering, c.ordering ';
				}
				else {
					$orderby 	= ' '.$filter_order.' '.$filter_order_Dir.' , i.ordering, c.ordering ';
				}
			}
		}
		
		$nullDate = $db->quote($db->getNullDate());
		$date = JFactory::getDate();
		$nowDate = $db->quote($date->toSql());
		
		//TODO
		/*
		 $query = 'SELECT DISTINCT i.id, i.name, i.alias, i.cat_id, c.alias as category_alias, @num := @num + 1 AS position, '
		 .' CASE WHEN (i.special_price > 0.0 AND i.special_price < i.price) THEN i.special_price ELSE i.price END as final_price '
		 .' FROM (SELECT @num := 0) AS n, #__djc2_items AS i '
		 .' LEFT JOIN #__djc2_categories as c ON c.id = i.cat_id '
		 .' LEFT JOIN #__djc2_producers as p ON p.id = i.producer_id '
		 .' WHERE i.published = 1 AND (i.publish_up = ' . $nullDate . ' OR i.publish_up <= ' . $nowDate . ') AND (i.publish_down = ' . $nullDate . ' OR i.publish_down >= ' . $nowDate . ') AND c.published = 1 '.$category_limit
		 .' ORDER BY '. $orderby;
		 */
		$query = 'SELECT k.*, @num := @num + 1 AS position' .
			' FROM (SELECT @num := 0) AS n,' .
			' (SELECT DISTINCT i.id, i.name, i.alias, i.cat_id, c.alias as category_alias,' .
			' CASE WHEN (i.special_price > 0.0 AND i.special_price < i.price) THEN i.special_price ELSE i.price END as final_price' .
			' FROM #__djc2_items AS i ' .
			' LEFT JOIN #__djc2_categories as c ON c.id = i.cat_id ' .
			' LEFT JOIN #__djc2_producers as p ON p.id = i.producer_id ' .
			' WHERE i.parent_id = 0 AND i.published = 1 AND (i.publish_up = ' . $nullDate . ' OR i.publish_up <= ' . $nowDate . ') AND (i.publish_down = ' . $nullDate . ' OR i.publish_down >= ' . $nowDate . ') AND c.published = 1 '.$category_limit .
			' ORDER BY '. $orderby.') as k';
		
		$navigation = array('prev'=>null, 'next'=>null);
		
		$db->setQuery('SELECT subq.position FROM ('.$query.') as subq WHERE subq.id = '.$id.' ORDER BY subq.position DESC LIMIT 1');
		$position = $db->loadResult();
		
		if (!$position) {
			return false;
		}
		//$pos_query = 'SELECT subq.* FROM ('.$query.') as subq WHERE subq.position='.($position - 1).' OR subq.position='.($position + 1).' ORDER BY subq.position ASC';
		//$db->setQuery($pos_query);
		
		$prev_query = 'SELECT subq.* FROM ('.$query.') as subq WHERE subq.position < '.$position.' ORDER BY subq.position DESC LIMIT 1';
		$next_query = 'SELECT subq.* FROM ('.$query.') as subq WHERE subq.position > '.$position.' ORDER BY subq.position ASC LIMIT 1';
		
		$db->setQuery($prev_query);
		$prev = $db->loadObject();
		if (!empty($prev) && $prev->id > 0 && $prev->cat_id > 0) {
			$navigation['prev'] = $prev;
			$navigation['prev']->slug = $prev->id.':'.$prev->alias;
			$navigation['prev']->catslug = $prev->cat_id.':'.$prev->category_alias;
		}
		
		$db->setQuery($next_query);
		$next = $db->loadObject();
		if (!empty($next) && $next->id > 0 && $next->cat_id > 0) {
			$navigation['next'] = $next;
			$navigation['next']->slug = $next->id.':'.$next->alias;
			$navigation['next']->catslug = $next->cat_id.':'.$next->category_alias;
		}
		
		//$nav_rows = $db->loadObjectList();
		//echo str_replace('#__', 'j25_', $pos_query);
		
		/*if (count($nav_rows) > 0) {
		 foreach($nav_rows as $row) {
		 if ($row->position > $position) {
		 $navigation['next'] = $row;
		 $navigation['next']->slug = $row->id.':'.$row->alias;
		 $navigation['next']->catslug = $row->cat_id.':'.$row->category_alias;
		 } else if ($row->position < $position) {
		 $navigation['prev'] = $row;
		 $navigation['prev']->slug = $row->id.':'.$row->alias;
		 $navigation['prev']->catslug = $row->cat_id.':'.$row->category_alias;
		 }
		 }
		 }*/
		
		return $navigation;
	}
	
	public function &getChildrenModel(){
		if (!$this->childrenModel) {
			JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models', 'DJCatalog2Model');
			$this->childrenModel = JModelLegacy::getInstance('Items', 'Djcatalog2Model', array('ignore_request'=>true));
		}
		
		return $this->childrenModel;
	}
	
	public function getChildren($item_id) {
		if ((int)$item_id <= 0) {
			return false;
		}
		
		if (!isset($this->children[$item_id])) {
			$model = $this->getChildrenModel();
			
			$state		= $model->getState();
			
			$model->setState('list.start', 0);
			$model->setState('list.limit', 0);
			$model->setState('filter.state', 1);
			$model->setState('filter.catalogue',false);
			$model->setState('filter.parent', $item_id);
			$model->setState('list.ordering', 'i.ordering');
			$model->setState('list.direction', 'asc');
			//$model->setState('list.fields_visibility', 'cart_variant');
			$model->setState('list.fields_visibility', '*');
			
			$this->children[$item_id] = $model->getItems();
		}
		
		return $this->children[$item_id];
	}
	
	public function getCombinations($item_id) {
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__djc2_items_combinations');
		$query->where('item_id = '.(int)$item_id);
		$db->setQuery($query);
		$combinations = $db->loadObjectList('id');
		
		if (count($combinations) > 0) {
			$query = $db->getQuery(true);
			$query->select('cf.*, f.name as field_name, fo.value as field_value, fo.params');
			$query->from('#__djc2_items_combinations_fields AS cf');
			$query->join('left', '#__djc2_items_extra_fields as f ON f.id = cf.field_id');
			$query->join('left', '#__djc2_items_extra_fields_options as fo ON fo.id = cf.value AND fo.field_id = cf.field_id AND fo.field_id = f.id');
			$query->where('cf.combination_id IN (' . implode(',', array_keys($combinations)).')' );
			$query->order('f.ordering, fo.ordering ASC');
			$db->setQuery($query);
			
			$fields = $db->loadObjectList();
			
			foreach($fields as $field) {
				
				$field->option_params = new Registry($field->params);
				
				if (!isset($combinations[$field->combination_id]->fields)) {
					$combinations[$field->combination_id]->fields = array();
				}
				$combinations[$field->combination_id]->fields[$field->field_id] = $field;
			}
		}
		
		return $combinations;
	}
	
	public function getCombination($combination_id) {
		if (!isset($this->_combinations[$combination_id])) {
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__djc2_items_combinations');
			$query->where('id = '.(int)$combination_id);
			$db->setQuery($query);
			$combination = $db->loadObject();
			
			if (!empty($combination)) {
				$query = $db->getQuery(true);
				$query->select('cf.*, f.name as field_name, fo.value as field_value');
				$query->from('#__djc2_items_combinations_fields AS cf');
				$query->join('left', '#__djc2_items_extra_fields as f ON f.id = cf.field_id');
				$query->join('left', '#__djc2_items_extra_fields_options as fo ON fo.id = cf.value AND fo.field_id = cf.field_id AND fo.field_id = f.id');
				$query->where('cf.combination_id = '.$combination->id );
				$query->order('f.ordering ASC, fo.ordering ASC');
				$db->setQuery($query);
				
				$fields = $db->loadObjectList();
				
				$combination->fields = array();
				
				foreach($fields as $field) {
					$combination->fields[$field->field_id] = $field;
				}
			}
			
			$this->_combinations[$combination_id] = $combination;
		}
		
		return $this->_combinations[$combination_id];
	}
	
	public function getCustomisations($item_id = 0) {
		if (!isset($this->_customisations[(int)$item_id])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			if ((int)$item_id > 0) {
				//$query->select('ic.*, c.name, c.input_params, c.price as def_price, c.min_quantity as def_min_quantity, c.max_quantity as def_max_quantity, c.required as def_required');
				$query->select('ic.*, c.name, c.input_params, c.type, c.tax_rule_id, c.price_modifier');
				$query->from('#__djc2_items_customisations AS ic');
				$query->join('inner', '#__djc2_customisations AS c ON c.id=ic.customisation_id');
				$query->where('item_id = '.(int)$item_id);
				$query->where('c.type='.$db->quote('i'));
				$query->order('c.ordering');
			} else {
				$query->select('c.*, id as customisation_id, 0 as item_id');
				$query->from('#__djc2_customisations AS c');
				$query->where('(c.type='.$db->quote('c') .' OR c.type='.$db->quote('a').')');
				$query->order('c.ordering');
			}
			
			$db->setQuery($query);
			
			$customisations = $db->loadObjectList();
			foreach($customisations as $key => &$custom) {
				if (!empty($custom->input_params)) {
					$input_params = json_decode(trim($custom->input_params), true);
					$custom->input_params = $input_params;
				} /*else {
				unset($customisations[$key]);
				continue;
				}*/
				else {
					$custom->input_params = array();
				}
				
				$custom->_cid = ($custom->customisation_id.'-'.$custom->item_id);
			}
			unset($custom);
			
			$this->_customisations[(int)$item_id] = $customisations;
		}
		
		return $this->_customisations[(int)$item_id];
	}
	
	public function getCustomisationsForm($customisations, $item, $params, $data = array()) {
		if (count($customisations) < 1) {
			return false;
		}
		
		$source = 	'<?xml version="1.0" encoding="UTF-8"?>' .
			'<form><fieldset addfieldpath="/administrator/components/com_djcatalog2/models/fields">';
		
		
		$source .= '<field name="customisation" type="checkboxes" label="COM_DJCATALOG2_CUSTOMISATION_OPT_LBL">';
		foreach ($customisations as $custom) {
			$optionName = $custom->name;
			
			if ($custom->price > 0.0) {
				$taxRuleId = (isset($item)) ? $item->tax_rule_id : $custom->tax_rule_id;
				$prices = Djcatalog2HelperPrice::getPrices($custom->price, $custom->price, $item->tax_rule_id, false, $params);
				$optionName .= ' &lt;span class="djc_custom_price"&gt;(+' . htmlspecialchars(DJCatalog2HtmlHelper::formatPrice($prices['display'], $params), ENT_QUOTES, 'UTF-8') . ')&lt;/span&gt;';
			}
			
			$source .= '<option value="'.$custom->_cid.'">'.$optionName.'</option>';
		}
		$source .= '</field>';
		
		foreach ($customisations as $custom) {
			$showon = 'showon="customisation:' . $custom->_cid.'"';
			
			$noteLbl = '';
			$noteDesc = '';
			
			if (count($custom->input_params) > 0) {
				$noteLbl = JText::sprintf('COM_DJCATALOG2_CUSTOMISATION_OPT_NOTE', $custom->name);
			}
			
			if ($custom->min_quantity > 0 || $custom->max_quantity > 0) {
				$noteLbl = JText::sprintf('COM_DJCATALOG2_CUSTOMISATION_OPT_NOTE', $custom->name);
				
				if ($custom->min_quantity > 0 && $custom->max_quantity > 0) {
					$noteDesc = JText::sprintf('COM_DJCATALOG2_CUSTOMISATION_MIN_MAX_NOTE', $custom->min_quantity, $custom->max_quantity);
				} else if ($custom->min_quantity > 0) {
					$noteDesc = JText::sprintf('COM_DJCATALOG2_CUSTOMISATION_MIN_NOTE', $custom->min_quantity);
				} else if ($custom->max_quantity > 0) {
					$noteDesc = JText::sprintf('COM_DJCATALOG2_CUSTOMISATION_MAX_NOTE', $custom->max_quantity);
				}
			}
			
			if ($noteLbl != '' || $noteDesc != '') {
				$source .= '<field '.$showon.' type="note" name="'.$custom->_cid.'-note-inputparams" label="'.$noteLbl.'" description="'.$noteDesc.'" />';
			}
			
			foreach ($custom->input_params as $ik => $inputParam) {
				
				$input = JArrayHelper::toObject($inputParam);
				//$name = 'customisation-' . $custom->_cid .'-'.$ik;
				$name = 'customValues-'.$custom->_cid .'['.$ik.']';
				
				switch($input->type) {
					case 'file': {
						//$source .= '<field '.$showon.' name="'.$name.'" type="djcplupload" label="'.$input->label.'" multiple_files="false" limit="1" preview="true" caption="false" download="false" extensions="jpg,png,gif" />';
						// CUSTOM
						$ext = trim($input->allowed_types);
						$ext = ($ext == '') ? 'jpg,png' : $ext;
						
						$maxSize = trim($input->max_size);
						$maxSize = ((int)$maxSize > 0) ? $maxSize : 2048;
						
						$fileDesc = JText::sprintf('COM_DJCATALOG2_PLUPLOAD_FILE_INFO_SPACER', ($maxSize/1024).'MB', str_replace(',', ', ', $ext));
						$fieldLbl = $input->label .' &lt;br /&gt;&lt;small&gt;('.$fileDesc.')&lt;/small&gt;';
						
						$source .= '<field '.$showon.' name="'.$name.'" type="djcplupload" label="'.$fieldLbl.'" multiple_files="false" limit="1" preview="false" caption="false" download="false" extensions="'.$ext.'" max_size="'.$maxSize.'" />';
						//$source .= '<field '.$showon.' type="note" name="'.$name.'-spacer" description="'.$fileDesc.'" label="" />';
						break;
					}
					case 'text': {
						$source .= '<field '.$showon.'  name="'.$name.'" type="text" label="'.$input->label.'" />';
						break;
					}
				}
				
			}
		}
		
		$source .= '</fieldset></form>';
		
		$form = JForm::getInstance('com_djcatalog2.cart.customisation', $source, array('control' => false), false, false);
		$app = JFactory::getApplication();
		
		if (!empty($data)) {
			$form->bind($data);
		}
		
		return $form;
	}
	
	public function getTierPrices($item_id) {
		
		if (!isset($this->_price_tiers[$item_id])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djc2_items_price_tiers')->where('item_id='.(int)$item_id)->order('quantity ASC');
			$db->setQuery($query);
			$this->_price_tiers[$item_id] = $db->loadObjectList();
		}
		
		return $this->_price_tiers[$item_id];
	}
	
	public function hit($pk = 0) {
		$pk = (! empty ( $pk )) ? $pk : ( int ) $this->getState ( 'item.id' );
		
		Djcatalog2Helper::pushRecentItem($pk);
		
		$db = $this->getDbo();
		$db->setQuery( 'UPDATE #__djc2_items' . ' SET hits = hits + 1' . ' WHERE id = ' . ( int ) $pk);
		$db->query();
		
		return true;
	}
}
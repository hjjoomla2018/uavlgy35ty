<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

use Joomla\Registry\Registry;

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

class Djcatalog2ModelQuery extends JModelForm
{
	protected $_item = null;

	protected $_context = 'com_djcatalog2.query';

	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	protected function populateState()
	{
		$table = $this->getTable();
		$key = 'qid';
	
		// Get the pk of the record from the request.
		$pk = JFactory::getApplication()->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);
	
		// Load the parameters.
		$value = JComponentHelper::getParams('com_djcatalog2');
		$this->setState('params', $value);
	}

	public function getTable($type = 'Quotes', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);
	
			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
		}
	
		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		
		$item = JArrayHelper::toObject($properties, 'JObject');
		
		if (!is_array($item->items)) {
			if (isset($item->id)) {
				$this->_db->setQuery('SELECT * FROM #__djc2_quote_items WHERE quote_id=\''.$item->id.'\'');
				$item->items = $this->_db->loadObjectList();
			} else {
				$item->items = array();
			}
		}

		if (property_exists($item, 'params'))
		{
			$registry = new Registry();
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}
		
		$name = array();
		if (!empty($item->firstname)) {
			$name[] = $item->firstname;
		}
		if (!empty($item->lastname)) {
			$name[] = $item->lastname;
		}
		
		$item->_name = (count($name) > 0) ? implode(' ', $name) : '';
	
		return $item;
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_djcatalog2.userprofile', 'userprofile', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		
		return $form;
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'content') {
		if (!($form instanceof JForm))
		{
			$this->setError('JERROR_NOT_A_FORM');
			return false;
		}
		
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_djcatalog2');
		$user = JFactory::getUser();
		
		$form->removeGroup('djcatalog2delivery');
		$form->removeGroup('djcatalog2orderdetails');
		
		$form->removeField('user_id', 'djcatalog2profile');
		$form->removeField('client_type', 'djcatalog2profile');
		$form->removeField('customer_group_id', 'djcatalog2profile');

		$plugin = JFactory::getApplication()->getParams()->get('cart_query_captcha', JFactory::getConfig()->get('captcha'));

		if ($user->guest == false || ($plugin === 0 || $plugin === '0' || $plugin === '' || $plugin === null)) {
			$form->removeField('captcha', 'djcatalog2captcha');
		} else {
			JFactory::getApplication()->getParams()->set('captcha', $plugin);
		}
		
		$group = 'djcatalog2profile';
		
		if ($user->guest == false) {
			$form->setValue('email', $group, $user->email);
			$form->setFieldAttribute('email', 'readonly', 'true', $group);
			$form->setFieldAttribute('email', 'class', $form->getFieldAttribute('email', 'class').' readonly', $group);
		}
		
		$form->removeField('client_type', 'djcatalog2profile');
		$form->removeField('customer_group_id', 'djcatalog2profile');
		
		
		$fields = array('firstname', 'lastname', 'company', 'position', 'address', 'city', 'postcode', 'country_id', 'state_id', 'vat_id', 'phone', 'fax', 'www', 'customer_note');
		//$delivery = array('company', 'address', 'city', 'postcode', 'country_id', 'phone');
		$message = array('customer_note');
		
		$formFields = array(
				'djcatalog2profile' => $fields,
				//'djcatalog2delivery' => $delivery,
				'djcatalog2message' => $message
		);
		
		foreach ($formFields as $group => $fields) {
			$paramSfx = '';
			switch ($group) {
				case 'djcatalog2profile' :
				case 'djcatalog2message' : $paramSfx = 'queryfield'; break;
		
				case 'djcatalog2delivery' : $paramSfx = 'deliveryfield'; break;
				default: break;
			}
			
			foreach ($fields as $field) {
				// in case config is broken - using defaults from XML file
				if ($params->get('cart_'.$paramSfx.'_'.$field, false) === false) {
					continue;
				}
					
				if ($params->get('cart_'.$paramSfx.'_'.$field, '0') == '0') {
					$form->removeField($field, $group);
				} else {
					if ($params->get('cart_'.$paramSfx.'_'.$field, '0') == '2') {
						$form->setFieldAttribute($field, 'required', 'required', $group);
						$form->setFieldAttribute($field, 'class', $form->getFieldAttribute($field, 'class').' required', $group);
					} else {
						$form->setFieldAttribute($field, 'required', false, $group);
							
						$class = $form->getFieldAttribute($field, 'class', '', $group);
						$class = str_replace('required', '', $class);
							
						$form->setFieldAttribute($field, 'class', $class, $group);
					}
				}
			}
		}
		
		$tos_link = $params->get('cart_tos_link', '');
		$tos_link = JUri::isInternal($tos_link) ? JRoute::_($tos_link) : $tos_link;
		if ($tos_link) {
			$form->setFieldAttribute('tos', 'label', JText::sprintf('COM_DJCATALOG2_TOS_WITH_LINK', $tos_link), 'djcatalog2statements');
		}
		
		$gdpr_policy = $params->get('cart_gdpr_policy');
		if ($gdpr_policy) {
			$policy_info = JText::sprintf('COM_DJCATALOG2_GDPR_POLICY_AGREE', $app->get('sitename'));
			if (trim($params->get('cart_gdpr_policy_info')) != '') {
				$policy_info = $params->get('cart_gdpr_policy_info');
			}
			$form->setFieldAttribute('gdpr_policy', 'label', $policy_info, 'djcatalog2statements');
		} else {
			$form->removeField('gdpr_policy', 'djcatalog2statements');
		}
		
		$gdpr_agreement = $params->get('cart_gdpr_agreement');
		if ($gdpr_agreement) {
			$agreement_info = JText::sprintf('COM_DJCATALOG2_GDPR_AGREE', $app->get('sitename'));
			if (trim($params->get('cart_gdpr_agreement_info')) != '') {
				$agreement_info = $params->get('cart_gdpr_agreement_info');
			}
			$form->setFieldAttribute('gdpr_agreement', 'label', $agreement_info, 'djcatalog2statements');
		} else {
			$form->removeField('gdpr_agreement', 'djcatalog2statements');
		}
	}
	
	protected function loadFormData()
	{
		$data = Djcatalog2Helper::getUserProfile(JFactory::getUser()->id);
		$data = JArrayHelper::fromObject($data, false);
		$data= array('djcatalog2profile'=> $data);

		$post_data = (array)JFactory::getApplication()->getUserState('com_djcatalog2.query.data', array());

		if (!empty($post_data)) {
			foreach($post_data as $k=>$v) {
				$data[$k] = $v;
			}
		}
		
		$this->preprocessData('com_djcatalog2.query', $data);

		return $data;
	}
	
	protected function preprocessData($context, &$data, $group = 'content')
	{
		// Get the dispatcher and load the users plugins.
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
	
		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array($context, $data));
	
		// Check for errors encountered while preparing the data.
		if (count($results) > 0 && in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
		}
	}
	
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}
	
	protected function prepareTable(&$table)
	{
	}

	public function save($data)
	{
		
		$table = $this->getTable();

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}

			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			// Clean the cache.
			$this->cleanCache();

		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}

		$this->setState($this->getName() . '.new', $isNew);

		return true;
	}
}
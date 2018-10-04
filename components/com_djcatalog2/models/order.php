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

jimport('joomla.application.component.modelitem');

class Djcatalog2ModelOrder extends JModelAdmin
{
	protected $_item = null;
	
	protected $_context = 'com_djcatalog2.order';
	
	public $delivery_methods = false;
	public $payment_methods = false;
	
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	protected function populateState()
	{
		$table = $this->getTable();
		$key = 'oid';
		
		// Get the pk of the record from the request.
		$pk = JFactory::getApplication()->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);
		
		// Load the parameters.
		$value = JComponentHelper::getParams('com_djcatalog2');
		$this->setState('params', $value);
	}
	
	public function getTable($type = 'Orders', $prefix = 'Djcatalog2Table', $config = array())
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
				$this->_db->setQuery('SELECT * FROM #__djc2_order_items WHERE order_id=\''.$item->id.'\'');
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
		
		
		$plugin = JFactory::getApplication()->getParams()->get('cart_captcha', JFactory::getConfig()->get('captcha'));
		
		if ($user->guest == false || ($plugin === 0 || $plugin === '0' || $plugin === '' || $plugin === null)) {
			$form->removeField('captcha', 'djcatalog2captcha');
		} else {
			JFactory::getApplication()->getParams()->set('captcha', $plugin);
		}
		
		$group = 'djcatalog2profile';
		$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2');
		
		if ($user->guest == false && !$salesman) {
			$form->setValue('email', $group, $user->email);
			$form->setFieldAttribute('email', 'readonly', 'true', $group);
			$form->setFieldAttribute('email', 'class', $form->getFieldAttribute('email', 'class', '', $group).' readonly', $group);
		}
		
		if (!$salesman) {
			$form->removeField('user_id', 'djcatalog2profile');
		}
		
		$form->removeField('client_type', 'djcatalog2profile');
		$form->removeField('customer_group_id', 'djcatalog2profile');
		
		$fields = array('company', 'position', 'address', 'city', 'postcode', 'country_id', 'state_id', 'vat_id', 'phone', 'fax', 'www');
		$delivery = array('company', 'address', 'city', 'postcode', 'country_id', 'state_id', 'phone');
		$message = array('customer_note', 'tos');
		
		$formFields = array(
			'djcatalog2profile' => $fields,
			'djcatalog2delivery' => $delivery,
			'djcatalog2message' => $message
		);
		
		foreach ($formFields as $group => $fields) {
			$paramSfx = '';
			switch ($group) {
				case 'djcatalog2profile' :
				case 'djcatalog2message' : $paramSfx = 'orderfield'; break;
				
				// TODO: common settings, perhaps temporarily
				//case 'djcatalog2delivery' : $paramSfx = 'deliveryfield'; break;
				case 'djcatalog2delivery' : $paramSfx = 'orderfield'; break;
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
						$form->setFieldAttribute($field, 'class', $form->getFieldAttribute($field, 'class', '', $group).' required', $group);
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
		
		$delivery_methods = $this->getDeliveryMethods();
		
		$current_delivery_id = $default_delivery_id = 0;
		
		if (empty($delivery_methods)) {
			$form->removeField('delivery_method_id', 'djcatalog2orderdetails');
			//$form->removeGroup('djcatalog2delivery');
		} else {
			$form->setFieldAttribute('delivery_method_id', 'required', true, 'djcatalog2orderdetails');
			if (!($current_delivery_id = $form->getFieldAttribute('delivery_method_id', 'value', false, 'djcatalog2orderdetails'))) {
				$default_delivery_id = $delivery_methods[array_keys($delivery_methods)[0]]->id;
				//$form->setFieldAttribute('delivery_method_id', 'default', $default_delivery_id, 'djcatalog2orderdetails');
			}
		}
		
		$current_delivery_id = ($current_delivery_id > 0) ? $current_delivery_id : $default_delivery_id;
		$payment_methods = $this->getPaymentMethods($current_delivery_id);
		
		$all_payment_methods = $current_delivery_id > 0 ? $this->getPaymentMethods(0) : $payment_methods;
		
		if (empty($payment_methods) && empty($all_payment_methods)) {
			$form->removeField('payment_method_id', 'djcatalog2orderdetails');
		} else {
			$form->setFieldAttribute('payment_method_id', 'required', true, 'djcatalog2orderdetails');
			if (empty($delivery_methods)) {
				$form->setFieldAttribute('payment_method_id', 'no_shipment', true, 'djcatalog2orderdetails');
			}
			if (!($current_payment_id = $form->getFieldAttribute('payment_method_id', 'value', false, 'djcatalog2orderdetails'))) {
				
				if (empty($payment_methods) && empty($delivery_methods)) {
					$form->removeField('payment_method_id', 'djcatalog2orderdetails');
				} else {
					$default_payment_id = (empty($payment_methods)) ? '' : $payment_methods[array_keys($payment_methods)[0]]->id;
					//$form->setFieldAttribute('payment_method_id', 'default', $default_payment_id, 'djcatalog2orderdetails');
				}
			}
		}
	}
	
	protected function loadFormData()
	{
		$app = JFactory::getApplication();
		$user_id = $app->getUserState('com_djcatalog2.checkout.user_id', null);
		$db = JFactory::getDbo();
		$data = Djcatalog2Helper::getUserProfile(JFactory::getUser($user_id)->id);
		$data = JArrayHelper::fromObject($data, false);
		$data= array('djcatalog2profile'=> $data, 'djcatalog2delivery' => array());
		
		$post_data = (array)JFactory::getApplication()->getUserState('com_djcatalog2.order.data', array());
		
		if (!empty($post_data)) {
			foreach($post_data as $k=>$v) {
				$data[$k] = $v;
			}
		}
		
		if (empty($data['djcatalog2delivery'])) {
			$db->setQuery('select * from #__djc2_orders where user_id='.(int)$user_id.' order by id desc limit 1');
			$lastOrder = $db->loadObject();
			if ($lastOrder && $user_id > 0) {
				$delFields = array('firstname', 'lastname', 'company', 'address', 'city', 'postcode', 'country_id', 'state_id', 'phone');
				$data['djcatalog2delivery']['delivery_to_billing'] = $lastOrder->delivery_to_billing;
				foreach ($delFields as $field) {
					$attr = 'delivery_' . $field;
					if (isset($lastOrder->$attr)) {
						$data['djcatalog2delivery'][$field] = $lastOrder->$attr;
					}
				}
				
			} else {
				$data['djcatalog2delivery'] = $data['djcatalog2profile'];
			}
		}

		$basket = Djcatalog2HelperCart::getInstance();
		
		if (!empty($basket->delivery) && isset($basket->delivery->id)) {
			$data['djcatalog2orderdetails']['delivery_method_id'] = $basket->delivery->id;
		}
		
		if (!empty($basket->payment) && isset($basket->payment->id)) {
			$data['djcatalog2orderdetails']['payment_method_id'] = $basket->payment->id;
		}
		
		$this->preprocessData('com_djcatalog2.order', $data);
		
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
	
	protected function prepareTable($table)
	{
		$db = JFactory::getDbo();
		
		if (empty($table->order_number)) {
			$db->setQuery('select max(order_number) from #__djc2_orders');
			$current = (int)$db->loadResult();
			$table->order_number = $current + 1;
		}
	}
	
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}
	
	public function validate($form, $data, $group = null)
	{
		$ret = parent::validate($form, $data, $group);
		
		if ($ret == false) {
			return $ret;
		}
		
		if ($group == 'djcatalog2profile' || $group == 'djcatalog2billing') {
			return $ret;
		}
		
		if (!isset( $data['djcatalog2orderdetails'])) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_INVALID_DELIVERY_METHOD'));
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_INVALID_PAYMENT_METHOD'));
			return false;
		}
		
		$db = JFactory::getDbo();
		
		$delivery = isset($data['djcatalog2orderdetails']['delivery_method_id']) ? $data['djcatalog2orderdetails']['delivery_method_id'] : 0;
		$payment = isset($data['djcatalog2orderdetails']['payment_method_id']) ? $data['djcatalog2orderdetails']['payment_method_id'] : 0;
		
		//TODO: 1. check if delivery method is required at all, e.g. intangible products, etc.
		$deliveryMethods = $this->getDeliveryMethods();
		
		if (count($deliveryMethods) > 0) {
			$basket = Djcatalog2HelperCart::getInstance();
			$tangible = false;
			foreach ($basket->items as $item) {
				if ($item->tangible) {
					$tangible = true;
					break;
				}
			}
			
			if ($tangible && !$delivery) {
				$this->setError(JText::_('COM_DJCATALOG2_ERROR_MUST_SELECT_DELIVERY_METHOD'));
				return false;
			}
		}
		
		//TODO: 2. payment method may also be not necessary, e.g. free offers, 100% discount, etc.
		
		$paymentMethods = $this->getPaymentMethods();
		
		if (!$payment && count($paymentMethods) > 0) {
			return $ret;
		}
		
		//TODO: 3. Check delivery method's & payment method's availability - for given customer group etc.
		
		$extValid = true;
		
		$db->setQuery('select m.id
				from #__djc2_payment_methods as m
				left join #__djc2_deliveries_payments as dp on dp.payment_id = m.id
				where m.published = 1 and (dp.delivery_id = '.$delivery.' or dp.payment_id is null)
				');
		$availablePayments = $db->loadColumn();
		
		if ($payment) {
			if (empty($availablePayments) || !in_array($payment, $availablePayments)) {
				$this->setError(JText::_('COM_DJCATALOG2_ERROR_INVALID_PAYMENT_METHOD'));
				$extValid = false;
			} else {
				if (!isset($paymentMethods[$payment])) {
					$this->setError(JText::_('COM_DJCATALOG2_ERROR_INVALID_PAYMENT_METHOD'));
					$extValid = false;
				} else {
					$validCountry = false;
					$validPostcode = false;
					$paymentMethod = $paymentMethods[$payment];
					$deliveryOpt = ( isset($data['djcatalog2delivery']) && isset($data['djcatalog2delivery']['delivery_to_billing']) ) ? ($data['djcatalog2delivery']['delivery_to_billing'] == 1 ? 'djcatalog2profile' : 'djcatalog2delivery') : 'djcatalog2profile';
					
					if (!empty($paymentMethod->countries) && is_array($paymentMethod->countries)) {
						$paymentCountry = isset($data[$deliveryOpt]['country_id']) ? $data[$deliveryOpt]['country_id'] : 0;
						$validCountry = (bool)($paymentCountry > 0 && in_array($paymentCountry, $paymentMethod->countries));
					} else {
						$validCountry = true;
					}
					
					if (!empty($paymentMethod->postcodes) && is_array($paymentMethod->postcodes)) {
						$paymentPostcode = isset($data[$deliveryOpt]['postcode']) ? $data[$deliveryOpt]['postcode'] : '';
						if (trim($paymentPostcode) == '') {
							$validPostcode = false;
						} else {
							if (count($paymentMethod->postcodes) == 1) {
								$validPostcode = (bool)($paymentPostcode == $paymentMethod->postcodes[0]);
							} else {
								$validPostcode = (bool)($paymentPostcode >= $paymentMethod->postcodes[0] && $paymentPostcode <= $paymentMethod->postcodes[1]);
							}
						}
					} else {
						$validPostcode = true;
					}
					
					if (!$validCountry || !$validPostcode) {
						$this->setError(JText::_('COM_DJCATALOG2_ERROR_INVALID_PAYMENT_METHOD'));
						$extValid = false;
					}
				}
			}
		} else if (count($availablePayments) > 0) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_INVALID_PAYMENT_METHOD'));
			$extValid = false;
		}
		
		if ($delivery) {
			/*$db->setQuery('select id from #__djc2_delivery_methods where published=1 and id='.(int)$delivery);
			if (!$db->loadResult()) {
				$this->setError(JText::_('COM_DJCATALOG2_ERROR_INVALID_DELIVERY_METHOD'));
				$extValid = false;
			}*/
			if (!isset($deliveryMethods[$delivery])) {
				$this->setError(JText::_('COM_DJCATALOG2_ERROR_INVALID_DELIVERY_METHOD'));
				$extValid = false;
			} else {
				$validCountry = false;
				$validPostcode = false;
				$deliveryMethod = $deliveryMethods[$delivery];
				$deliveryOpt = ( isset($data['djcatalog2delivery']) && isset($data['djcatalog2delivery']['delivery_to_billing']) ) ? ($data['djcatalog2delivery']['delivery_to_billing'] == 1 ? 'djcatalog2profile' : 'djcatalog2delivery') : 'djcatalog2profile';
				
				if (!empty($deliveryMethod->countries) && is_array($deliveryMethod->countries)) {
					$deliveryCountry = isset($data[$deliveryOpt]['country_id']) ? $data[$deliveryOpt]['country_id'] : 0;
					$validCountry = (bool)($deliveryCountry > 0 && in_array($deliveryCountry, $deliveryMethod->countries));
				} else {
					$validCountry = true;
				}
				
				if (!empty($deliveryMethod->postcodes) && is_array($deliveryMethod->postcodes)) {
					$deliveryPostcode = isset($data[$deliveryOpt]['postcode']) ? $data[$deliveryOpt]['postcode'] : '';
					if (trim($deliveryPostcode) == '') {
						$validPostcode = false;
					} else {
						if (count($deliveryMethod->postcodes) == 1) {
							$validPostcode = (bool)($deliveryPostcode == $deliveryMethod->postcodes[0]);
						} else {
							$validPostcode = (bool)($deliveryPostcode >= $deliveryMethod->postcodes[0] && $deliveryPostcode <= $deliveryMethod->postcodes[1]);
						}
					}
				} else {
					$validPostcode = true;
				}
				
				if (!$validCountry || !$validPostcode) {
					$this->setError(JText::_('COM_DJCATALOG2_ERROR_INVALID_DELIVERY_METHOD'));
					$extValid = false;
				}
			}
		}
		
		if ($extValid == false) {
			return false;
		}
		
		return $ret;
	}
	
	public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$table = $this->getTable();
		$db = JFactory::getDbo();
		
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		
		$deliveryObject = $paymentObject = $deliveryPlg = $paymentPlg = false;
		
		JPluginHelper::importPlugin('djcatalog2payment');
		JPluginHelper::importPlugin('djcatalog2delivery');
		
		if (!empty($data['delivery_method_id'])) {
			$db->setQuery('select * from #__djc2_delivery_methods where id='.(int)$data['delivery_method_id']);
			if ($deliveryObject = $db->loadObject()) {
				$params = new Registry();
				$params->loadString($deliveryObject->params, 'JSON');
				$deliveryObject->params = $params;
				$deliveryPlg = JPluginHelper::getPlugin('djcatalog2delivery', $deliveryObject->plugin);
			}
		}
		
		if (!empty($data['payment_method_id'])) {
			$db->setQuery('select * from #__djc2_payment_methods where id='.(int)$data['payment_method_id']);
			if ($paymentObject = $db->loadObject()) {
				$params = new Registry();
				$params->loadString($paymentObject->params, 'JSON');
				$paymentObject->params = $params;
				$paymentPlg = JPluginHelper::getPlugin('djcatalog2payment', $paymentObject->plugin);
			}
		}
		
		
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
			
			$deliveryRes = $dispatcher->trigger('onDJC2BeforeSaveOrder', array('com_djcatalog2.checkout.delivery', $table, $isNew, $deliveryObject));
			$paymentRes = $dispatcher->trigger('onDJC2BeforeSaveOrder', array('com_djcatalog2.checkout.payment', $table, $isNew, $paymentObject));
			
			if (in_array(false, $deliveryRes, true) || in_array(false, $paymentRes, true))
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
			
			$deliveryRes = $dispatcher->trigger('onDJC2AfterSaveOrder', array('com_djcatalog2.order.delivery', $table, $isNew, $deliveryObject));
			$paymentRes = $dispatcher->trigger('onDJC2AfterSaveOrder', array('com_djcatalog2.order.payment', $table, $isNew, $paymentObject));
			
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			
			return false;
		}
		
		$pkName = $table->getKeyName();
		$orderId = $table->$pkName;
		
		foreach($data['order_items']['item_id'] as $k => $itemId) {
			if ($itemId == 0 || $data['order_items']['item_type'][$k] != 'item') continue;
			
			$quantity = $data['order_items']['quantity'][$k];
			if (!(int)$quantity) continue;
			
			if ($data['order_items']['combination_id'][$k] == 0) {
				$db->setQuery('UPDATE #__djc2_items SET stock = stock - '.(int)$quantity.' WHERE id='.(int)$itemId);
				$db->execute();
			} else {
				$db->setQuery('UPDATE #__djc2_items_combinations SET stock = stock - '.(int)$quantity.' WHERE id='.(int)$data['order_items']['combination_id'][$k]);
				$db->execute();
			}
		}
		
		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $orderId);
		}
		$this->setState($this->getName() . '.number', $table->order_number);
		$this->setState($this->getName() . '.new', $isNew);
		
		return true;
	}
	
	public function getDeliveryMethods() {
		if ($this->delivery_methods === false) {
			
			$user	= JFactory::getUser();
			$userGroups = implode(',', $user->getAuthorisedViewLevels());
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('d.*');
			$query->from('#__djc2_delivery_methods AS d');
			$query->where('d.published=1');
			
			if(!$user->authorise('djcatalog2.salesman', 'com_djcatalog2')) {
				$query->where('access IN ('.$userGroups.')');
			}
			$query->order('d.ordering asc');
			
			$db->setQuery($query);
			$delivery_methods = $db->loadObjectList('id');
			foreach($delivery_methods as &$method) {
				$method->countries = $method->countries != '' ? explode(',', $method->countries) : null;
				$method->postcodes = $method->postcodes != '' ? explode('|', $method->postcodes) : null;
			}
			unset($method);
			
			$this->delivery_methods = count($delivery_methods) ? $delivery_methods : array();
		}
		return $this->delivery_methods;
	}
	
	public function getPaymentMethods($delivery_method_id = 0) {
		if (!is_array($this->payment_methods)) {
			$this->payment_methods = array();
		}
		
		if (!isset($this->payment_methods[$delivery_method_id])) {
			$db = JFactory::getDbo();
			
			if ($delivery_method_id > 0) {
				$query = 'select m.*
					from #__djc2_payment_methods as m
					left join #__djc2_deliveries_payments as dp on dp.payment_id = m.id
					where m.published = 1 and (dp.delivery_id = '.(int)$delivery_method_id.' or dp.payment_id is null)
					group by m.id order by m.ordering asc ';
			} else if ($delivery_method_id == '*') {
				$query = 'select m.*
					from #__djc2_payment_methods as m
					where m.published = 1
					order by m.ordering asc ';
			} else {
				$query = 'select m.*
					from #__djc2_payment_methods as m
					left join #__djc2_deliveries_payments as dp on dp.payment_id = m.id
					where m.published = 1 and dp.payment_id is null
					group by m.id order by m.ordering asc ';
			}
			
			$db->setQuery($query);
			$payment_methods = $db->loadObjectList('id');
			
			$this->payment_methods[$delivery_method_id] = count($payment_methods) ? $payment_methods : array();
		}
		return $this->payment_methods[$delivery_method_id];
	}
	
	public function getPaymentMethod($payment_method_id) {
		$db = JFactory::getDbo();
		$db->setQuery('select * from #__djc2_payment_methods where id='.(int)$payment_method_id);
		$result = $db->loadObject();
		
		if ($result) {
			$params = new Registry();
			$params->loadString(trim($result->params), 'JSON');
			$result->params = $params;
		}
		
		return $result;
	}
	
	public function changeStatus($order, $value, $notifyUser, $notifyAdmin, $statusComment = '') {
		if (empty($order->id)) {
			return false;
		}
		
		$date = JFactory::getDate();
		$db = JFactory::getDbo();
		
		$invoiceStatuses = array('C', 'P', 'F');
		
		if (empty($order->invoice_number) && in_array($value, $invoiceStatuses)) {
			$invoiceDate = (empty($order->invoice_date) || $order->invoice_date == '0000-00-00 00:00:00') ? $date->toSql(true) : $order->invoice_date;
			$paymentDate = (empty($order->payment_date) || $order->payment_date == '0000-00-00 00:00:00') ? $date->toSql(true) : $order->payment_date;
			$serviceDate = (empty($order->service_date) || $order->service_date == '0000-00-00 00:00:00') ? $date->toSql(true) : $order->service_date;
			
			//$invoiceNumber = $this->getNextInvoiceNumber($date);
			require_once JPATH_ROOT.'/components/com_djcatalog2/helpers/invoice.php';
			$invoiceCounter = 0;
			$invoiceNumber = DJCatalog2HelperInvoice::getNext($invoiceCounter, $date);
			
			$query = $db->getQuery(true);
			$query->update('#__djc2_orders');
			$query->set('status='.$db->quote($value));
			$query->set('invoice_counter='.$invoiceCounter);
			$query->set('invoice_number='.$db->quote($invoiceNumber));
			$query->set('invoice_date='.$db->quote($invoiceDate));
			$query->set('payment_date='.$db->quote($paymentDate));
			$query->set('service_date='.$db->quote($serviceDate));
			$query->where('id='.(int)$order->id);
			$db->setQuery($query);
			
			if (!$db->execute()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
			
			DJCatalog2HelperInvoice::update($invoiceCounter, $date);
		} else {
			$query = $db->getQuery(true);
			$query->update('#__djc2_orders');
			$query->set('status='.$db->quote($value));
			$query->where('id='.(int)$order->id);
			$db->setQuery($query);
			
			if (!$db->execute()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}
		
		$table = $this->getTable('Orders');
		
		if ($table->load((int)$order->id)) {
			$data = $table->getProperties();
			foreach ($data['items'] as $k=>$v) {
				$data['items'][$k] = JArrayHelper::fromObject($v);
			}
			
			$data['status_comment'] = $statusComment;
			
			return $this->_sendEmail($data, $notifyUser, $notifyAdmin);
		}
		
		return true;
	}
	
	protected function _sendEmail($order, $notifyUser, $notifyAdmin)
	{
		require_once JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'html.php';
		require_once JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'djcatalog2.php';
		
		$app		= JFactory::getApplication();
		$params 	= JComponentHelper::getParams('com_djcatalog2');
		$config 	= JFactory::getConfig();
		
		$mailfrom	= $config->get('mailfrom');
		$fromname	= $config->get('fromname');
		$sitename	= $config->get('sitename');
		
		$contact_list = $params->get('contact_list', false);
		$recipient_list = array();
		if ($contact_list !== false) {
			$recipient_list = explode(PHP_EOL, $params->get('contact_list', ''));
		}
		
		$list_is_empty = true;
		foreach ($recipient_list as $r) {
			if (strpos($r, '@') !== false) {
				$list_is_empty = false;
				break;
			}
		}
		
		if ($list_is_empty) {
			$recipient_list[] = $mailfrom;
		}
		$recipient_list = array_unique($recipient_list);
		
		$subject	= JText::sprintf('COM_DJCATALOG2_EMAIL_NEW_STATUS_SUBJECT', $order['order_number'], $sitename);
		
		$admin_sent = ($notifyAdmin == false);
		if ($notifyAdmin) {
			$admin_body = DJCatalog2HtmlHelper::getEmailTemplate($order, 'admin.order_status');
			
			$mail = JFactory::getMailer();
			
			//$mail->addRecipient($mailfrom);
			foreach($recipient_list as $recipient) {
				$mail->addRecipient($recipient);
			}
			
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($subject);
			$mail->setBody($admin_body);
			$mail->isHtml(true);
			$admin_sent = $mail->Send();
		}
		
		$client_sent = ($notifyUser == false);
		if ($notifyUser) {
			$client_body = DJCatalog2HtmlHelper::getEmailTemplate($order, 'order_status');
			$mail = JFactory::getMailer();
			$mail->addRecipient($order['email']);
			
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($subject);
			$mail->setBody($client_body);
			$mail->isHtml(true);
			$client_sent = $mail->Send();
		}
		
		return $client_sent && $admin_sent;
	}
	
	/*public function getNextInvoiceNumber($date = false) {
		$db = JFactory::getDbo();
		
		if (!$date) {
			$date = JFactory::getDate();
		}
		
		$db->setQuery('select count(*) from #__djc2_orders where (invoice_date > 0 or (invoice_number IS NOT NULL and invoice_number != "")) and year(invoice_date) = '.$db->quote($date->format('Y')));
		
		$count = $db->loadResult();
		$count++;
		
		$number = str_pad($count, 6, '0', STR_PAD_LEFT).'/'.$date->format("Y");
		
		return $number;
	}*/
	
}
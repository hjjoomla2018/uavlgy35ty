<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

class DJCatalog2ControllerCart extends JControllerLegacy
{
	
	function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	function add() {
		$app = JFactory::getApplication();
		$item_id = $app->input->getInt('item_id', 0);
		$combination_id = $app->input->getInt('combination_id', 0);
		
		//$quantity = max(1, $app->input->getInt('quantity', 0));
		$quantity = $app->input->getFloat('quantity', 0);
		
		$customisations = $app->input->get('customisation', array(), 'array');
		
		$return = base64_decode($app->input->get('return', null, 'base64'));
		
		$is_ajax = (bool)($app->input->get('ajax', null) == '1');
		
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCartRoute(), false);
		}
		
		if (!$item_id) {
			if ($is_ajax) {
				$response = array(
					'code' 		=> '400',
					'error' 	=> '1',
					'message' 	=>  JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'),
					'html'		=> '',
					'item_id'	=> $item_id
				);
				echo json_encode($response);
				$app->close();
			} else {
				$this->setRedirect($return, JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'), 'error');
				return false;
			}
		}
		
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		if ($basket->addItem($item_id, $combination_id, $quantity) == false) {
			if ($is_ajax) {
				$response = array(
					'code' 		=> '400',
					'error' 	=> '1',
					'message' 	=> JText::_('COM_DJCATALOG2_ADD_TO_CART_FAILED'),
					'html'		=> '',
					'item_id'	=> $item_id
				);
				echo json_encode($response);
				$app->close();
			} else {
				$this->setRedirect($return, JText::_('COM_DJCATALOG2_ADD_TO_CART_FAILED'), 'error');
				return false;
			}
		}
		
		$basket->saveToStorage();
		
		$item_obj = $basket->getItem($item_id, $combination_id);
		
		if (is_array($customisations) && !empty($customisations)) {
			foreach($customisations as $ckey => $customId) {
				
				$customValues = $app->input->get('customValues-'.$customId, array(), 'array');
				
				$temp = array(
					'id' => $customId,
					'data' => array()
				);
				
				if (is_array($customValues) && count($customValues)) {
					$temp['data'] = $customValues;
				}
				$customisations[$ckey] = $temp;
			}
			
			$basket->addCustomisations($customisations, $item_obj);
		}
		
		$items = $basket->getItems();
		
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$total = $basket->getTotal();
		$product_total = $basket->getProductTotal();
		
		foreach($total as $k=>$v) {
			$total[$k] =  DJCatalog2HtmlHelper::formatPrice($v, $params);
		}
		
		foreach($product_total as $k=>$v) {
			$product_total[$k] =  DJCatalog2HtmlHelper::formatPrice($v, $params);
		}
		
		if ($is_ajax) {
			$response = array(
				'code' 		=> '200',
				'error' 	=> '0',
				'message' 	=>  JText::sprintf('COM_DJCATALOG2_ADD_TO_CART_SUCCESS', $item_obj->name, JRoute::_(DJCatalogHelperRoute::getCartRoute())),
				'html'		=> '',
				'item_id'	=> $item_id,
				'item_name'	=> $item_obj->name,
				'basket_count' => count($items),
				'total' => $total,
				'product_total' => $product_total
			);
			echo json_encode($response);
			$app->close();
		}
		
		$msg = JText::sprintf('COM_DJCATALOG2_ADD_TO_CART_SUCCESS', $item_obj->name, JRoute::_(DJCatalogHelperRoute::getCartRoute()));
		$this->setRedirect($return, $msg, 'message');
		return true;
	}
	
	function add_multiple_combinations() {
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		$item_id = $app->input->getInt('item_id', 0);
		
		$quantities = $app->input->get('combination_qty', array(), 'array');
		$customisations = $app->input->get('customisation', array(), 'array');
		
		$return = base64_decode($app->input->get('return', null, 'base64'));
		
		$is_ajax = (bool)($app->input->get('ajax', null) == '1');
		
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCartRoute(), false);
		}
		
		if (!$item_id) {
			if ($is_ajax) {
				$response = array(
					'code' 		=> '400',
					'error' 	=> '1',
					'message' 	=>  JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'),
					'html'		=> '',
					'item_id'	=> $item_id
				);
				echo json_encode($response);
				$app->close();
			} else {
				$this->setRedirect($return, JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'), 'error');
				return false;
			}
		}
		
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		foreach($quantities as $combination_id => $quantity) {
			if (!$quantity) {
				unset($quantities[$combination_id]);
				continue;
			}
			
			if ($basket->addItem($item_id, $combination_id, $quantity) == false) {
				if ($is_ajax) {
					$response = array(
						'code' 		=> '400',
						'error' 	=> '1',
						'message' 	=> JText::_('COM_DJCATALOG2_ADD_TO_CART_FAILED'),
						'html'		=> '',
						'item_id'	=> $item_id
					);
					echo json_encode($response);
					$app->close();
				} else {
					$this->setRedirect($return, JText::_('COM_DJCATALOG2_ADD_TO_CART_FAILED'), 'error');
					return false;
				}
			}
		}
		
		$basket->saveToStorage();
		
		if (is_array($customisations) && !empty($customisations)) {
			foreach($customisations as $ckey => $customId) {
				
				$customValues = $app->input->get('customValues-'.$customId, array(), 'array');
				
				$temp = array(
					'id' => $customId,
					'data' => array()
				);
				
				if (is_array($customValues) && count($customValues)) {
					$temp['data'] = $customValues;
				}
				$customisations[$ckey] = $temp;
			}
			
			foreach($quantities as $combination_id => $quantity) {
				$item_obj = $basket->getItem($item_id, $combination_id);
				$basket->addCustomisations($customisations, $item_obj);
			}
		}
		
		$items = $basket->getItems();
		
		if ($is_ajax) {
			$response = array(
				'code' 		=> '200',
				'error' 	=> '0',
				'message' 	=>  JText::sprintf('COM_DJCATALOG2_ADD_TO_CART_SUCCESS_COMBINATIONS', count($quantities) , JRoute::_(DJCatalogHelperRoute::getCartRoute())),
				'html'		=> '',
				'item_id'	=> '',
				'item_name'	=> '',
				'basket_count' => count($items)
			);
			echo json_encode($response);
			$app->close();
		}
		
		$msg = JText::sprintf('COM_DJCATALOG2_ADD_TO_CART_SUCCESS_COMBINATIONS', count($quantities), JRoute::_(DJCatalogHelperRoute::getCartRoute()));
		$this->setRedirect($return, $msg, 'message');
		return true;
	}
	
	public function update() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		//$item_id = $app->input->getInt('item_id', 0);
		$sid = $app->input->getCmd('sid', 0);
		
		//$quantity = max(0, $app->input->getInt('quantity', 0));
		$quantity = $app->input->getFloat('quantity', 0);
		
		$return = base64_decode($app->input->get('return', null, 'base64'));
		
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCartRoute(), false);
		}
		
		if (!$sid) {
			$this->setRedirect($return, JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'), 'error');
			return false;
		}
		
		$msg = JText::_('COM_DJCATALOG2_UPDATE_CART_SUCCESS');
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		if (!$quantity || $quantity <= 0.0000) {
			$basket->removeItem($sid);
			$msg = JText::_('COM_DJCATALOG2_PRODUCT_REMOVED_FROM_CART');
		}
		else if ($basket->updateQuantity($sid, $quantity) == false) {
			$this->setRedirect($return, JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'), 'error');
			return false;
		}
		
		$basket->saveToStorage();
		
		$this->setRedirect($return, $msg, 'message');
		return true;
		
	}
	
	public function update_batch() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2');
		
		$quantities = $app->input->get('quantity', array(), 'array');
		$prices = $app->input->get('price', array(), 'array');
		
		$return = base64_decode($app->input->get('return', null, 'base64'));
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCartRoute(), false);
		}
		
		if (empty($quantities)) {
			$this->setRedirect($return, JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'), 'error');
			return false;
		}
		
		$attributes = $app->input->get('attribute', array(), 'array');
		
		$msg = JText::_('COM_DJCATALOG2_UPDATE_CART_SUCCESS');
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		foreach ($quantities as $item_id => $quantity) {
			$item_attr = isset($attributes[$item_id]) ? $attributes[$item_id] : null;
			if (!$quantity || $quantity <= 0.0000) {
				$basket->removeItem($item_id);
			}
			else if ($basket->updateQuantity($item_id, $quantity, $item_attr) == false) {
				$this->setRedirect($return, JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'), 'error');
				return false;
			}
			
			if (isset($prices[$item_id]) && is_numeric($prices[$item_id])) {
				$basket->prices[$item_id] = $prices[$item_id];
			}
		}
		
		if ($salesman) {
			$basket->recalculate();
		}
		
		$basket->saveToStorage();
		
		$this->setRedirect($return, $msg, 'message');
		return true;
		
	}
	
	public function remove() {
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		$sid = $app->input->getCmd('sid', 0);
		
		$return = base64_decode($app->input->get('return', null, 'base64'));
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCartRoute(), false);
		}
		
		// sid can be empty - e.g. when removing global product customisations
		/*if (!$sid) {
		 $this->setRedirect($return, JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'), 'error');
		 return false;
		 }*/
		
		$msg = JText::_('COM_DJCATALOG2_PRODUCT_REMOVED_FROM_CART');
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		if ($basket->removeItem($sid) == false) {
			$this->setRedirect($return, JText::_('COM_DJCATALOG2_UPDATE_CART_FAILED'), 'error');
			return false;
		}
		
		$basket->saveToStorage();
		
		$this->setRedirect($return, $msg, 'message');
		return true;
		
	}
	
	public function clear() {
		if (!JSession::checkToken('post') && !JSession::checkToken('get')) {
			jexit(JText::_('JINVALID_TOKEN'));
		}
		
		$app = JFactory::getApplication();
		
		$return = base64_decode($app->input->get('return', null, 'base64'));
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCartRoute(), false);
		}
		
		
		$msg = JText::_('COM_DJCATALOG2_CART_HAS_BEEN_CLEARED');
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		$basket->clear();
		
		$this->setRedirect($return, $msg, 'message');
		return true;
		
	}
	
	public function clearfree() {
		
		$app = JFactory::getApplication();
		
		$return = base64_decode($app->input->get('return', null, 'base64'));
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCartRoute(), false);
		}
		
		
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		foreach ($basket->items as $item) {
			if ($item->_prices['base']['display'] == 0.0 || !$item->onstock || $item->stock == 0.0) {
				$basket->removeItem($item->_sid);
			}
		}
		
		$basket->saveToStorage();
		
		$this->setRedirect($return, JText::_('COM_DJCATALOG2_UPDATE_CART_SUCCESS'));
		return true;
		
	}
	
	public function add_customisation() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		
		$customisations = $app->input->get('customisation', array(), 'array');
		
		$return = base64_decode($app->input->get('return', null, 'base64'));
		
		$is_ajax = (bool)($app->input->get('ajax', null) == '1');
		
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCartRoute(), false);
		}
		
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		if (is_array($customisations) && !empty($customisations)) {
			foreach($customisations as $ckey => $customId) {
				
				$customValues = $app->input->get('customValues-'.$customId, array(), 'array');
				
				$temp = array(
					'id' => $customId,
					'data' => array()
				);
				
				if (is_array($customValues) && count($customValues)) {
					$temp['data'] = $customValues;
				}
				$customisations[$ckey] = $temp;
			}
			
			$basket->addCustomisations($customisations, null);
		} else {
			if ($is_ajax) {
				$response = array(
					'code' 		=> '400',
					'error' 	=> '1',
					'message' 	=>  JText::_('COM_DJCATALOG2_CUSTOMISATION_ADD_TO_CART_ERROR'),
					'html'		=> ''
				);
				echo json_encode($response);
				$app->close();
			} else {
				$this->setRedirect($return, JText::_('COM_DJCATALOG2_CUSTOMISATION_ADD_TO_CART_ERROR'), 'error');
				return false;
			}
		}
		
		$items = $basket->getItems();
		
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$total = $basket->getTotal();
		$product_total = $basket->getProductTotal();
		
		foreach($total as $k=>$v) {
			$total[$k] =  DJCatalog2HtmlHelper::formatPrice($v, $params);
		}
		
		foreach($product_total as $k=>$v) {
			$product_total[$k] =  DJCatalog2HtmlHelper::formatPrice($v, $params);
		}
		
		if ($is_ajax) {
			$response = array(
				'code' 		=> '200',
				'error' 	=> '0',
				'message' 	=>  JText::_('COM_DJCATALOG2_CUSTOMISATION_ADD_TO_CART_SUCCESS'),
				'html'		=> '',
				'basket_count' => count($items),
				'total' => $total,
				'product_total' => $product_total
			);
			echo json_encode($response);
			$app->close();
		}
		
		$msg = JText::_('COM_DJCATALOG2_CUSTOMISATION_ADD_TO_CART_SUCCESS');
		$this->setRedirect($return, $msg, 'message');
		return true;
	}
	
	public function coupon_apply() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$nowDate = $db->Quote(JFactory::getDate()->toSql());
		
		$code = $app->input->getVar('coupon_code', '');
		$return = base64_decode($app->input->get('return', null, 'base64'));
		
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCheckoutRoute(), false);
		}
		
		// get coupon by coupon code provided by user
		$coupon = Djcatalog2HelperCoupon::getCouponByCode($code);
		
		if (!$coupon) {
			$this->setRedirect($return, JText::_('COM_DJCATALOG2_INVALID_COUPON_CODE'), 'error');
			return false;
		}
		
		// get basket object from storage
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		// try to assign a coupon to current basket
		if(!$basket->setCoupon($coupon)){
			$this->setRedirect($return, $coupon->getError(), 'error');
			return false;
		}
		
		$coupon->increaseReuse();
		
		$this->setRedirect($return, JText::_('COM_DJCATALOG2_COUPON_APPLIED'), 'message');
		
		return true;
	}
	
	public function coupon_remove() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		
		$return = base64_decode($app->input->get('return', null, 'base64'));
		if (!$return) {
			$return = JRoute::_(DJCatalogHelperRoute::getCheckoutRoute(), false);
		}
		
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		$coupon = $basket->removeCoupon();
		
		if(!$coupon){
			$this->setRedirect($return, JText::_('COM_DJCATALOG2_NO_COUPON_TO_REMOVE'), 'message');
			return false;
		}
		
		$coupon->decreaseReuse();
		
		$this->setRedirect($return, JText::_('COM_DJCATALOG2_COUPON_REMOVED'), 'message');
		return true;
	}
	
	public function getSummary() {
		$app = JFactory::getApplication();
		$delivery_id = $app->input->getInt('delivery', 0);
		$payment_id = $app->input->getInt('payment', 0);
		
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		$output = array('error'=> 0, 'data' => array());
		
		try {
			$basket->setDelivery($delivery_id);
			$basket->setPayment($payment_id);
		}
		catch (Exception $e) {
			$output['error'] = 1;
			$output['error_message'] = $e->getMessage();
		}
		
		$basket->saveToStorage();
		
		$params = Djcatalog2Helper::getParams();
		
		$payment_price = $delivery_price = 0;
		
		if (!empty($basket->delivery) && isset($basket->delivery->_prices)) {
			$delivery_price = $basket->delivery->_prices['total']['gross'];
		}
		if (!empty($basket->payment) && isset($basket->payment->_prices)) {
			$payment_price = $basket->payment->_prices['total']['gross'];
		}
		
		$output['data']['products'] = DJCatalog2HtmlHelper::formatPrice($basket->product_total['gross'], $params, false);
		$output['data']['delivery'] = DJCatalog2HtmlHelper::formatPrice($delivery_price, $params, false);
		$output['data']['payment'] = DJCatalog2HtmlHelper::formatPrice($payment_price, $params, false);
		$output['data']['total'] = DJCatalog2HtmlHelper::formatPrice($basket->total['gross'], $params, false);
		
		if (!count(array_diff(ob_list_handlers(), array('default output handler'))) || ob_get_length()) {
			@ob_clean();
		}
		
		echo json_encode($output);
		
		$app->close();
	}
	
	public function getInfo() {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$basket = Djcatalog2HelperCart::getInstance(true);
		$items = $basket->getItems();
		
		$total = $basket->getTotal();
		$product_total = $basket->getProductTotal();
		
		foreach($total as $k=>$v) {
			$total[$k] =  DJCatalog2HtmlHelper::formatPrice($v, $params);
		}
		
		foreach($product_total as $k=>$v) {
			$product_total[$k] =  DJCatalog2HtmlHelper::formatPrice($v, $params);
		}
		
		if (!count(array_diff(ob_list_handlers(), array('default output handler'))) || ob_get_length()) {
			@ob_clean();
		}
		
		$response = array(
			'code' 		=> '200',
			'error' 	=> '0',
			'html'		=> '',
			'basket_count' => count($items),
			'total' => $total,
			'product_total' => $product_total
		);
		echo json_encode($response);
		
		$app->close();
	}
	
	public function checkout() {
		
		JSession::checkToken('get') or JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		$params     = JComponentHelper::getParams('com_djcatalog2');
		$user = JFactory::getUser();
		
		if ($this->allowCheckout() == false) {
			return false;
		}
		
		if (!$user->guest || ($params->get('cart_registered', '1') == '0' && $params->get('cart_skip_login', '0') == '1')) {
			$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getCheckoutRoute() , false));
		} else {
			$this->setRedirect(JRoute::_(DJCatalog2HelperRoute::getCartRoute().'&layout=login' , false));
		}
		
		return true;
	}
	
	public function query() {
		
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = JFactory::getApplication();
		
		if ($this->allowQuery() == false) {
			return false;
		}
		
		$app->redirect(JRoute::_(DJCatalogHelperRoute::getQueryRoute(), false));
		return true;
	}
	
	public function confirm() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		if ($this->allowCheckout() == false) {
			return false;
		}
		
		$app = JFactory::getApplication();
		
		
		$date = JFactory::getDate();
		$model = $this->getModel('Order');
		$db = JFactory::getDbo();
		
		$params = Djcatalog2Helper::getParams();
		
		$post_data  = $this->input->post->get('jform', array(), 'array');
		
		$juser = JFactory::getUser();
		$salesman = $juser->authorise('djcatalog2.salesman', 'com_djcatalog2');
		$user_id = null;
		if ($salesman && !empty($post_data['djcatalog2profile'])) {
			$user_id = (int)$post_data['djcatalog2profile']['user_id'];
		}
		
		$basket = Djcatalog2HelperCart::getInstance();
		
		$items = $basket->getItems();
		
		$user = Djcatalog2Helper::getUser($user_id);
		$user_data = Djcatalog2Helper::getUserProfile($user->id);
		$user_data = JArrayHelper::fromObject($user_data);
		
		$form = $model->getForm(array(), false);
		
		if (!$form) {
			$app->enqueueMessage($model->getError(), 'error');
			return false;
		}
		
		$form_data = array();
		$fields = $form->getFieldset('basicprofile');
		foreach ($fields as $field) {
			if (isset($user_data[$field->fieldname])) {
				$form_data[$field->fieldname] = $user_data[$field->fieldname];
			}
			
			if (isset($post_data['djcatalog2profile'][$field->fieldname])) {
				$form_data[$field->fieldname] = $post_data['djcatalog2profile'][$field->fieldname];
			}
			
			if (!isset($form_data[$field->fieldname])) {
				$form_data[$field->fieldname] = null;
			}
		}
		
		$data = $post_data;
		$data['djcatalog2profile'] = $form_data;
		
		
		if (empty($data) || empty($data['djcatalog2profile'])) {
			$data['djcatalog2profile'] = $user_data;
		}
		
		if (isset($data['djcatalog2delivery']) && isset($data['djcatalog2delivery']['delivery_to_billing']) && $data['djcatalog2delivery']['delivery_to_billing'] == 1) {
			foreach ($data['djcatalog2profile'] as $k=>$v) {
				$data['djcatalog2delivery'][$k] = $v;
			}
		}
		
		if (!isset($data['djcatalog2orderdetails'])) {
			$data['djcatalog2orderdetails'] = array();
		}
		if (!isset($data['djcatalog2orderdetails']['delivery_method_id'])) {
			$data['djcatalog2orderdetails']['delivery_method_id'] = empty($basket->delivery->id) ? 0 : $basket->delivery->id;
		}
		if (!isset($data['djcatalog2orderdetails']['payment_method_id'])) {
			$data['djcatalog2orderdetails']['payment_method_id'] = empty($basket->payment->id) ? 0 : $basket->payment->id;
		}
		
		// Test whether the data is valid.
		$validData = $model->validate($form, $data);
		
		$app->setUserState('com_djcatalog2.order.data', $validData);
		
		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();
			
			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
			
			// Save the data in the session.
			$app->setUserState('com_djcatalog2.order.data', $data);
			
			// Redirect back to the quote screen.
			$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getCheckoutRoute(), false));
			
			return false;
		}
		
		if (isset($validData['djcatalog2profile'])) {
			$currentCountry = $currentVatid = null;
			if (isset($validData['djcatalog2profile']['country_id'])) {
				$currentCountry = $validData['djcatalog2profile']['country_id'];
			}
			if (isset($validData['djcatalog2profile']['vat_id'])) {
				$currentVatid = $validData['djcatalog2profile']['vat_id'];
			}
			
			if ($user_data['country_id'] != $currentCountry || $user_data['vat_id'] != $currentVatid) {
				$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getCheckoutRoute(), false), JText::_('COM_DJCATALOG2_CHECHOUT_COUNTRY_TAX_CHANGED'), 'notice');
				return false;
			}
		 }
		
		$totals = $basket->getTotal();
		$tax_rules = $user_data['tax_rules'];
		
		$app->setUserState('com_djcatalog2.order.data', $validData);
		
		$orderData = $validData['djcatalog2profile'];
		$messageData = $validData['djcatalog2message'];
		$statementsData = $validData['djcatalog2statements'];
		
		$deliveryData = isset($validData['djcatalog2delivery']) ? $validData['djcatalog2delivery'] : array();
		$orderDetailsData = isset($validData['djcatalog2orderdetails']) ? $validData['djcatalog2orderdetails'] : array();
		
		$order = array();
		
		$order['id'] 				= null;
		$order['user_id'] 			= $user->id;
		$order['token'] 			= Djcatalog2Helper::createSecureToken();
		
		$order['salesman_id'] = $salesman ? $juser->id : 0;
		
		if (($user->guest || empty($user->email)) && !empty($orderData['email'])) {
			$order['email'] 		= $orderData['email'];
		} else {
			if (!empty($quoteData['email'])) {
				$order['email'] 		= $orderData['email'];
			} else {
				$order['email'] 		= $user->email;
			}
		}
		
		$order['order_number']	 	= null;
		$order['invoice_number'] 	= null;
		$order['created_date'] 		= $date->toSql(true);
		
		$order['total'] 			= $totals['net'];
		$order['tax'] 				= $totals['tax'];
		$order['grand_total'] 		= $totals['gross'];
		
		if($basket->coupon) {
			$order['coupon_id']		= $basket->coupon->id;
			$order['coupon_code']		= $basket->coupon->code;
			$order['coupon_type']		= $basket->coupon->type;
			$order['coupon_value']		= $basket->coupon->value;
		} else if ($basket->discount_value > 0.0) {
			$order['coupon_id']       = 0;
			$order['coupon_code']     = '-';
			$order['coupon_type']     = 'other';
			$order['coupon_value']    = $basket->discount_value;
		}
		
		$order['currency'] 			= strtoupper($params->get('cart_currency', ''));
		$order['status'] 			= $user->guest ? 'N' : 'A';
		
		$order['firstname'] 		= !empty($orderData['firstname']) ? $orderData['firstname'] : '';
		$order['lastname'] 			= !empty($orderData['lastname']) ? $orderData['lastname'] : '';
		$order['company'] 			= !empty($orderData['company']) ? $orderData['company'] : '';
		$order['address'] 			= !empty($orderData['address']) ? $orderData['address'] : '';
		$order['city'] 				= !empty($orderData['city']) ? $orderData['city'] : '';
		$order['postcode'] 			= !empty($orderData['postcode']) ? $orderData['postcode'] : '';
		
		$order['position']       = !empty($orderData['position']) ? $orderData['position'] : '';
		$order['phone']          = !empty($orderData['phone']) ? $orderData['phone'] : '';
		$order['fax']            = !empty($orderData['fax']) ? $orderData['fax'] : '';
		$order['www']            = !empty($orderData['www']) ? $orderData['www'] : '';
		
		$order['country_id'] 		= !empty($orderData['country_id']) ? $orderData['country_id'] : '';
		$order['state_id'] 		= !empty($orderData['state_id']) ? $orderData['state_id'] : '';
		
		if ((empty($orderData['country_name']) || $orderData['country_name'] == '*') && !empty($orderData['country_id'])) {
			$db->setQuery('select country_name from #__djc2_countries where id='.(int)$orderData['country_id']);
			$country = $db->loadResult();
			$order['country'] = $country ? $country : '';
		} else {
			$order['country'] = !empty($orderData['country_name']) ? $orderData['country_name'] : '';
		}
		
		if ((empty($orderData['state_name']) || $orderData['state_name'] == '*') && !empty($orderData['state_id'])) {
			$db->setQuery('select name from #__djc2_countries_states where id='.(int)$orderData['state_id']);
			$state = $db->loadResult();
			$order['state'] = $state ? $state : '';
		} else {
			$order['state'] = !empty($orderData['state_name']) ? $orderData['state_name'] : '';
		}
		
		$order['vat_id'] 			= !empty($orderData['vat_id']) ? $orderData['vat_id'] : '';
		
		$order['customer_note'] 	= !empty($messageData['customer_note']) ? $messageData['customer_note'] : '';
		
		$gdpr_policy = $params->get('cart_gdpr_policy');
		if (!empty($statementsData['gdpr_policy']) && $gdpr_policy){
			$order['gdpr_policy'] = 1;
		}
		
		$gdpr_agreement = $params->get('cart_gdpr_agreement');
		if (!empty($statementsData['gdpr_agreement']) && $gdpr_agreement){
			$order['gdpr_agreement'] = 1;
		}
		
		// shipping details
		foreach($deliveryData as $k=>$v) {
			if ($k == 'delivery_to_billing') {
				$order[$k] = $v;
			} else {
				$order['delivery_'.$k] = $v;
			}
		}
		
		if ((empty($deliveryData['country_name']) || $deliveryData['country_name'] == '*') && !empty($deliveryData['country_id'])) {
			$db->setQuery('select country_name from #__djc2_countries where id='.(int)$deliveryData['country_id']);
			$country = $db->loadResult();
			$order['delivery_country'] = $country ? $country : '';
		} else {
			$order['delivery_country'] = !empty($deliveryData['country_name']) ? $deliveryData['country_name'] : '';
		}
		
		if ((empty($deliveryData['state_name']) || $deliveryData['state_name'] == '*') && !empty($deliveryData['state_id'])) {
			$db->setQuery('select name from #__djc2_countries_states where id='.(int)$deliveryData['state_id']);
			$state = $db->loadResult();
			$order['delivery_state'] = $state ? $state : '';
		} else {
			$order['delivery_state'] = !empty($deliveryData['state_name']) ? $deliveryData['state_name'] : '';
		}
		
		// order payment & delivery
		if (!empty($orderDetailsData)) {
			if (isset($orderDetailsData['delivery_method_id'])) {
				
				try {
					$basket->setDelivery((int)$orderDetailsData['delivery_method_id']);
				}
				catch (Exception $e) {
					$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getCheckoutRoute(), false), $e->getMessage(), 'error');
					return false;
				}
				
				$order['delivery_method_id'] = $orderDetailsData['delivery_method_id'];
				if (!empty($basket->delivery)) {
					$order['delivery_method'] = $basket->delivery->name;
					$order['delivery_price'] = $basket->delivery->_prices['total']['net'];
					$order['delivery_tax'] = $basket->delivery->_prices['total']['tax'];
					$order['delivery_total'] = $basket->delivery->_prices['total']['gross'];
					$order['delivery_tax_rate'] = round(Djcatalog2HelperPrice::getTaxRate($basket->delivery->tax_rule_id)/100, 4);
				}
			}
			if (isset($orderDetailsData['payment_method_id'])) {
				
				try {
					$basket->setPayment((int)$orderDetailsData['payment_method_id']);
				}
				catch (Exception $e) {
					$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getCheckoutRoute(), false), $e->getMessage(), 'error');
					return true;
				}
				
				$order['payment_method_id'] = $orderDetailsData['payment_method_id'];
				if (!empty($basket->payment)) {
					$order['payment_method'] = $basket->payment->name;
					$order['payment_price'] = $basket->payment->_prices['total']['net'];
					$order['payment_tax'] = $basket->payment->_prices['total']['tax'];
					$order['payment_total'] = $basket->payment->_prices['total']['gross'];
					$order['payment_tax_rate'] = round(Djcatalog2HelperPrice::getTaxRate($basket->payment->tax_rule_id)/100, 4);
				}
			}
		}

		$parents = array();
		foreach ($items as $item) {
			if ($item->parent_id > 0) {
				$parents[] = $item->parent_id;
			}
		}
		
		if (count($parents) > 0) {
			$itemsModel = JModelLegacy::getInstance('Items', 'Djcatalog2Model', array('ignore_request'=>true));
			$state      = $itemsModel->getState();
			$itemsModel->setState('list.start', 0);
			$itemsModel->setState('list.limit', 0);
			$itemsModel->setState('filter.catalogue',false);
			$itemsModel->setState('list.ordering', 'i.name');
			$itemsModel->setState('list.direction', 'asc');
			$itemsModel->setState('filter.parent', '*');
			$itemsModel->setState('filter.state', '3');
			
			$itemsModel->setState('filter.item_ids', $parents);
			
			$parentItems = $itemsModel->getItems();
			
			foreach ($items as $id=>$item) {
				if ($item->parent_id > 0 && isset($parentItems[$item->parent_id])) {
					$items[$id]->parent =  $parentItems[$item->parent_id];
				} else {
					$items[$id]->parent =  false;
				}
			}
		}
		
		$order_items = array();
		foreach($items as $item) {
			$record = array();
			$record['id'] = 0;
			$record['item_type'] = 'item';
			$record['item_id'] 		= $item->id;
			$record['combination_id'] 	= $item->_combination_id;
			
			if (!empty($item->parent)) {
				$item->name = $item->parent->name . ' ['.$item->name.']';
			}
			if ($comboStr = $basket->getCombinationAttributes($item, 'string_values')) {
				$item->name = $item->name .' - '.$comboStr;
			}
			
			$record['item_name'] 	= $item->name;
			$record['sku'] 			= $item->sku ? $item->sku : $item->id;
			$record['quantity'] 	= $item->_quantity;
			$record['unit'] 		= $item->_unit;
			$record['cost'] 		= $item->_prices['total']['net'];
			$record['base_cost'] 	= $item->_prices['base']['net'];
			$record['tax'] 			= $item->_prices['total']['tax'];
			$record['total'] 		= $item->_prices['total']['gross'];
			$record['tax_rate'] 	= (isset($tax_rules[$item->tax_rule_id]) && $item->tax_rule_id > 0 ) ? round(($tax_rules[$item->tax_rule_id]/100), 4) : 0;
			
			$record['additional_info'] = $basket->getItemAttributes($item, true, 'json');
			$record['combination_info'] = $basket->getCombinationAttributes($item, 'json');
			
			$order_items[] = $record;
			
			$customs = $basket->getCustomisations($item->_sid);
			if (count($customs)) {
				$customInfos = $customs;//$basket->getCustomInfos($customs);
				foreach($customInfos as $customItem) {
					$record2 = array();
					$record2['id'] = 0;
					$record2['item_type'] 	= 'customisation';
					$record2['item_id'] 	= 0;
					$record2['combination_id'] 	= 0;
					$record2['item_name'] 	= $customItem->name.' ('.$record['item_name'].')';
					$record2['sku']			= '';
					$record2['quantity'] 	= $customItem->_quantity;
					$record2['cost'] 		= $customItem->_prices['total']['net'];
					$record2['base_cost'] 	= $customItem->_prices['base']['net'];
					$record2['tax'] 			= $customItem->_prices['total']['tax'];
					$record2['total'] 		= $customItem->_prices['total']['gross'];
					$record2['tax_rate'] 	= (isset($tax_rules[$customItem->tax_rule_id]) && $customItem->tax_rule_id > 0 ) ? round(($tax_rules[$customItem->tax_rule_id]/100), 4) : 0;
					
					$record2['additional_info'] = $basket->getCustomisationData($customItem, 'json');
					$record2['combination_info'] = null;
					
					$order_items[] = $record2;
				}
			}
		}
		
		$customs = $basket->getCustomisations(0);
		if (count($customs)) {
			$customInfos = $customs;//$basket->getCustomInfos($customs);
			foreach($customInfos as $customItem) {
				$record2 = array();
				$record2['id'] = 0;
				$record2['item_type'] 	= 'customisation';
				$record2['item_id'] 	= 0;
				$record2['combination_id'] 	= 0;
				$record2['item_name'] 	= $customItem->name;
				$record2['sku']			= '';
				$record2['quantity'] 	= $customItem->_quantity;
				$record2['cost'] 		= $customItem->_prices['total']['net'];
				$record2['base_cost'] 	= $customItem->_prices['base']['net'];
				$record2['tax'] 			= $customItem->_prices['total']['tax'];
				$record2['total'] 		= $customItem->_prices['total']['gross'];
				$record2['tax_rate'] 	= (isset($tax_rules[$customItem->tax_rule_id]) && $customItem->tax_rule_id > 0 ) ? round(($tax_rules[$customItem->tax_rule_id]/100), 4) : 0;
				
				$record2['additional_info'] = $basket->getCustomisationData($customItem, 'json');
				$record2['combination_info'] = null;
				
				$order_items[] = $record2;
			}
		}
		
		$order['order_items'] = array();
		
		foreach ($order_items as $pos => $rec) {
			foreach ($rec as $key => $value) {
				if (!isset($order['order_items'][$key])) {
					$order['order_items'][$key] = array();
				}
				
				$order['order_items'][$key][] = $value;
			}
		}

		if ($model->save($order) == false) {
			$app->setUserState('com_djcatalog2.order.data', $data);
			$msg = $model->getError();
			$app->redirect(JRoute::_(DJCatalogHelperRoute::getCheckoutRoute(), false), JText::_('COM_DJCATALOG2_ORDER_STORE_ERRROR').' '.$msg);
			return false;
		}
		
		$basket->clear();
		$app->setUserState('com_users.edit.profile.redirect', null);
		
		$order['id'] = $model->getState('order.id');
		$order['order_number'] = $model->getState('order.number');
		
		$order['items'] = $order_items;
		
		if ($this->_sendEmail($order, 'order') == false) {
			$app->enqueueMessage(JText::_('COM_DJCATLAOG2_ORDER_NOTIFICATION_ERROR'), 'error');
		}
		
		//$app->redirect(JRoute::_(DJCatalogHelperRoute::getOrderRoute($order['id']), false), JText::_('COM_DJCATALOG2_ORDER_SENT'));
		$app->redirect(JRoute::_(DJCatalogHelperRoute::getOrderRoute($order['id']).'&finished=1&token='.$order['token'], false), JText::_('COM_DJCATALOG2_ORDER_SENT'));
		return true;
		
	}
	
	public function query_confirm() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		if ($this->allowQuery() == false) {
			return false;
		}
		
		$app = JFactory::getApplication();
		$date = JFactory::getDate();
		$model = $this->getModel('Query');
		$db = JFactory::getDbo();
		
		$post_data  = $app->input->post->get('jform', array(), 'array');
		$basket = Djcatalog2HelperCart::getInstance();
		$items = $basket->getItems();
		
		$user = Djcatalog2Helper::getUser();
		$user_data = Djcatalog2Helper::getUserProfile($user->id);
		$user_data = JArrayHelper::fromObject($user_data);
		
		$form = $model->getForm(array(), false);
		
		if (!$form) {
			$app->enqueueMessage($model->getError(), 'error');
			return false;
		}
		
		$form_data = array();
		$fields = $form->getFieldset('basicprofile');
		foreach ($fields as $field) {
			if (isset($user_data[$field->fieldname])) {
				$form_data[$field->fieldname] = $user_data[$field->fieldname];
			}
			
			if (isset($post_data['djcatalog2profile'][$field->fieldname])) {
				$form_data[$field->fieldname] = $post_data['djcatalog2profile'][$field->fieldname];
			}
			
			if (!isset($form_data[$field->fieldname])) {
				$form_data[$field->fieldname] = null;
			}
		}
		
		$data = $post_data;
		$data['djcatalog2profile'] = $form_data;
		
		
		if (empty($data) || empty($data['djcatalog2profile'])) {
			$data['djcatalog2profile'] = $user_data;
		}
		
		// Test whether the data is valid.
		$validData = $model->validate($form, $data);
		
		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();
			
			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
			
			// Save the data in the session.
			$app->setUserState('com_djcatalog2.query.data', $data);
			
			// Redirect back to the quote screen.
			$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getQueryRoute(), false));
			
			return false;
		}
		
		$app->setUserState('com_djcatalog2.query.data', $validData);
		
		$quoteData = $validData['djcatalog2profile'];
		$messageData = $validData['djcatalog2message'];
		$statementsData = $validData['djcatalog2statements'];
		
		$quote = array();
		
		$quote['id'] 				= null;
		$quote['user_id'] 			= $user->id;
		
		if (($user->guest || empty($user->email)) && !empty($quoteData['email'])) {
			$quote['email'] 		= $quoteData['email'];
		} else {
			if (!empty($quoteData['email'])) {
				$quote['email'] 		= $quoteData['email'];
			} else {
				$quote['email'] 		= $user->email;
			}
		}
		
		$quote['created_date'] 		= $date->toSql(true);
		
		$quote['firstname'] 		= !empty($quoteData['firstname']) ? $quoteData['firstname'] : '';
		$quote['lastname'] 			= !empty($quoteData['lastname']) ? $quoteData['lastname'] : '';
		$quote['company'] 			= !empty($quoteData['company']) ? $quoteData['company'] : '';
		$quote['address'] 			= !empty($quoteData['address']) ? $quoteData['address'] : '';
		$quote['city'] 				= !empty($quoteData['city']) ? $quoteData['city'] : '';
		$quote['postcode'] 			= !empty($quoteData['postcode']) ? $quoteData['postcode'] : '';
		
		$quote['position']       = !empty($quoteData['position']) ? $quoteData['position'] : '';
		$quote['phone']          = !empty($quoteData['phone']) ? $quoteData['phone'] : '';
		$quote['fax']            = !empty($quoteData['fax']) ? $quoteData['fax'] : '';
		$quote['www']            = !empty($quoteData['www']) ? $quoteData['www'] : '';
		
		$quote['country_id'] 		= !empty($quoteData['country_id']) ? $quoteData['country_id'] : '';
		$quote['state_id'] 			= !empty($quoteData['state_id']) ? $quoteData['state_id'] : '';
		
		if ((empty($quoteData['country_name']) || $quoteData['country_name'] == '*') && !empty($quoteData['country_id'])) {
			$db->setQuery('select country_name from #__djc2_countries where id='.(int)$quoteData['country_id']);
			$country = $db->loadResult();
			$quote['country'] = $country ? $country : '';
		} else {
			$quote['country'] = @$quoteData['country_name'];
		}
		
		if ((empty($quoteData['state_name']) || $quoteData['state_name'] == '*') && !empty($quoteData['state_id'])) {
			$db->setQuery('select name from #__djc2_countries_states where id='.(int)$quoteData['state_id']);
			$state = $db->loadResult();
			$quote['state'] = $state ? $state : '';
		} else {
			$quote['state'] = @$quoteData['state_name'];
		}
		
		$quote['vat_id'] 			= !empty($quoteData['vat_id']) ? $quoteData['vat_id'] : '';
		
		$quote['customer_note'] 	= !empty($messageData['customer_note']) ? $messageData['customer_note'] : '';
		
		$gdpr_agreement = $params->get('cart_gdpr_agreement');
		if (!empty($statementsData['gdpr_agreement']) && $gdpr_agreement){
			$quote['gdpr_agreement'] = 1;
		}
		
		$parents = array();
		foreach ($items as $item) {
			if ($item->parent_id > 0) {
				$parents[] = $item->parent_id;
			}
		}
		
		if (count($parents) > 0) {
			$itemsModel = JModelLegacy::getInstance('Items', 'Djcatalog2Model', array('ignore_request'=>true));
			$state      = $itemsModel->getState();
			$itemsModel->setState('list.start', 0);
			$itemsModel->setState('list.limit', 0);
			$itemsModel->setState('filter.catalogue',false);
			$itemsModel->setState('list.ordering', 'i.name');
			$itemsModel->setState('list.direction', 'asc');
			$itemsModel->setState('filter.parent', '*');
			$itemsModel->setState('filter.state', '3');
			
			$itemsModel->setState('filter.item_ids', $parents);
			
			$parentItems = $itemsModel->getItems();
			
			foreach ($items as $id=>$item) {
				if ($item->parent_id > 0 && isset($parentItems[$item->parent_id])) {
					$items[$id]->parent =  $parentItems[$item->parent_id];
				} else {
					$items[$id]->parent =  false;
				}
			}
		}
		
		$quote_items = array();
		foreach($items as $item) {
			$record = array();
			$record['id'] = 0;
			$record['item_type'] 	= 'item';
			$record['item_id'] 		= $item->id;
			$record['combination_id'] 	= $item->_combination_id;
			$record['sku'] 			= $item->sku ? $item->sku : $item->id;
			
			if (!empty($item->parent)) {
				$item->name = $item->parent->name . ' ['.$item->name.']';
			}
			if ($comboStr = $basket->getCombinationAttributes($item, 'string_values')) {
				$item->name = $item->name .' - '.$comboStr;
			}
			
			$record['price'] 		= $item->final_price;
			$record['total'] 		= $record['price'] * $item->_quantity;
			
			$record['item_name'] 	= $item->name;
			$record['quantity'] 	= $item->_quantity;
			$record['unit'] 		= $item->_unit;
			
			$record['additional_info'] = $basket->getItemAttributes($item, true, 'json');
			$record['combination_info'] = $basket->getCombinationAttributes($item, 'json');
			
			$quote_items[] = $record;
			
			$customs = $basket->getCustomisations($item->_sid);
			if (count($customs)) {
				$customInfos = $customs;//$basket->getCustomInfos($customs);
				foreach($customInfos as $customItem) {
					$record2 = array();
					$record2['id'] = 0;
					$record2['item_type'] 	= 'customisation';
					$record2['item_id'] 	= 0;
					$record2['combination_id'] 	= 0;
					$record2['item_name'] 	= $customItem->name.' ('.$record['item_name'].')';
					$record2['sku']			= '';
					$record2['quantity'] 	= $customItem->_quantity;
					$record2['price'] 		= $customItem->price;
					$record2['total'] 		= $record2['price'] * $customItem->_quantity;
					
					$record2['additional_info'] = $basket->getCustomisationData($customItem, 'json');
					$record2['combination_info'] = null;
					
					$quote_items[] = $record2;
				}
			}
		}
		
		$customs = $basket->getCustomisations(0);
		if (count($customs)) {
			$customInfos = $customs;//$basket->getCustomInfos($customs);
			foreach($customInfos as $customItem) {
				$record2 = array();
				$record2['id'] = 0;
				$record2['item_type'] 	= 'customisation';
				$record2['item_id'] 	= 0;
				$record2['combination_id'] 	= 0;
				$record2['item_name'] 	= $customItem->name;
				$record2['sku']			= '';
				$record2['quantity'] 	= $customItem->_quantity;
				$record2['price'] 		= $customItem->price;
				$record2['total'] 		= $record2['price'] * $customItem->_quantity;
				
				$record2['additional_info'] = $basket->getCustomisationData($customItem, 'json');
				$record2['combination_info'] = null;
				
				$quote_items[] = $record2;
			}
		}
		
		$quote['quote_items'] = array();
		
		foreach ($quote_items as $pos => $rec) {
			foreach ($rec as $key => $value) {
				if (!isset($quote['quote_items'][$key])) {
					$quote['quote_items'][$key] = array();
				}
				
				$quote['quote_items'][$key][] = $value;
			}
		}
		
		if ($model->save($quote) == false) {
			$msg = $model->getError();
			$app->redirect(JRoute::_(DJCatalogHelperRoute::getQueryRoute(), false), JText::_('COM_DJCATALOG2_QUOTE_STORE_ERRROR').' '.$msg);
			return false;
		}
		
		$basket->clear();
		
		$quote['id'] = $model->getState('query.id');
		
		$quote['items'] = $quote_items;
		
		if ($this->_sendEmail($quote, 'query') == false) {
			$app->enqueueMessage(JText::_('COM_DJCATLAOG2_QUOTE_NOTIFICATION_ERROR'), 'error');
		}
		
		if ($user->guest) {
			$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getCartRoute(), false), JText::_('COM_DJCATALOG2_QUOTE_SENT'));
		} else {
			$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getQuestionRoute($quote['id']), false), JText::_('COM_DJCATALOG2_QUOTE_SENT'));
		}
		
		return true;
		
	}
	
	protected function allowCheckout() {
		$app = JFactory::getApplication();
		
		$juser = JFactory::getUser();
		$salesman = $juser->authorise('djcatalog2.salesman', 'com_djcatalog2') || $juser->authorise('core.admin', 'com_djcatalog2');
		
		$user_profile = Djcatalog2Helper::getUserProfile($app->getUserState('com_djcatalog2.checkout.user_id', null));
		$user = Djcatalog2Helper::getUser($app->getUserState('com_djcatalog2.checkout.user_id', null));
		
		$params     = JComponentHelper::getParams('com_djcatalog2');
		
		if ($params->get('cart_enabled', '1') != '1') {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		// TODO: allow guest orders - add new paramater
		$auth = ($params->get('cart_registered', '1') == '1' &&  $user->guest) ? false : true;
		
		if (!$auth) {
			//$return_url = base64_encode(DJCatalogHelperRoute::getCheckoutRoute());
			//$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
			$this->setRedirect(JRoute::_(DJCatalog2HelperRoute::getCartRoute().'&layout=login' , false));
			return false;
		}
		
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		$basket->recalculate();
		
		if (empty($basket) || !$basket->getItems()) {
			$app->redirect(JUri::base(), JText::_('COM_DJCATALOG2_CART_IS_EMPTY'));
			return false;
		}
		
		if (!$salesman) {
			$invalidCart = false;
			$nullPrices = false;
			foreach ($basket->items as $key => $item) {
				$qty = $basket->quantities[$item->_sid];
				if ($item->_prices['base']['display'] == 0.0) {
					$nullPrices = true;
				}
				else if (($qty > $item->stock && $item->onstock != 2) || !$item->onstock || $qty == 0) {
					$basket->quantities[$item->id] = $item->stock;
					if ($qty > 0 && $item->stock > 0 && $item->onstock) {
						$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_CHECKOUT_FORCE_QTY_UPDATE', $item->name));
					} else {
						$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_CHECKOUT_PRODUCT_OUT_OF_STOCK', $item->name));
						$basket->removeItem($item->_sid, true);
					}
					
					$invalidCart = true;
				}
			}
			
			
			foreach($basket->customisations as $sid => $customisations) {
				if (is_array($customisations)) {
					foreach($customisations as $custom) {
						
						$quantity = $custom->_quantity;
						
						if ($custom->type == 'c') {
							$quantity = 0;
							foreach($basket->quantities as $qty) {
								$quantity += $qty;
							}
						}
						
						if ($quantity < $custom->min_quantity) {
							$customInfo = $custom->name.' - '.JText::sprintf('COM_DJCATALOG2_CUSTOMISATION_MIN_NOTE', $custom->min_quantity);
							$app->enqueueMessage($customInfo);
							$invalidCart = true;
						} else if ($custom->max_quantity > 0 && $quantity > $custom->max_quantity) {
							$customInfo = $custom->name.' - '.JText::sprintf('COM_DJCATALOG2_CUSTOMISATION_MAX_NOTE', $custom->max_quantity);
							$app->enqueueMessage($customInfo);
							$invalidCart = true;
						}
					}
				}
				
			}
			
			$total = $basket->getTotal();
			
			if ($total['net'] == 0.0000) {
				$app->enqueueMessage(JText::_('COM_DJCATALOG2_CHECKOUT_ZERO_TOTAL_BASKET'));
			} else if ($nullPrices) {
				$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_CHECKOUT_EMPTY_PRICES', JRoute::_('index.php?option=com_djcatalog2&task=cart.clearfree')));
			}
			
			if ($invalidCart) {
				$basket->recalculate();
				$basket->saveToStorage();
			}
			
			if ($nullPrices || $invalidCart) {
				$app->redirect(JRoute::_(DJCatalogHelperRoute::getCartRoute(), false));
				return true;
			}
		}
		
		/*foreach ($basket->items as $item) {
		 if ($item->_prices['base']['display'] == 0.0 || !$item->onstock || floatval($item->stock) == 0.0) {
		 $app->redirect(JRoute::_(DJCatalogHelperRoute::getCartRoute(), false), JText::sprintf('COM_DJCATALOG2_CHECKOUT_EMPTY_PRICES', JRoute::_('index.php?option=com_djcatalog2&task=cart.clearfree')));
		 return true;
		 }
		 }*/
		
		return true;
	}
	
	protected function allowQuery() {
		$app = JFactory::getApplication();
		
		$user_profile = Djcatalog2Helper::getUserProfile();
		$user = Djcatalog2Helper::getUser();
		
		$params 	= JComponentHelper::getParams('com_djcatalog2');
		
		if ($params->get('cart_query_enabled', '1') != '1') {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$auth = ($params->get('cart_query_registered', '1') == '1' && $user->guest) ? false : true;
		
		if (!$auth) {
			$return_url = base64_encode(DJCatalogHelperRoute::getQueryRoute());
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false));
			return false;
		}
		
		$basket = Djcatalog2HelperCart::getInstance(true);
		
		$basket->recalculate();
		
		if (empty($basket) || !$basket->getItems()) {
			$app->redirect(JUri::base(), JText::_('COM_DJCATALOG2_CART_IS_EMPTY'));
			return false;
		}
		
		return true;
	}
	
	private function _sendEmail($data, $type)
	{
		$app		= JFactory::getApplication();
		$params 	= JComponentHelper::getParams('com_djcatalog2');
		$notifyOwners = (bool)($params->get('cart_query_notifyowners', 0) == '1');
		
		$user = JFactory::getUser();
		
		$mailfrom	= $app->getCfg('mailfrom');
		$fromname	= $app->getCfg('fromname');
		$sitename	= $app->getCfg('sitename');
		
		$vendors = Djcatalog2Helper::getVendors($user->id);
		
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
		
		if ($user->authorise('djcatalog2.salesman', 'com_djcatalog2')) {
			$recipient_list[] = $user->email;
		}
		
		if (count($vendors) > 0) {
			foreach($vendors as $vendor) {
				$recipient_list[] = $vendor->email;
			}
		}
		
		$recipient_list = array_unique($recipient_list);
		
		$subject = null;
		$admin_body = null;
		$client_body = null;
		
		switch($type) {
			case 'order' :
				$subject = JText::sprintf('COM_DJCATALOG2_EMAIL_NEW_ORDER_SUBJECT', $data['order_number'], $sitename);
				$admin_body = DJCatalog2HtmlHelper::getEmailTemplate($data, 'admin.order');
				$client_body = DJCatalog2HtmlHelper::getEmailTemplate($data, 'order');
				break;
			case 'query' :
				$subject = JText::sprintf('COM_DJCATALOG2_EMAIL_NEW_QUOTE_SUBJECT', $sitename);
				$admin_body = DJCatalog2HtmlHelper::getEmailTemplate($data, 'admin.quote');
				$client_body = DJCatalog2HtmlHelper::getEmailTemplate($data, 'quote');
				break;
		}
		
		if (!$admin_body) {
			return false;
		}
		
		// Send admin's email first
		$mail = JFactory::getMailer();
		
		//$mail->addRecipient($mailfrom);
		foreach ($recipient_list as $recipient) {
			$mail->addRecipient(trim($recipient));
		}
		
		$mail->setSender(array($mailfrom, $fromname));
		$mail->addReplyTo($data['email'], $data['firstname'].' '.$data['lastname']);
		$mail->setSubject($subject . ' - '.$data['firstname'].' '.$data['lastname']);
		$mail->setBody($admin_body);
		$mail->isHtml(true);
		$admin_sent = $mail->Send();
		
		// Send an email to customer
		$mail = JFactory::getMailer();
		
		//$mail->addRecipient($mailfrom);
		$mail->addRecipient($data['email']);
		
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($client_body);
		$mail->isHtml(true);
		$mail->Send();
		
		if ($notifyOwners && $type == 'query') {
			$db = JFactory::getDbo();
			$itemIds = array();
			foreach($data['items'] as $item) {
				$itemIds[] = $item['item_id'];
			}
			
			$query = $db->getQuery(true);
			$query->select('u.email, i.email as alt_email, u.id, u.name, i.id as item_id');
			$query->from('#__djc2_items AS i');
			$query->join('LEFT', '#__users AS u ON i.created_by = u.id');
			$query->where('i.id IN ('.implode(',', $itemIds).')');
			$db->setQuery($query);
			
			$owners = $db->loadObjectList('item_id');
			if (count($owners) > 0) {
				$allItems = $data['items'];
				$ownerItems = array();
				foreach($owners as $item_id => $owner) {
					if (!array_key_exists($owner->id, $ownerItems)) {
						$ownerItems[$owner->id] = array('owner'=>$owner, 'items'=>array());
					}
					foreach($allItems as $item) {
						if ($item['item_id'] == $item_id) {
							$ownerItems[$owner->id]['items'][] = $item;
						}
					}
				}
				
				foreach ($ownerItems as $ownerData) {
					if (empty($ownerData['items'])) {
						continue;
					}
					
					$owner_email = $ownerData['owner']->alt_email ? $ownerData['owner']->alt_email : $ownerData['owner']->email;
					if (trim($owner_email) == '') {
						continue;
					}
					
					$data['items'] = $ownerData['items'];
					$data['isOwner'] = true;
					
					$ownerBody = DJCatalog2HtmlHelper::getEmailTemplate($data, 'admin.quote');
					
					$mail = JFactory::getMailer();
					$mail->setSender(array($mailfrom, $fromname));
					$mail->addRecipient(trim($owner_email));
					$mail->addReplyTo($data['email'], $data['firstname'].' '.$data['lastname']);
					$mail->setSubject($subject . ' - '.$data['firstname'].' '.$data['lastname']);
					$mail->setBody($ownerBody);
					$mail->isHtml(true);
					$mail->Send();
				}
			}
			
		}
		return $admin_sent;
	}
	
	public function selectUser() {
		$app = JFactory::getApplication();
		$juser = JFactory::getUser();
		$salesman = $juser->authorise('djcatalog2.salesman', 'com_djcatalog2') || $juser->authorise('core.admin', 'com_djcatalog2');
		
		if (!$salesman) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$user_id = $app->input->getInt('user_id');
		if (!$user_id) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_INVALID_REQUEST'), 400);
		}
		
		$app->setUserState('com_djcatalog2.checkout.user_id', $user_id);
		$app->setUserState('com_djcatalog2.order.data', null);
		$basket = Djcatalog2HelperCart::getInstance(true);
		$basket->clear();
		
		$this->setRedirect(JURI::base(), JText::_('COM_DJCATALOG2_USER_SELECTED'). JFactory::getUser($user_id)->name);
		
		return true;
	}
	
	public function selectCheckoutUser() {
		$app = JFactory::getApplication();
		$juser = JFactory::getUser();
		$salesman = $juser->authorise('djcatalog2.salesman', 'com_djcatalog2') || $juser->authorise('core.admin', 'com_djcatalog2');
		
		if (!$salesman) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$user_id = $app->input->getInt('user_id');
		if (!$user_id) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_INVALID_REQUEST'), 400);
		}
		
		$app->setUserState('com_djcatalog2.checkout.user_id', $user_id);
		$app->setUserState('com_djcatalog2.order.data', null);
		
		$this->setRedirect(JRoute::_(DJCatalog2HelperRoute::getCheckoutRoute() , false), JText::_('COM_DJCATALOG2_USER_SELECTED'). JFactory::getUser($user_id)->name);
		
		return true;
	}
	
	public function changeStatus() {
		$app = JFactory::getApplication();
		$juser = JFactory::getUser();
		$salesman = $juser->authorise('djcatalog2.salesman', 'com_djcatalog2') || $juser->authorise('core.admin', 'com_djcatalog2');
		
		if (!$salesman) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$order_id = $app->input->getInt('oid');
		$status = $app->input->getCmd('status');
		
		$statuses = array('N', 'A', 'P', 'C', 'R', 'W', 'F');
		
		if (!$order_id || !$status || !in_array($status, $statuses)) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_INVALID_REQUEST'), 400);
		}
		
		$model = $this->getModel('Order');
		$order = $model->getItem($order_id);
		
		if (!$order || $order->salesman_id != $juser->id) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		if ($order->status != $status) {
			$this->setMessage(JText::_('COM_DJCATALOG2_STATUS_CHANGED_BY_SALESMAN'));
			$model->changeStatus($order, $status, true, true, JText::_('COM_DJCATALOG2_STATUS_CHANGED_BY_SALESMAN'));
		}
		
		$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getOrderRoute($order_id),false));
		return true;
	}
}
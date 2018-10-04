<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'price.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'quantity.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'coupon.php');

class Djcatalog2HelperCart {
	static $baskets = array();
	
	public $items = array();
	public $quantities = array();
	public $prices = array();
	
	public $delivery = false;
	public $payment = false;
	
	public $coupon = false;
	public $coupon_value = 0.0;
	
	public $product_total = array();
	public $total = array();
	public $sub_totals = array();
	public $product_old_total = array();
	public $attributes = null;
	public $attribute_values = array();
	public $customisations = array();
	
	protected static $tmpFiles = array();
	
	/**
	 *
	 * Retrieves or creates DJCatalog2HelperCart object
	 * @param bool $from_storage
	 * @param array $cart_items
	 * @return DJCatalog2HelperCart
	 */
	
	public static function getInstance($from_storage = true, $cart_items = array()) {
		$app = JFactory::getApplication();
		$params= Djcatalog2Helper::getParams();
		
		if ($from_storage) {
			$stored_items = $app->getUserState('com_djcatalog2.cart.items', array());
			if (empty($cart_items) && !empty($stored_items)) {
				$cart_items = $stored_items;
			} else if ($params->get('cart_cookie_enable', 1)) {
				$cookie_val = $app->input->cookie->getString('djc2cart');
				if ($cookie_val != '') {
					try {
						$cart_items = json_decode($cookie_val, true);
					} catch (Exception $e) {
						$cart_items = array();
					}
				}
			}
		}
		
		$delivery_id = $app->getUserState('com_djcatalog2.cart.delivery', false);
		$payment_id = $app->getUserState('com_djcatalog2.cart.payment', false);
		
		$hash = md5(serialize($cart_items).':'.$delivery_id.':'.$payment_id);
		
		if (isset(self::$baskets[$hash])) {
			return self::$baskets[$hash];
		}
		
		$basket = new Djcatalog2HelperCart();
		
		if (!empty($cart_items)) {
			
			$basket->items = array();
			$basket->quantities = array();
			
			JModelLegacy::addIncludePath(JPath::clean(JPATH_ROOT.'/components/com_djcatalog2/models'), 'DJCatalog2Model');
			$model = JModelLegacy::getInstance('Items', 'DJCatalog2Model', array('ignore_request'=>true));
			$itemModel = JModelLegacy::getInstance('Item', 'DJCatalog2Model', array('ignore_request'=>true));
			
			$state		= $model->getState();
			
			$model->setState('list.start', 0);
			$model->setState('list.limit', 0);
			
			$user = Djcatalog2Helper::getUserProfile($app->getUserState('com_djcatalog2.checkout.user_id', null));
			if (isset($user->customer_group_id)) {
				$model->setState('filter.customergroup', $user->customer_group_id);
			}
			
			$model->setState('filter.catalogue',false);
			$model->setState('list.ordering', 'i.name');
			$model->setState('list.direction', 'asc');
			$model->setState('filter.parent', '*');
			$model->setState('filter.state', '3');
			
			$basketIds = array();
			$ids = array();
			foreach ($cart_items as $sid => $qty) {
				$sidParts = self::parseSid($sid);
				if (!$sidParts || empty( $sidParts['id'] )) {
					continue;
				}
				$sidParts['qty'] = $qty;
				$basketIds[$sid] = $sidParts;
				$ids[] = $sidParts['id'];
			}
			
			$model->setState('filter.item_ids', $ids);
			$list = $model->getItems();
			
			if (count($list) > 0) {
				foreach ($basketIds as $sid => $sidData) {
					if (isset($list[$sidData['id']])) {
						$item = clone $list[$sidData['id']];
						$item->_sid = $sid;
						$item->_combination_id = $sidData['combination_id'];
						
						if ($item->_combination_id > 0) {
							if ($combination = $itemModel->getCombination($item->_combination_id)) {
								$item->_combination = $combination;
								$item->stock = $combination->stock;
								$item->onstock = ($item->onstock > 0) ? ($combination->stock > 0 ? 1 : 0) : 0;
								if ($combination->sku != '') {
									$item->sku = $combination->sku;
								} else if ($item->sku != '') {
									$item->sku .= '-'.$combination->id;
								}
								if ($combination->price > 0) {
									$item->final_price = $item->price = $combination->price;
								}
							} else {
								continue;
							}
						}
						
						if ($item->price_tier_modifier != '0') {
							$item->_price_tiers = $itemModel->getTierPrices($item->id);
						} else {
							$item->_price_tiers = array();
						}
						
						$unit = DJCatalog2HelperQuantity::getUnit($item->unit_id);
						$item->_unit = $unit->unit;
						
						$basket->items[$sid] = $item;
						
						$qty = DJCatalog2HelperQuantity::validateQuantity($sidData['qty'], $unit);
						$basket->quantities[$sid] = $qty;
					}
				}
			}

			if ($from_storage) {
				$stored_attributes = $app->getUserState('com_djcatalog2.cart.attributes', array());
				$stored_customisations = $app->getUserState('com_djcatalog2.cart.customisations', array());
				
				$basket->attribute_values = $stored_attributes;
				$basket->customisations = $stored_customisations;
			}
			
			$stored_prices = $app->getUserState('com_djcatalog2.cart.prices', array());
			if (!empty($stored_prices)) {
				$basket->prices = $stored_prices;
			}
		}
		
		$coupon_id = $app->getUserState('com_djcatalog2.cart.coupon', false);
		
		if ($delivery_id !== false) {
			$basket->setDelivery($delivery_id);
		}
		if ($payment_id !== false) {
			$basket->setPayment($payment_id);
		}
		if ($coupon_id !== false) {
			$coupon = Djcatalog2HelperCoupon::getCouponById($coupon_id);
			$basket->setCoupon($coupon, false);
		}	
		
		$basket->recalculate();
		
		self::$baskets[$hash] = $basket;
		
		return self::$baskets[$hash];
	}
	
	public function getTotal(){
		return $this->total;
	}
	public function getProductTotal(){
		return $this->product_total;
	}
	public function getProductOldTotal(){
		return $this->product_old_total;
	}
	public function getSubTotals(){
		return $this->total;
	}
	public function getItems(){
		return $this->items;
	}
	public function removeItem($sid, $lazy = false) {
		
		foreach ($this->items as $k=>$v) {
			if ($v->_sid == $sid) {
				unset($this->items[$k]);
			}
		}
		
		if (isset($this->quantities[$sid])) {
			unset($this->quantities[$sid]);
		}
		
		if (isset($this->attribute_values[$sid])) {
			unset($this->attribute_values[$sid]);
		}
		
		if (isset($this->customisations[$sid])) {
			unset($this->customisations[$sid]);
		}
		
		if (!$lazy) {
			$this->recalculate();
		}
		
		return true;
	}
	public function addItem($item, $combination_id = 0, $quantity = 1, $lazy = false) {
		
		if (is_scalar($item) && (int)$item > 0) {
			
			JModelLegacy::addIncludePath(JPath::clean(JPATH_ROOT.'/components/com_djcatalog2/models'), 'DJCatalog2Model');
			$model = JModelLegacy::getInstance('Items', 'DJCatalog2Model', array('ignore_request'=>true));
			
			$state		= $model->getState();
			$model->setState('list.start', 0);
			$model->setState('list.limit', 0);
			
			$user = Djcatalog2Helper::getUserProfile();
			if (isset($user->user_group_id)) {
				$model->setState('filter.customergroup', $user->user_group_id);
			}
			
			$model->setState('filter.catalogue',false);
			$model->setState('filter.parent', '*');
			$model->setState('list.ordering', 'i.name');
			$model->setState('list.direction', 'asc');
			
			$item_ids = array($item);
			
			$model->setState('filter.state', '3');
			$model->setState('filter.item_ids', $item_ids);
			
			$items = $model->getItems($item);
			if (count($items) > 0) {
				$item = current($items);
				
				$itemModel = JModelLegacy::getInstance('Item', 'DJCatalog2Model', array('ignore_request'=>true));
				
				if ($item->price_tier_modifier != '0') {
					$item->_price_tiers = $itemModel->getTierPrices($item->id);
				} else {
					$item->_price_tiers = array();
				}
			}
		}
		
		if (!is_object($item) || $item->available != 1) {
			$this->recalculate();
			return false;
		}
		
		// if a product has combinations and a combination has not been specified,
		// then product cannot be added to cart
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')->from('#__djc2_items_combinations')->where('item_id='.(int)$item->id);
		$db->setQuery($query);
		$possibleCombinations = $db->loadColumn();
		
		if (count($possibleCombinations) && !$combination_id) {
			$this->recalculate();
			return false;
		} else if ($combination_id && !in_array($combination_id, $possibleCombinations)) {
			$this->recalculate();
			return false;
		}
		
		//$item_id = $item->id;
		$sid = static::getSid($item->id, $combination_id);
		
		foreach ($this->items as $k=>$v) {
			if (isset($v->_sid) && $v->_sid == $sid) {
				unset($this->items[$k]);
			}
		}
		
		$unit = DJCatalog2HelperQuantity::getUnit($item->unit_id);
		$item->_unit = $unit->unit;
		
		$this->items[$sid] = clone $item;
		$this->items[$sid]->_sid = $sid;
		$this->items[$sid]->_combination_id = $combination_id;
		
		
		if (isset($this->quantities[$sid])) {
			$quantity += (($unit->is_int) ? (int)$this->quantities[$sid] : floatval($this->quantities[$sid]) + 0);
		}

		$quantity = DJCatalog2HelperQuantity::validateQuantity($quantity, $unit);
		
		$this->quantities[$sid] = $quantity;
		
		
		if (!$lazy) {
			$this->recalculate();
		}
		
		return true;
	}
	
	public function getItem($item_id, $combination_id = 0) {
		$sid = static::getSid($item_id, $combination_id);
		return $this->getItemBySid($sid);
	}
	
	public function getItemBySid($sid) {
		if (!$sid || !isset($this->items[$sid])) {
			return false;
		}
		
		return $this->items[$sid];
	}
	
	public static function getSid($item_id, $combination_id = 0) {
		return $item_id.'.'.$combination_id;
	}
	
	public static function parseSid($sid) {
		$parts = explode('.', $sid);
		if (count($parts) < 2) {
			return false;
		}
		$retVal = array(
			'id' => $parts[0],
			'combination_id' => $parts[1]
		);
		
		return $retVal;
	}
	
	public function getCombinationAttributes($item, $format = 'array') {
		
		if (empty($item->_combination) || empty($item->_combination->fields)) {
			return $format == 'array' ? array() : '';
		}
		
		$data = JArrayHelper::fromObject($item->_combination);
		$fields = array_values($data['fields']);
		
		if ($format == 'string' || $format == 'string_values') {
			$pairs = array();
			foreach($fields as $field) {
				$pairs[] = $format == 'string_values' ? $field['field_value'] : $field['field_name'].': '.$field['field_value'];
			}
			return implode(', ', $pairs);
		}
		
		return ($format == 'array') ? $fields : json_encode($fields);
	}
	
	public function getItemAttributes($item, $translate = false, $format = 'array') {
		if (!$item || !isset($this->items[$item->_sid])) {
			return false;
		}
		
		if (!isset($this->attribute_values[$item->_sid])) {
			return $format == 'array' ? array() : '';
		}
		
		$attributes = $this->attribute_values[$item->_sid];
		if ($translate) {
			$fields = $this->getAttributes();
			foreach($fields as $key=>$field) {
				$value = '';
				
				if (isset($attributes[$field->id])) {
					$value = $attributes[$field->id];
				}
				
				if (!empty($value)) {
					switch ($field->type) {
						case 'text':
						case 'textarea':
						case 'calendar': {
							$attributes[$field->id] = $value;
							break;
						}
						case 'select':
						case 'radio':
						case 'checkbox': {
							$selected = (is_array($value)) ? $value : array($value);
							$values = array();
							foreach($field->optionlist as $option) {
								if (in_array($option->id, $selected)) {
									$values[] = $option->value;
								}
							}
							
							$attributes[$field->id] = implode(', ', $values);
							break;
						}
					}
				}
			}
			
			if ($format == 'json') {
				$output = array();
				foreach($attributes as $key => $attribute) {
					if (isset($fields[$key])) {
						$output[$fields[$key]->name] = $attribute;
					}
				}
				$attributes = json_encode($output);
			}
		}
		
		return $attributes;
	}
	
	public function addCustomisations($customisations, $item) {
		if (count($customisations) < 1 /*|| empty($item)*/)  {
			return false;
		}
		
		$app = JFactory::getApplication();
		
		JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models', 'DJCatalog2Model');
		$itemModel = JModelLegacy::getInstance('Item', 'Djcatalog2Model', array('ignore_request'=>true));
		
		$individualCustoms = (!empty($item)) ? $itemModel->getCustomisations($item->id) : array();
		$commonCustoms = $itemModel->getCustomisations(0);
		
		$availCustoms = array_merge($individualCustoms, $commonCustoms);
		
		if (count($availCustoms) < 1) {
			return false;
		}
		
		$cartCustoms = array();
		$itemCustoms = array();
		
		$formData = array(
			'customisation' => array()
		);
		
		foreach ($availCustoms as $availCustom) {
			foreach($customisations as $custom) {
				if ($custom['id'] == $availCustom->_cid) {
					
					$formData['customisation'][] = $availCustom->_cid;
					
					$customisation = array(
						'id' => $availCustom->customisation_id,
						'name' => $availCustom->name,
						'type' => $availCustom->type,
						'price' => $availCustom->price,
						'tax_rule_id' => $availCustom->tax_rule_id,
						'price_modifier' => $availCustom->price_modifier,
						'required' => $availCustom->required,
						'min_quantity' => $availCustom->min_quantity,
						'max_quantity' => $availCustom->max_quantity,
						'item_id' => (!empty($item) && $availCustom->type != 'c') ? $item->id : 0
					);
					
					$customisation = JArrayHelper::toObject($customisation);
					$customisation->data = array();
					
					foreach ($availCustom->input_params as $ik => $inputParam) {
						$input = JArrayHelper::toObject($inputParam);
						
						if (isset($custom['data'][$ik])) {
							$customisation->data[$ik] = array(
								'id' => $ik,
								'name' => $input->label,
								'type' => $input->type,
								'value' => $custom['data'][$ik]
							);
						}
						
						$formData['customValues-'.$availCustom->_cid.'['.$ik.']'] = $custom['data'][$ik];
					}
					
					if ($availCustom->type == 'c') {
						$cartCustoms[] = $customisation;
					} else {
						$itemCustoms[] = $customisation;
					}
				}
			}
		}
		
		$this->customisations[$item->_sid] = (count($itemCustoms) > 0) ? $itemCustoms : null;
		$this->customisations[0] = (count($cartCustoms) > 0) ? $cartCustoms : null;
		
		$app->setUserState('com_djcatalog2.recent_customisation', $formData);
		
		$this->saveToStorage();
		
		return true;
	}
	
	public function getCustomisations($sid) {
		if (isset($this->customisations[$sid])) {
			return $this->customisations[$sid];
		}
		return array();
	}
	
	public function getCustomisationData($customisation, $format = 'json') {
		if (empty($customisation) || empty($customisation->data)) {
			return '';
		}
		
		$data = array();
		foreach($customisation->data as $k => $custom) {
			
			$cData = new stdClass();
			$cData->name = $custom['name'];
			$cData->type = $custom['type'];
			$cData->value = trim($custom['value']);
			
			if ($cData->value == '') {
				$data[] = $cData;
				continue;
			}
			
			if ($cData->type == 'file') {
				$fData = json_decode($cData->value);
				if (empty($fData) || !is_array($fData)) {
					continue;
				}
				
				foreach($fData as $file) {
					if (!isset(static::$tmpFiles[$file->id])) {
						$upFile =  self::prepareCustomisationFile($file);
						
						static::$tmpFiles[$file->id] = $upFile;
					}
					$cData->value = static::$tmpFiles[$file->id];
				}
			}
			
			$data[] = $cData;
		}
		
		return ($format == 'json') ? json_encode($data) : '';
	}
	
	protected static function prepareCustomisationFile($file) {
		$app = JFactory::getApplication();
		$uploaded = $app->getUserState('com_djcatalog2.customisation_files', array());
		
		if (is_array($uploaded) && array_key_exists($file->id, $uploaded)) {
			if (JFile::exists(JPath::clean($uploaded[$file->id]->fullpath))) {
				return $uploaded[$file->id];
			}
		}
		
		$source = JPath::clean(JPATH_ROOT . '/media/djcatalog2/tmp/'.$file->fullname);
		$destination = JPath::clean( DJCATATTFOLDER.'/customisation' );
		
		if (!JFolder::exists($destination)) {
			JFolder::create($destination, 0755);
		}
		
		$ext = JFile::getExt($file->fullname);
		$newName = $file->caption.'.' . $file->id . '.' . $ext;
		$newName = JString::strtolower(DJCatalog2FileHelper::createFileName($newName, $destination));
		$newPath = $destination . '/' . $newName;
		
		if (JFile::copy($source, $newPath)) {
			$file->file_id = md5($file->id.':'.$file->caption.':'.$file->fullname);
			$file->fullname = $newName;
			$file->url = 'media/djcatalog2/files/customisation/'.$newName;
			$file->size = filesize($newPath);
			$file->path = 'media/djcatalog2/files/customisation';
			$file->fullpath = $file->path.'/'.$file->fullname;
			
			$uploaded[$file->id] = $file;
			$app->setUserState('com_djcatalog2.customisation_files', $uploaded);
			
			return $file;
		}
		
		if (isset($uploaded[$file->id])) {
			unset($uploaded[$file->id]);
		}
		
		$app->setUserState('com_djcatalog2.customisation_files', $uploaded);
		
		return false;
	}
	
	public function updateQuantity($item_id, $quantity, $attributes = null) {
		if (!isset($this->quantities[$item_id])) {
			if (!$this->addItem($item_id, 0, $quantity)) {
				return false;
			}
		} else {
			$this->quantities[$item_id] = $quantity;
		}
		
		if (is_array($attributes) && count($attributes) > 0) {
			$attributes = $this->validateAttributes($attributes);
			$this->attribute_values[$item_id] = $attributes;
		}
		
		$this->recalculate();
		
		return true;
	}
	
	public function validateAttributes($attributes) {
		$fields = $this->getAttributes();
		foreach($attributes as $key => $attribute) {
			if (!isset($fields[$key])) {
				unset($attributes[$key]);
				continue;
			}
			
			if (is_array($attribute)) {
				if (isset($fields[$key]->optionlist) && count($fields[$key]->optionlist)) {
					foreach($attribute as $opt_key => $option) {
						$exist = false;
						foreach ($fields[$key]->optionlist as $field_option) {
							if ($field_option->id == $option) {
								$exist = true;
								break;
							}
						}
						if (!$exist) {
							unset($attribute[$opt_key]);
						}
					}
				} else {
					unset($attributes[$key]);
				}
				
				if (count($attribute) < 1) {
					unset($attributes[$key]);
				}
			} else {
				if (isset($fields[$key]->optionlist) && count($fields[$key]->optionlist)) {
					$exist = false;
					foreach ($fields[$key]->optionlist as $field_option) {
						if ($field_option->id == $attribute) {
							$exist = true;
							break;
						}
					}
					if (!$exist) {
						unset($attributes[$key]);
					}
				}
			}
		}
		
		return $attributes;
	}
	
	public function setDelivery($delivery_id) {
		if ($delivery_id == 0) {
			$this->delivery = false;
			return false;
		}
		$db = JFactory::getDbo();
		$db->setQuery('select * from #__djc2_delivery_methods where id ='.(int)$delivery_id.' and published=1');
		$delivery = $db->loadObject();
		if (!$delivery) {
			$this->delivery = false;
			$this->recalculate();
			$this->saveToStorage();
			throw new Exception('Invalid delivery method', 500);
		}
		
		
		JPluginHelper::importPlugin('djcatalog2delivery');
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onDJC2SetDeliveryMethod', array('com_djcatalog2.cart.set_delivery', &$this, &$delivery));

		$this->delivery = $delivery;
		
		$this->recalculate();
		
		return $this->delivery;
	}
	
	public function setPayment($payment_id) {
		if ($payment_id == 0) {
			$this->payment = false;
			return false;
		}
		$db = JFactory::getDbo();
		$db->setQuery('select * from #__djc2_payment_methods where id ='.(int)$payment_id.' and published=1');
		$payment = $db->loadObject();
		if (!$payment) {
			$this->payment = false;
			$this->recalculate();
			$this->saveToStorage();
			throw new Exception('Invalid payment method', 500);
		}
		
		if ($this->delivery) {
			$db->setQuery('select delivery_id from #__djc2_deliveries_payments where payment_id='.(int)$payment_id);
			$validDeliveries = $db->loadColumn();
			if (!empty($validDeliveries) && !in_array((int)$this->delivery->id, $validDeliveries)) {
				$this->payment = false;
				$this->recalculate();
				$this->saveToStorage();
				throw new Exception('Invalid payment method', 500);
			}
		}
		
		JPluginHelper::importPlugin('djcatalog2payment');
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onDJC2SetPaymentMethod', array('com_djcatalog2.cart.set_payment', &$this, &$payment));
		
		$this->payment = $payment;
		
		$this->recalculate();
		
		return $this->payment;
	}
	
	public function setCoupon(&$coupon, $check = true) {
		
		if($this->coupon) {
			$coupon->setError(JText::_('COM_DJCATALOG2_COUPON_ALREADY_APPLIED'));
			return false;
		}
		
		// check basic and assign product restrictions
		if($check && (!$coupon->checkRestrictions() || !$coupon->checkProductRestriction())) {
			return false;
		}
		
		// restrictions are met, apply the coupon
		$this->coupon = $coupon;
		
		// recalculate the cart prices
		$this->recalculate();
		// save cart state to storage
		$this->saveToStorage();
		
		return true;
	}
	
	public function removeCoupon() {
		
		if(!$this->coupon) return false;
		
		$coupon = $this->coupon;
		$this->coupon = false;
		$this->subscription = null;
		
		// recalculate the cart prices
		$this->recalculate();
		
		// save cart state to storage
		$this->saveToStorage();
		
		return $coupon;
	}
	
	public function recalculate() {
		$params= Djcatalog2Helper::getParams();
		
		$sub_totals = array();
		$product_sub_totals = array();
		$product_sub_old_totals = array();
		
		$total =array('net'=>0, 'tax'=>0, 'gross'=>0.0);
		$product_total =array('net'=>0, 'tax'=>0, 'gross'=>0.0);
		$product_old_total =array('net'=>0, 'tax'=>0, 'gross'=>0.0);
		
		$tax_already_incl = (bool)($params->get('price_including_tax', 1) == 1);
		
		if($this->coupon) {
			$this->coupon->resetValue();
		}
		
		
		// prepare data for tier discount rules
		// i - indvidual, a - all, c - same category, p - same producer
		$tierRules = array('a' => 0, 'i' => array(), 'c' => array(), 'p' => array());
		
		foreach($this->items as $k=>&$item) {
			if (empty($item->id) || empty($item->_sid)) {
				unset($this->items[$k]);
				continue;
			}
			$item->_quantity = (isset($this->quantities[$item->_sid])) ? $this->quantities[$item->_sid] : 1;
			$item->final_price = (isset($this->prices[$item->_sid])) ? $this->prices[$item->_sid] : $item->final_price;
			
			if (!$item->tax_rule_id) {
				$item->tax_rule_id = 0;
			}
			
			$tierRules['a'] += (int)$item->_quantity;
			if (!isset($tierRules['i'][$item->id])) {
				$tierRules['i'][$item->id] = 0;
			}
			$tierRules['i'][$item->id] += (int)$item->_quantity;
			
			if (!isset($tierRules['c'][$item->cat_id])) {
				$tierRules['c'][$item->cat_id] = 0;
			}
			$tierRules['c'][$item->cat_id] += (int)$item->_quantity;
			
			if (!isset($tierRules['p'][$item->producer_id])) {
				$tierRules['p'][$item->producer_id] = 0;
			}
			$tierRules['p'][$item->producer_id] += (int)$item->_quantity;
		}
		unset($item);
		
		// apply tier discounts
		$this->applyTierPrices($tierRules);
		
		foreach($this->items as $k=>&$item) {
			$finalPrice = $item->final_price;
			$basePrice = $item->price;
			
			/*$customisations = $this->getCustomisations($item->_sid);
			 foreach($customisations as $customisation) {
			 $finalPrice += $customisation['price'];
			 $basePrice += $customisation['price'];
			 }*/
			
			if($this->coupon) {
				$finalPrice = $this->coupon->getPrice($finalPrice, $item->id, $item->_quantity);
			}
			
			$item->_prices = Djcatalog2HelperPrice::getCartPrices($finalPrice, $basePrice, $item->tax_rule_id, false,  $item->_quantity, $params);
			
			if($this->coupon) {
				if (!isset($product_sub_old_totals[$item->tax_rule_id])) {
					$product_sub_old_totals[$item->tax_rule_id] = array('net'=>0, 'tax'=>0, 'gross'=>0.0);
				}
				
				if (!isset($item->_old_prices)) {
					$item->_old_prices = Djcatalog2HelperPrice::getCartPrices($item->final_price, $item->price, $item->tax_rule_id, false,  $item->_quantity, $params);
				}
				
				$product_sub_old_totals[$item->tax_rule_id]['net'] += ($item->_old_prices['total']['net'] );
				$product_sub_old_totals[$item->tax_rule_id]['gross'] += ($item->_old_prices['total']['gross']);
				$product_sub_old_totals[$item->tax_rule_id]['tax'] += ($item->_old_prices['total']['tax']);
			}
			
			if (!isset($sub_totals[$item->tax_rule_id])) {
				$sub_totals[$item->tax_rule_id] = array('net'=>0, 'tax'=>0, 'gross'=>0.0);
			}
			
			$sub_totals[$item->tax_rule_id]['net'] += ($item->_prices['total']['net'] );
			$sub_totals[$item->tax_rule_id]['gross'] += ($item->_prices['total']['gross']);
			$sub_totals[$item->tax_rule_id]['tax'] += ($item->_prices['total']['tax']);
		}
		unset($item);
		
		if (!empty($this->customisations)) {
			
			foreach ($this->customisations as $sid => &$customOptions) {
				if (!is_array($customOptions)) {
					continue;
				}
				foreach($customOptions as &$customOption) {
					$customOption->_quantity = 1;
					
					if ($customOption->price_modifier == 'm') {
						if ($sid == 0) {
							$customOption->_quantity = 0;
							foreach($this->quantities as $qty) {
								$customOption->_quantity += $qty;
							}
						} else {
							if (isset($this->quantities[$sid])) {
								$customOption->_quantity = $this->quantities[$sid];
							}
						}
					}
					
					if (!$customOption->tax_rule_id) {
						$customOption->tax_rule_id = 0;
					}
					
					$customOption->_prices = Djcatalog2HelperPrice::getCartPrices($customOption->price, $customOption->price, $customOption->tax_rule_id, false,  $customOption->_quantity, $params);
					
					if (!isset($sub_totals[$customOption->tax_rule_id])) {
						$sub_totals[$customOption->tax_rule_id] = array('net'=>0, 'tax'=>0, 'gross'=>0.0);
					}
					
					$sub_totals[$customOption->tax_rule_id]['net'] += ($customOption->_prices['total']['net'] );
					$sub_totals[$customOption->tax_rule_id]['gross'] += ($customOption->_prices['total']['gross']);
					$sub_totals[$customOption->tax_rule_id]['tax'] += ($customOption->_prices['total']['tax']);
				}
				unset($customOption);
			}
			unset($customOptions);
		}
		
		if($this->coupon) {
			foreach ($product_sub_old_totals as $tax_rule_id => $sub_total) {
				if ($tax_already_incl) {
					$product_sub_old_totals[$tax_rule_id]['tax'] = Djcatalog2HelperPrice::calculate($sub_total['gross'], 'T', $tax_rule_id);
					$product_sub_old_totals[$tax_rule_id]['net'] = $sub_total['gross'] - $sub_total['tax'];
				} else {
					$product_sub_old_totals[$tax_rule_id]['tax'] = Djcatalog2HelperPrice::calculate($sub_total['net'], 'T', $tax_rule_id);
					$product_sub_old_totals[$tax_rule_id]['gross'] = $sub_total['net'] + $sub_total['tax'];
				}
				
				$product_old_total ['net'] += $product_sub_old_totals[$tax_rule_id]['net'];
				$product_old_total ['tax'] += $product_sub_old_totals[$tax_rule_id]['tax'];
				$product_old_total ['gross'] += $product_sub_old_totals[$tax_rule_id]['gross'];
			}
		}
		
		$product_sub_totals = $sub_totals;
		
		foreach ($product_sub_totals as $tax_rule_id => $sub_total) {
			if ($tax_already_incl) {
				$product_sub_totals[$tax_rule_id]['tax'] = Djcatalog2HelperPrice::calculate($sub_total['gross'], 'T', $tax_rule_id);
				$product_sub_totals[$tax_rule_id]['net'] = $product_sub_totals[$tax_rule_id]['gross'] - $product_sub_totals[$tax_rule_id]['tax'];
			} else {
				$product_sub_totals[$tax_rule_id]['tax'] = Djcatalog2HelperPrice::calculate($sub_total['net'], 'T', $tax_rule_id);
				$product_sub_totals[$tax_rule_id]['gross'] = $product_sub_totals[$tax_rule_id]['net'] + $product_sub_totals[$tax_rule_id]['tax'];
			}
			
			$product_total ['net'] += $product_sub_totals[$tax_rule_id]['net'];
			$product_total ['tax'] += $product_sub_totals[$tax_rule_id]['tax'];
			$product_total ['gross'] += $product_sub_totals[$tax_rule_id]['gross'];
		}
		
		if (!empty($this->delivery)) {
			$this->delivery->_quantity = 1;
			
			$deliveryPrice = ($this->delivery->free_amount > 0 && $this->delivery->free_amount <= $product_total ['gross']) ? 0.0 : $this->delivery->price;
			
			$this->delivery->_prices = Djcatalog2HelperPrice::getCartPrices($deliveryPrice, $deliveryPrice, $this->delivery->tax_rule_id, false,  $this->delivery->_quantity, $params);
			if (!$this->delivery->tax_rule_id) {
				$this->delivery->tax_rule_id = 0;
			}
			if (!isset($sub_totals[$this->delivery->tax_rule_id])) {
				$sub_totals[$this->delivery->tax_rule_id] = array('net'=>0, 'tax'=>0, 'gross'=>0.0);
			}
			
			$sub_totals[$this->delivery->tax_rule_id]['net'] += ($this->delivery->_prices['total']['net'] );
			$sub_totals[$this->delivery->tax_rule_id]['gross'] += ($this->delivery->_prices['total']['gross']);
			$sub_totals[$this->delivery->tax_rule_id]['tax'] += ($this->delivery->_prices['total']['tax']);
		}
		
		if (!empty($this->payment)) {
			$this->payment->_quantity = 1;
			
			$paymentPrice = ($this->payment->free_amount > 0 && $this->payment->free_amount <= $product_total ['gross']) ? 0.0 : $this->payment->price;
			
			$this->payment->_prices = Djcatalog2HelperPrice::getCartPrices($paymentPrice, $paymentPrice, $this->payment->tax_rule_id, false,  $this->payment->_quantity, $params);
			if (!$this->payment->tax_rule_id) {
				$this->payment->tax_rule_id = 0;
			}
			if (!isset($sub_totals[$this->payment->tax_rule_id])) {
				$sub_totals[$this->payment->tax_rule_id] = array('net'=>0, 'tax'=>0, 'gross'=>0.0);
			}
			
			$sub_totals[$this->payment->tax_rule_id]['net'] += ($this->payment->_prices['total']['net'] );
			$sub_totals[$this->payment->tax_rule_id]['gross'] += ($this->payment->_prices['total']['gross']);
			$sub_totals[$this->payment->tax_rule_id]['tax'] += ($this->payment->_prices['total']['tax']);
		}
		
		foreach ($sub_totals as $tax_rule_id => $sub_total) {
			if ($tax_already_incl) {
				$sub_totals[$tax_rule_id]['tax'] = Djcatalog2HelperPrice::calculate($sub_total['gross'], 'T', $tax_rule_id);
				$sub_totals[$tax_rule_id]['net'] = $sub_totals[$tax_rule_id]['gross'] - $sub_totals[$tax_rule_id]['tax'];
			} else {
				$sub_totals[$tax_rule_id]['tax'] = Djcatalog2HelperPrice::calculate($sub_total['net'], 'T', $tax_rule_id);
				$sub_totals[$tax_rule_id]['gross'] = $sub_totals[$tax_rule_id]['net'] + $sub_totals[$tax_rule_id]['tax'];
			}
			
			$total ['net'] += $sub_totals[$tax_rule_id]['net'];
			$total ['tax'] += $sub_totals[$tax_rule_id]['tax'];
			$total ['gross'] += $sub_totals[$tax_rule_id]['gross'];
		}
		
		$this->product_old_total = $product_old_total;
		$this->product_total = $product_total;
		$this->sub_totals = $sub_totals;
		$this->total = $total;
		
		return true;
	}
	
	public function saveToStorage() {
		$app = JFactory::getApplication();
		$params = Djcatalog2Helper::getParams();
		$app->setUserState('com_djcatalog2.cart.prices', $this->prices);
		$app->setUserState('com_djcatalog2.cart.items', $this->quantities);
		$app->setUserState('com_djcatalog2.cart.attributes', $this->attribute_values);
		
		if (!empty($this->delivery)) {
			$app->setUserState('com_djcatalog2.cart.delivery', $this->delivery->id);
		} else {
			$app->setUserState('com_djcatalog2.cart.delivery', null);
		}
		
		if (!empty($this->payment)) {
			$app->setUserState('com_djcatalog2.cart.payment', $this->payment->id);
		} else {
			$app->setUserState('com_djcatalog2.cart.payment', null);
		}
		
		$app->setUserState('com_djcatalog2.cart.coupon', @$this->coupon->id);
		
		$app->setUserState('com_djcatalog2.cart.customisations', $this->customisations);
		
		if ($params->get('cart_cookie_enable', 1)) {
			$cookie_val = json_encode($this->quantities);
			$cookie_time = (int)$params->get('cart_cookie_time', 2419200);
			$app->input->cookie->set('djc2cart', $cookie_val, (time() + $cookie_time), $app->get('cookie_path', '/'), $app->get('cookie_domain', ''));
		} else if ( $app->input->cookie->getString('djc2cart') != '' ) {
			$app->input->cookie->set('djc2cart', '', (time() - 3600), $app->get('cookie_path', '/'), $app->get('cookie_domain', ''));
		}
		
		return true;
	}
	
	public function clear() {
		$app = JFactory::getApplication();
		
		$this->items = array();
		$this->quantities = array();
		$this->prices = array();
		$this->total = array();
		$this->sub_totals = array();
		$this->product_total = array();
		$this->delivery = false;
		$this->payment = false;
		$this->attribute_values = array();
		$this->customisations = array();
		
		$app->setUserState('com_djcatalog2.cart.prices', null);
		$app->setUserState('com_djcatalog2.cart.items', null);
		$app->setUserState('com_djcatalog2.cart.attributes', null);
		$app->setUserState('com_djcatalog2.cart.delivery', null);
		$app->setUserState('com_djcatalog2.cart.payment', null);
		$app->setUserState('com_djcatalog2.cart.coupon', null);
		$app->setUserState('com_djcatalog2.cart.customisations', null);
		$app->setUserState('com_djcatalog2.recent_customisation', null);
		$app->setUserState('com_djcatalog2.customisation_files', null);
		
		$app->input->cookie->set('djc2cart', '', (time() - 3600), $app->get('cookie_path', '/'), $app->get('cookie_domain', ''));
	}
	
	public function getAttributes() {
		if (is_null($this->attributes)) {
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query->select('f.*');
			$query->from('#__djc2_cart_extra_fields AS f');
			$query->where('f.published=1');
			$query->order('f.ordering ASC');
			
			$db->setQuery($query);
			$attributes = $db->loadObjectList('id');
			
			if (count($attributes) > 0) {
				$query = $db->getQuery(true);
				$query->select('o.*');
				$query->from('#__djc2_cart_extra_fields_options AS o');
				$query->order('o.id ASC');
				
				$db->setQuery($query);
				$options = $db->loadObjectList();
				
				foreach($options as $k=>$v) {
					if (isset($attributes[$v->field_id])) {
						if (!isset($attributes[$v->field_id]->optionlist)) {
							$attributes[$v->field_id]->optionlist = array();
						}
						$attributes[$v->field_id]->optionlist[] = $v;
					}
				}
			}
			
			$this->attributes = $attributes;
		}
		
		return $this->attributes;
	}
	
	protected function applyTierPrices($rules) {
		foreach($this->items as $k=>&$item) {
			if ($item->price_tier_modifier != '0' && count($item->_price_tiers)) {
				if (isset($item->_tier_applied)) {
					continue;
				}
				$item->_tier_applied = true;
				
				$quantity = $item->_quantity;
				
				switch($item->price_tier_break) {
					case 'i': {
						if (isset($rules['i'][$item->id]) && $rules['i'][$item->id] > 0) {
							$quantity = $rules['i'][$item->id];
						}
						break;
					}
					case 'c': {
						if ($item->cat_id && isset($rules['c'][$item->cat_id]) && $rules['c'][$item->cat_id] > 0) {
							$quantity = $rules['c'][$item->cat_id];
						}
						break;
					}
					case 'p': {
						if ($item->producer_id && isset($rules['p'][$item->producer_id]) && $rules['p'][$item->producer_id] > 0) {
							$quantity = $rules['p'][$item->producer_id];
						}
						break;
					}
					case 'a': {
						if ($rules['a'] > 0) {
							$quantity = $rules['a'];
						}
						break;
					}
				}
				
				$discounts = Djcatalog2HelperPrice::getTierDiscounts($item->final_price, $item->_price_tiers, $item->price_tier_modifier, $quantity);
				$item->final_price = $discounts['price'];
			}
		}
		unset($item);
	}
}
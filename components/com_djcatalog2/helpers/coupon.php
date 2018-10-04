<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;

class Djcatalog2HelperCoupon extends JObject {
	
	static $coupons = array();
	public $coupon_value = 0.0;
	public $now_date = null;
	
	public static function getCouponById($id) {
		
		$db = JFactory::getDBO();
		$db->setQuery('select code from #__djc2_coupons where id='.(int)$id);
		$code = $db->loadResult();
		
		return self::getCouponByCode($code);
	}
	
	public static function getCouponByCode($code) {
		
		if(empty($code)) return null;
		
		$key = md5($code);
		
		//if(isset(self::$coupons[$key])) return self::$coupons[$key];
		
		$db = JFactory::getDBO();
		$now = JFactory::getDate();
		$nowDate = $db->Quote($now->toSql());
		
		$db->setQuery('SELECT * FROM #__djc2_coupons WHERE code='.$db->quote($code). ' AND published=1 AND start_date < '.$nowDate.' LIMIT 1');
		$row = $db->loadAssoc();
		//self::debug($row);
		if(empty($row)) return null;
		
		//$properties = $row->getProperties(true);
		//self::debug($properties);
		self::$coupons[$key] = JArrayHelper::toObject($row, 'Djcatalog2HelperCoupon');
		
		self::$coupons[$key]->coupon_value = self::$coupons[$key]->value;
		self::$coupons[$key]->now_date = $now;
		
		// Musi być w tym miejscu, a nie wcześniej bo JArrayHelper::toObject() pieprzy tablicę
		if (!empty(self::$coupons[$key]->product_id)) {
			$registry = new Registry;
			$registry->loadString(self::$coupons[$key]->product_id);
			$temp = (array)$registry->toArray();
			self::$coupons[$key]->product_id = array();
			for ($i=0; $i < count($temp); $i++) {
				self::$coupons[$key]->product_id[] = $temp[$i];
			}
		} else {
			self::$coupons[$key]->product_id = array();
		}
		
		if (!empty(self::$coupons[$key]->category_id)) {
			$registry = new Registry;
			$registry->loadString(self::$coupons[$key]->category_id);
			$temp = (array)$registry->toArray();
			self::$coupons[$key]->category_id = array();
			for ($i=0; $i < count($temp); $i++) {
				self::$coupons[$key]->category_id[] = $temp[$i];
			}
		} else {
			self::$coupons[$key]->category_id = array();
		}
		
		if (!empty(self::$coupons[$key]->excluded_product_id)) {
			self::$coupons[$key]->excluded_product_id = explode(',', self::$coupons[$key]->excluded_product_id);
		} else {
			self::$coupons[$key]->excluded_product_id = array();
		}
		return self::$coupons[$key];
	}
	
	public function getPrice($price, $item_id = 0, $quantity = 1) {
		
		// check product restriction
		//if(count($this->product_id) > 0 && /*$this->product_id != $item_id*/ !in_array($item_id, $this->product_id)) return $price;
		if (false == $this->checkProductAllowed($item_id)) {
			return $price;
		}
		
		if($this->type == 'percent') {
			// percentage discount
			$price -= $price * ($this->value / 100);
		} else {
			// amount discount
			$app = JFactory::getApplication();
			
			$total = $price * $quantity;
			
			if($total > $this->coupon_value) {
				$total -= $this->coupon_value;
				$price = $total / $quantity;
				$this->coupon_value = 0.0;
			} else {
				$this->coupon_value -= $total;
				$price = 0.0;
			}
		}
		
		return $price;
	}

	public function resetValue(){
		
		$this->coupon_value = $this->value;
		return true;
	}
	
	public function checkRestrictions() {
		
		$db = JFactory::getDbo();
		
		// check expire date restriction
		$expire = JFactory::getDate($this->expire_date)->toUnix();
		if($expire > 0 && $expire < JFactory::getDate($this->now_date)->toUnix()) {
			$this->setError(JText::_('COM_DJCATALOG2_COUPON_EXPIRED'));
			return false;
		}
		// check user restriction
		$user = JFactory::getUser();
		if($this->user_id && ($this->user_id != $user->id || $user->id == 0)) {
			if($user->id == 0) $this->setError(JText::_('COM_DJCATALOG2_COUPON_RESTRICT_LOGIN'));
			else $this->setError(JText::_('COM_DJCATALOG2_COUPON_RESTRICT_TO_USER'));
			return false;
		}
		// check reuse restriction
		if($this->reuse && $this->reuse_count >= $this->reuse_limit) {
			$this->setError(JText::_('COM_DJCATALOG2_COUPON_RESTRICT_REUSE'));
			return false;
		}
		// check user reuse restriction
		if($this->user_reuse) {
			if($user->id == 0) {
				$this->setError(JText::_('COM_DJCATALOG2_COUPON_RESTRICT_LOGIN'));
				return false;
			}
			$db->setQuery('SELECT count(*) FROM #__djc2_coupons_used WHERE coupon_id='.$this->id.' AND user_id='.$user->id);
			$count = $db->loadResult();
			
			if($count >= $this->user_reuse_limit) {
				$this->setError(JText::_('COM_DJCATALOG2_COUPON_RESTRICT_USER_REUSE'));
				return false;
			}
		}
		
		return true;
	}
	
	public function checkProductRestriction() {
		
		// check product restriction
		if(count($this->product_id) > 0 || count($this->category_id) > 0 || count($this->excluded_product_id) > 0) {
			$basket = Djcatalog2HelperCart::getInstance(true);
			foreach($basket->items as $item) {
				if(/*$item->id == $this->product_id*/ in_array($item->id, $this->product_id)) {
					//return true;
				}
				
				if ($this->checkProductAllowed($item->id)) {
					return true;
				}
			}
			
			$this->setError(JText::_('COM_DJCATALOG2_COUPON_RESTRICT_TO_PRODUCT'));
			return false;
		}
		
		return true;
	}
	
	public function checkProductAllowed($item_id) {
		if (is_array($this->excluded_product_id) && count($this->excluded_product_id) > 0) {
			if (in_array($item_id, $this->excluded_product_id)) {
				return false;
			}
		}
		
		$verification = (bool)(empty($this->product_id) && empty($this->category_id));
		//$verification = false;
		
		if (is_array($this->product_id) && count($this->product_id) > 0) {
			if (in_array($item_id, $this->product_id)) {
				$verification = true;
			}
		}
		
		if (is_array($this->category_id) && count($this->category_id) > 0) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)->select('category_id')->from('#__djc2_items_categories')->where('item_id='.(int)$item_id);
			$db->setQuery($query);
			$item_categories = $db->loadColumn();
			if (count($item_categories) > 0) {
				foreach ($item_categories as $item_category) {
					if (in_array($item_category, $this->category_id)) {
						$verification = true;
						break;
					}
				}
			}
		}
		
		return $verification;
	}
	
	public function increaseReuse() {
		
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$nowDate = $db->Quote($this->now_date->toSql());
		
		$db->setQuery('SELECT reuse_count FROM #__djc2_coupons WHERE id='.$this->id);
		$count = $db->loadResult();
		
		$db->setQuery('UPDATE #__djc2_coupons SET reuse_count='.($count+1).' WHERE id='.$this->id);
		if(!$db->query()){
			throw new Exception($db->getErrorMsg(), 500);
		}
		
		//if($coupon->user_reuse) {
			$db->setQuery( 'INSERT INTO #__djc2_coupons_used (coupon_id, user_id, used_date) 
							VALUES ('.$this->id.','.$user->id.','.$nowDate.')' );
			if(!$db->query()){
				throw new Exception($db->getErrorMsg(), 500);
			}
		//}
		
	}
	
	public function decreaseReuse() {
		
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		
		$db->setQuery('SELECT reuse_count FROM #__djc2_coupons WHERE id='.$this->id);
		$count = $db->loadResult();
		
		$db->setQuery('UPDATE #__djc2_coupons SET reuse_count='.($count-1).' WHERE id='.$this->id);
		if(!$db->query()){
			throw new Exception($db->getErrorMsg(), 500);
		}
		
		//if($coupon->user_reuse) {
			//$db->setQuery('SELECT id FROM #__djc2_coupons_used WHERE coupon_id='.$coupon->id.' AND user_id='.$user->id.' ORDER BY used_date DESC LIMIT 1');
			$db->setQuery('DELETE FROM #__djc2_coupons_used WHERE coupon_id='.$this->id.' AND user_id='.$user->id.' ORDER BY used_date DESC LIMIT 1');
			if(!$db->query()){
				throw new Exception($db->getErrorMsg(), 500);
			}
		//}
		
		
	}
	
	private static function debug($msg) {
		
		$app = JFactory::getApplication();
		$app->enqueueMessage("<pre>".print_r($msg, true)."</pre>");
		
	}
}
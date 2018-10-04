<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access');

class DJCatalog2HelperQuantity {
	
	static $loaded = false;
	static $units = array();
	
	public static function getUnit($unit_id) {
		if (static::$loaded == false) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djc2_units')->order('is_default DESC, ordering ASC');
			$db->setQuery($query);
			
			static::$units = $db->loadObjectList('id');
			static::$loaded = true;
			
			if (count(static::$units) == 0) {
				static::$units = array(static::createGenericUnit());
			}
		}
		
		// first unit is default
		if ($unit_id == 0 || !isset(static::$units[$unit_id])) {
			foreach(static::$units as $k=>$v) {
				$unit_id = $k;
				break;
			}
		}
		
		/*if (!isset(static::$units[$unit_id])) {
			return false;
		}*/
		
		return static::$units[$unit_id];
	}
	
	protected static function createGenericUnit() {
		$unit = new stdClass();
		
		$unit->id = 0;
		$unit->name = (JText::_('COM_DJCATALOG2_UNIT_PC') != 'COM_DJCATALOG2_UNIT_PC') ? JText::_('COM_DJCATALOG2_UNIT_PC') : 'Piece';
		$unit->unit = 'pc';
		$unit->is_int = true;
		$unit->min_quantity = 1;
		$unit->max_quantity = 0;
		$unit->step = 1;
		$unit->show_box = 1;
		$unit->show_buttons = 1;
		$unit->show_unit = false;
		
		return $unit;
	}
	
	public static function validateQuantity($quantity, $unit, $itemType = 'item') {
		if (!is_numeric($quantity)) {
			return false;
		}
		
		if (is_numeric($unit)){
			$unit = static::getUnit($unit);
		}
		
		$quantity = ($unit->is_int) ? intval($quantity) : floatval($quantity);
		
		if (static::getPrec($quantity) < static::getPrec($unit->min_quantity) || $quantity <= 0.0000) {
			return $unit->min_quantity;
		} else if (static::getPrec($quantity) > static::getPrec($unit->max_quantity) && $unit->max_quantity > 0.0000) {
			return $unit->max_quantity;
		}
		
		if (static::setPrec($unit->step) > 0) {
			$stepQty = static::getPrec(floatval($unit->min_quantity));
			$step = static::getPrec($unit->step);
			$quantity = static::getPrec($quantity);
			while ( $stepQty < $quantity ) {
				$stepQty += $step;
			}
			return $unit->is_int ? static::setPrec($stepQty) : number_format(static::setPrec($stepQty), 4);
		}
		
		return $quantity;
	}
	
	public static function renderInput($unit, $item, $options = array()) {
		$layout = new JLayoutFile('com_djcatalog2.quantity', null, array('component'=> 'com_djcatalog2'));
		return $layout->render(array('unit' => $unit, 'item' => $item, 'options' => $options));
	}
	
	public static function formatAmount($quantity, $params) {
		$decSep = null;
		$thSep = null;
		
		switch($params->get('thousand_separator',0)) {
			case 0: $thSep=''; break;
			case 1: $thSep=' '; break;
			case 2: $thSep='\''; break;
			case 3: $thSep=','; break;
			case 4: $thSep='.'; break;
			default: $thSep=''; break;
		}
		
		switch($params->get('decimal_separator',0)) {
			case 0: $decSep=','; break;
			case 1: $decSep='.'; break;
			default: $decSep=','; break;
		}
		
		if ((float)((int)$quantity) != (float)$quantity) {
			
			// reducing number of zeroes after dec point
			$precision = 0;
			$multiplier = 10;
			$tmp = $quantity;
			while ((int)$tmp != $tmp && $precision <= 4) {
				$tmp = $quantity * $multiplier;
				$multiplier *= 10;
				$precision++;
			}
			
			return number_format($quantity, $precision, $decSep, $thSep);
		}
		
		return number_format($quantity, 0, $decSep, $thSep);
	}
	
	protected static function getPrec($number) {
		return round($number*10000);
	}
	
	protected static function setPrec($number) {
		return $number/10000;
	}
}

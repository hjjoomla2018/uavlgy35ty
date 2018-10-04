<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
use Joomla\Registry\Registry;

// No direct access
defined('_JEXEC') or die;

class Djcatalog2TableCoupons extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_coupons', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		if (!in_array($array['type'], array('percent', 'amount'))) {
			$array['type'] = 'percent';
		}
		
		if (isset($array['product_id']) && is_array($array['product_id'])) {
			$registry = new Registry();
			$registry->loadArray($array['product_id']);
			$array['product_id'] = (string)$registry;
		} else if (!isset($array['product_id'])) {
			$array['product_id'] = '';
		}
		
		if (isset($array['category_id']) && is_array($array['category_id'])) {
			$registry = new Registry();
			$registry->loadArray($array['category_id']);
			$array['category_id'] = (string)$registry;
		}  else if (!isset($array['category_id'])) {
			$array['category_id'] = '';
		}
		
		$array['code'] = strtoupper($array['code']);
		
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		
		if (!$this->id) {
			if (!intval($this->created)) {
				$this->created = $date->toSql();
			}
			
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
		} else {
			$this->modified = $date->toSql();
			$this->modified_by = $user->get('id');
		}
		
		$table = JTable::getInstance('Coupons', 'Djcatalog2Table');
		if ($table->load(array('code'=>$this->code)) && ($table->id != $this->id || $this->id==0)) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_COUPON_CODE'));
			return false;
		}
		return parent::store($updateNulls);
	}
}


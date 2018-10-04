<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access
defined('_JEXEC') or die;

class Djcatalog2TableVendors extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_vendors', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		$ret = parent::bind($array, $ignore);
		
		if ($ret && isset($array['customers'])) {
			$this->customers = $array['customers'];
		}
		
		return $ret;
	}
	public function store($updateNulls = false)
	{
		$customers = $this->customers;
		unset($this->customers);
		
		$ret = parent::store($updateNulls);
		
		if ($ret) {
			$this->_db->setQuery('delete from #__djc2_vendors_customers where vendor_id='.$this->id);
			$this->_db->execute();
			
			$temp = array_values(array_unique($customers));
			if (count($temp) < 1) {
				return true;
			}

			$query = $this->_db->getQuery(true);
			$query->insert('#__djc2_vendors_customers');
			$query->columns('vendor_id, customer_id');
			
			foreach($temp as $customer) {
				if ((int)$customer) {
					$query->values($this->id.', '.(int)$customer);
				}
			}
			
			$this->_db->setQuery($query);
			if (!$this->_db->execute()){
				$this->setError($this->_db->getErrorMsg());
				return  false;
			}
			
			return true;
		}
		$this->customers = $customers;
		return $ret;
	}
}

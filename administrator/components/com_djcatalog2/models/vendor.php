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

class Djcatalog2ModelVendor extends JModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Vendors', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.vendor', 'vendor', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.vendor.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function prepareTable($table)
	{
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}

	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			if ((!isset($item->customers) || !is_array($item->customers)) && isset($item->id)){
				$this->_db->setQuery('SELECT customer_id FROM #__djc2_vendors_customers WHERE vendor_id=\''.$item->id.'\'');
				$item->customers = $this->_db->loadColumn();
			} else {
				$item->customers = array();
			}
			return $item;
		} else {
			return false;
		}
	}
	
	public function delete(&$cid) {
		if (parent::delete($cid)) {
			$cids = implode(',', $cid);
			$this->_db->setQuery("delete from #__djc2_vendors_customer WHERE vendor_id IN ( ".$cids." )");
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		return false;
	}
}
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

//jimport('joomla.application.component.modeladmin');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'modeladmin.php');

class Djcatalog2ModelPayment extends DJCJModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Payments', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.payment', 'payment', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.payment.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'djcatalog2payment')
	{
		return parent::preprocessForm($form, $data, $group);	
	}
	
	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			if ((!isset($item->deliveries) || !is_array($item->deliveries)) && isset($item->id)){
				$this->_db->setQuery('SELECT delivery_id FROM #__djc2_deliveries_payments WHERE payment_id=\''.$item->id.'\'');
				$item->deliveries = $this->_db->loadColumn();
			}
			
			if (is_string($item->countries)) {
				$item->countries = explode(',', $item->countries);
			}
				
			return $item;
		} else {
			return false;
		}
	}

	protected function _prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$db = JFactory::getDbo();

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
		
		if (empty($table->id)) {
			if (empty($table->ordering)) {
				$db->setQuery('SELECT MAX(ordering) FROM #__djc2_payment_methods');
				$max = $db->loadResult();
		
				$table->ordering = $max+1;
			}
		}
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}

	public function delete(&$cid) {
		if (parent::delete($cid)) {
			if (count( $cid )) {
				$cids = implode(',', $cid);
				$this->_db->setQuery("DELETE FROM #__djc2_deliveries_payments WHERE payment_id IN ( ".$cids." )");
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
			return true;
		}
		return false;
	}
}
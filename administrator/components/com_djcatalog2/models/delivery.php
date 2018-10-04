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


class Djcatalog2ModelDelivery extends JModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Deliveries', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.delivery', 'delivery', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);
		
		if ($item) {
			if (is_string($item->countries)) {
				$item->countries = explode(',', $item->countries);
			}
		}
		
		return $item;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.delivery.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'djcatalog2delivery')
	{
		return parent::preprocessForm($form, $data, $group);
	}

	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$db = JFactory::getDbo();

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
		
		if (empty($table->id)) {
			if (empty($table->ordering)) {
				$db->setQuery('SELECT MAX(ordering) FROM #__djc2_delivery_methods');
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
				$this->_db->setQuery("DELETE FROM #__djc2_deliveries_payments WHERE delivery_id IN ( ".$cids." )");
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
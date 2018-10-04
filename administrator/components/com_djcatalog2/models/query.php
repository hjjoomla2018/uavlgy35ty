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

class Djcatalog2ModelQuery extends JModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Quotes', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.query', 'query', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
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
				$this->_db->setQuery('SELECT * FROM #__djc2_quote_items WHERE quote_id=\''.$item->id.'\'');
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
	

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.query.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		$db = JFactory::getDbo();
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}

	public function delete(&$cid) {
		if (parent::delete($cid)) {
			
			$cids = implode(',', $cid);
			
			$this->_db->setQuery("delete from #__djc2_quote_items WHERE quote_id IN ( ".$cids." )");
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		return false;
	}
	
	public function save($data) {
		
		
		if (!parent::save($data)) {
			return false;
		}
		
		return true;
	}
}
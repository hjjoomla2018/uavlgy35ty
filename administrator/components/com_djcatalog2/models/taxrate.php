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

class Djcatalog2ModelTaxrate extends DJCJModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Taxrates', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.taxrate', 'taxrate', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.taxrate.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function _prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}

	public function delete(&$cid) {
		
		if (count( $cid ))
		{
			$cids = implode(',', $cid);
		
			$this->_db->setQuery("SELECT COUNT(*) FROM #__djc2_items WHERE tax_rate_id IN ( ".$cids." )");
			if ($this->_db->loadResult() > 0) {
				$this->setError(JText::_('COM_DJCATALOG2_DELETE_TAXRATES_HAVE_ITEMS'));
				return false;
			}
		}
		/*
		if (parent::delete($cid)) {
			
			$cids = implode(',', $cid);
			
			$this->_db->setQuery("update #__djc2_users set user_group_id = 0 WHERE user_group_id IN ( ".$cids." )");
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		return false;*/
		
		//return parent::delete($cid);
		
		if (parent::delete($cid)) {
			$cids = implode(',', $cid);
			$this->_db->setQuery('delete from #__djc2_tax_rules where tax_rate_id in ('.$cids.')');
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		
		return false;
	}
	
	/*
	public function save($data) {
		$return = parent::save($data);
		if ($return) {
			if (isset($data['is_default']) && (int)$data['is_default'] == 1) {
				$id = (int)$this->getState($this->getName() . '.id');
				if ($id > 0) {
					$db = JFactory::getDbo();
					$db->setQuery('update #__djc2_tax_rates set is_default=0 where id !='.$id);
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
			}
		}
		return $return;
	}*/
}
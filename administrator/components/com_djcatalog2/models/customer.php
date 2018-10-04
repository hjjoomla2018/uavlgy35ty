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

class Djcatalog2ModelCustomer extends DJCJModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Customers', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.customer', 'customer', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.customer.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}
		
		$app = JFactory::getApplication();
		$user_id = $app->input->getInt('user_id');
		if ($user_id && empty($data->user_id) && is_object($data)) {
			$data->user_id = $user_id;
		}
		
		return $data;
	}

	protected function _prepareTable(&$table)
	{
		/*jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
		*/
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}

	public function delete(&$cid) {
		return parent::delete($cid);
	}
	
	public function save($data) {
		return parent::save($data);
	}
}
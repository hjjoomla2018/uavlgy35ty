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

class Djcatalog2ModelCountry extends DJCJModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Countries', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.country', 'country', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.country.data', array());

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

		$table->country_name		= htmlspecialchars_decode($table->country_name, ENT_QUOTES);
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
			$this->_db->setQuery("SELECT COUNT(*) FROM #__djc2_countries WHERE is_default=1 AND id IN ( ".$cids." )");
			if ($this->_db->loadResult() > 0) {
				$this->setError(JText::_('COM_DJCATALOG2_DELETE_COUNTRIES_DEFAULT'));
				return false;
			}
				
			$this->_db->setQuery("SELECT COUNT(*) FROM #__djc2_users WHERE country_id IN ( ".$cids." )");
			if ($this->_db->loadResult() > 0) {
				$this->setError(JText::_('COM_DJCATALOG2_DELETE_COUNTRIES_HAVE_ITEMS'));
				return false;
			}
		}
		return parent::delete($cid);
	}
	
	public function save($data) {
		$return = parent::save($data);
		if ($return) {
			if (isset($data['is_default']) && (int)$data['is_default'] == 1) {
				$id = (int)$this->getState($this->getName() . '.id');
				if ($id > 0) {
					$db = JFactory::getDbo();
					$db->setQuery('update #__djc2_countries set is_default=0 where id !='.$id);
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
			}
		}
		return $return;
	}
}
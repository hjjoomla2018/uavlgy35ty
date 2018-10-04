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

class Djcatalog2TableCustomers extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_users', 'id', $db);
	}
	function bind($array, $ignore = '')
	{
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		$app = JFactory::getApplication();
		
		if (!$this->user_id) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_USER_ID_MISSING'));
			return false;
		}	
		$table = JTable::getInstance('Customers', 'Djcatalog2Table');
		
		if ($table->load(array('user_id'=>$this->user_id)) && ($table->id != $this->id || $this->id==0)) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_USER_ID'));
			return false;
		}
		
		return parent::store($updateNulls);
	}
	
}

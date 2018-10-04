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

class Djcatalog2TableTaxrates extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_tax_rates', 'id', $db);
	}
	function bind($array, $ignore = '')
	{
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		$user	= JFactory::getUser();
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();

		$table = JTable::getInstance('Taxrates', 'Djcatalog2Table');

		if ($table->load(array('name'=>$this->name)) && ($table->id != $this->id || $this->id==0)) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_NAME'));
			return false;
		}

		return parent::store($updateNulls);
	}
}

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

class Djcatalog2TableCountries extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_countries', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
	
		$query = 'select count(*) from #__djc2_countries where (country_2_code='.$db->quote($this->country_2_code).' OR country_3_code='.$db->quote($this->country_3_code).' OR country_name='.$db->quote($this->country_name).')';
		
		if ($this->id > 0) {
			$query.= ' AND id !='.$this->id;
		}
		
		$db->setQuery($query);
		$count = $db->loadResult();
	
		if ($count > 0) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_COUNTRY'));
			return false;
		}
	
		return parent::store($updateNulls);
	}
}

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

class Djcatalog2TableStates extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_countries_states', 'id', $db);
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
		
		
		
		$id = (int)$this->id;
		$country_id = (int)$this->country_id;
		$name = $this->name;
		
		$query = $db->getQuery(true);
		$where = array();
		
		$query->select('count(*)');
		$query->from('#__djc2_countries_states');
		
		if ($id) {
			$where[] = 'id != '.$id;
		}
		if ($country_id > 0) {
			$where[] = 'country_id='.$country_id;
		}
		
		$where[] = 'name='.$db->quote($name);
		
		$query->where($where);
		
		$db->setQuery($query);
		$count = $db->loadResult();

		if ($count > 0) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_NAME'));
			return false;
		}
	
		return parent::store($updateNulls);
	}
}

<?php
use Joomla\Registry\Registry;

/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access
defined('_JEXEC') or die;

class Djcatalog2TableLabels extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_labels', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new Registry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}
		
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('count(*)');
		$query->from('#__djc2_labels');
		$query->where('name = '.$db->quote($this->name));
		
		if ($this->id > 0) {
			$query->where('id !='.$this->id);
		}
		
		$db->setQuery($query);
		$count = $db->loadResult();
		
		if ($count > 0) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_NAME'));
			return false;
		}
		
		return parent::store($updateNulls);
	}
}

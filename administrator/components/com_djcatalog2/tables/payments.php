<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
use Joomla\Registry\Registry;

// No direct access
defined('_JEXEC') or die;

class Djcatalog2TablePayments extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_payment_methods', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new Registry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}
		
		if (isset($array['countries']) && is_array($array['countries'])) {
			$array['countries'] = implode(',', $array['countries']);
		} else {
			$array['countries'] = isset($array['countries']) ? $array['countries'] : '';
			$this->countries = $array['countries'];
		}
		
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		return parent::store($updateNulls);
	}
}

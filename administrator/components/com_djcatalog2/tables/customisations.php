<?php
use Joomla\Registry\Registry;
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

// No direct access
defined('_JEXEC') or die;

class Djcatalog2TableCustomisations extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_customisations', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new Registry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}
		
		if (isset($array['input_params']) && is_array($array['input_params'])) {
			$registry = new Registry();
			$registry->loadArray($array['input_params']);
			$array['input_params'] = (string)$registry;
		}
		
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		return parent::store($updateNulls);
	}
}

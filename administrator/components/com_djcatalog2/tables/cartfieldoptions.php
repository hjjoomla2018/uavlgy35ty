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

class Djcatalog2TableCartFieldOptions extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_cart_extra_fields_options', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		return parent::store($updateNulls);
	}
}


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

class Djcatalog2TableUnits extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_units', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		if ($array['is_int'] == 1) {
			$lowest = (int)$array['min_quantity'];
			if ($lowest < 1) {
				$lowest = 1;
			}
			
			$step = (int)$array['step'];
			if ($step < 1) {
				$step = 1;
			}
			
			$array['min_quantity'] = $lowest;
			$array['step'] = $step;
		}
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		return parent::store($updateNulls);
	}
}

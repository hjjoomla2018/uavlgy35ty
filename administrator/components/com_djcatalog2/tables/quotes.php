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

class Djcatalog2TableQuotes extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_quotes', 'id', $db);
		$this->items = array();
	}
	public function bind($array, $ignore = '')
	{
		$db = JFactory::getDbo();
		
		$user = JFactory::getUser();
		$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2') || $user->authorise('core.admin', 'com_djcatalog2');
		
		$params = JComponentHelper::getParams('com_djcatalog2');

		if (!empty($array['quote_items']) && is_array($array['quote_items'])) {
			$items = array();
			$rows = $array['quote_items'];
			foreach($rows['id'] as $id => $value) {
				$row = new stdClass();
				
				if (empty($rows['item_name'][$id]) || (!$salesman && floatval($rows['quantity'][$id] == 0.0))) {
					continue;
				}
					
				$row->id = $value;
				if (isset($rows['item_type'])) {
					$row->item_type         = $rows['item_type'][$id];
				}
				if (isset($rows['combination_id'])) {
					$row->combination_id         = $rows['combination_id'][$id];
				}
				$row->quote_id        = $array['id'];
				$row->item_id           = (empty($rows['item_id'][$id])) ? 0 : $rows['item_id'][$id];
				$row->sku        		= $rows['sku'][$id];
				$row->item_name         = $rows['item_name'][$id];
				$row->quantity          = $rows['quantity'][$id];
				
				if (isset($rows['unit'][$id])) {
					$row->unit = $rows['unit'][$id];
				}
				
				$row->price             = $rows['price'][$id];
				$row->total             = $rows['total'][$id];
				
				if (!empty($rows['additional_info'][$id])) {
					$row->additional_info   = $rows['additional_info'][$id];
				}
				
				if (!empty($rows['combination_info'][$id])) {
					$row->combination_info   = $rows['combination_info'][$id];
				}

				$items[] = $row;
			}

			$array['items'] = $items;
			unset($array['quote_items']);
		}
		
		if (isset($array['items']) &&  count($array['items']) > 0) {
			$grand_total = 0.0;
			foreach($array['items'] as $item) {
				$grand_total += round($item->total, 2);
			}
				
			$array['grand_total'] = $grand_total;
		}
		return parent::bind($array, $ignore);
	}

	public function load($keys = null, $reset = true)
	{
		$return = parent::load($keys, $reset);

		if ($return !== false && (int)$this->id > 0 && empty($this->items)) {
			$db = JFactory::getDbo();
			$db->setQuery('select * from #__djc2_quote_items where quote_id='.(int)$this->id);
			$this->items = $db->loadObjectList('id');
		}

		return $return;
	}
	public function store($updateNulls = false)
	{
		
		
		$items = $this->items;
		unset($this->items);

		$success = parent::store($updateNulls);
		//$this->items = $items;

		if (!$success) {
			return false;
		}

		$db = JFactory::getDbo();

		$do_not_delete = array();
		if (count($items)) {
			foreach ($items as &$obj) {
				$obj->quote_id = $this->id;
				if ($obj->id > 0) {
					$ret = $db->updateObject( '#__djc2_quote_items', $obj, 'id', false);
				} else {
					$ret = $db->insertObject( '#__djc2_quote_items', $obj, 'id');
				}
				if ($ret) {
					$do_not_delete[] = $obj->id;
				} else {
					$this->setError($db->getErrorMsg());
				}
			}
			unset($obj);
		}

		if (count($do_not_delete) > 0) {
			$db->setQuery('delete from #__djc2_quote_items where quote_id='.(int)$this->id.' and id not in ('.implode(',', $do_not_delete).')');
		} else {
			$db->setQuery('delete from #__djc2_quote_items where quote_id='.(int)$this->id);
		}

		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		$this->items = $items;
		unset($items);

		return true;

	}
}

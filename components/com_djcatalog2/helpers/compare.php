<?php
/**
 * @version $Id $
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined('_JEXEC') or die('Restricted access');

class Djcatalog2HelperCompare {
	
	public static function canAdd() {
		$params = Djcatalog2Helper::getParams();
		$app	= JFactory::getApplication();
		$limit = (int)$params->get('compare_limit', 4);
		$stored_items = $app->getUserState('com_djcatalog2.compare.items', array());
		
		if (count($stored_items) >= $limit) {
			return false;
		}
		
		return true;
	}
	
	public static function add($item_id) {
		$params = Djcatalog2Helper::getParams();
		$app	= JFactory::getApplication();
		$limit = (int)$params->get('compare_limit', 4);
		$stored_items = $app->getUserState('com_djcatalog2.compare.items', array());
		
		if (!in_array($item_id, $stored_items)) {
			$stored_items[] = $item_id;
			$app->setUserState('com_djcatalog2.compare.items', $stored_items);
		}
		
		return true;
	}
	
	public static function remove($item_id) {
		$params = Djcatalog2Helper::getParams();
		$app	= JFactory::getApplication();
		$stored_items = $app->getUserState('com_djcatalog2.compare.items', array());
		
		$key = array_search($item_id, $stored_items);
		
		if ($key !== false) {
			unset($stored_items[$key]);
			$app->setUserState('com_djcatalog2.compare.items', $stored_items);
		}
		
		return true;
	}
	
	public static function getItemIds() {
		$params = Djcatalog2Helper::getParams();
		$app	= JFactory::getApplication();
		$stored_items = $app->getUserState('com_djcatalog2.compare.items', array());
		
		return $stored_items;
	}
	
	public static function getItems() {
		$params = Djcatalog2Helper::getParams();
		$app	= JFactory::getApplication();
		$stored_items = self::getItemIds();
		
		if (count($stored_items) < 1) {
			return array();
		}
		
		JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models', 'DJCatalog2Model');
		$itemsModel = JModelLegacy::getInstance('Items', 'DJCatalog2Model', array('ignore_request'));
		
		$state = $itemsModel->getState();
		$itemsModel->setState('list.start', 0);
		$itemsModel->setState('list.limit', 0);
		$itemsModel->setState('list.fields_visibility', 'compare');
		$itemsModel->setState('params', $params);
		$itemsModel->setState('filter.item_ids', $stored_items);
			
		$items = $itemsModel->getItems();
		return $items; 
	}
}
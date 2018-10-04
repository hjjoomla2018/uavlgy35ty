<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');
require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'categories.php');

class DJCatalogHelperRoute
{
	protected static $lookup;
	protected static $producer_lookup;
	
	protected static $allowed_separators = array('-', ',');
	protected static $allowed_positions = array(-1, 1);
	
	public static function getItemRoute($id, $catid = 0, $producerid = null, $layout = '')
	{
		
		$link = 'index.php?option=com_djcatalog2&view=item&id='. $id;
		
		if ($layout != '') {
			$link .= '&layout='.$layout;
		}
		
		$input = JFactory::getApplication()->input;
		
		if ($input->get('option') == 'com_djcatalog2' && $input->get('view') == 'archived') {
			if (($Itemid = $input->get('Itemid')) > 0) {
				$link .= '&Itemid='.$Itemid;
			}
		} else {
			$needles = array(
					'item'  => array((int) $id)
			);
			
			if ((int)$catid >= 0)
			{
				$user	= JFactory::getUser();
				$groups	= $user->getAuthorisedViewLevels();
				
				$categories = Djc2Categories::getInstance(array('state'=>'1', 'access' => $groups));
				$category = $categories->get((int)$catid);
				if($category)
				{
					$path = $category->getPath();
					$path[] = 0;
					JArrayHelper::toInteger($path);
					$needles['items'] = ($path);
					$link .= '&cid='.$catid;
				}
			}
			
			if ($producerid === null){
				if (self::$producer_lookup=== null) {
					self::$producer_lookup = array();
					$db = JFactory::getDbo();
					$db->setQuery('select id, producer_id from #__djc2_items where published=1');
					$ids = $db->loadObjectList();
					if (count($ids) > 0) {
						foreach($ids as $row) {
							if ($row->producer_id > 0) self::$producer_lookup[$row->id] = $row->producer_id;
						}
					}
				}
				if (isset(self::$producer_lookup[(int)$id])) {
					$producerid = self::$producer_lookup[(int)$id];
				}
			}
			if ($producerid !== null && (int)$producerid >= 0) {
				if (!isset($needles['items']) ||  !is_array($needles['items'])) {
					$needles['items'] = array();
				}
				$producer_needles = array();
				foreach($needles['items'] as $k=>$v) {
					$producer_needles[] = $v.'-'.(int)$producerid;
				}
				$needles['items'] = array_merge($producer_needles, $needles['items']);
			}
			
			if ($item = self::_findItem($needles)) {
				$link .= '&Itemid='.$item;
			}
		}

		return $link;
	}
	
	public static function getMyItemsRoute()
	{
		$needles = array(
				'myitems' => array(0),
				'items' => array(0)
		);
		
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=myitems';
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getProducersRoute()
	{
		$needles = array(
				'producers' => array(0),
				'items' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=producers';
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getProducerRoute($id)
	{
		$needles = array(
			'producer'  => array((int) $id),
			'producers'  => array(0),
			'items'  => array(0)
		);
		$link = 'index.php?option=com_djcatalog2&view=producer&pid='. $id;

		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	public static function getCategoryRoute($catid, $producerid = null)
	{
		$needles = array(
			'items'  => array((int) $catid)
		);
		
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=items';
		if ((int)$catid >= 0)
		{
			$user	= JFactory::getUser();
			$groups	= $user->getAuthorisedViewLevels();
			
			$categories = Djc2Categories::getInstance(array('state'=>'1', 'access' => $groups));
			$category = $categories->get((int)$catid);
			if($category)
			{
				$path = $category->getPath();
				$path[] = 0;
				JArrayHelper::toInteger($path);
				$needles['items'] = ($path);
				$link .= '&cid='.$catid;
			}
		}
		if ($producerid !== null && (int)$producerid >= 0) {
			$link .= '&pid='.$producerid;
			$producer_needles = array();
			foreach($needles['items'] as $k=>$v) {
				$producer_needles[] = $v.'-'.(int)$producerid;
			}
			$needles['items'] = array_merge($producer_needles, $needles['items']);
		}
		
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.self::_findItem($needles);
		}
		
		return $link;
	}
	
	public static function getCartRoute()
	{
		$needles = array(
				'cart' => array(0),
				'items' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=cart';
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getCheckoutRoute()
	{
		$needles = array(
				'checkout' => array(0),
				'cart' => array(0),
				'items' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=checkout';
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getQueryRoute()
	{
		$needles = array(
				'query' => array(0),
				'cart' => array(0),
				'items' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=query';
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getOrderRoute($id)
	{
		$needles = array(
				'orders' => array(0),
				'items' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=order&oid='.(int)$id;
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getOrdersRoute()
	{
		$needles = array(
				'orders' => array(0),
				'items' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=orders';
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getQuestionRoute($id)
	{
		$needles = array(
				'questions' => array(0),
				'items' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=question&qid='.(int)$id;
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getQuestionsRoute()
	{
		$needles = array(
				'questions' => array(0),
				'items' => array(0)
		);
	
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=questions';
	
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getComparisonRoute()
	{
		$needles = array(
			'compare' => array(0),
			'items' => array(0)
		);
		
		//Create the link
		$link = 'index.php?option=com_djcatalog2&view=compare';
		
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
		
		return $link;
	}

	public static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$params = JComponentHelper::getParams('com_djcatalog2');
		

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_djcatalog2');
			$items		= $menus->getItems('component_id', $component->id);
			
			$seoMenus = $params->get('seo_menus', array());
			$seoMenusLookup = count($seoMenus) > 0 ? true : false;

			$seoMenuItems = $params->get('seo_menuitems', array());
			$seoMenuItemsLookup = count($seoMenuItems) > 0 ? true : false;
			
			$activeMenu = $menus->getActive();
			$templateStyle = (!empty($activeMenu) && isset($activeMenu->template_style_id)) ? $activeMenu->template_style_id : 0;
			$keepTemplateStyle = (bool)$params->get('seo_style_consistency', false);
			
			if (count($items)) {
                foreach ($items as $item)
                {
                	if ($seoMenusLookup && !in_array($item->menutype, $seoMenus) && !in_array($item->id, $seoMenuItems)) {
                		continue;
                	}
                	
                	if ($seoMenuItemsLookup && !in_array($item->id, $seoMenuItems)) {
                		continue;
                	}
                	
                	if ($keepTemplateStyle && $templateStyle != $item->template_style_id) {
                		continue;
                	}
                	
                    if (isset($item->query) && isset($item->query['view']))
                    {
                        $view = $item->query['view'];
                        if (!isset(self::$lookup[$view])) {
                            self::$lookup[$view] = array();
                        }
                        
                        if ($view == 'items') {
                            $cid = 0;
                            if (isset($item->query['cid'])) {
                                $cid = $item->query['cid'];
                            }
                            
                            $cid = (isset($item->query['cid'])) ? (int)$item->query['cid'] : 0;
                            if (isset($item->query['pid']) && (int)$item->query['pid'] > 0) {
                                $cid .= '-'.$item->query['pid'];
                            }
                            self::$lookup[$view][$cid] = $item->id;
                        }
                        else if ($view == 'producer') {
                            if (isset($item->query['pid'])) {
                                self::$lookup[$view][$item->query['pid']] = $item->id;
                            }
                        }
                        else if ($view == 'item') {
                            if (isset($item->query['id'])) {
                                self::$lookup[$view][$item->query['id']] = $item->id;
                            }
                        } else if ($view == 'myitems') {
                            self::$lookup[$view][0] = $item->id;
                        }
                        else if ($view == 'producers') {
                            self::$lookup[$view][0] = $item->id;
                        } 
                        else {
                            self::$lookup[$view][0] = $item->id;
                        }
                    }
                }
            }
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$view]))
				{
					if (is_array($ids)) {
						foreach($ids as $id)
						{
							if (isset(self::$lookup[$view][$id])) {
								return self::$lookup[$view][$id];
							}
						}
					} else if (isset(self::$lookup[$view][$ids])) {
						return self::$lookup[$view][$ids];
					}
				}
			}
		}
		//else {
		/*$active = $menus->getActive();
		if ($active && $active->component == 'com_djcatalog2') {
			return $active->id;
		}*/
		
		/*else {
			$default = $menus->getDefault();
			return $default->id;
		}*/
		//}

		return null;
	}
	
	public static function formatAlias($id, $type = null) {
		//return $id;
		// TODO
		
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$advanced = (int)$params->get('seo_advanced', 0) && $type;
		
		$position = (int)$params->get('seo_id_position', -1);
		$separator_id = (int)$params->get('seo_alias_separator', 0);
		if (!array_key_exists($separator_id, self::$allowed_separators)) {
			return $id;
		}
		
		$separator = self::$allowed_separators[$separator_id];
		
		if (!in_array($position, self::$allowed_positions)) {
			return $id;
		}
		
		$parts = explode(':', $id, 2);
		$segment = $id;
		if (count($parts) == 2) {
			if ($advanced) {
				$segment = $parts[1];
			} else {
				$segment = ($position == 1) ? $parts[1].$separator.$parts[0] : $parts[0].$separator.$parts[1];
			}
		}
		return $segment;
	}
	
	public static function parseAlias($alias, $type = null, $query = array()) {
		//return $alias;
		// TODO
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$advanced = (int)$params->get('seo_advanced', 0) && $type;
		
		$position = (int)$params->get('seo_id_position', -1);
		$separator_id = (int)$params->get('seo_alias_separator', 0);
		if (!array_key_exists($separator_id, self::$allowed_separators)) {
			return $alias;
		}
		
		$separator = self::$allowed_separators[$separator_id];
		
		if (!in_array($position, self::$allowed_positions)) {
			return $alias;
		}
		
		$id = $alias;
		$temp = str_replace(':', $separator, $alias);
		$parts = explode($separator, $temp);

		if (count($parts) > 0) {
			if ($advanced) {
				$parts = self::getUrlParts($alias, $position, $type, $query);
				if (empty($parts)) {
					return false;
				}
			}
			if ($position == 1) {
				$id = (int)end($parts);
				unset($parts[count($parts)-1]);
			} else {
				$id = (int)$parts[0];
				unset($parts[0]);
			}
		}
		$slug = '';
		if (count($parts) > 0) {
			$slug = ':';
			$slug .= implode('-',$parts);
		}
		return $id.$slug;
	}
	
	public static function getUrlParts($alias, $position = -1, $type = null, $urlQuery = array()) {
		if (!$type || empty($alias)) {
			return false;
		}
		
		$alias = str_replace(':', '-', $alias);
		
		$db = JFactory::getDbo();
		
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		$categories = Djc2Categories::getInstance(array('state'=>'1', 'access' => $groups));
		
		if ($type == 'item') {
			$query = $db->getQuery(true)->select('id, cat_id')->from('#__djc2_items')->where('alias LIKE '.$db->quote($db->escape(trim($alias))));
			$db->setQuery($query);
			$items = $db->loadObjectList('id');
			if (count($items) < 1) {
				return false;
			}
			
			$currentCatId = 0;
			if (isset($urlQuery['cid'])) {
				$currentCatId = (int)$urlQuery['cid'];
			}
			$linkCategory = $categories->get($currentCatId);

			if (count($items) == 1 || $currentCatId == 0 || empty($linkCategory)) {
				return ($position == 1) ? array($alias, current($items)->id) : array(current($items)->id, $alias); 
			}

			// main category check
			foreach($items as $itemId => $item) {
				if ($item->cat_id == $currentCatId) {
					return ($position == 1) ? array($alias, $item->id) : array($item->id, $alias);
				}
			}
			foreach($items as $itemId => $item) {
				$category = $categories->get($item->cat_id);
				if (empty($category)) {
					continue;
				}
				$path = $category->getPath();
				JArrayHelper::toInteger($path);
				if (in_array($item->cat_id, $path)) {
					return ($position == 1) ? array($alias, $itemId) : array($itemId, $alias);
				}
			}
			// additional categories check
			$query = $db->getQuery(true);
			$query->select('item_id, category_id')->from('#__djc2_items_categories')->where('item_id IN ('.implode(',', array_keys($items)).')');
			$db->setQuery($query);
			$itemsCategories = $db->loadObjectList();
			foreach($itemsCategories as $xref) {
				if ($xref->category_id == $linkCategory->id) {
					return ($position == 1) ? array($alias, $xref->item_id) : array($xref->item_id, $alias);
				}
				$category = $categories->get($xref->category_id);
				if (empty($category)) {
					continue;
				}
				$path = $category->getPath();
				JArrayHelper::toInteger($path);
				if (in_array($xref->category_id, $path)) {
					return ($position == 1) ? array($alias, $xref->item_id) : array($xref->item_id, $alias);
				}
			}
			return false;
			
		} else if ($type == 'category') {
			$query = $db->getQuery(true)->select('id')->from('#__djc2_categories')->where('alias LIKE '.$db->quote($db->escape(trim($alias))));
			$db->setQuery($query);
			$items = $db->loadObjectList('id');
			if (count($items) < 1) {
				return false;
			}
			
			$currentCatId = 0;
			if (isset($urlQuery['cid'])) {
				$currentCatId = (int)$urlQuery['cid'];
			}
			$menuCategory = $categories->get($currentCatId);
			
			if (count($items) == 1 || $currentCatId == 0 || empty($menuCategory)) {
				return ($position == 1) ? array($alias, current($items)->id) : array(current($items)->id, $alias);
			}
			
			foreach($items as $catId => $item) {
				$category = $categories->get($item->id);
				if (empty($category)) {
					continue;
				}
				$path = $category->getPath();
				JArrayHelper::toInteger($path);
				if (in_array($item->id, $path)) {
					return ($position == 1) ? array($alias, $catId) : array($catId, $alias);
				}
			}
			return false;
		} else if ($type == 'producer') {
			$query = $db->getQuery(true)->select('id')->from('#__djc2_producers')->where('alias LIKE '.$db->quote($db->escape(trim($alias))));
			$db->setQuery($query);
			$items = $db->loadObjectList('id');
			if (count($items) < 1) {
				return false;
			}
			
			return ($position == 1) ? array($alias, current($items)->id) : array(current($items)->id, $alias);
		} else {
			return false;
		}
	}
}

class DJCatalog2HelperRoute extends DJCatalogHelperRoute {
}

?>

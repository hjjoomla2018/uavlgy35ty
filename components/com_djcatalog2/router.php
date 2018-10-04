<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined('_JEXEC') or die;

require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'route.php');

function DJCatalog2BuildRoute(&$query)
{
	$segments = array();
	
	$app		= JFactory::getApplication();
	$menu	   = $app->getMenu('site');
	
	$params = JComponentHelper::getParams( 'com_djcatalog2' );
	$component_views = array('items' => 'items', 'item' => 'item',
		'producer' => 'producer', 'myitems' => 'myitems',
		'itemform' => 'itemform', 'producers' => 'producers',
		'cart'=>'cart', 'orders'=>'orders', 'order'=>'order',
		'checkout'=>'checkout', 'query' => 'query', 'map' => 'map',
		'archived' => 'archived', 'questions' => 'questions', 'question' => 'question', 'compare' => 'compare', 'orderform' => 'orderform', 'questionform' => 'questionform' );
	foreach($component_views as $view_name => $seotag) {
		$alias = $params->get('seo_'.$view_name.'_view', $view_name);
		$alias = JApplication::stringURLSafe($alias);
		if(trim(str_replace('-','',$alias)) != '') {
			$component_views[$view_name] = trim($alias);
		}
	}
	
	$default_menu = $menu->getDefault();
	$menuItem = null;
	
	if (empty($query['Itemid'])) {
		//JLog::add(' empty Itemid '.print_r($query, true));
		unset($query['Itemid']);
		$menuItem = $menu->getActive();
	} else {
		//JLog::add(' NOT empty Itemid '.print_r($query, true));
		$menuItem = $menu->getItem($query['Itemid']);
	}
	
	//JLog::add(' menu Item '.@$menuItem->component.' '.print_r(@$menuItem->query,true).' -----------------------');
	
	//$option = (empty($menuItem->component)) ? null : $menuItem->component;
	
	$mView  = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
	$mCatid = (empty($menuItem->query['cid'])) ? null : (int)$menuItem->query['cid'];
	$mProdid   = (empty($menuItem->query['pid'])) ? null : (int)$menuItem->query['pid'];
	$mId	= (empty($menuItem->query['id'])) ? null : (int)$menuItem->query['id'];
	$mLayout = (empty($menuItem->query['layout'])) ? null : (int)$menuItem->query['layout'];
	
	$view = !empty($query['view']) ? $query['view'] : null;
	$cid = !empty($query['cid']) ? $query['cid'] : null;
	$pid = !empty($query['pid']) ? $query['pid'] : null;
	$id = !empty($query['id']) ? $query['id'] : null;
	$layout = !empty($query['layout']) ? $query['layout'] : null;
	
	$mECatid = (empty($menuItem->query['ecid'])) ? null : (int)$menuItem->query['ecid'];
	$mEId	= (empty($menuItem->query['eid'])) ? null : (int)$menuItem->query['eid'];
	
	$ecid = !empty($query['ecid']) ? $query['ecid'] : null;
	$eid = !empty($query['eid']) ? $query['eid'] : null;
	
	$qid = !empty($query['qid']) ? $query['qid'] : null;
	$oid = !empty($query['oid']) ? $query['oid'] : null;
	
	$task = !empty($query['task']) ? $query['task'] : null;
	
	// JoomSEF bug workaround
	if (isset($query['start']) && isset($query['limitstart'])) {
		if ((int)$query['limitstart'] != (int)$query['start'] && (int)$query['start'] > 0) {
			// let's make it clear - 'limitstart' has higher priority than 'start' parameter,
			// however ARTIO JoomSEF doesn't seem to respect that.
			$query['start'] = $query['limitstart'];
			unset($query['limitstart']);
		}
	}
	// JoomSEF workaround - end
	//if ($view && $option == 'com_djcatalog2') {
	if ($view) {
		if ($view != $mView || empty($query['Itemid'])) {
			if (!(($view == 'item' || $view == 'items ' || $view == 'archived') && ($mView == 'items' || $mView == 'archived')) || $params->get('seo_skip_item_view', 0) == '0') {
				$segments[] = $view;
			}
		}
		
		unset($query['view']);
		
		if ($view == 'item') {
			if ($view == $mView && intval($id) > 0 && intval($id) == $mId) {
				unset($query['id']);
				unset($query['cid']);
			} else if ((($mView == 'items' || $mView == 'archived') || $mView === null) && intval($id) > 0) {
				if (intval($cid) != intval($mCatid)) {
					//$segments[] = (int)$cid == 0 ? 'all' : DJCatalogHelperRoute::formatAlias($cid);
					$segments[] = (int)$cid == 0 ? 'all' : DJCatalogHelperRoute::formatAlias($cid, 'category');
				}
				//$segments[] = DJCatalogHelperRoute::formatAlias($id);
				$segments[] = DJCatalogHelperRoute::formatAlias($id, 'item');
				unset($query['id']);
				unset($query['cid']);
			}
			
			if ($layout && $layout != 'default' && $layout != $mLayout) {
				
				$segments[] = $layout;
				unset($query['layout']);
				
				if (($layout == 'preview' || $layout == 'print' || $layout == 'contact') && isset($query['tmpl'])) {
					unset($query['tmpl']);
				}
			}
		}
		
		if ($view == 'items' || $view == 'archived') {
			if ($cid === null) {
				$cid = '0';
			}
			if (intval($cid) != intval($mCatid) /*|| $mCatid === null*/) {
				//$segments[] = (int)$cid == 0 ? 'all' : DJCatalogHelperRoute::formatAlias($cid);
				$segments[] = (int)$cid == 0 ? 'all' : DJCatalogHelperRoute::formatAlias($cid, 'category');
			} else if (@$menuItem->id == $default_menu->id && (int)$cid == 0 && (isset($query['cm']) || isset($query['ind']) || (int)$mProdid != (int)$pid) ) {
				$segments[] = 'all';
			}
			unset($query['cid']);
			
			if ( (isset($query['pid']) && $query['pid'] === '') ||  (intval($mProdid) == intval($pid) /*&& (intval($mProdid) > 0 || intval($pid) > 0)*/)) {
				unset($query['pid']);
			}
		}
		
		if ($view == 'producer') {
			if (!($view == $mView && intval($pid) > 0 && intval($pid) == $mProdid) && $mView != 'producer') {
				$segments[] = DJCatalogHelperRoute::formatAlias($pid, 'producer');
			}
			unset($query['pid']);
		}
		
		if ($view == 'itemform') {
			if (intval($id) > 0) {
				$segments[] = $id;
				unset($query['id']);
			}
		}
		
		if ($view == 'question') {
			if (intval($qid) > 0) {
				$segments[] = $qid;
				unset($query['qid']);
			}
		}
		
		if ($view == 'order') {
			if (intval($oid) > 0) {
				$segments[] = $oid;
				unset($query['oid']);
			}
		}
		
		if ($view == 'orderform') {
			if (intval($id) > 0) {
				$segments[] = $id;
				unset($query['id']);
			}
		}
		
		if ($view == 'questionform') {
			if (intval($id) > 0) {
				$segments[] = $id;
				unset($query['id']);
			}
		}
		
		if ($view == 'map') {
			if ($cid === null) {
				$cid = '0';
			}
			if (intval($cid) != intval($mCatid) /*|| $mCatid === null*/) {
				$segments[] = (int)$cid == 0 ? 'all' : DJCatalogHelperRoute::formatAlias($cid);
			} else if ($menuItem->id == $default_menu->id && (int)$cid == 0 && (int)$mProdid != (int)$pid)  {
				$segments[] = 'all';
			}
			unset($query['cid']);
			
			if ( (isset($query['pid']) && $query['pid'] === '') ||  (intval($mProdid) == intval($pid) /*&& (intval($mProdid) > 0 || intval($pid) > 0)*/)) {
				unset($query['pid']);
			}
		}
	}
	
	if (!empty($segments[0]) && array_key_exists($segments[0], $component_views)) {
		$segments[0] = $component_views[$segments[0]];
	}
	
	return $segments;
}

function DJCatalog2ParseRoute($segments) {
	$app	= JFactory::getApplication();
	$menu	= $app->getMenu();
	$activemenu = $menu->getActive();
	$db = JFactory::getDBO();
	$params = JComponentHelper::getParams( 'com_djcatalog2' );
	$advanced = (int)$params->get('seo_advanced', 0);
	
	$catalogViews = array('item', 'items', 'producer', 'itemform', 'myitems', 'producers', 'cart', 'checkout', 'orders', 'order', 'query', 'map', 'archived', 'questions', 'question', 'compare', 'orderform', 'questionform');
	
	$component_views = array('items' => 'items', 'item' => 'item',
		'producer' => 'producer', 'myitems' => 'myitems',
		'itemform' => 'itemform', 'producers' => 'producers',
		'cart'=>'cart', 'orders'=>'orders', 'order'=>'order',
		'checkout'=>'checkout', 'query'=>'query', 'map' => 'map',
		'archived' => 'archived', 'questions' => 'questions', 'question' => 'question', 'compare' => 'compare', 'orderform' => 'orderform', 'questionform' => 'questionform');
	
	foreach($component_views as $view_name => $seotag) {
		$view_alias = $params->get('seo_'.$view_name.'_view', $view_name);
		$view_alias = JApplication::stringURLSafe(trim($view_alias));
		if (count($segments)) {
			if ($segments[0] == $view_alias || str_replace(':', '-', $segments[0]) == $view_alias) {
				$segments[0] = $view_name;
				break;
			}
		}
	}
	$query=array();
	
	if (!empty($activemenu) && is_array($activemenu->query)) {
		foreach($activemenu->query as $k=>$v) {
			$query[$k] = $v;
		}
	}
	
	if (count($segments)) {
		if (!in_array($segments[0], $catalogViews)) {
			if ($activemenu) {
				$temp=array();
				$temp[0] = $activemenu->query['view'];
				switch ($temp[0]) {
					case 'item' : {
						$temp[1] = @$activemenu->query['id'];
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
					case 'items' :
					case 'archived': {
						$temp[1] = @$activemenu->query['cid'];
						
						/*if (count($segments) == 1) {
						 $tempSegment = DJCatalogHelperRoute::parseAlias($segments[0]);
						 $parts = explode(':', $tempSegment, 2);
						 $id = $parts[0];
						 $alias = isset($parts[1]) ? $parts[1] : null;
						 if ((int)$id > 0) {
						 $user	= JFactory::getUser();
						 $groups	= $user->getAuthorisedViewLevels();
						 $categories = Djc2Categories::getInstance(array('state'=>'1', 'access' => $groups));
						 $category = $categories->get((int)$id);
						 if (!empty($category) && ($category->alias == $alias || empty($alias))) {
						 $temp[1] = $segments[0];
						 } else {
						 $temp[0] = 'item';
						 }
						 }
						 } else {
						 $temp[0] = 'item';
						 }*/
						
						if (count($segments) == 1) {
							if ($segments[0] == 'all') {
								$temp[0] = 'items';
								$temp[1] = 0;
							} else {
								$tempQuery = is_array($activemenu) && isset($activemenu->query) ? $activemenu->query : array();
								$tempSegment = DJCatalogHelperRoute::parseAlias($segments[0], 'category', $tempQuery);
								if ($advanced) {
									if ($tempSegment) {
										$temp[1] = $segments[0];
									} else {
										$temp[0] = 'item';
									}
								} else {
									$parts = explode(':', $tempSegment, 2);
									$id = $parts[0];
									$alias = isset($parts[1]) ? $parts[1] : null;
									if ((int)$id > 0) {
										$user	= JFactory::getUser();
										$groups	= $user->getAuthorisedViewLevels();
										$categories = Djc2Categories::getInstance(array('state'=>'1', 'access' => $groups));
										$category = $categories->get((int)$id);
										if (!empty($category) && ($category->alias == $alias || empty($alias))) {
											$temp[1] = $segments[0];
										} else {
											$temp[0] = 'item';
										}
									} else if (!$alias || ($id == 0 && strpos($id, $tempSegment) !== 0)) {
										// this should always result in 404
										$temp[0] = 'item';
										$temp[1] = 0;
									}
								}
							}
						} else {
							$temp[0] = 'item';
						}
						
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
					case 'producer' : {
						$temp[1] = @$activemenu->query['pid'];
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
					case 'myitems' : {
						//$temp[1] = @$activemenu->query['id'];
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
					case 'producers' : {
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
					case 'itemform' : {
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
					case 'orderform' : {
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
					case 'questionform' : {
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
					case 'cart' :
					case 'checkout' :
					case 'query' :
					case 'orders' :
					case 'order' :
					case 'questions' :
					case 'question' : {
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
					case 'map' : {
						$temp[1] = @$activemenu->query['cid'];
						foreach ($segments as $k=>$v) {
							$temp[$k+1] = $v;
						}
						break;
					}
				}
				
				$segments = $temp;
			}
		}
		if (isset($segments[0])) {
			switch($segments[0]) {
				case 'items':
				case 'archived': {
					$query['view'] = $segments[0];
					if (isset($segments[1]) && $segments[1] != '') {
						//$query['cid']=($segments[1] == 'all') ? 0 : DJCatalogHelperRoute::parseAlias($segments[1]);
						$query['cid']=($segments[1] == 'all') ? 0 : DJCatalogHelperRoute::parseAlias($segments[1], 'category', $query);
					}
					break;
				}
				case 'itemstable': {
					$query['view'] = 'itemstable';
					if (isset($segments[1])) {
						//$query['cid']=($segments[1] == 'all') ? 0 :DJCatalogHelperRoute::parseAlias($segments[1]);
						$query['cid']=($segments[1] == 'all') ? 0 :DJCatalogHelperRoute::parseAlias($segments[1], 'category', $query);
					}
					break;
				}
				case 'item': {
					$query['view'] = 'item';
					
					// checking if last segment matches layout
					$layout = null;
					$layouts = array('preview', 'print', 'contact');
					$scount = count($segments);
					if (in_array($segments[$scount-1], $layouts)) {
						$layout = $segments[$scount-1];
						unset($segments[$scount-1]);
						
						if ($layout == 'preview' || $layout == 'print' || $layout == 'contact') {
							$query['tmpl'] = 'component';
						}
					}
					
					if (count($segments) > 2) {
						if (isset($segments[1]) && $segments[1] != '') {
							//$query['cid']=($segments[1] == 'all') ? 0 : DJCatalogHelperRoute::parseAlias($segments[1]);
							$query['cid']=($segments[1] == 'all') ? 0 : DJCatalogHelperRoute::parseAlias($segments[1], 'category', $query);
						}
						if (isset($segments[2])) {
							//$query['id']= DJCatalogHelperRoute::parseAlias($segments[2]);
							$query['id']= DJCatalogHelperRoute::parseAlias($segments[2], 'item', $query);
						}
					} else if (isset($segments[1])) {
						//$query['id']=  DJCatalogHelperRoute::parseAlias($segments[1]);
						$query['id']=  DJCatalogHelperRoute::parseAlias($segments[1], 'item', $query);
						if ($activemenu && $activemenu->query['option'] == 'com_djcatalog2' && $activemenu->query['view'] == 'items' && !empty($activemenu->query['cid'])) {
							$query['cid'] = $activemenu->query['cid'];
						}
					}
					
					if ($layout) {
						$query['layout'] = $layout;
					}
					
					break;
				}
				case 'producer': {
					$query['view'] = 'producer';
					if (isset($segments[1])) {
						//$query['pid']=$segments[1];
						$query['pid']=  DJCatalogHelperRoute::parseAlias($segments[1], 'producer', $query);
					}
					break;
				}
				case 'itemform': {
					$query['view'] = 'itemform';
					if (isset($segments[1])) {
						$query['id']=$segments[1];
					}
					break;
				}
				case 'myitems': {
					$query['view'] = 'myitems';
					break;
				}
				case 'producers': {
					$query['view'] = 'producers';
					break;
				}
				case 'cart': {
					$query['view'] = 'cart';
					break;
				}
				case 'orders': {
					$query['view'] = 'orders';
					break;
				}
				case 'checkout': {
					$query['view'] = 'checkout';
					break;
				}
				case 'query': {
					$query['view'] = 'query';
					break;
				}
				case 'order': {
					$query['view'] = 'order';
					if (isset($segments[1])) {
						$query['oid']=$segments[1];
					}
					break;
				}
				case 'orderform': {
					$query['view'] = 'orderform';
					if (isset($segments[1])) {
						$query['id']=$segments[1];
					}
					break;
				}
				case 'questions': {
					$query['view'] = 'questions';
					break;
				}
				case 'question': {
					$query['view'] = 'question';
					if (isset($segments[1])) {
						$query['qid']=$segments[1];
					}
					break;
				}
				case 'questionform': {
					$query['view'] = 'questionform';
					if (isset($segments[1])) {
						$query['id']=$segments[1];
					}
					break;
				}
				case 'map': {
					$query['view'] = 'map';
					if (isset($segments[1])) {
						$query['cid']=($segments[1] == 'all') ? 0 : DJCatalogHelperRoute::parseAlias($segments[1]);
					}
					break;
				}
				default: {
					$query['view'] = $segments[0];
				}
			}
		}
	}
	
	return $query;
}

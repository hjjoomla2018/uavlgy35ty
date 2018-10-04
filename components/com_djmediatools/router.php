<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */
defined('_JEXEC') or die;
 
function DJMediatoolsBuildRoute(&$query)
{
	$segments = array();

	$app		= JFactory::getApplication();
	$menu		= $app->getMenu('site');
	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
	} else {
		$menuItem = $menu->getItem($query['Itemid']);
	}
	
	$mView	= (@$menuItem->query['option'] != 'com_djmediatools' || empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
	$mId	= (@$menuItem->query['option'] != 'com_djmediatools' || empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
	
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
	
	if(isset($query['view'])) {
		switch ($query['view']) {
			case 'category': {
				if ($mView && $query['view'] == $mView && isset($query['id'])) {
						
					unset($query['view']);
					
					if (intval($query['id']) == $mId) {
						unset($query['id']);
					} else {
						$segments[] = $query['id'];
						unset($query['id']);
					}
					
				} else {
											
					$segments[] = 'album';//$query['view'];
					$segments[] = $query['id'];
					unset($query['view']);
					unset($query['id']);						
				}
				
				break;
			}
			case 'categories': {
				if ($query['view'] == $mView && isset($query['id'])) {
					
					unset($query['view']);
					
					if (intval($query['id']) == $mId) {
						unset($query['id']);						
					} else {
						$segments[] = @$query['id'] ? $query['id'] : 'all';
						unset($query['id']);
					}
				}
				else {
					$segments[] = 'albums'; //$query['view'];
					$segments[] = @$query['id'] ? $query['id'] : 'all';
					unset($query['view']);
					unset($query['id']);					
				}
				break;
			}
			case 'item': {
				$segments[] = 'media';
				unset($query['view']);
				unset($query['tmpl']);
				
				if($mView == 'category' && isset($query['cid']) && intval($query['cid']) == $mId) {
					unset($query['cid']);
				} else {
					$segments[] = $query['cid'];
					unset($query['cid']);
				}
				
				$segments[] = $query['id'];
				unset($query['id']);
				
				break;
			}
		}
	}
	
	return $segments;
}

function DJMediatoolsParseRoute($segments) {
	
	$app	= JFactory::getApplication();
	$menu	= $app->getMenu();
	$activemenu = $menu->getActive();
	$db = JFactory::getDBO();
	
	//$app->enqueueMessage(print_r($segments, true));
	$query=array();
	if (isset($segments[0])) {
		switch($segments[0]) {
			case 'albums':
			case 'categories': {
				$query['view'] = 'categories';
				if (isset($segments[1])) {
					$query['id'] = ($segments[1] == 'all') ? 0 : $segments[1];
				} 
				break;
			}
			case 'album':
			case 'category': {
				$query['view'] = 'category';
				if (isset($segments[1])) {
					$query['id']= $segments[1];
				} 
				break;
			}
			case 'media': {
				$query['view'] = 'item';
				$query['tmpl'] = 'component';
				
				if(isset($segments[2])) {
					// not current album
					$query['cid'] = $segments[1];
					$query['id'] = $segments[2];
				} else {
					if($activemenu && $activemenu->query['view'] == 'category' && isset($activemenu->query['id'])) {
						// item from current menu album
						$query['cid'] = $activemenu->query['id'];
						$query['id'] = $segments[1];
					} else {
						// shouldn't happen
					}
				}
								
				break;
			}
			default: {
				
				$query['view'] = 'category';
				if (isset($segments[0])) {
					$query['id']= $segments[0];
				} 
				
				break;
			}
		}
	}
	
	//$app->enqueueMessage(print_r($query, true));	
	
	return $query;
}

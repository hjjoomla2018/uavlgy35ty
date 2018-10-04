<?php
/**
 * @version 1.0
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
 
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

class DJMediatoolsHelperRoute
{
	protected static $lookup;
	public static function getCategoryRoute($id, $parent = 0)
	{
		$needles = array(
			'category' => array((int) $id),
			'categories' => array((int) $id, (int) $parent)
		);
		//Create the link
		$link = 'index.php?option=com_djmediatools&view=category&id='. $id;
		
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}
	
	public static function getCategoryFormRoute($id, $parent = 0)
	{
		$needles = array(
				'category' => array((int) $id),
				'categories' => array((int) $id, (int) $parent)
		);
		//Create the link
		$link = 'index.php?option=com_djmediatools&view=categoryform&id='. $id;
		
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
	
		return $link;
	}
	
	public static function getCategoriesRoute($id = 0)
	{
		$needles = array(
			'categories'  => array((int) $id)
		);
		
		//Create the link
		$link = 'index.php?option=com_djmediatools&view=categories&id='. $id;
		
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}

		return $link;
	}
	
	public static function getItemRoute($id, $category = 0, $parent = 0) {
		
		$needles = array(
				'category' => array((int) $category),
				'categories' => array((int) $category, (int) $parent)
		);
		
		$link = 'index.php?option=com_djmediatools&view=item&cid='.$category.'&id='.$id.'&tmpl=component';
		
		if ($item = self::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		}
		
		return $link;
	}
	
	protected static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');

		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_djmediatools');
			$items		= $menus->getItems('component_id', $component->id);
			if (count($items)) {
				foreach ($items as $item)
				{
					if (isset($item->query) && isset($item->query['view']))
					{
						$view = $item->query['view'];
						if (!isset(self::$lookup[$view])) {
							self::$lookup[$view] = array();
						}
						if (isset($item->query['id'])) {
							self::$lookup[$view][$item->query['id']] = $item->id;
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
					foreach($ids as $id)
					{
						if (isset(self::$lookup[$view][(int)$id])) {
							return self::$lookup[$view][(int)$id];
						}
					}
				}
			}
		}
		else
		{
			$active = $menus->getActive();
			if ($active && $active->component == 'com_djmediatools') {
				return $active->id;
			}
		}

		return null;
	}
}
?>

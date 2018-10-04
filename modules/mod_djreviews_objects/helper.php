<?php
/**
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

class DJReviewsObjectsModuleHelper {
	public static function getItems($groups, &$params) {
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$query->select('o.*, count(r.id) as review_count');
		$query->from('#__djrevs_objects AS o');
		$query->join('LEFT', '#__djrevs_reviews AS r ON r.object_id=o.id');
		$query->where('o.rating_group_id IN ('.implode(',', $groups).')');
		//$query->where('o.avg_rate > 0');
		
		$query->group('o.id');
		
		$order = $params->get('order', 'o.avg_rate DESC');
		$query->order($order);
		
		$limit = $params->get('limit', 10);
		
		$db->setQuery($query, 0, $limit);
		
		$items = $db->loadObjectList();

		if (count($items)) {
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('djreviews');
			
			foreach ($items as $k=>$v) {
				if (!empty($v->plugin)) {
					$plgParts = explode('.', $v->plugin, 2);
					if (count($plgParts) == 2) {
						JPluginHelper::importPlugin($plgParts[0], $plgParts[1]);
					}
				}
				$dispatcher->trigger('onObjectPrepare', array($v->object_type, &$v));
			}
		}
		
		return $items;
	}
}
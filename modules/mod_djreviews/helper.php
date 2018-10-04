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

class DJReviewsModuleHelper {
	public static function getItems($groups, $params) {
		JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_djreviews/models', 'DJReviewsModel');
		$model = JModelLegacy::getInstance('ReviewsList', 'DJReviewsModel', array('ignore_request'=>true));

		$limit = (int)$params->get('limit', 10);
		if (!$limit) {
			$limit = 10;
		}
		
		$order = $params->get('order', 'latest');
		$orderby = 'a.created';
		$orderdir = 'desc';
		
		switch($order) {
			case 'oldest' : {
				$orderby = 'a.created';
				$orderdir = 'asc';
				break;
			}
			case 'lowest' : {
				$orderby = 'a.avg_rate';
				$orderdir = 'asc';
				break;
			}
			case 'highest' : {
				$orderby = 'a.avg_rate';
				$orderdir = 'desc';
				break;
			}
			case 'latest' :
			default: {
				$orderby = 'a.created';
				$orderdir = 'desc';
				break;
			}
		}
		
		$ratedOnly = $params->get('rated_only', 1);
		
		$state = $model->getState();
		$model->setState('filter.rating_group', $groups);
		$model->setState('filter.published', 'front');
		$model->setState('filter.object_id', false);
		$model->setState('list.start', 0);
		$model->setState('list.limit', $limit);
		$model->setState('list.ordering', $orderby);
		$model->setState('list.direction', $orderdir);
		
		if ($ratedOnly) {
			$model->setState('filter.rated', 1);
		}
		
		return $model->getItems();
	}
}
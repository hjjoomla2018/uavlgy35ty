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

class DJReviewsTableObjects extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djrevs_objects', 'id', $db);
	}
	function bind($array, $ignore = '')
	{
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		return parent::store($updateNulls);
	}
	
	public function load($keys = null, $reset = true) {
		if (parent::load($keys, $reset)) {
			$query = $this->_db->getQuery(true);
			
			$query->select('f.*, i.rating');
			$query->from('#__djrevs_rating_fields as f');
			$query->join('LEFT', '#__djrevs_objects_items as i ON i.field_id = f.id AND i.object_id = '.(int)$this->id);
			$query->where('f.group_id='.(int)$this->rating_group_id.' AND f.type="rating"');
			$query->order('f.ordering ASC');

			$this->_db->setQuery($query);
			$this->rating_fields = $this->_db->loadObjectList('id');
			
			return true;
		}
		
		return false;
	}
	
	public function recalculateRating() {
		if (!$this->id) {
			return false;
		}
		
		$query = $this->_db->getQuery(true);
		$query->select('f.id, f.weight, AVG(i.rating) as rating');
		$query->from('#__djrevs_rating_fields as f');
		$query->join('LEFT', '#__djrevs_reviews_items as i ON i.field_id = f.id');
		$query->join('LEFT', '#__djrevs_reviews as r ON r.id = i.review_id');
		$query->where('r.published > 0 AND f.published=1 AND f.type="rating" AND r.object_id='.(int)$this->id);
		//$query->order('f.ordering ASC');
		$query->group('f.id, f.weight');
		
		$this->_db->setQuery($query);
		
		$avgs = $this->_db->loadObjectList();

		$avg = 0.0;
		$objects_avgs = array();
		
		if (count($avgs)) {
			$num = 0.0;
			$den = 0.0;
			
			foreach ($avgs as $value) {
				if ($value->rating == 0) {
					continue;
				}
				$num += $value->weight * $value->rating;
				$den += $value->weight;
				
				$objects_avgs[] = array('field_id' => $value->id, 'rating' => $value->rating);
			}
			$avg = ($den == 0) ? 0 : $num / $den;
		}
		
		$this->avg_rate = $avg;
		
		$is_stored = $this->store(false);
		
		if ($is_stored == false) {
			return false;
		}
		
		$this->_db->setQuery('DELETE FROM #__djrevs_objects_items WHERE object_id='.(int)$this->id);
		if ($this->_db->query() == false) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		if (count($objects_avgs)) {
			$query = $this->_db->getQuery(true);
			$query->insert('#__djrevs_objects_items');
			$query->columns(array('object_id', 'field_id', 'rating'));
			foreach ($objects_avgs as $avg) {
				$query->values((int)$this->id.', '.$avg['field_id'].', '.$avg['rating']);
			}

			$this->_db->setQuery($query);
			if ($this->_db->query() == false) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		
		return true;
		
	}
}
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

class DJReviewsTableReviews extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djrevs_reviews', 'id', $db);
	}
	public function bind($array, $ignore = '')
	{
		$return = parent::bind($array, $ignore);
		if (!$return) {
			return false;
		}
		
		if (isset($array['rating'])) {
			$this->rating = $array['rating'];
		}
		
		return true;
	}
	
	public function check() {

		if (empty($this->object_id)) {
			$this->setError(JText::_('COM_DJREVIEWS_INTERNAL_ERROR_OBJECT_ID'));
			return false;
		}
		
		if (empty($this->object_type)) {
			$this->setError(JText::_('COM_DJREVIEWS_INTERNAL_ERROR_OBJECT_TYPE'));
			return false;
		}
		
		if (empty($this->rating_group_id)) {
			$this->setError(JText::_('COM_DJREVIEWS_INTERNAL_ERROR_RATING_GROUP'));
			return false;
		}
		
		$this->_db->setQuery('SELECT COUNT(*) FROM #__djrevs_objects WHERE id='.(int)$this->object_id);
		if (!$this->_db->loadResult()) {
			$this->setError(JText::_('COM_DJREVIEWS_INTERNAL_ERROR_OBJECT_ID'));
			return false;
		}
		
		$this->_db->setQuery('SELECT COUNT(*) FROM #__djrevs_objects WHERE object_type='.$this->_db->quote($this->object_type));
		if (!$this->_db->loadResult()) {
			$this->setError(JText::_('COM_DJREVIEWS_INTERNAL_ERROR_OBJECT_TYPE'));
			return false;
		}
		
		$this->_db->setQuery('SELECT COUNT(*) FROM #__djrevs_rating_groups WHERE id='.(int)$this->rating_group_id);
		if (!$this->_db->loadResult()) {
			$this->setError(JText::_('COM_DJREVIEWS_INTERNAL_ERROR_RATING_GROUP'));
			return false;
		}
		
		return parent::check();
	}
	
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();

		/**
		 * TODO: it might or might not be temporary, but let's establish min/max rate and set it between <1,5>
		 */
		
		$min = 1;
		$max = 5;

		if (!$this->id) {
			if (!intval($this->created)) {
				$this->created = $date->toSql();
			}
			
			if ($user->id > 0 && !$user->guest) {
				if (empty($this->created_by)) {
					$this->created_by = $user->get('id');
				}
			}
			
			if (empty($this->ip)) {
				$client  = @$_SERVER['HTTP_CLIENT_IP'];
			    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
			    $remote  = $_SERVER['REMOTE_ADDR'];
			
			    if(filter_var($client, FILTER_VALIDATE_IP)) {
			        $this->ip = $client;
			    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
			        $this->ip = $forward;
			    } else {
			        $this->ip = $remote;
			    }
			}
			if (empty($this->post_info)) {
				$this->post_info = json_encode($_SERVER);
			}
		}

		$actual_user = $user;
		
		if ($this->created_by > 0 && $this->created_by != $user->id) {
			$actual_user = JFactory::getUser($this->created_by);
		}
		
		if ($actual_user) {
			if (empty($this->user_name)) {
				$this->user_name = $actual_user->name;
			}
			if (empty($this->user_login)) {
				$this->user_login = $actual_user->username;
			}
			if (empty($this->email)) {
				$this->email = $actual_user->email;
			}
		}
		
		$rating = null;
		if (isset($this->rating)) {
			$rating = $this->rating;
			$text_rating = array();
			unset($this->rating);
			
			//$this->_db->setQuery('SELECT id, weight FROM #__djrevs_rating_fields WHERE published=1 AND group_id='.(int)$this->rating_group_id);
			//$fields = $this->_db->loadObjectList('id');
			
			$query = $db->getQuery(true);
			$query->select('f.*');
			$query->from('#__djrevs_rating_fields AS f');
			$query->join('INNER', '#__djrevs_fields_groups AS fg ON fg.field_id = f.id AND fg.group_id = '.(int)$this->rating_group_id);
			$query->where('f.published = 1');
			$db->setQuery($query);
			$fields = $db->loadObjectList('id');

			$avg = 0.0;
			$num = 0.0;
			$den = 0.0;
			if (!empty($fields)) {
				foreach ($fields as $field_id => $field) {
					if (isset($rating[$field_id])) {
						if ($field->type == 'rating' && $rating[$field_id] > 0) {
							// not higher than $max
							$rating[$field_id] = min($max, $rating[$field_id]);
							
							// not lower than $min
							$rating[$field_id] = max($min, $rating[$field_id]);
							
							// integer - TODO: change this in the future.
							$rating[$field_id] = floor($rating[$field_id]);
							
							$num += $field->weight * $rating[$field_id];
							$den += $field->weight;
						} else if ($field->type == 'text' || $field->type == 'list') {
							$temp = trim($rating[$field_id]);
							if ($temp != '') {
								$text_rating[$field_id] = $temp;
							}
							unset($rating[$field_id]);
						}
					}
				}
				$avg = ($den == 0) ? 0 : $num / $den;
			}
			
			$this->avg_rate = $avg;
		}
		$is_stored = parent::store($updateNulls);
		
		if (!$is_stored) {
			return false;
		}

		if (!empty($rating)) {
			$this->_db->setQuery('DELETE FROM #__djrevs_reviews_items WHERE review_id='.(int)$this->id);
			if ($this->_db->query() == false) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			foreach ($rating as $field_id => $rate) {
				$rate_o = new stdClass();
				$rate_o->field_id = $field_id;
				$rate_o->review_id = $this->id;
				$rate_o->rating = floatval($rate);
				
				if ($this->_db->insertObject('#__djrevs_reviews_items', $rate_o) == false) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				
			}
		}
		
		if (!empty($text_rating)) {
			$this->_db->setQuery('DELETE FROM #__djrevs_reviews_items_text WHERE review_id='.(int)$this->id);
			if ($this->_db->query() == false) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			foreach ($text_rating as $field_id => $value) {
				$rate_o = new stdClass();
				$rate_o->field_id = $field_id;
				$rate_o->review_id = $this->id;
				$rate_o->rating = $value;
				if ($this->_db->insertObject('#__djrevs_reviews_items_text', $rate_o) == false) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}
		foreach ($text_rating as $k=>$v){
			$rating[$k] = $v;
		}
		$this->rating = $rating;
		unset($rating);
		
		if (!empty($this->object_id)) {
			$object = JTable::getInstance('Objects', 'DJReviewsTable');
			if ($object->load($this->object_id)) {
				return $object->recalculateRating();
			} else {
				$this->setError($object->getError());
				return false;
			}
		}
		return true;
	}
	
	public function loadGreedy($keys = null, $reset = true) {
		if ($loaded = $this->load($keys, $reset)) {
			
			$query = 'SELECT field_id, rating FROM #__djrevs_reviews_items WHERE review_id = '.$this->id.
					' UNION ALL SELECT field_id, rating FROM #__djrevs_reviews_items_text WHERE review_id = '.$this->id;
			
			$this->_db->setQuery($query);
			$rating = $this->_db->loadObjectList();
			
			
			$this->rating = array();
			if (!empty($rating)) {
				foreach ($rating as $k=>$v) {
					$this->rating[$v->field_id] = $v->rating;
				}
			}
		}
		
		return $loaded;
	}
}
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

require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djreviews/models/reviews.php');

jimport('joomla.application.component.helper');
jimport('joomla.html.pagination');

class DJReviewsModelReviewsList extends DJReviewsModelReviews
{
	public $object = null;
	
	public static $option_list = array(); 
	
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	public function populateState($ordering = null, $direction = null) {
		
		// This is ignored when calling a model from API scope

		// List state information.
		parent::populateState('a.created', 'desc');
		
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_djreviews'); 
		
		$object_id = $app->input->getInt('id');
		$start = $app->input->getInt('limitstart', 0);
		
		$limit = $app->input->getInt('limit', $params->get('revlist_limit', 10));
		
		$this->setState('filter.object_id', $object_id);
		$this->setState('list.start', $start);
		$this->setState('list.limit', $limit);
		
		$this->setState('filter.published', 'front');
		
	}
	
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$this->_db->setQuery($query, $limitstart, $limit);
		
		$result = $this->_db->loadObjectList('id');
		return $result;
	}
	
	public function getItems(){
		$items = parent::getItems();
		
		if(!empty($items)) {
			$items_ids = array_keys($items);
			
			$id_list = implode(',', $items_ids);
			$db = JFactory::getDbo();
			$query = "SELECT r.*, f.label as f_name FROM #__djrevs_reviews_items r "
					."INNER JOIN #__djrevs_rating_fields f ON f.id=r.field_id "
					."WHERE r.review_id IN (".$id_list.")";
			
			$db->setQuery($query);
				
			$ratings = $db->loadObjectList();
			
			foreach($ratings as $rating) {
				if (!isset($items[$rating->review_id])) {
					continue;
				}
				if (!isset($items[$rating->review_id]->rating_list)) {
					$items[$rating->review_id]->rating_list = array();
				}
				$items[$rating->review_id]->rating_list[] = $rating;
			}
			
			$query = "SELECT r.*, f.label as f_name, f.type FROM #__djrevs_reviews_items_text r "
					."INNER JOIN #__djrevs_rating_fields f ON f.id=r.field_id "
					."WHERE r.review_id IN (".$id_list.")";
						
			$db->setQuery($query);
	
			$text_fields = $db->loadObjectList();
				
			foreach($text_fields as $rating) {
				if (!isset($items[$rating->review_id])) {
					continue;
				}
				if (!isset($items[$rating->review_id]->text_rating_list)) {
					$items[$rating->review_id]->text_rating_list = array();
				}
				if ($rating->type == 'list') {
					$rating->rating = self::getListRating($rating);
				}
				$items[$rating->review_id]->text_rating_list[] = $rating;
			}
		}

		return $items;
	}
	
	public function getObject() {
		if (empty($this->object)) {
			$db = JFactory::getDbo();
			$object_id = $this->getState('filter.object_id');
			
			if ($object_id) {
				$query = $db->getQuery(true);
				$query->select('ro.* ');
				$query->from('#__djrevs_objects ro');
				$query->where('ro.id='.(int)$object_id);
				$db->setQuery($query);
				$this->object = $db->loadObject();
				
				$ratingModel = JModelLegacy::getInstance('Rating', 'DJReviewsModel', array('ignore_request'=>true));
				$this->object->_rating = $ratingModel->getItem($object_id);
			}	
		}
		return $this->object;
	}
	
	public function getPagination() {
		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Create the pagination object.
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new JPagination($this->getTotal(), $this->getStart(), $limit, 'djreviews_');
		
		
		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
	
	public static function getListRating($rating) {
		if (empty(self::$option_list)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djrevs_rating_fields')->where('type='.$db->quote('list'));
			$db->setQuery($query);
			self::$option_list = $db->loadObjectList('id');
		}
		if (!isset(self::$option_list[$rating->field_id])) {
			return null;
		}
		if (!isset(self::$option_list[$rating->field_id]->_options)) {
			$options = array();
			$options_set = explode(PHP_EOL, self::$option_list[$rating->field_id]->list_options);
			foreach($options_set as $option_element) {
				$pair = explode('=', $option_element, 2);
				if (count($pair) == 2) {
					$opt = new stdClass();
					$opt->value = htmlspecialchars(trim($pair[0]));
					$opt->text = $pair[1];
					$options[$opt->value] = $opt;
				}
			}
			
			self::$option_list[$rating->field_id]->_options = $options;
		}
		
		if (isset(self::$option_list[$rating->field_id]->_options[$rating->rating])) {
			return self::$option_list[$rating->field_id]->_options[$rating->rating]->text;
		}
		
		return false;
	}
}
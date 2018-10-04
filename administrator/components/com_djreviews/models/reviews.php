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

jimport('joomla.application.component.modellist');

class DJReviewsModelReviews extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'a.id', 'a.title', 'a.email', 'a.object_id', 'a.object_type', 'a.user_name', 'a.user_login', 'a.published', 'a.created', 'o.name', 'g.name', 'a.avg_rate'	
			);
		}

		parent::__construct($config);
	}
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.created', 'desc');

		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$state = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published');
		$this->setState('filter.published', $state);
		
		$object_type = $this->getUserStateFromRequest($this->context.'.filter.rating_object_type', 'filter_rating_object_type');
		$this->setState('filter.rating_object_type', $object_type);
		
		$rating_group = $this->getUserStateFromRequest($this->context.'.filter.rating_group', 'filter_rating_group');
		$this->setState('filter.rating_group', $rating_group);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_djreviews');
		$this->setState('params', $params);
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.rating_object_type');
		$id	.= ':'.serialize($this->getState('filter.rating_group'));
		$id	.= ':'.$this->getState('filter.object_id');
		$id	.= ':'.$this->getState('filter.rated');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$select_default = 'a.*, uc.name AS editor, g.name as group_name, o.name as object_name, o.link as object_url, ua.id as user_id ';

		$query->select($this->getState('list.select', $select_default));

		$query->from('#__djrevs_reviews AS a');

		// Join over the users for the checked out user.
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
		
		// Join over the users for the author user.
		$query->join('LEFT', '#__users AS ua ON ua.id=a.created_by');
		
		// Join over groups
		$query->join('LEFT', '#__djrevs_rating_groups AS g ON g.id=a.rating_group_id');
		
		// Join over objects
		$query->join('LEFT', '#__djrevs_objects AS o ON o.id=a.object_id');

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else {
				$search = $db->quote('%'.$db->escape($search, true).'%');
				$query->where('(a.title LIKE '.$search.' OR a.message LIKE '.$search.' OR a.email LIKE '.$search.' OR a.user_name LIKE '.$search.' OR a.user_login LIKE '.$search.')');
			}
		}
		
		$state = $this->getState('filter.published', false);
		if (is_numeric($state)) {
			$query->where('a.published='.(int)$state);
		} else if ($state == 'front') {
			$query->where('a.published!=0');
		}
		
		$rating_group = $this->getState('filter.rating_group', false);
		if ($rating_group) {
			if (is_array($rating_group)) {
				JArrayHelper::toInteger($rating_group);
				$query->where('a.rating_group_id IN ('.implode(',', $rating_group).')');
			} else {
				$query->where('a.rating_group_id='.(int)$rating_group);
			}
		}
		
		$object_type = $this->getState('filter.rating_object_type', false);
		if ($object_type) {
			$query->where('a.object_type='.$db->quote($object_type));
		}
		
		$object_id = $this->getState('filter.object_id', false);
		if ((int)$object_id) {
			$query->where('a.object_id='.(int)$object_id);
		}
		
		$rated = $this->getState('filter.rated');
		if ($rated) {
			$query->where('a.avg_rate > 0.0');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.name');
		$orderDirn	= $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
	
	public function getRatingGroups() {
		$this->_db->setQuery('SELECT id as value, name as text FROM #__djrevs_rating_groups ORDER BY name ASC');
		$groups = $this->_db->loadObjectList();
		return $groups;
	}
	
	public function getRatingObjects() {
		$this->_db->setQuery('SELECT object_type as value, object_type as text FROM #__djrevs_reviews GROUP BY object_type ORDER BY object_type ASC ');
		$objects = $this->_db->loadObjectList('value');
		
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('djreviews');
		
		foreach($objects as $k=>$object) {
			$results = $dispatcher->trigger('onObjectPrepareName', array($object->text));
			if (!empty($results)) {
				foreach($results as $result) {
					if (trim($result) == false) continue;
					$objects[$k]->text = $result;
					break;
				}
			}
		}
		return $objects;
	}
}
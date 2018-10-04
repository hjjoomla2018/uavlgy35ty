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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.modellist');

class DJMediatoolsModelCategories extends JModelList
{
	private $_categories = null;
	private $_category = null;
	private $_params = null;
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'parent_id', 'a.parent_id', 'parent_title',
				'ordering', 'a.ordering',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'published', 'a.published'
			);
		}

		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		
		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		$this->setState('list.start', $limitstart);

		$orderCol	= JRequest::getCmd('filter_order', 'ordering');
		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = 'ordering';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder	=  JRequest::getCmd('filter_order_Dir', 'ASC');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$id = JRequest::getVar('id', 0, '', 'int');
		$this->setState('category.id', $id);
		
		$this->setState('filter.published',	1);
		
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('category.id');
		$id	.= ':'.$this->getState('filter.published');
		
		return parent::getStoreId($id);
	}
	
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*, CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug'
			)
		);
		$query->from('#__djmt_albums AS a');
		
		// Join over the categories.
		$query->select('c.title AS parent_title');
		$query->join('LEFT', '#__djmt_albums AS c ON c.id = a.parent_id');
		
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published = 0 OR a.published = 1)');
		}
		
		$query->where('a.visible = 1');
		
		// Filter by category state
		$category = $this->getState('category.id');
		if (is_numeric($category)) {
			$query->where('a.parent_id = ' . (int) $category);
		}
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		$query->order($db->escape($orderCol.' '.$orderDirn));
		
		return $query;
	}
	
	public function getItems()
	{
		$id = $this->getState('category.id');
		if ($this->_categories === null) $this->_categories = array();
		
		if(!isset($this->_categories[$id])) $this->_categories[$id] = parent::getItems();
		
		return $this->_categories[$id];
	}
	
	public function getItem($id = null)
	{
		if (is_null($id)) {
			$id = $this->getState('category.id');
		}
		if ($this->_category === null) $this->_category = array();
		
		if (!isset($this->_category[$id]))
		{
			$this->_category[$id] = false;

			if($id == 0) {
				$this->_category[$id] = 'root';
			} else {
				// Get a level row instance.
				JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_djmediatools'.DS.'tables');
				$table = JTable::getInstance('Categories', 'DJMediatoolsTable');
	
				// Attempt to load the row.
				if ($table->load($id))
				{
					// Check published state.
					if ($published = $this->getState('filter.published'))
					{
						if ($table->published != $published) {
							return $this->_category[$id];
						}
					}
	
					// Convert the JTable to a clean JObject.
					$properties = $table->getProperties(1);
					$this->_category[$id] = JArrayHelper::toObject($properties, 'JObject');
					$this->_category[$id]->params = new JRegistry($this->_category[$id]->params); 
				}
				else if ($error = $table->getError()) {
					$this->setError($error);
				}
			}
		}

		return $this->_category[$id];
	}
	
	function getParams($component = true) {
		
			// we have to take clear JRegistry object to avoid overriding static component params
			$params = new JRegistry;
			
			// global params first
			$cparams = JComponentHelper::getParams( 'com_djmediatools' );
			$params->merge($cparams);
			
			// override global params with menu params only for component view
			if($component) {
				$app = JFactory::getApplication();
				$mparams = $app->getParams('com_djmediatools');
				//$mparams = clone($mparams);
				$params->merge($mparams);
			}
			
			// override global/menu params with category params
			$id = $this->getState('category.id');
			$category = $this->getItem($id);
			if($category && $category != 'root') {				
				$cparams = $category->params;
				$params->merge($cparams);
			}
			
			// set default values
			$params->def('blank', $params->get('blank', JURI::root(true).'/components/com_djmediatools/assets/images/blank.gif'));
			
		return $params;
	}
	
	private function getSortedItems(&$items, $parent = 0, $level = 0)
	{
	
		$categories = array();
	
		foreach($items as $key => $item) {
				
			if(isset($item->level)) {
				continue;
			}
			if($item->parent_id == $parent) {
				$item->key = $key;
				$item->level = $level;
				$categories[] = $item;
				$categories = array_merge($categories, $this->getSortedItems($items, $item->id, $level + 1));
			}
	
		}
	
		return $categories;
	}
	
	public function getSelectOptions ($disable_default = false, $disable_self = false, $self_id = 0, $only_component = false){
	
		$this->getState('filter.published');
		$this->setState('filter.published', '');
		$this->getState('filter.category.id');
		$this->setState('filter.category.id', '');
		$this->state->set('list.ordering','a.ordering');
		$this->state->set('list.direction','asc');
		$this->setState('list.start', 0);
		$this->setState('list.limit', 0);
		//$only_component = (JRequest::getVar('view')=='item' && JRequest::getVar('option')=='com_djmediatools' ? true : false);
	
		$options = array();
		if(!$disable_default) $options[] = JHTML::_('select.option', '0', JText::_('COM_DJMEDIATOOLS_ROOT_CATEGORY'),'value','text');
		
		$cats = $this->getItems();
		$items = $this->getSortedItems($cats);
		$level = 0;
		$disabled = false;
		if(!$self_id) $self_id = JRequest::getInt('id', null);
	
		foreach ($items as $item) {
			$prefix = '';
			for ($i = 0; $i < $item->level; $i++) {
				$prefix .= ' - ';
			}
	
			if($disable_self) {
				if($disabled && $item->level <= $level) {
					$disabled = false;
					$disable_self = false;
				} else if($item->id == $self_id) {
					$disabled = true;
					$level = $item->level;
				}
					
			} else if($only_component && $item->source!='component') {
				$disabled = true;
			} else {
				$disabled = false;
			}
				
			$options[] = JHTML::_('select.option', $item->id, $prefix . $item->title,'value','text', $disabled);
		}
	
		return $options;
	
	}
}

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class Djcatalog2ModelRelateditems extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'alias', 'a.alias',
				'category_name',
				'producer_name',
				'ordering', 'a.ordering',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'language', 'a.language'
				);
		}

		parent::__construct($config);
	}
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.name', 'asc');
		
		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$category = $this->getUserStateFromRequest($this->context.'.filter.category', 'filter_category', '');
		$this->setState('filter.category', $category);

		$producer = $this->getUserStateFromRequest($this->context.'.filter.producer', 'filter_producer', '');
		$this->setState('filter.producer', $producer);
		
		$item_id = $this->getUserStateFromRequest($this->context.'.filter.item_id', 'item_id', '');
		$this->setState('filter.item_id', $item_id);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_djcatalog2');
		$this->setState('params', $params);
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.category');
		$id	.= ':'.$this->getState('filter.producer');
		$id	.= ':'.$this->getState('filter.item_id');

		return parent::getStoreId($id);
	}
	
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();
	
		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}
		// Load the list items.
		$query = $this->_getListQuery();
		//echo str_replace('#_', 'jos',(string)$query);die();
		$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
	
		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	
		// Add the items to the internal cache.
		$this->cache[$store] = $items;
		
		$this->bindImages($store);
	
		return $this->cache[$store];
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$item_id =$this->getState('filter.item_id');
		
		// Select the required fields from the table.
		$query->select(
		$this->getState(
				'list.select',
				'a.*'
				)
				);
				$query->from('#__djc2_items AS a');

				// Join over the categories.
				$query->select('c.name AS category_name, c.id AS cat_id');
				//$query->join('INNER', '#__djc2_items_categories AS ic ON a.id = ic.item_id AND ic.default=1');
				//$query->join('LEFT', '#__djc2_categories AS c ON c.id = ic.category_id');
				$query->join('LEFT', '#__djc2_categories AS c ON c.id = a.cat_id');
				
				// Join over the producers.
				$query->select('p.name AS producer_name');
				$query->join('LEFT', '#__djc2_producers AS p ON p.id = a.producer_id');
				
				// Join over the users for the checked out user.
				$query->select('uc.name AS editor');
				$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
				
				//$query->select('img.fullname AS item_image, img.caption AS image_caption, img.path as image_path, img.fullpath as image_fullpath');
				//$query->join('LEFT', '#__djc2_images AS img ON img.item_id=a.id AND img.type=\'item\' AND img.ordering=1');
				//$query->join('left', '(select im1.fullname, im1.caption, im1.type, im1.item_id, im1.path, im1.fullpath from #__djc2_images as im1, (select item_id, type, min(ordering) as lowest_order from #__djc2_images group by item_id, type) as im2 where im1.item_id = im2.item_id and im1.type=im2.type and im1.ordering = im2.lowest_order group by im1.type, im1.item_id, im1.path, im1.fullpath) AS img ON img.item_id = a.id AND img.type=\'item\'');
				
				$query->select('rc.related_count');
				$query->join('LEFT', ' (SELECT COUNT(item_id) as related_count, related_item FROM #__djc2_items_related WHERE item_id=\''.$item_id.'\' GROUP BY related_item) AS rc ON a.id=rc.related_item ');
				
				
				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published)) {
					$query->where('a.published = ' . (int) $published);
				}
				else if ($published === '') {
					$query->where('(a.published = 0 OR a.published = 1)');
				}


				// Filter by search in title.
				$search = $this->getState('filter.search');
				if (!empty($search)) {
					if (stripos($search, 'id:') === 0) {
						$query->where('a.id = '.(int) substr($search, 3));
					}
					else {
						$search = $db->quote('%'.$db->escape($search, true).'%');
						$query->where('(a.name LIKE '.$search.' OR a.alias LIKE '.$search.')');
					}
				}

				// Filter by category state
				$category = $this->getState('filter.category');
				if (is_numeric($category) && $category != 0) {
					//$query->where('a.cat_id = ' . (int) $category);
					$categories = Djc2Categories::getInstance(array('state'=>'1'));
					if ($parent = $categories->get((int)$category) ) {
						$childrenList = array($parent->id);
						$parent->makeChildrenList($childrenList);
						if ($childrenList) {
							$cids = implode(',', $childrenList);
							$db->setQuery('SELECT item_id 
										   FROM #__djc2_items_categories AS ic
										   INNER JOIN #__djc2_categories AS c ON c.id=ic.category_id 
										   WHERE category_id IN ('.$cids.') ');
							$items = $db->loadColumn();
							if (count ($items)) {
								$items = array_unique($items);
								$query->where('a.id IN ('.implode(',',$items).')');
							} else {
								$query->where('1=0');
							}
							//$where[] = 'i.cat_id IN ( '.$cids.' )';
						}
						else if ($category != 0){
							$query->where('1=0');
						}
					}
				}

				// Filter by producer state
				$producer = $this->getState('filter.producer');
				if (is_numeric($producer) && $producer != 0) {
					$query->where('a.producer_id = ' . (int) $producer);
				}


				// Add the list ordering clause.
				$orderCol	= $this->state->get('list.ordering');
				$orderDirn	= $this->state->get('list.direction');
				if ($orderCol == 'a.ordering' || $orderCol == 'category_name') {
					$orderCol = 'category_name '.$orderDirn.', a.ordering';
				}
				
				$query->order($db->escape('rc.related_count desc, '.$orderCol.' '.$orderDirn));
				// $query->order($db->escape($orderCol.' '.$orderDirn));
				
				return $query;
	}
	
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$this->_db->setQuery($query, $limitstart, $limit);
		$result = $this->_db->loadObjectList('id');
	
		return $result;
	}
	
	protected function _getListCount($query)
	{
		// Use fast COUNT(*) on JDatabaseQuery objects if there no GROUP BY or HAVING clause:
		if ($query instanceof JDatabaseQuery
		&& $query->type == 'select'
				&& $query->group === null
				&& $query->having === null)
		{
				
			$query = clone $query;
			$query->clear('select')->clear('order')->select('COUNT(*)');
			$this->_db->setQuery($query);
			return (int) $this->_db->loadResult();
		}
	
		// Otherwise fall back to inefficient way of counting all results.
		$this->_db->setQuery($query);
		$this->_db->execute();
	
		return (int) $this->_db->getNumRows();
	}

	public function getCategories(){
		if(empty($this->_categories)) {
			$query = "SELECT * FROM #__djc2_categories ORDER BY name";
			$this->_categories = $this->_getList($query,0,0);
		}
		return $this->_categories;
	}

	public function getProducers(){
		if(empty($this->_producers)) {
			$query = "SELECT * FROM #__djc2_producers ORDER BY name";
			$this->_producers = $this->_getList($query,0,0);
		}
		return $this->_producers;
	}
	
	public function bindImages($store) {
		if (!empty($this->cache[$store])) {
			$ids = array_keys($this->cache[$store]);
			if (empty($ids)) {
				return;
			}
			$db = JFactory::getDbo();
	
			$query = $db->getQuery(true);
			$query->select('i.id, img.fullname as item_image, img.caption AS image_caption, img.path AS image_path, img.fullpath AS image_fullpath');
			$query->from('#__djc2_items as i');
			$query->join('inner', '#__djc2_images as img on img.id=(select id from #__djc2_images where type=\'item\' and item_id=i.id order by ordering asc limit 1)');
			$query->where('i.id IN ('.implode(',', $ids).')');
			$db->setQuery($query);
			$image_list = $db->loadObjectList('id');
	
			foreach($this->cache[$store] as $k=>$row) {
				$this->cache[$store][$k]->item_image = isset($image_list[$row->id]) ? $image_list[$row->id]->item_image : null;
				$this->cache[$store][$k]->image_caption = isset($image_list[$row->id]) ? $image_list[$row->id]->image_caption : null;
				$this->cache[$store][$k]->image_path = isset($image_list[$row->id]) ? $image_list[$row->id]->image_path : null;
				$this->cache[$store][$k]->image_fullpath = isset($image_list[$row->id]) ? $image_list[$row->id]->image_fullpath : null;
			}
		}
	}
	
	public function assign($cid, $listed_cid, $item_id) {
		if (is_array($cid) && count($listed_cid) > 0 && !empty($item_id)) {
			$db = JFactory::getDbo();
			$query = 'DELETE FROM #__djc2_items_related WHERE item_id=\''.$item_id.'\' and related_item in ('.implode(',',$listed_cid).')';
            $db->setQuery($query);
            $db->query();
            if ($db->getErrorMsg()){
            	$this->setError($db->getErrorMsg());
                return false;
			}
			
			if (count($cid) > 0) {
				foreach ($cid as $value) {
					$db->setQuery('INSERT INTO #__djc2_items_related SET item_id=\''.$item_id.'\', related_item=\''.$value.'\'');
					$db->query();
				}
			}
			return true;
		} else {
			return false;
		}
		
	}

}
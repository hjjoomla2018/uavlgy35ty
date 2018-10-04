<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class Djcatalog2ModelItems extends JModelList
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
				'a.featured',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'language', 'a.language',
				'hits', 'a.hits', 'a.available', 'a.access', 'a.price', 'a.special_price'
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
		
		$additional = $this->getUserStateFromRequest($this->context.'.filter.additional', 'filter_additional', '');
		$this->setState('filter.additional', $additional);
		
		$parent = ($app->input->get('layout', false) == false) ? $this->getUserStateFromRequest($this->context.'.filter.parent', 'filter_parent', 0) : 0;
		$this->setState('filter.parent', $parent);

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
		$id	.= ':'.$this->getState('filter.parent');
		$id	.= ':'.$this->getState('filter.ids');
		$id	.= ':'.$this->getState('filter.additional');

		return parent::getStoreId($id);
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
			$query->clear('select')->clear('order')->select('COUNT(distinct(a.id))');
			$this->_db->setQuery($query);
			return (int) $this->_db->loadResult();
		}
	
		// Otherwise fall back to inefficient way of counting all results.
		$this->_db->setQuery($query);
		$this->_db->execute();
	
		return (int) $this->_db->getNumRows();
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
		
		$task = JFactory::getApplication()->input->get('task');
		
		if ($task == 'export_filtered' || $task == 'export_selected') {
			$this->bindAttributes($store);
		}
		if ($task != 'export_filtered' && $task != 'export_selected') {
			$this->bindImages($store);
		}
		
		return $this->cache[$store];
	}

	protected function getListQuery()
	{
		
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		// Select the required fields from the table.
		
		//$select_default = 'a.*, c.name AS category_name, c.id AS cat_id, p.name AS producer_name, uc.name AS editor, img.fullname AS item_image, img.caption AS image_caption, img.path as image_path, img.fullpath as image_fullpath ';
		$select_default = 'a.*, c.name AS category_name, c.id AS cat_id, p.name AS producer_name, uc.name AS editor ';
		
		$query->select($this->getState('list.select', $select_default));
		$query->from('#__djc2_items AS a');
		
		// Join over the categories.
		//$query->select('c.name AS category_name, c.id AS cat_id');
		//$query->join('INNER', '#__djc2_items_categories AS ic ON a.id = ic.item_id AND ic.default=1');
		//$query->join('LEFT', '#__djc2_categories AS c ON c.id = ic.category_id');
		$query->join('LEFT', '#__djc2_categories AS c ON c.id = a.cat_id');
		
		// Join over the producers.
		//$query->select('p.name AS producer_name');
		$query->join('LEFT', '#__djc2_producers AS p ON p.id = a.producer_id');
		
		// Join over the users for the checked out user.
		//$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
		
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		
		//$query->select('img.fullname AS item_image, img.caption AS image_caption');
		//$query->join('LEFT', '#__djc2_images AS img ON img.item_id=a.id AND img.type=\'item\' AND img.ordering=1');
		//$query->join('left', '(SELECT im1.* from #__djc2_images as im1 GROUP BY im1.item_id, im1.type ORDER BY im1.ordering asc) AS img ON img.item_id = a.id AND img.type=\'item\'');
		//$query->join('left', '(select im1.fullname, im1.caption, im1.type, im1.item_id, im1.path, im1.fullpath from #__djc2_images as im1, (select item_id, type, min(ordering) as lowest_order from #__djc2_images group by item_id, type) as im2 where im1.item_id = im2.item_id and im1.type=im2.type and im1.ordering = im2.lowest_order group by im1.type, im1.item_id, im1.path, im1.fullpath) AS img ON img.item_id = a.id AND img.type=\'item\'');
		//$query->join('left', '#__djc2_images as img on img.id = (select id from #__djc2_images where type=\'item\' and item_id = a.id order by ordering asc limit 1)');
		
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		}
		/*else if ($published === '') {
		 $query->where('(a.published = 0 OR a.published = 1)');
		}*/
		
		
		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else {
				$search = $db->quote('%'.$db->escape($search, true).'%');
				$query->where('(a.name LIKE '.$search.' OR a.alias LIKE '.$search.' OR a.sku LIKE '.$search.')');
			}
		}
		
		// Filter by category state
		$category = $this->getState('filter.category');
		if (is_numeric($category) && $category != 0) {
		
			$category_subquery = 'SELECT DISTINCT ic.item_id '
					.'FROM #__djc2_items_categories AS ic '
					.'INNER JOIN #__djc2_categories AS cc ON cc.id=ic.category_id ';
		
			$categories = Djc2Categories::getInstance();
			if ($parent = $categories->get((int)$category) ) {
				$childrenList = array($parent->id);
				$parent->makeChildrenList($childrenList);
				if ($childrenList) {
					$cids = implode(',', $childrenList);
					$category_subquery .= ' WHERE ic.category_id IN ('.$cids.')';
		
					$query->join('inner', '('.$category_subquery.') as category_filter ON a.id = category_filter.item_id');
		
				}
			}
		}
		
		// Filter by producer state
		$producer = $this->getState('filter.producer');
		if (is_numeric($producer) && $producer != 0) {
			$query->where('a.producer_id = ' . (int) $producer);
		}
		
		$parent = $this->getState('filter.parent');
		if (is_numeric($parent)) {
			$query->where('a.parent_id = ' . (int) $parent);
		}
		
		// Filter by primary keys
		$item_ids = $this->getState('filter.ids');
		if ($item_ids != '') {
			$query->where('a.id IN ('.$item_ids.')');
		}
		
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		$additional = $this->getState('filter.additional');
		if ($additional) {
			switch($additional) {
				case 'FTR': {
					$query->where('a.featured=1');
					break;
				}
				case 'NFTR': {
					$query->where('a.featured=0');
					break;
				}
				case 'AVL': {
					$query->where('a.available=1');
					break;
				}
				case 'NAVL': {
					$query->where('a.available=0');
					break;
				}
				case 'HCHILD': {
					if (!is_numeric($parent) || $parent < 1) {
						$subquery = $db->getQuery(true);
						$subquery->select('parent_id, COUNT(*) as child_count');
						$subquery->from('#__djc2_items');
						$subquery->group('parent_id');
						
						$query->join('LEFT', '('.(string)$subquery.') AS chl ON chl.parent_id=a.id');
						$query->where('chl.child_count > 0');
						break;
					}
				}
				case 'NHCHILD': {
					if (!is_numeric($parent) || $parent < 1) {
						$subquery = $db->getQuery(true);
						$subquery->select('parent_id, COUNT(*) as child_count');
						$subquery->from('#__djc2_items');
						$subquery->group('parent_id');
						
						$query->join('LEFT', '('.(string)$subquery.') AS chl ON chl.parent_id=a.id');
						$query->where('(chl.child_count = 0 OR chl.child_count IS NULL)');
						break;
					}
				}
				default: break;
			}
		}
		
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.name');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		if ($orderCol == 'a.ordering' || $orderCol == 'category_name') {
			$orderCol = 'c.name '.$orderDirn.', a.ordering';
		}
		
		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
				
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
	function recreateThumbnails($cid = array())
	{
		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$query = 'SELECT fullname FROM #__djc2_images'
					. ' WHERE item_id IN ( '.$cids.' )'
					. ' AND type=\'item\''
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			$items = $this->_db->loadObjectList();
			$params = JComponentHelper::getParams( 'com_djcatalog2' );
				
			foreach($items as $item) {
				DJCatalog2ImageHelper::processImage(DJCATIMGFOLDER, $item->fullname, 'item', $params);
			}
		}
		return true;
	}
	function bindAttributes($store) {
		if (!empty($this->cache[$store])) {
			$ids = array_keys($this->cache[$store]);
			if (empty($ids)) {
				return;
			}
			$db = JFactory::getDbo();
				
			$query_int = $db->getQuery(true);
			$query_text = $db->getQuery(true);
			$query_date = $db->getQuery(true);
				
			$query_int->select('fields.alias, fields.type, fields.ordering, fieldvalues.item_id, fieldvalues.field_id, fieldvalues.id as value_id, fieldoptions.id as option_id, fieldoptions.value');
			$query_int->from('#__djc2_items_extra_fields_values_int as fieldvalues');
			$query_int->join('inner', '#__djc2_items as items on items.id=fieldvalues.item_id' );
			$query_int->join('inner','#__djc2_items_extra_fields as fields ON fields.id = fieldvalues.field_id');
			$query_int->join('left','#__djc2_items_extra_fields_options as fieldoptions ON fieldoptions.id = fieldvalues.value AND fieldoptions.field_id = fields.id');
			$query_int->where('fieldvalues.item_id IN ('.implode(',',$ids).')');
			$query_int->order('fieldvalues.field_id asc, fieldvalues.field_id asc');
				
			$query_text->select('fields.alias, fields.type, fields.ordering, fieldvalues.item_id, fieldvalues.field_id, fieldvalues.id as value_id, 0 as option_id, fieldvalues.value');
			$query_text->from('#__djc2_items_extra_fields_values_text as fieldvalues');
			$query_text->join('inner', '#__djc2_items as items on items.id=fieldvalues.item_id' );
			$query_text->join('inner','#__djc2_items_extra_fields as fields ON fields.id = fieldvalues.field_id');
			$query_text->where('fieldvalues.item_id IN ('.implode(',',$ids).')');
			$query_text->order('fieldvalues.field_id asc, fieldvalues.field_id asc');
				
			$query_date->select('fields.alias, fields.type, fields.ordering, fieldvalues.item_id, fieldvalues.field_id, fieldvalues.id as value_id, 0 as option_id, fieldvalues.value');
			$query_date->from('#__djc2_items_extra_fields_values_date as fieldvalues');
			$query_date->join('inner', '#__djc2_items as items on items.id=fieldvalues.item_id' );
			$query_date->join('inner','#__djc2_items_extra_fields as fields ON fields.id = fieldvalues.field_id');
			$query_date->where('fieldvalues.item_id IN ('.implode(',',$ids).')');
			$query_date->order('fieldvalues.field_id asc, fieldvalues.field_id asc');
				
			//$query = 'SELECT * FROM (('.(string)$query_int.') UNION DISTINCT ('.(string)$query_text.')) as list ORDER BY list.field_id asc, list.item_id asc';
			//echo str_replace('#_','jos',$query);die();
				
			// I decided not to use UNION because of FaLang translation issues
				
			$db->setQuery($query_int);
			$int_attributes = $db->loadObjectList();
				
			$db->setQuery($query_text);
			$text_attributes = $db->loadObjectList();
				
			$db->setQuery($query_date);
			$date_attributes = $db->loadObjectList();
				
				
			foreach ($text_attributes as $attribute) {
				$field = '_'.$attribute->alias;
				$this->cache[$store][$attribute->item_id]->$field = $attribute->value;
				//$this->cache[$store][$attribute->item_id]->$field = $attribute->optionvalues ? $attribute->optionvalues : $attribute->value;
			}
			foreach ($date_attributes as $attribute) {
				$field = '_'.$attribute->alias;
				$this->cache[$store][$attribute->item_id]->$field = $attribute->value;
			}
			foreach ($int_attributes as $attribute) {
				$field = '_'.$attribute->alias;
				if (!isset($this->cache[$store][$attribute->item_id]->$field) || !is_array($this->cache[$store][$attribute->item_id]->$field)) {
					$this->cache[$store][$attribute->item_id]->$field = array();
				}
				$tmp_arr = $this->cache[$store][$attribute->item_id]->$field;
				$tmp_arr[] = $attribute->value;
				$this->cache[$store][$attribute->item_id]->$field = $tmp_arr;
			}
		}
	}
	
	function bindImages($store) {
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

}
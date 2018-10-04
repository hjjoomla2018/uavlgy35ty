<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

class modDjc2RelateditemsHelper {
	var $_data = null;
	var $_cparams = null;
	var $_mparams = null;
	var $_categoryparams = array();
	
	function __construct( $params=array() )
	{
		$app = JFactory::getApplication();
		
		$cparams = $app->getParams('com_djcatalog2');
		$ncparams = new JRegistry();
		$ncparams->merge($cparams);
		
		$this->_cparams = $ncparams;
		
		$this->_mparams = $params;
	}
	function getData() {
		$app = JFactory::getApplication();
		
		if (!$this->_data){
			$option = $app->input->get('option', '', 'string');
			$view = $app->input->get('view', '', 'string');
			$id = $app->input->get('id', '', 'int');
			if ($option != 'com_djcatalog2' || $view != 'item' || !$id) {
				return false;
			}
			$db					= JFactory::getDbo();
			$db->setQuery($this->_buildQuery());
			$this->_data = $db->loadObjectList('id');
			
			$ids = array_keys($this->_data);
			if (empty($ids)) {
				return false;
			}
			
			$query = $db->getQuery(true);
			$query->select('i.id, img.fullname as item_image, img.caption AS image_caption, img.path AS image_path, img.fullpath AS image_fullpath');
			$query->from('#__djc2_items as i');
			$query->join('inner', '#__djc2_images as img on img.id=(select id from #__djc2_images where type=\'item\' and item_id=i.id order by ordering asc limit 1)');
			$query->where('i.id IN ('.implode(',', $ids).')');
			$db->setQuery($query);
			$image_list = $db->loadObjectList('id');
			
			foreach ($this->_data as $key => $item) {
				if ($this->_mparams->get('show_price') == 2 || ( $this->_mparams->get('show_price') == 1 && $item->price > 0.0)) {
					$catParams = $this->getCategoryParams($item->cat_id);
					if ($item->price != $item->final_price) {
						$this->_data[$key]->price = DJCatalog2HtmlHelper::formatPrice($item->price, $catParams);
						$this->_data[$key]->special_price = DJCatalog2HtmlHelper::formatPrice($item->special_price, $catParams);
					} else {
						$this->_data[$key]->price = DJCatalog2HtmlHelper::formatPrice($item->price, $catParams);
						$this->_data[$key]->special_price = null;
					}
				}
				else {
					$this->_data[$key]->price = null;
					$this->_data[$key]->special_price = null;
				}
				
				$this->_data[$key]->slug = (empty($this->_data[$key]->alias)) ? $this->_data[$key]->id : $this->_data[$key]->id.':'.$this->_data[$key]->alias;
				$this->_data[$key]->catslug = (empty($this->_data[$key]->category_alias)) ? $this->_data[$key]->category_id : $this->_data[$key]->category_id.':'.$this->_data[$key]->category_alias;
				$this->_data[$key]->prodslug = (empty($this->_data[$key]->producer_alias)) ? $this->_data[$key]->producer_id : $this->_data[$key]->producer_id.':'.$this->_data[$key]->producer_alias;
				
				$this->_data[$key]->item_image = isset($image_list[$this->_data[$key]->id]) ? $image_list[$this->_data[$key]->id]->item_image : null;
				$this->_data[$key]->image_caption = isset($image_list[$this->_data[$key]->id]) ? $image_list[$this->_data[$key]->id]->image_caption : null;
				$this->_data[$key]->image_path = isset($image_list[$this->_data[$key]->id]) ? $image_list[$this->_data[$key]->id]->image_path : null;
				$this->_data[$key]->image_fullpath = isset($image_list[$this->_data[$key]->id]) ? $image_list[$this->_data[$key]->id]->image_fullpath : null;
			}
		}
		return $this->_data;
	}
	
	function getCategoryParams($catid) {
		if (!isset($this->_categoryparams[$catid])) {
			$categories = Djc2Categories::getInstance(array('state'=>'1'));
			$category = $categories->get($catid);
			$this->_categoryparams[$catid] = $this->_cparams;
			if (!empty($category)) {
				$catpath = array_reverse($category->getPath());
				foreach($catpath as $k=>$v) {
					$parentCat = $categories->get((int)$v);
					if (!empty($parentCat) && !empty($category->params)) {
						$catparams = new JRegistry($parentCat->params); 
						$this->_categoryparams[$catid]->merge($catparams);
					}
				}
			}
		}		
		return $this->_categoryparams[$catid];
	}
	
	function _buildQuery()
	{
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT i.*, CASE WHEN (i.special_price > 0.0 AND i.special_price < i.price) THEN i.special_price ELSE i.price END as final_price, c.id AS category_id, p.id AS producer_id, c.name AS category, c.alias as category_alias, p.name AS producer, p.alias as producer_alias, p.published as publish_producer '
			. ' FROM #__djc2_items AS i '
			. ' LEFT JOIN #__djc2_categories AS c ON c.id = i.cat_id '
			. ' LEFT JOIN #__djc2_producers AS p ON p.id = i.producer_id '
			. $where
			. $orderby
		;
		
		//echo str_replace('#_', 'jos', $query);die();
		return $query;
	}

	function _buildContentOrderBy()
	{
		$filter_order		= $this->_mparams->get('orderby','i.ordering');
		$filter_order_Dir	= $this->_mparams->get('orderdir','asc');
		$filter_featured	= $this->_mparams->get('featured_first', 0);
		$limit = ($this->_mparams->get('items_limit',0));
		if ($filter_order != 'i.ordering' && $filter_order != 'category' && $filter_order != 'producer' && $filter_order != 'i.price' && $filter_order != 'i.name' && $filter_order != 'rand()') {
			$filter_order = 'i.ordering';
		}
		if ($filter_order_Dir != 'asc' && $filter_order_Dir != 'desc') {
			$filter_order_Dir = 'asc';
		}
		
		if ($filter_featured) {
			$orderby 	= ' ORDER BY i.featured DESC, '.$filter_order.' '.$filter_order_Dir.' , i.ordering, c.ordering ';
		}
		else if ($filter_order == 'i.ordering'){
			$orderby 	= ' ORDER BY i.ordering '.$filter_order_Dir.', c.ordering '.$filter_order_Dir;
		} 
		else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , i.ordering, c.ordering ';
		}
		if ($limit > 0) {
			$orderby .= ' LIMIT '.$limit;
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$db					= JFactory::getDBO();
		$app = JFactory::getApplication();
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		$groups = implode(',', $groups);
		
		$source = $this->_mparams->get('source', 'related');
		
		$filter_catid		= ($source == 'category' || $source == 'catnprod') ? true : false;
		$filter_producerid	= ($source == 'producer' || $source == 'catnprod') ? true : false;
		
		$filter_featured	= $this->_mparams->get('featured_only', 0);
		
		$nullDate = $db->quote($db->getNullDate());
		$date = JFactory::getDate();
		$nowDate = $db->quote($date->toSql());
		
		$option = $app->input->get('option', '', 'string');
		$view = $app->input->get('view', '', 'string');
		$id = $itemId = $app->input->get('id', '', 'int');
		
		if ($option != 'com_djcatalog2' || $view != 'item' || !$id) {
			return false;
		}
		
		if ($filter_catid) {
			$filter_catid = $app->input->getInt('cid');
		}
		
		if ($filter_producerid) {
			$db->setQuery('select producer_id from #__djc2_items where id='.(int)$id);
			$filter_producerid = (int)$db->loadResult();
		}

		$where = array();
		
		if ($filter_featured > 0) {
			$where[] = 'i.featured = 1';
		}

		if ($filter_catid > 0) {
			$where[] = 'i.cat_id = '.(int)$filter_catid;
		}
		
		if ($filter_producerid > 0) {
			$where[] = 'i.producer_id = '.(int)$filter_producerid;
		}
		
		if (!$filter_catid && !$filter_producerid) {
			$where[] = '(i.id IN (SELECT related_item FROM #__djc2_items_related WHERE item_id='.(int)$id.') )';
		} else {
			$where[] = 'i.id != '.(int)$id;
			$where[] = 'i.parent_id = 0';
		}

		$where[] = '(i.publish_up = ' . $nullDate . ' OR i.publish_up <= ' . $nowDate . ')';
		$where[] = '(i.publish_down = ' . $nullDate . ' OR i.publish_down >= ' . $nowDate . ')';
		
		$where[] = 'i.published = 1';
		$where[] = 'c.published = 1';
		
		$where[] = 'i.access IN ('.$groups.')';
		$where[] = 'c.access IN ('.$groups.')';
		
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		return $where;
	}
}

?>

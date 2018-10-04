<?php
use Joomla\Registry\Registry;
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined('_JEXEC') or die();

class Djc2CategoryNode extends JObject
{
    public $id = 0;
    public $name = 'root';
    public $catslug = '0:all';
    public $parent_id = null;
    public $published = 1;
    public $_parent = null;
    public $_children = array();
    public $access = 0;
    public static $default_access = 0;

    public function __construct($category = null)
    {
    	if ($category) {
			$this->setProperties($category);
    	} else {
    		if (!self::$default_access) {
    			/*$db = JFactory::getDbo();
    			$query = $db->getQuery(true);
    			$query->select('a.id');
    			$query->from('#__viewlevels AS a');
    			$query->order('a.ordering ASC, a.id ASC');
    			
    			$db->setQuery($query);
    			
    			self::$default_access = $db->loadResult();*/
    			self::$default_access = JFactory::getApplication()->get('access');
    		}
    		$this->access = self::$default_access;
    	}
    }
    
	public function setParent(&$parent) {
    	$this->_parent = $parent;
    }
	public function addChild(&$child) {
		if (!isset($this->_children[$child->id])){
			$this->_children[$child->id] = $child;			
		}
    }
    public function getPath() {
    	if (isset($this->_path)) {
    		return $this->_path;
    	}
    	$path = new stdClass();
    	$path->items = array();
    	$slugs = array();
    	$current = $this;
    	while ($current->parent_id != null) {
    		$pathElement = new stdClass();
    		$pathElement->slug = ($current->alias) ? $current->id.':'.$current->alias : $current->id;
    		$pathElement->id = $current->id;
    		$pathElement->alias = $current->alias;
    		$slugs[] = $pathElement->slug;
    		$path->items[] = $pathElement;
    		$current = $current->_parent;
    	}
    	$this->_path = $slugs;
    	return $this->_path;
    }
    public function getParams() {
    	if (isset($this->_params)) {
    		return $this->_params;
    	}
    	
    	$branch = array();
    	$current = $this;
    	while ($current->parent_id != null) {
    		$branch[] = &$current;
    		$current = &$current->_parent;
    	}
    	
    	// Need to watch this. If we use empty new Registry() will be used as global params
    	// then product params on the listing page will not be generated properly
    	
    	$globalParams = new Registry();//JComponentHelper::getParams('com_djcatalog2');
    	//$globalParams = JComponentHelper::getParams('com_djcatalog2');
    	
    	$params = clone $globalParams;
    	
    	for ($i = count($branch)-1; $i >= 0; $i--) {
    		if (empty($branch[$i]->_params)) {
    			$branchParams = clone $params;
    			if (!empty($branch[$i]->params)) {
    				$itemParams = new Registry($branch[$i]->params);
    				$branchParams->merge($itemParams);
    			}
    			$branch[$i]->_params = $branchParams;
    		}
    		
    		if ($i == 0) {
    			$params = $branch[$i]->_params;
    		} else {
    			$params = clone $branch[$i]->_params;
    		}
    	}
    	
    	return $params;
    }
    public function getChildren() {
    	return $this->_children;
    }
    public function makeChildrenList(&$list) {
    	if (count($this->_children)) {
    		foreach ($this->_children as $child ){
    			$list[] = $child->id;
    			$child->makeChildrenList($list);
    		}
    	}
    }
	public function getParent() {
    	return $this->_parent;
    }
    public function getProductCount() {
    	if (!isset($this->item_count)) {
    		$db = JFactory::getDbo();
    		$category_subquery = $db->getQuery(true);
    		 
    		$category_subquery->select('ic.item_id');
    		$category_subquery->from('#__djc2_items_categories AS ic');
    		$category_subquery->join('INNER', '#__djc2_categories AS c ON c.id=ic.category_id');
    		$category_subquery->where('c.published = 1');
    		 
    		$childrenList = array($this->id);
    		$this->makeChildrenList($childrenList);
    		 
    		if (!empty($childrenList)) {
    			$cids = implode(',', $childrenList);
    			$category_subquery->where('ic.category_id IN ('.$cids.')');
    		}
    		 
    		$product_query = $db->getQuery(true);
    		 
    		$product_query->select('COUNT(DISTINCT i.id)');
    		$product_query->from('#__djc2_items AS i');
    		$product_query->join('INNER', '('.(string)$category_subquery.') AS category_filter ON i.id = category_filter.item_id');
    		$product_query->where('i.published = 1 AND i.parent_id = 0');
    		 
    		$nullDate = $db->quote($db->getNullDate());
    		$date = JFactory::getDate();
    		$nowDate = $db->quote($date->toSql());
    		 
    		$product_query->where('(i.publish_up = ' . $nullDate . ' OR i.publish_up <= ' . $nowDate . ')');
    		$product_query->where('(i.publish_down = ' . $nullDate . ' OR i.publish_down >= ' . $nowDate . ')');
    		 
    		$db->setQuery($product_query);
    		 
    		$count = $db->loadResult();
    		if ($count !== false) {
    			$this->item_count = $count;
    		}	
    	}
    	
    	return (isset($this->item_count)) ? $this->item_count : false;
    }
}

class Djc2Categories
{
    protected $nodes = array();
    protected $options = array();
    static $instances = null;

    function __construct($options)
    {
    	$params = JComponentHelper::getParams('com_djcatalog2');
    	
    	$category_ordering = $params->get('category_ordering', 'c.ordering');
    	if ($category_ordering != 'c.ordering' && $category_ordering != 'c.name') {
    		$category_ordering = 'c.ordering';
    	}
    	
    	if (!array_key_exists('order', $options) || !$options['order']) {
    		$options['order'] = $category_ordering;
    	}
    	if (!array_key_exists('order_dir', $options) || !$options['order_dir']) {
    		$options['order_dir'] = 'ASC';	
    	}
    	
    	$this->options = $options;
    	$this->nodes[0] = new Djc2CategoryNode();
    	$this->categories = $this->getCategories($options);
    	$this->createNodes($this->categories);
    	$this->buildTree();
    }
    
    protected function createNodes($categories) {
    	foreach ($categories as $category) {
    		$this->nodes[$category->id] = new Djc2CategoryNode($category);
    	}
    }
    protected function buildTree() {
    	foreach ($this->nodes as $key=>$node) {
    		if ($node->id != 0 && isset($this->nodes[$node->parent_id])) {
    			$this->nodes[$node->id]->setParent($this->nodes[$node->parent_id]);
    		} else if ($node->id != 0) {
    			unset($this->nodes[$key]);
    			continue;
    		}
    		if (!is_null($node->parent_id) && isset($this->nodes[$node->parent_id])) {
    			$this->nodes[$node->parent_id]->addChild($node);
    		}
    	}
    }
    public function &get($id) {
    	$node = false;
    	if (isset($this->nodes[$id])) {
    		$node = $this->nodes[$id];
    	}
    	//else return false;
    	return $node;
    }
    public static function getInstance($options = array()) {
    	
    	$hash = md5(serialize($options));
    	
		if (isset(self::$instances[$hash])) {
			return self::$instances[$hash];
		}
    	self::$instances[$hash] = new Djc2Categories($options);
    	return self::$instances[$hash];
    }
	public function getOptionList($default = null, $disableChildren = false, $selectedCategory = null, $disableDefault=false, $allowedCategories = array(), $default_value = '') {
    	$options = array();
    	if ($default) {
    		$options[] = JHTML::_('select.option', $default_value, $default,'value','text', $disableDefault);
    	}
    	foreach ($this->nodes[0]->_children as $node) {
    		$this->makeOptionList($node, $options, 0, $disableChildren, $selectedCategory, $allowedCategories);
    	}
    	return $options;
    }
    protected function makeOptionList(&$node, &$list, $level=0, $disableChildren = false, $selectedCategory = null, $allowedCategories = array()) {
    	$prefix = '';
    	for ($i = 0; $i < $level; $i++) {
        	$prefix .= '|--';
    	}
    	
    	/*if ($prefix != '') {
    		$prefix = '&nbsp;'.$prefix.'&nbsp;';
    	}*/
    	
    	$item = new stdClass();
    	$item->value = $node->id;
    	$item->text = $prefix ? $prefix.' '.$node->name : $node->name;
    	if ($node->id == $selectedCategory && $disableChildren || (!in_array($node->id, $allowedCategories) && count($allowedCategories) > 0)) {
    		$item->disable = true;
    	} else {
    		$item->disable = null;
    	}
    	$list[] = $item;
    	foreach ($node->_children as $child) {
    		if ($item->disable) {
    			$this->makeOptionList($child, $list, $level+1, $disableChildren, $child->id, $allowedCategories);
    		} else {
    			$this->makeOptionList($child, $list, $level+1, $disableChildren, $selectedCategory, $allowedCategories);
    		}
    	}
    }
	public function getCategoryList() {
    	$categories = array();
    	foreach ($this->nodes[0]->_children as $node) {
    		$this->makeCategoryList($node, $categories, 0);
    	}
    	return $categories;
    }
    protected function makeCategoryList(&$node, &$list, $level=0) {
    	$prefix = '';
    	for ($i = 0; $i < $level; $i++) {
        	$prefix .= '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    	}
    	if ($level > 0) {
    		$prefix .= '<sup>|_</sup>&nbsp;';
    	}
    	$item = $node;
    	$item->treename = $prefix.$node->name;
    	$list[] = $item;
    	foreach ($node->_children as $child) {
    		$this->makeCategoryList($child, $list, $level+1);
    	}
    }
    
    protected function getCategories($options = array()) {
    	
    	$where = array();
    	
    	if (array_key_exists('state', $options)) {
    		if ($options['state'] == 1) {
    			$where[] = 'c.published=1';
    		}
    	}
    	if (array_key_exists('access', $options)) {
    		$groups = (is_array($options['access'])) ? implode(',', array_unique($options['access'])) : $options['access'];
    		$where[] = 'c.access IN ('.$groups.') ';
    	}
    	$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
    	
    	$orderby = ' ORDER BY c.parent_id ASC, '.$options['order'].' '.$options['order_dir'];
    	
    	$db	= JFactory::getDbo();
		$app = JFactory::getApplication();
		$query = ' SELECT c.* ' //, img.fullname AS item_image, img.caption AS image_caption, img.path as image_path, img.fullpath as image_fullpath '
				//.' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as catslug '
				.' FROM #__djc2_categories AS c '
				//. ' LEFT JOIN (select im1.fullname, im1.caption, im1.type, im1.item_id, im1.path, im1.fullpath from #__djc2_images as im1, (select item_id, type, min(ordering) as lowest_order from #__djc2_images group by item_id, type) as im2 where im1.item_id = im2.item_id and im1.type=im2.type and im1.ordering = im2.lowest_order group by im1.type, im1.item_id, im1.path, im1.fullpath) AS img ON img.item_id = c.id AND img.type=\'category\''
				//.' LEFT JOIN #__djc2_images AS img ON img.item_id = c.id AND img.type=\'category\' AND img.ordering = 1 '
				//.' LEFT JOIN #__djc2_images as img on img.id = (select id from #__djc2_images where type=\'category\' and item_id = c.id order by ordering asc limit 1) '
				.$where
				.$orderby;
		$db->setQuery($query);
		$list = $db->loadObjectList();
		
		$query = $db->getQuery(true);
		$query->select('i.id, img.fullname as item_image, img.caption AS image_caption, img.path AS image_path, img.fullpath AS image_fullpath');
		$query->from('#__djc2_categories as i');
		$query->join('inner', '#__djc2_images as img on img.id=(select id from #__djc2_images where type=\'category\' and item_id=i.id order by ordering asc limit 1)');
		$db->setQuery($query);
		$image_list = $db->loadObjectList('id');
		
		foreach ($list as $k=>$v) {
			$list[$k]->catslug  = $list[$k]->slug = (!empty($v->alias)) ? $v->id.':'.$v->alias : $v->id;
			
			$list[$k]->item_image = isset($image_list[$v->id]) ? $image_list[$v->id]->item_image : null;
			$list[$k]->image_caption = isset($image_list[$v->id]) ? $image_list[$v->id]->image_caption : null;
			$list[$k]->image_path = isset($image_list[$v->id]) ? $image_list[$v->id]->image_path : null;
			$list[$k]->image_fullpath = isset($image_list[$v->id]) ? $image_list[$v->id]->image_fullpath : null;
		}
		
		return $list;
    } 
    
}

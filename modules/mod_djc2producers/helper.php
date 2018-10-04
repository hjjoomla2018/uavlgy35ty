<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access');

class DJCatalog2ModProducer{
	public $_producers = null;
	
	/*public static function getProducers($filter_catid = 0){
		
		$db = JFactory::getDBO();
		$query = 'SELECT *, '
				.' CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(":", id, alias) ELSE id END as prodslug '
				.' FROM #__djc2_producers ORDER BY name';
		$db->setQuery($query);
		$_producers = $db->loadAssocList();
		return $_producers;
	}
	*/
	function getProducers($filter_catid = 0, &$params){
		if(!$this->_producers) {
			$db = JFactory::getDbo();
			$query = null;
			if ($filter_catid > 0) {
				$categories = Djc2Categories::getInstance(array('state'=>'1'));
				if ($parent = $categories->get((int)$filter_catid) ) {
					$childrenList = array($parent->id);
					$parent->makeChildrenList($childrenList);
					$query = 'SELECT DISTINCT p.* '
							//. ' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(":", p.id, p.alias) ELSE p.id END as value '
							.' FROM #__djc2_producers as p '
							.' INNER JOIN #__djc2_items AS i ON p.id = i.producer_id AND i.published = 1'
							.' INNER JOIN #__djc2_items_categories AS c ON c.item_id = i.id '
							.' WHERE c.category_id IN ('.implode(',', $childrenList).') AND p.published=1 ORDER BY '.$params->get('orderby', 'p.ordering').' '.$params->get('orderdir', 'asc');
					
					
				}
			} else {
				$query = 'SELECT p.* '
						//. ' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(":", p.id, p.alias) ELSE p.id END as value '
						.' FROM #__djc2_producers as p WHERE p.published=1 ORDER BY '.$params->get('orderby', 'p.ordering').' '.$params->get('orderdir', 'asc');
			}
			$db->setQuery($query);
			$this->_producers = $db->loadAssocList('id');
			
			if (count($this->_producers) > 0 && is_array($this->_producers)) {
				$query = $db->getQuery(true);
				$query->select('i.id, img.fullname as item_image, img.caption AS image_caption, img.path AS image_path, img.fullpath AS image_fullpath');
				$query->from('#__djc2_producers as i');
				$query->join('inner', '#__djc2_images as img on img.id=(select id from #__djc2_images where type=\'producer\' and item_id=i.id order by ordering asc limit 1)');
				$db->setQuery($query);
					
				$image_list = $db->loadObjectList('id');
				
				foreach ($this->_producers as $k=>$v) {
					$this->_producers[$k]['item_image'] = isset($image_list[$v['id']]) ? $image_list[$v['id']]->item_image : null;
					$this->_producers[$k]['image_caption'] = isset($image_list[$v['id']]) ? $image_list[$v['id']]->image_caption : null;
					$this->_producers[$k]['image_path'] = isset($image_list[$v['id']]) ? $image_list[$v['id']]->image_path : null;
					$this->_producers[$k]['image_fullpath'] = isset($image_list[$v['id']]) ? $image_list[$v['id']]->image_fullpath : null;
				}
			}
			
		}
		return $this->_producers;
	}
	
	
} ?>
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

require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'route.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_djcatalog2'.DS.'lib'.DS.'categories.php');

class DJC2CategoriesModuleHelper {
	public static function getHtml($cid, $expand, $params, $root_id = 0, $moduleId) {
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		
		$categories = Djc2Categories::getInstance(array('state'=>'1', 'access' => $groups));
		$root = $categories->get(0);
		$class_sfx	= htmlspecialchars($params->get('moduleclass_sfx', ''));
		
		$current = $categories->get($cid);

		$path = array();
		if (!empty($current)) {
			foreach ($current->getPath() as $item) {
				$path[] = (int)$item;
			}
		}
		
		if ((int)$root_id > 0) {
			if ($new_root = $categories->get($root_id)) {
				$root = $new_root;
			}
		}
		
		$show_count = (bool)$params->get('display_counter', false);
		$layout = $params->get('category_layout', 'list');
		
		$html = '';
		if ($layout == 'list') {
			$html = '<ul class="menu'.$class_sfx.' nav mod_djc2categories_list">';
			self::makeList($html, $root, $path, $expand, $cid, 0, $show_count);
			$html .= '</ul>';
		} else {
			$html = '<form class="mod_djc2categories_form" name="mod_djc2categories_form" id="mod_djc2categories_form-'.$moduleId.'" method="post" action="'.JRoute::_('index.php?option=com_djcatalog2&task=search').'">';
			
			$category_options = $categories->getOptionList('- '.JText::_('MOD_DJC2CATEGORIES_SELECT_CATEGORY').' -');
			if ((int)$root_id > 0) {
				$category_path = $current->getPath();
				$parent_category = null;
				$parent_id = (count($category_path) || (int)$cid == 0) ? $root_id : 0;

				$parent_category = $categories->get($parent_id);
		
				if ($parent_category) {
					$childrenList = array($parent_category->id);
					$parent_category->makeChildrenList($childrenList);
					foreach ($category_options as $key => $option) {
						if (!in_array($option->value, $childrenList)) {
							unset($category_options[$key]);
						}
						if ($option->value == $parent_category->id) {
							$category_options[$key]->text = '- '.JText::_('MOD_DJC2CATEGORIES_SELECT_CATEGORY').' -';
						}
					}
				}
			}
			$html .= JHtml::_('select.genericlist', $category_options, 'cid', 'class="inputbox mod_djc2categories_list" onchange="this.form.submit();"', 'value', 'text', $cid, 'mod_djc2categories_list-'.$moduleId);
			$html .= '<noscript><input type="submit" /></noscript>';
			$html .= '</form>';
		}
		return $html;
	}
	private static function makeList(&$html, &$root, $path, $expand, $cid, $level = 0, $show_count = false) {
		$children = $root->getChildren();
		foreach($children as $child) {
			$current = (($child->id == $cid)) ? true:false;
			$parent = (count($child->getChildren())) ? true:false;
			$active = (($current || in_array($child->id, $path))) ? true:false;
			$deeper = ($parent && $expand) ? true:false;

			$class = 'djc_catid-'.$child->id.' level'.$level;
			$class .= ( $current ) ? ' current':'';
			$class .= ( $active ) ? ' active':'';
			$class .= ( $parent ) ? ' parent':'';
			$class .= ( $deeper ) ? ' deeper':'';
			
			$display_name = $child->name;
			if ($show_count) {
				if (($count = $child->getProductCount()) !== false) {
					$display_name = $child->name.' <small class="djc_category_counter">['.$count.']</small>';
				}
			}
			
			$html.= '<li class="'.$class.'"><a href="'.JRoute::_(DJCatalogHelperRoute::getCategoryRoute($child->id.':'.$child->alias), true).'">'.$display_name.'</a>';
			if (($active || $expand) && count($child->getChildren())) {
				$html .= '<ul class="nav-child unstyled small">';
				$level++;
				self::makeList($html, $child, $path, $expand, $cid, $level, $show_count);
				$level--;
				$html .= '</ul>';
			}
			$html .= '</li>';
		}
	}
}
?>

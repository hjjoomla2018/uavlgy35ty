<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined ('_JEXEC') or die('Restricted access');

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
$app = JFactory::getApplication();
$option = $app->input->get('option', '', 'string');
$view = $app->input->get('view', '', 'string');
$cid = $app->input->getInt('cid', 0);
	
$visibility = $params->get('visibility', null);

if ($visibility == '1' && !($option == 'com_djcatalog2' && $view == 'items')) {
	return false;
}

if ($visibility == '2' && !($option == 'com_djcatalog2' && ($view == 'items' || $view == 'item'))) {
	return false;
}

$category_switch = $params->get('catsw', 0);
$categories = $params->get('categories');

if ($category_switch > 0) {
	if ($category_switch == 1 && !empty($categories)) {
		if (!($option == 'com_djcatalog2' && in_array($cid, $categories))) {
			return false;
		}
	} else if ($category_switch == 2 && ($cid == 0 || $option != 'com_djcatalog2')) {
		return false;
	}
} 

require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'route.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'theme.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'djcatalog2.php');
DJCatalog2ThemeHelper::setThemeAssets();

$data = DJC2FiltersModuleHelper::getData($params);

$isempty = true;
foreach($data as $key => $group) {
	$data[$key]->isempty = true;
	foreach ($group->attributes as $item) {
		if (!empty($item->selectedOptions) || $item->availableOptions > 0 || $item->selected) {
			$isempty = false;
			$data[$key]->isempty = false;
		}
	}
}

if ($isempty == false || JPluginHelper::isEnabled('system', 'djcatalog2ajaxfilters')) {
	
	$document= JFactory::getDocument();
	$module_id = $module->id;
	
	$module_css = array();
	$module_float 	= $params->get('module_float','');
	$module_width 	= $params->get('module_width','');
	$module_height 	= $params->get('module_height','');
	$module_text_align = $params->get('module_text_align','');
	if ($module_float == 'left') {
		$module_css[] = 'float: left;';
		//$module_css[] = 'clear: right;';
		$module_css[] = 'margin: auto;';
	} else if ($module_float == 'right') {
		$module_css[] = 'float: right;';
		//$module_css[] = 'clear: left;';
		$module_css[] = 'margin: auto;';
	} else {
		$module_css[] = 'float: none;';
	}
	
	//$module_css[] = 'display: inline-block;';
	//$module_css[] = 'vertical-align: top;';
	
	if ($module_text_align) {
		$module_css[] = 'text-align: '.$module_text_align.';';
	}
	if (preg_match('#^(\d+)(px|%)?$#', $module_width, $width_matches)) {
		$unit = 'px';
		$width = $width_matches[1];
		if (count($width_matches) == 3) {
			$unit = $width_matches[2];
		}
		$module_css[] = 'width: '.$width.$unit.';';
	}
	if (preg_match('#^(\d+)(px|%)?$#', $module_height, $height_matches)) {
		$unit = 'px';
		$height = $height_matches[1];
		if (count($height_matches) == 3) {
			$unit = $height_matches[2];
		}
		$module_css[] = 'min-height: '.$height.$unit.';';
	}
	if (!empty($module_css)) {
		$css_style = '#mod_djc2filters-'.$module_id.' .mod_djc2filters_attribute {'.implode(PHP_EOL, $module_css).'}';
		$document->addStyleDeclaration($css_style);
	}
	
	require(JModuleHelper::getLayoutPath('mod_djc2filters'));
}
else {
	return false;
}




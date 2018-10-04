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

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

require_once(dirname(__FILE__).DS.'helper.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'route.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'theme.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_djcatalog2'.DS.'lib'.DS.'categories.php');
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'image.php');
$app = JFactory::getApplication();
$db = JFactory::getDbo();

$lang = JFactory::GetLanguage();
$lang->load('com_djcatalog2');
$p = new DJCatalog2ModProducer();

$cid = null;

if ($app->input->getInt('cid',0) != 0 && !$params->get('filter')) {
	$cid = $app->input->get('cid',0, 'string');
}
else $cid = 0;

$producers = $p->getProducers($cid, $params);

$order = $app->input->get('order',false,'default','string');
$orderDir = $app->input->get('dir',false,'cmd');
$prod_slug = $app->input->get('pid', 0, 'string');
$prod_id = (int)$prod_slug;

$Itemid = $app->input->get('Itemid', 0, 'int');

$document= JFactory::getDocument();
$module_id = $module->id;

DJCatalog2ThemeHelper::setThemeAssets();

if ($params->get('layout', 'default') == 'grid') {
	$module_css = array();
	$module_float 	= $params->get('module_float','');
	$module_width 	= $params->get('module_width','');
	$module_height 	= $params->get('module_height','');
	$module_text_align = $params->get('module_text_align','');
	$columns = (int)$params->get('bootstrap_columns', 1);
	
	if ($columns > 1) {
		$module_float = null;
		$module_width = null;
	}
	
	if ($module_float == 'left') {
		$module_css[] = 'float: left;';
		//$module_css[] = 'clear: right;';
		$module_css[] = 'margin: auto;';
	} else if ($module_float == 'right') {
		$module_css[] = 'float: right;';
		//$module_css[] = 'clear: left;';
		$module_css[] = 'margin: auto;';
	}
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
		$module_css[] = 'height: '.$height.$unit.';';
	}
	if (!empty($module_css)) {
		$css_style = '#mod_djc_producers-'.$module_id.' .mod_djc_item {'.implode(PHP_EOL, $module_css).'}';
		$document->addStyleDeclaration($css_style);
	}
}

require(JModuleHelper::getLayoutPath('mod_djc2producers', $params->get('layout', 'default')));

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
require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'theme.php');
DJCatalog2ThemeHelper::setThemeAssets();

$jinput = JFactory::getApplication()->input;

$cid = $jinput->getInt('cid',0);
$option = $app->input->get('option', '', 'string');
$view = $app->input->get('view', '', 'string');

if ($option != 'com_djcatalog2') {
	$cid = 0;
}

$expand = $params->get('expand');

$moduleHtml = DJC2CategoriesModuleHelper::getHtml($cid, $expand, $params, (int)$params->get('parent_category',0), $module->id);

if (!$moduleHtml) {
	return false;
}

require(JModuleHelper::getLayoutPath('mod_djc2categories'));







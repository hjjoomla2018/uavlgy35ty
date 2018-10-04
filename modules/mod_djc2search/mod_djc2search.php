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

require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'route.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'theme.php');

$app = JFactory::getApplication();

DJCatalog2ThemeHelper::setThemeAssets();
$lang = JFactory::GetLanguage();
$lang->load('com_djcatalog2');
$cid = null;

if ($app->input->get('cid',0,'int') != 0 && !$params->get('filter')) {
	$cid = $app->input->get('cid',0,'int');
}
else $cid = 0;

$Itemid = $app->input->get('Itemid', 0, 'int');

require(JModuleHelper::getLayoutPath('mod_djc2search'));

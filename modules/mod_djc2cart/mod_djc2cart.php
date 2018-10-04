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

require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'route.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'theme.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'cart.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'html.php');

$app = JFactory::getApplication();

DJCatalog2ThemeHelper::setThemeAssets();

require(JModuleHelper::getLayoutPath('mod_djc2cart'));

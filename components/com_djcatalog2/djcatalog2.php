<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined('_JEXEC') or die('Restricted access');
//error_reporting(E_STRICT);

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/*
$lang = JFactory::getLanguage();
$lang->load('com_djcatalog2', JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djcatalog2'), 'en-GB', false, false);
$lang->load('com_djcatalog2', JPATH_ADMINISTRATOR, 'en-GB', false, false);
$lang->load('com_djcatalog2', JPATH_COMPONENT, 'en-GB', true, false);
$lang->load('com_djcatalog2', JPATH_ROOT, 'en-GB', true, false);

$lang->load('com_djcatalog2', JPATH_ADMINISTRATOR, null, true, false);
$lang->load('com_djcatalog2', JPATH_ROOT, null, true, false);
*/

require_once(JPATH_COMPONENT.DS.'defines.djcatalog2.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'categories.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'file.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'image.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'upload.php');

require_once(JPATH_COMPONENT.DS.'controller.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'route.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'html.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'theme.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'djcatalog2.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'price.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'cart.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'quantity.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'compare.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'coupon.php');

DJCatalog2Helper::loadComponentLanguage();

JPluginHelper::importPlugin('djcatalog2');

$controller = JControllerLegacy::getInstance('Djcatalog2');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

?>


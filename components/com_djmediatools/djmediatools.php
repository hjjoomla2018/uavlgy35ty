<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */
 
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

$lang = JFactory::getLanguage();
if ($lang->get('lang') != 'en-GB') {
	$lang = JFactory::getLanguage();
	$lang->load('com_djmediatools', JPATH_SITE, 'en-GB', false, false);
	$lang->load('com_djmediatools', JPATH_COMPONENT, 'en-GB', false, false);
	$lang->load('com_djmediatools', JPATH_SITE, null, true, false);
	$lang->load('com_djmediatools', JPATH_COMPONENT, null, true, false);
}

require_once(JPATH_COMPONENT.DS.'controller.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'route.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'image.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'video.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'upload.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'optimize.php');

function djdebug($array, $type = 'message'){

	$app = JFactory::getApplication();
	$app->enqueueMessage("<pre>".print_r($array,true)."</pre>", $type);

}

$controller = JControllerLegacy::getInstance('DJMediaTools');
$controller->execute( JFactory::getApplication()->input->get('task') );
$controller->redirect();

?>


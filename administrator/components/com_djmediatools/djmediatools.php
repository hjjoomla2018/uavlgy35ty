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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

$lang = JFactory::getLanguage();
if ($lang->get('lang') != 'en-GB') {
	$lang->load('com_djmediatools', JPATH_ADMINISTRATOR, 'en-GB', false, false);
	$lang->load('com_djmediatools', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', false, false);
	$lang->load('com_djmediatools', JPATH_ADMINISTRATOR, null, true, false);
	$lang->load('com_djmediatools', JPATH_COMPONENT_ADMINISTRATOR, null, true, false);
}

// Include dependancies
jimport('joomla.application.component.controller');
require_once(JPATH_COMPONENT.DS.'lib'.DS.'image.php');
require_once(JPATH_COMPONENT.DS.'lib'.DS.'video.php');
require_once(JPATH_COMPONENT.DS.'lib'.DS.'upload.php');
require_once(JPATH_COMPONENT.DS.'lib'.DS.'djlicense.php');
require_once(JPATH_COMPONENT.DS.'lib'.DS.'optimize.php');

$db = JFactory::getDBO();
$db->setQuery("SELECT manifest_cache FROM #__extensions WHERE element='com_djmediatools' LIMIT 1");
$version = json_decode($db->loadResult());
$version = $version->version;

define('DJMEDIATOOLSFOOTER', '<div style="text-align: center; margin: 10px 0;">DJ-MediaTools (version '.$version.'), &copy; 2012-'.JFactory::getDate()->format('Y').' Copyright by <a target="_blank" href="http://dj-extensions.com">DJ-Extensions.com</a>, All Rights Reserved.<br /><a target="_blank" href="http://dj-extensions.com"><img src="'.JURI::base().'components/com_djmediatools/assets/logo.png" alt="dj-extensions.com" style="margin: 20px 0 0;" /></a></div>');

$controller	= JControllerLegacy::getInstance('djmediatools');

$document = JFactory::getDocument();
if ($document->getType() == 'html') {
	$document->addStyleSheet(JURI::base(true).'/components/com_djmediatools/assets/style.css');
}

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

function djdebug($array, $type = 'message'){
	
	$app = JFactory::getApplication();	
	$app->enqueueMessage("<pre>".print_r($array,true)."</pre>", $type);
	
}

?>
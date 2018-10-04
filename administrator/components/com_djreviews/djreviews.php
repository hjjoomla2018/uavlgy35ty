<?php
/**
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

$version = new JVersion;
if (version_compare($version->getShortVersion(), '2.5.5', '<')) {
	$app = JFactory::getApplication();
	$app->redirect(JRoute::_('index.php'), 'ERROR: DJ-Reviews requires at least Joomla! ver. 2.5.5. Older versions are not supported!', 'error');
}

if (!JFactory::getUser()->authorise('core.manage', 'com_djreviews')) {
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 500);
}

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

$lang = JFactory::getLanguage();
if ($lang->get('lang') != 'en-GB') {
    $lang = JFactory::getLanguage();
    $lang->load('com_djreviews', JPATH_ADMINISTRATOR, 'en-GB', false, false);
    $lang->load('com_djreviews', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', false, false);
    $lang->load('com_djreviews', JPATH_ADMINISTRATOR, null, true, false);
    $lang->load('com_djreviews', JPATH_COMPONENT_ADMINISTRATOR, null, true, false);
}

// DJ-Reviews version no.
$db = JFactory::getDBO();
$db->setQuery("SELECT manifest_cache FROM #__extensions WHERE type='component' AND element='com_djreviews' LIMIT 1");
$version = json_decode($db->loadResult());
$version = (empty($version->version)) ? 'undefined' : $version->version;

define('DJREVIEWSVERSION', $version);

$year = JFactory::getDate()->format('Y');
define('DJREVIEWSFOOTER', '<div style="text-align: center; margin: 10px 0;">DJ-Reviews (ver. '.DJREVIEWSVERSION.'), &copy; 2009-'.$year.' Copyright by <a target="_blank" href="http://dj-extensions.com">dj-extensions.com</a>, All Rights Reserved.<br /><a target="_blank" href="http://dj-extensions.com"><img src="'.JURI::base().'components/com_djreviews/assets/images/djextensions.png" alt="dj-extensions.com" style="margin-top: 20px;"/></a></div>');

jimport('joomla.utilities.string');
jimport('joomla.application.component.controller');

$version = new JVersion;

require_once(JPATH_COMPONENT.DS.'lib'.DS.'djlicense.php');

$document = JFactory::getDocument();
if ($document->getType() == 'html') {
	 if (version_compare($version->getShortVersion(), '3.0.0', '<')) { 
	 	$document->addStyleSheet(JURI::base().'components/com_djreviews/assets/css/adminstyle_legacy.css');
	 }
	 else {
		$document->addStyleSheet(JURI::base().'components/com_djreviews/assets/css/adminstyle.css');
	 }
}

$controller	= JControllerLegacy::getInstance('DJReviews');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

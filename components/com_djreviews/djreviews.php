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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

$lang = JFactory::getLanguage();
if ($lang->get('lang') != 'en-GB') {
	$lang = JFactory::getLanguage();
	$lang->load('com_djreviews', JPATH_ROOT, 'en-GB', false, false);
	$lang->load('com_djreviews', JPATH_COMPONENT, 'en-GB', false, false);
	$lang->load('com_djreviews', JPATH_ROOT, null, true, false);
	$lang->load('com_djreviews', JPATH_COMPONENT, null, true, false);
}

require_once(JPath::clean(JPATH_ROOT.'/components/com_djreviews/defines.djreviews.php'));
require_once(JPath::clean(JPATH_ROOT.'/components/com_djreviews/controller.php'));
require_once(JPath::clean(JPATH_ROOT.'/components/com_djreviews/helpers/djreviews.php'));

$controller = JControllerLegacy::getInstance('DJReviews');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

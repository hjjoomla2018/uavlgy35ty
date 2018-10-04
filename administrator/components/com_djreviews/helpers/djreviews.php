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

class DJReviewsHelper
{
	public static function addSubmenu($vName = 'cpanel')
	{
		$app = JFactory::getApplication();
		$version = new JVersion;

		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			JSubMenuHelper::addEntry(JText::_('COM_DJREVIEWS_CPANEL'), 'index.php?option=com_djreviews&view=cpanel', $vName=='cpanel');
			JSubMenuHelper::addEntry(JText::_('COM_DJREVIEWS_REVIEWS'), 'index.php?option=com_djreviews&view=reviews', $vName=='reviews');
			JSubMenuHelper::addEntry(JText::_('COM_DJREVIEWS_RATING_GROUPS'), 'index.php?option=com_djreviews&view=groups', $vName=='groups');
			JSubMenuHelper::addEntry(JText::_('COM_DJREVIEWS_RATING_FIELDS'), 'index.php?option=com_djreviews&view=fields', $vName=='fields');
		} else {
			JHtmlSidebar::addEntry(JText::_('COM_DJREVIEWS_CPANEL'), 'index.php?option=com_djreviews&view=cpanel', $vName=='cpanel');
			JHtmlSidebar::addEntry(JText::_('COM_DJREVIEWS_REVIEWS'), 'index.php?option=com_djreviews&view=reviews', $vName=='reviews');
			JHtmlSidebar::addEntry(JText::_('COM_DJREVIEWS_RATING_GROUPS'), 'index.php?option=com_djreviews&view=groups', $vName=='groups');
			JHtmlSidebar::addEntry(JText::_('COM_DJREVIEWS_RATING_FIELDS'), 'index.php?option=com_djreviews&view=fields', $vName=='fields');
		}
	}

	public static function getActions($asset = null, $assetId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if ( !$asset) {
			$assetName = 'com_djreviews';
		} else if ($assetId != 0){
			$assetName = 'com_djreviews.'.$asset.$assetId;
		} else {
			$assetName = 'com_djreviews.'.$asset;
		}

		$actions = array(
			'core.admin', 'core.manage'
		);
		
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
}

<?php
/**
 * @version 1.0
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

defined('_JEXEC') or die;

jimport( 'joomla.application.component.model');


class DJMediatoolsModelCPanel extends JModelLegacy {

	function __construct()
	{
		parent::__construct();
	}
	
	function performChecks() {
		
		$app = JFactory::getApplication();
		
		$checks = array();
	
		$checks['images'] = JPATH_ROOT.DS.'media'.DS.'djmediatools'.DS.'cache';
		$checks['licence'] = JPATH_COMPONENT;
	
		foreach ($checks as $type => $folder) {
			if (!is_writable($folder)) {
				$app->enqueueMessage(JText::_('COM_DJMEDIATOOLS_FOLDER_CHECK_'.strtoupper($type)), 'warning');
			}
		}
		
		if (!extension_loaded('gd')){
			$app->enqueueMessage(JText::_('COM_DJMEDIATOOLS_GD_CHECK_FAIL'), 'warning');
		}
		
		// lets play with memory limit
		$canIncrease = false;
		$memory_limit = null;
		if(function_exists('ini_get')) {
			$memory_limit = ini_get('memory_limit');
		}
		if(function_exists('ini_set')) {
			if(@ini_set('memory_limit', '256M')!==FALSE) $canIncrease = true;
		}
		if( (strstr($memory_limit, 'M') && (int) $memory_limit < 64) || strstr($memory_limit, 'K')) {
			
			if(!$canIncrease) $app->enqueueMessage(JText::sprintf('COM_DJMEDIATOOLS_LOW_MEMORY_LIMIT_CHECK', $memory_limit), 'warning');
		}
		
		if(extension_loaded('curl') !== true || !function_exists('curl_init')) {
			$app->enqueueMessage(JText::_('COM_DJMEDIATOOLS_CURL_CHECK_FAIL'), 'warning');
		}

		if(!@ini_get('allow_url_fopen')) {
			$app->enqueueMessage(JText::_('COM_DJMEDIATOOLS_URL_FOPEN_CHECK_FAIL'), 'warning');
		}
	}
}
?>

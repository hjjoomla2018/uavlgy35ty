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
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

class DJReviewsHelper {
	
	protected static $params = array();
	protected static $assets = false;
	
	public static function getParams($group_id = 0) {
		$group_id = (int)$group_id;
		if (!isset(self::$params[$group_id])) {
			if ($group_id == 0) {
				self::$params[$group_id] = JComponentHelper::getParams('com_djreviews');
			} else {
				$globalParams = JComponentHelper::getParams('com_djreviews');
				$db = JFactory::getDbo();
				$db->setQuery('SELECT params FROM #__djrevs_rating_groups WHERE id='.$group_id);
				$groupParams = $db->loadResult();
				if (!empty($groupParams)) {
					$groupParams = new Registry($groupParams);
					$globalParams->merge($groupParams); 
				}
				self::$params[$group_id] = $globalParams;
			}
		}
		return self::$params[$group_id];
	}
	
	public static function setAssets($group_id = 0){
        if (!self::$assets) {
            $params = self::getParams($group_id);
            
            $lang = JFactory::getLanguage();
            $lang->load('com_djreviews', JPATH_ROOT, 'en-GB', false, false);
            $lang->load('com_djreviews', JPATH_ROOT.JPath::clean('/components/com_djreviews'), 'en-GB', false, false);
            $lang->load('com_djreviews', JPATH_ROOT, null, true, false);
            $lang->load('com_djreviews', JPATH_ROOT.JPath::clean('/components/com_djreviews'), null, true, false);
            
            //JHtml::_('behavior.tooltip', '.djrv_tooltip');
            JHtml::_('bootstrap.popover', '.djrv_tooltip');
            
            $theme = $params->get('theme', 'bootstrap');
            $document = JFactory::getDocument();
            $cookie = JFactory::getApplication()->input->cookie;
            
            $isRTL = false;
            if (isset($document->direction) && $document->direction=='rtl'){
                $isRTL = true;
            } else if ($cookie->get('jmfdirection')){
                if ($cookie->get('jmfdirection')=='rtl'){
                    $isRTL = true;
                }
            } else if ($cookie->get('djdirection')){
                if ($cookie->get('djdirection')=='rtl'){
                    $isRTL = true;
                }
            }
            
            $css_suffix = ($isRTL) ? '.rtl' : '';
            
            JFactory::getDocument()->addStyleSheet(JUri::base().'components/com_djreviews/themes/'.$theme.'/css/theme'.$css_suffix.'.css');
			
            self::$assets = true;
        }
    }
}
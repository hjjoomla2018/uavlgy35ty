<?php
/**
 * @version $Id: opengraph.php 38 2014-10-29 07:42:48Z michal $
 * @package JMFramework
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Michal Olczyk - michal.olczyk@design-joomla.eu
 *
 * JMFramework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JMFramework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JMFramework. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

class JMFOpenGraph {
    public static function applyTags($appId = null) {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        
        // Check if we're dealing with HTML instance of JDocument class
        if (!($document instanceof JDocumentHTML)) {
            return false;
        }
        
        // Checking component's name
        $option = trim($app->input->getCmd('option', false));
        if (!$option) {
            return false;
        }

        // Each supported component should use a separate file for handling OO tags, eg. com_content.php
        $path = JMF_FRAMEWORK_PATH.JPath::clean('/includes/libraries/opengraph/handlers/'.$option.'.php');
        if (!JFile::exists($path)) {
            return false;
        }
        
        // Class should contain name of the component, eg. JMFOpenGraphCom_content
        $className = 'JMFOpenGraph'.ucfirst(strtolower($option));
        
        require_once $path;
        if (!class_exists($className)) {
            return false;
        }
        
        $handler = new $className();
        $tags = $handler->generateTags();
        
        if (empty($tags)) {
            return false;
        }
        
        // Name of the website
        $siteName = $app->get('sitename', false);
        
        if ($siteName) {
            $tags['sitename'] = array(
                'name'=> 'og:site_name',
                'content' => $siteName
            );    
        }
        
        
        // Optional app_id parameter. See Facebook documentation to lear more.
        if ($appId) {
            $tags['app_id'] = array(
                'name'=> 'fb:app_id',
                'content' => $appId
            );
        }
        
        // adding aggregated tags to document's head
        foreach ($tags as $name => $tag) {
            if (empty($tag['content'])) {
                continue;
            }
            if ($name == 'url' || $name == 'image') {
                $tag['content'] = self::encodeURI($tag['content']);
            } else {
                $tag['content'] = self::escape($tag['content']);
            }
            $document->addCustomTag('<meta property="'.$tag['name'].'" content="'.$tag['content'].'" />');
        }
        
        return true;
    }

    protected static function encodeURI($url) {
        $url = preg_replace('/\s/u', '%20', $url);
        $url = htmlspecialchars($url);
        
        return $url;
    }
    
    protected static function escape($var) {
        return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
    }
}

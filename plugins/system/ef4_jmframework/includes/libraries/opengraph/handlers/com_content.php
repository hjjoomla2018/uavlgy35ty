<?php
/**
 * @version $Id: com_content.php 38 2014-10-29 07:42:48Z michal $
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

class JMFOpenGraphCom_content {
    public function generateTags() {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        
        if (!($document instanceof JDocumentHTML)) {
            return false;
        }
        
        $option = $app->input->getCmd('option', false);
        if ($option != 'com_content') {
            return false;
        }
        
        $view = $app->input->getString('view', false);
        if ($view != 'article') {
            return false;
        }
        
        $db = JFactory::getDbo();
        $id = $app->input->getInt('id', 0);
        
        if (!$id) {
            return false;
        }
        
        // Retrieve article from database
        $db->setQuery("SELECT * FROM #__content WHERE id=".$id." LIMIT 1");        
        $item = $db->loadObject();
        
        if (!$item) {
            return false;
        }
        
        $tags = array();
        
        // Article title
        $tags['title'] = array(
            'name' => 'og:title', 
            'content' => htmlspecialchars($item->title)
        );
        
        // Article's URL
        $tags['url'] = array(
            'name' => 'og:url',
            'content' => (string)JUri::getInstance()
        );
        
        // Type of content
        $tags['type'] = array(
            'name' => 'og:type',
            'content' => 'article'
        );
        
        
        // Core article's image
        $images = json_decode($item->images);
        if (!empty($images)) {
            if (isset($images->image_fulltext) && !empty($images->image_fulltext)) {
                $tags['image'] = array(
                    'name' => 'og:image',
                    'content' => JUri::base().$images->image_fulltext
                );
            } else if (isset($images->image_intro) && !empty($images->image_intro)) {
                $tags['image'] = array(
                    'name' => 'og:image',
                    'content' => JUri::base().$images->image_intro
                );
            }
        }
        
        // If core image is not set, find one in the description
        if (!isset($tags['image'])) {
            $article_image = false;
            
            $pattern = '/<img [^<>]*src=[\\"\']?([^\\"\']+\.(png|jpg|gif))[\\"\']?/i';
            preg_match($pattern, $item->introtext, $matches);           
            
            if(empty($matches)) {
                preg_match($pattern, $item->fulltext, $matches);    
            }
            
            if (!empty($matches) && isset($matches[1])) {
                $article_image = $matches[1];
                
                $img_pos = strstr($article_image, 'http');
                
                if ($img_pos === false || $img_pos !== 0) {
                    $article_image = JUri::base().$article_image;
                }
                
                $tags['image'] = array(
                    'name' => 'og:image',
                    'content' => $article_image
                );
            }
        }
        
        // Article's content. Stripping tags and truncating to be max. 250 characters long
        if (!empty($item->introtext)) {
            $description = strip_tags($item->introtext);
            $length = JString::strlen($description);
            $limit = 250;
            
            if ($length > $limit) {
                $description = JString::substr($description, 0, ($limit-3)).'...';
            }
            
            $description = trim(preg_replace('/\s+/', ' ', $description));
            
            $tags['description'] = array(
                'name' => 'og:description',
                'content' => $description
            );
        }
        
        return $tags;
    }
}

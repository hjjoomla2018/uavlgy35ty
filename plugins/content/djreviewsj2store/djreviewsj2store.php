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

class plgContentDJReviewsJ2Store extends JPlugin {

    public function __construct(&$subject, $config = array())
    {
    	require_once JPath::clean(JPATH_ROOT.'/components/com_content/helpers/route.php');
        parent::__construct($subject, $config);
    }
    
    public function onContentAfterTitle($context, &$item, $params, $offset) {
        
    }
    
    /**
     * It returns only basic rating, without the form nor list of reviews
     *
     * @param object $row Article Object
     * @param JRegistry $params
     * @param int $page
     * @return boolean|Ambigous <boolean, string>
     */
    public function onContentBeforeDisplay($context, &$item, $params, $offset = 0) {
        if (!in_array($context, array('com_content.article.productlist', 'com_content.category.productlist'))) {
            return false;
        }
        //echo 'before Cnt:'.$context.'<br />';
        $app = JFactory::getApplication();
		
        if (!$app->isSite()) {
            return false;
        }
        
        $view = $app->input->get('view');
        $option = $app->input->getCmd('option');
        $task = $app->input->getCmd('task');
        $id = $app->input->getInt('id');
        
        if ($option != 'com_j2store') {
            return false;
        }

        if ($view != 'products' && $view != 'product') {
            return false;
        }
        
        if ((int)$this->params->get('enable_listing', 1) == 0 && $task != 'view') {
            return false;
        }
        
        $excluded_categories = $this->params->get('exclude_categories', array());
        if (in_array($item->catid, $excluded_categories)) {
            return false;
        }
        
        $excluded_articles = explode(',', $this->params->get('exclude_articles', ''));
        if (in_array($item->id, $excluded_articles)) {
            return false;
        }
        
        $group_id = $this->params->get('rating_group', false);
        if (!$group_id) {
            return false;
        }
        
        $link = ContentHelperRoute::getArticleRoute($item->id.':'.$item->alias, $item->catid.':'.$item->category_alias);
        
        if (!empty($item->params)) {
        	$params = $item->params;
        	if (is_object($item->params) == false) {
        		$params = new Registry($item->params);
        	}
        	$j2sParams = $params->get('j2store', array());
        	if (!empty($j2sParams) && !empty($j2sParams['j2store_product_id'])) {
        		$link = 'index.php?option=com_j2store&view=products&task=view&id='.$j2sParams['j2store_product_id'];
        	} else if (isset($item->j2store_product_id) && !empty($item->j2store_product_id)){
				$link = 'index.php?option=com_j2store&view=products&task=view&id='.$item->j2store_product_id;
			} else if ($app->input->getInt('id')) {
				$link = 'index.php?option=com_j2store&view=products&task=view&id='.$app->input->getInt('id');
			}
        }
		
		$link .= '&Itemid='.$app->getMenu('site')->getActive()->id;
		
        require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
        require_once JPath::clean(JPATH_ROOT.'/components/com_content/helpers/route.php');
        $review = DJReviewsAPI::getInstance(array(
                'group' => $group_id,
                'type'  => 'com_j2store.article',
                'name'  => $item->title,
                'link'  => $link,
                'id'    => $item->id,
        		'plugin' => 'content.djreviewsj2store'
                )
        );

        return $review->getRatingAvg();
        /*
        if ($view == 'article') {
            return $review->getRatingFull();
        } else  {
            return $review->getRatingAvg();
        }*/
    }
    
    /**
     * Should return everything - Full rating, list of reviews and add review form
     *
     * @param object $row DJ-Catalog2 Object
     * @param JRegistry $params
     * @param int $page
     * @return boolean|Ambigous <boolean, string>
     */
    public function onContentAfterDisplay($context, &$item, $params, $offset = 0) {
        if (!in_array($context, array('com_content.article.productlist', 'com_content.category.productlist'))) {
            return false;
        }
        //echo 'after Cnt:'.$context.'<br />';
        $app = JFactory::getApplication();
        
        $view = $app->input->get('view');
        $option = $app->input->getCmd('option');
        $task = $app->input->getCmd('task');
        $id = $app->input->getInt('id');
        $tmpl = $app->input->getCmd('tmpl');
        
        if ($option != 'com_j2store') {
            return false;
        }

        if (($view != 'products' && $view != 'product') || $task != 'view' || $tmpl == 'component') {
            return false;
        }
        
        $excluded_categories = $this->params->get('exclude_categories', array());
        if (in_array($item->catid, $excluded_categories)) {
            return false;
        }
        
        $excluded_articles = explode(',', $this->params->get('exclude_articles', ''));
        if (in_array($item->id, $excluded_articles)) {
            return false;
        }

        $group_id = $this->params->get('rating_group', false);
        if (!$group_id) {
            return false;
        }
		
        require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
        require_once JPath::clean(JPATH_ROOT.'/components/com_content/helpers/route.php');
		
		$link = ContentHelperRoute::getArticleRoute($item->id.':'.$item->alias, $item->catid.':'.$item->category_alias);
		if (!empty($item->params)) {
			$params = $item->params;
        	if (is_object($item->params) == false) {
        		$params = new Registry($item->params);
        	}
        	$j2sParams = $params->get('j2store', array());
			
			if (!empty($j2sParams) && !empty($j2sParams['j2store_product_id'])) {
				$link = 'index.php?option=com_j2store&view=products&task=view&id='.$j2sParams['j2store_product_id'];
			} else if (isset($item->j2store_product_id) && !empty($item->j2store_product_id)){
				$link = 'index.php?option=com_j2store&view=products&task=view&id='.$item->j2store_product_id;
			} else if ($app->input->getInt('id')) {
				$link = 'index.php?option=com_j2store&view=products&task=view&id='.$app->input->getInt('id');
			}
		}
		
		$link .= '&Itemid='.$app->getMenu('site')->getActive()->id;
		
        $review = DJReviewsAPI::getInstance(array(
                'group' => $group_id,
                'type'  => 'com_j2store.article',
                'name'  => $item->title,
				'link' => $link,
                'id'    => $item->id,
        		'plugin' => 'content.djreviewsj2store'
        )
        );

        return $review->getFullReview();
    }
    
    public function onContentPrepare($context, &$item, $params, $offset) {
       // echo 'prepare Cnt:'.$context.'<br />';
    }
    
    public function onContentAfterDelete($context, $article)
    {
        if ($context == 'com_content.article')
        {
            $id = $article->id;
        }
        else
        {
            return true;
        }
    
        // Remove item from the index.
        return $this->remove($id);
    }
    
    protected function remove($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('o.id');
        $query->from('#__djrevs_objects AS o');
        $query->where('o.object_type = '.$db->quote('com_j2store.article').' AND o.entry_id='.(int)$id);
        $db->setQuery($query);
    
        $object = $db->loadResult();
    
        if (!empty($object)) {
            $query = $db->getQuery(true);
            $query->select('r.id');
            $query->from('#__djrevs_reviews as r');
            $query->where('r.object_id='.$object);
            $db->setQuery($query);
    
            $reviewIds = $db->loadColumn();
            if (count($reviewIds) > 0) {
                foreach ($reviewIds as $reviewId) {
                    $db->setQuery('DELETE FROM #__djrevs_reviews WHERE id='.(int)$reviewId);
                    $db->query();
    
                    $db->setQuery('DELETE FROM #__djrevs_reviews_items WHERE review_id='.(int)$reviewId);
                    $db->query();
                }
            }
    
            $db->setQuery('DELETE FROM #__djrevs_objects WHERE id='.(int)$object);
            $db->query();
        }
    }
}
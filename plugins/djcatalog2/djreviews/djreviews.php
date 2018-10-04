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

jimport ( 'joomla.filesystem.file' );

class plgDJCatalog2DJReviews extends JPlugin {
	
    public function __construct(& $subject, $config) {
        parent::__construct ( $subject, $config );
        $this->loadLanguage ();
    }
	
	function onContentPrepareForm($form, $data)
    {
    	$app = JFactory::getApplication();
    	if (!$app->isAdmin()) {
    		return;
    	}
		
		if (JFile::exists(JPATH_ROOT.'/components/com_djreviews/djreviews.php') == false) {
			return;
		}
		
    	if ($form instanceof JForm && $form->getName() == 'com_djcatalog2.category') {
    		$xml = '<?xml version="1.0" encoding="utf-8"?>
			<form>
			<fields name="params">
			<fieldset name="PLG_DJCATALOG2_DJREVIEWS_FIELDSET" label="PLG_DJCATALOG2_DJREVIEWS_FIELDSET" addfieldpath="administrator/components/com_djreviews/models/fields">
				<field name="djrv_rating_group" default="" type="djreviewsratinggroup" label="PLG_DJCATALOG2_DJREVIEWS_RATING_GROUP" />
			</fieldset>
			</fields>
			</form>';
    		$form->load($xml, false, false);
    	}
    }
    
    /**
     * It returns only basic rating, without the form nor list of reviews
     * 
     * @param object $row DJ-Catalog2 Object
     * @param JRegistry $params
     * @param int $page 
     * @return boolean|Ambigous <boolean, string>
     */
    public function onAfterDJCatalog2DisplayTitle(&$row, &$params, $page = 0, $context = 'item') {
		$app = JFactory::getApplication();
		
		if (!$app->isSite()) {
			return false;
		}

		$view = $app->input->get('view');

    	$excluded_categories = $this->params->get('exclude_categories', array());
		if (in_array($row->cat_id, $excluded_categories)) {
			return false;
		}
		
		$excluded_products = explode(',', $this->params->get('exclude_items', ''));
		if (in_array($row->id, $excluded_products)) {
			return false;
		}
		
		if ($context == 'items.table' && $this->params->get('table_layout', 0) != '1') {
			return false;
		}
		
		if ($context == 'items.items' && $this->params->get('blog_layout', 1) != '1') {
			return false;
		}
		
		$group_id = $this->getCategoryGroup($row);
		
		if (!$group_id) {
			$group_id = $this->params->get('rating_group', false);
			if (!$group_id) {
				return false;
			}
		}
		
		if ($row->parent_id > 0) {
			return false;
		}

		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/components/com_djcatalog2/helpers/route.php');
		
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_djcatalog2.item',
				'name'	=> $row->name,
				'link'	=> DJCatalogHelperRoute::getItemRoute($row->slug, $row->catslug),
				'id'	=> $row->id,
				'plugin' => 'djcatalog2.djreviews'
				)
		);
		
		return $review->getRatingAvg();
		
		/*if ($view == 'item') {
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
    public function onAfterDJCatalog2DisplayContent(&$row, &$params, $page = 0, $context = 'item') {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        if ($document instanceof JDocumentHtml) {
        	// Keep session
        	JHTML::_ ( 'behavior.keepalive' );
        }
		
		$view = $app->input->get('view');

		if ($view != 'item') {
			return false;
		}

    	$excluded_categories = $this->params->get('exclude_categories', array());
		if (in_array($row->cat_id, $excluded_categories)) {
			return false;
		}
		
		$excluded_products = explode(',', $this->params->get('exclude_items', ''));
		if (in_array($row->id, $excluded_products)) {
			return false;
		}
		
		$group_id = $this->getCategoryGroup($row);
		if (!$group_id) {
	   	 	$group_id = $this->params->get('rating_group', false);
			if (!$group_id) {
				return false;
			}
		}
		
		if ($row->parent_id > 0) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/components/com_djcatalog2/helpers/route.php');
		
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_djcatalog2.item',
				'name'	=> $row->name,
				'link'	=> DJCatalogHelperRoute::getItemRoute($row->slug, $row->catslug),
				'id'	=> $row->id,
				'plugin' => 'djcatalog2.djreviews'
		)
		);

		return $review->getFullReview();
    }
    
    public function onContentAfterDelete($context, $article)
    {
    	if ($context == 'com_djcatalog2.item' || $context == 'com_djcatalog2.itemform')
    	{
    		$id = $article->id;
    		if (!$id) {
    			return true;
    		}
    	}
    	else
    	{
    		return true;
    	}
    
    	// Remove item from the index.
    	return $this->remove($id);
    }
    
    public function onDJReviewsAuthorise($data, $object, $action) {
    	$plugin = $object->plugin;
    	if ($plugin != 'djcatalog2.djreviews' || $object->object_type != 'com_djcatalog2.item') {
    		return true;
    	}
    	
    	$user = JFactory::getUser();
    	if ($user->guest) {
    		return true;
    	}
    	
    	$db = JFactory::getDbo();
    	
    	if ($action == 'create') {
    		$query = $db->getQuery(true)->select('created_by')->from('#__djc2_items')->where('id='.(int)$object->entry_id);
    		$db->setQuery($query);
    		 
    		$owner = $db->loadResult();
    		if ($owner == $user->id) {
    			return false;
    		}
    	}
    	
    	return true;
    }
    
    public function onDJReviewsNotification($data, $object) {
    	$plugin = $object->plugin;
    	if ($plugin != 'djcatalog2.djreviews' || $object->object_type != 'com_djcatalog2.item') {
    		return false;
    	}
    	
    	if ($this->params->get('notify_owners') != '1') {
    		return false;
    	}
    	
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select('u.email')->from('#__users AS u');
    	$query->join('inner', '#__djc2_items AS i ON i.created_by = u.id');
    	$query->where('i.id='.(int)$object->entry_id);
    	$db->setQuery($query);
    	
    	$email = $db->loadResult();
    	 
    	return $email;
    }
    
    protected function remove($id)
    {
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select('o.id');
    	$query->from('#__djrevs_objects AS o');
    	$query->where('o.object_type = '.$db->quote('com_djcatalog2.item').' AND o.entry_id='.(int)$id);
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
    	
    	return true;
    }

	protected function getCategoryGroup($item) {
		require_once(JPATH_ADMINISTRATOR.'/components/com_djcatalog2/lib/categories.php');
		
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		$categories = Djc2Categories::getInstance(array('state'=>'1', 'access'=> $groups));
		
		$category = $categories->get($item->cat_id);
		
		if ($category) {
			$params = $category->getParams();
			
			/*if (!is_object($params)) {
				$params = new Registry($params);
			}*/
			
			return $params->get('djrv_rating_group');
		}
	}
}
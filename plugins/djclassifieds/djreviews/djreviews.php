<?php
/**
 * @version $Id: djreviews.php 18 2014-10-30 11:01:55Z michal $
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

class plgDjclassifiedsDJReviews extends JPlugin {

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}
	
	
	/**
	 * It returns only basic rating, without the form nor list of reviews
	 *
	 * @param object $row Article Object
	 * @param JRegistry $params
	 * @param int $page
	 * @return boolean|Ambigous <boolean, string>
	 */
	public function onAfterDJClassifiedsDisplayTitle( &$item, $params, $view) {		
		$app = JFactory::getApplication();

		if (!$app->isSite()) {
			return false;
		}

		$group_id = $this->params->get('rating_group', false);
		
		if(isset($item->rev_group_id)){
			if($item->rev_group_id > 0){
				$group_id = $item->rev_group_id;
			}
		}
		
		if (!$group_id) {
			return false;
		}
        
        if ($view == 'items.table' && $this->params->get('table_layout', 0) != '1') {
            return false;
        }
        
        if ($view == 'items.blog' && $this->params->get('blog_layout', 1) != '1') {
            return false;
        }
        
		
		$excluded_categories = $this->params->get('exclude_categories', array());
		if (in_array($item->cat_id, $excluded_categories)) {
			return false;
		}
		
		$excluded_products = explode(',', $this->params->get('exclude_items', ''));
		if (in_array($item->id, $excluded_products)) {
			return false;
		}

		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php');
		
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_djclassifieds.item',
				'name'	=> $item->name,
				'link'	=> DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias),
				'id'	=> $item->id,
				'plugin' => 'djclassifieds.djreviews'
				)
		);

		return $review->getRatingAvg();
	}
	
	/**
	 * Should return everything - Full rating, list of reviews and add review form
	 *
	 * @param object $row DJ-Catalog2 Object
	 * @param JRegistry $params
	 * @param int $page
	 * @return boolean|Ambigous <boolean, string>
	 */
	public function onAfterDJClassifiedsDisplayContent( &$item, $params, $view) {	
				
		$app = JFactory::getApplication();
				
		if ($view != 'item') {
			return false;
		}

		$group_id = $this->params->get('rating_group', false);
		
		if(isset($item->rev_group_id)){
			if($item->rev_group_id > 0){
				$group_id = $item->rev_group_id;
			}
		}
		
		if (!$group_id) {
			return false;
		}
		
		$excluded_categories = $this->params->get('exclude_categories', array());
		if (in_array($item->cat_id, $excluded_categories)) {
			return false;
		}
		
		$excluded_products = explode(',', $this->params->get('exclude_items', ''));
		if (in_array($item->id, $excluded_products)) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php');
		
		$name = $item->name ? $item->name : $item->id.':'.$item->alias;
		
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_djclassifieds.item',
				'name'	=> $name,
				'link'	=> DJClassifiedsSEO::getItemRoute($item->id.':'.$item->alias,$item->cat_id.':'.$item->c_alias),
				'id'	=> $item->id,
				'plugin' => 'djclassifieds.djreviews'
			)
		);
		
		return $review->getFullReview();
	}
	
	public function onAfterDJClassifiedsDisplayAdvertAuthor( &$item, $params, $view) {
	
		$app = JFactory::getApplication();
	
		if ($view != 'item' || !$item->user_id) {
			return false;
		}
	
		$group_id = $this->params->get('rating_group_author', false);
		if (!$group_id) {
			return false;
		}
	
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php');
	
		$uid_slug = $item->user_id.':'.DJClassifiedsSEO::getAliasName($item->username);
		
		$name = $item->username ? $item->username : $item->user_id;
	
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_djclassifieds.author',
				'name'	=> $name,
				'link'	=> 'index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug,
				'id'	=> $item->user_id,
				'plugin' => 'djclassifieds.djreviews'
		)
		);
			
		return $review->getRatingAvg();
	
	}
	
	public function onAfterDJClassifiedsDisplayProfile( &$profile, $params, $view) {
	
		$app = JFactory::getApplication();		
	
		if (!$profile['id']) {
			return false;
		}
	
		$group_id = $this->params->get('rating_group_author', false);
		if (!$group_id) {
			return false;
		}				
	
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php');
	
		$uid_slug = $profile['id'].':'.DJClassifiedsSEO::getAliasName($profile['name']);
		
		$name = $profile['name'] ? $profile['name'] : $profile['id'];
		
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_djclassifieds.author',
				'name'	=> $name,
				'link'	=> 'index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug,
				'id'	=> $profile['id'],
				'plugin' => 'djclassifieds.djreviews'
		)
		);
	
			
		return $review->getFullReview();
	
	}
	
	public function onDJReviewsAuthorise($data, $object, $action) {
		$plugin = $object->plugin;
		if ($plugin != 'djclassifieds.djreviews') {
			return true;
		}
		 
		$user = JFactory::getUser();
		if ($user->guest) {
			return true;
		}
		
		$db = JFactory::getDbo();
		
		if ($action == 'create') {
			if ($object->object_type == 'com_djclassifieds.author') {
				if ((int)$object->entry_id == $user->id) {
					return false;
				}
					
			} else if ($object->object_type == 'com_djclassifieds.item') {
				$query = $db->getQuery(true)->select('user_id')->from('#__djcf_items')->where('id='.(int)$object->entry_id);
				$db->setQuery($query);
			
				$owner = $db->loadResult();
				if ($owner == $user->id) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	public function onDJReviewsNotification($data, $object) {
		$plugin = $object->plugin;
		if ($plugin != 'djclassifieds.djreviews') {
			return true;
		}
		 
		if ($this->params->get('notify_owners') != '1') {
			return false;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$email = null;
		
		if ($object->object_type == 'com_djclassifieds.author') {
			$query->select('u.email')->from('#__users AS u');
			$query->where('u.id='.(int)$object->entry_id);
			$db->setQuery($query);
			
			$email = $db->loadResult();
		} else if ($object->object_type == 'com_djclassifieds.item') {
			$query->select('u.email')->from('#__users AS u');
			$query->join('inner', '#__djcf_items AS i ON i.user_id = u.id');
			$query->where('i.id='.(int)$object->entry_id);
			$db->setQuery($query);
				
			$email = $db->loadResult();
		}
		 
		return $email;
	}
	
}
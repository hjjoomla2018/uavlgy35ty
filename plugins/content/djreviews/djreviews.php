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

class plgContentDJReviews extends JPlugin {
	
	protected $autoloadLanguage = true;

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}
	
	function onContentPrepareForm($form, $data)
	{
		$app = JFactory::getApplication();
		
		if (!($form instanceof JForm) || $app->isSite())
		{
			return;
		}
		
		$name = $form->getName();
		
		if ($name == 'com_categories.categorycom_content') {
			JForm::addFormPath(JPath::clean(JPATH_ROOT.'/plugins/content/djreviews/forms/'));
			$form->loadFile('category_params', true);
		} else if ($name == 'com_content.article') {
			JForm::addFormPath(JPath::clean(JPATH_ROOT.'/plugins/content/djreviews/forms/'));
			$form->loadFile('article_params', true);
		}
		
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
		if (!in_array($context, array('com_content.article', 'com_content.category', 'com_content.featured'))) {
			return false;
		}
		
		$app = JFactory::getApplication();
		
		if (!$app->isSite()) {
			return false;
		}
		
		$view = $app->input->get('view');

		if ((int)$this->params->get('enable_listing', 1) == 0 && $view != 'article') {
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
		
		$group_id = $this->getGroupId($item);
		if (!$group_id) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/components/com_content/helpers/route.php');
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_content.article',
				'name'	=> $item->title,
				'link'	=> ContentHelperRoute::getArticleRoute($item->id.':'.$item->alias, $item->catid.':'.$item->category_alias),
				'id'	=> $item->id
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
		if (!in_array($context, array('com_content.article', 'com_content.category', 'com_content.featured'))) {
			return false;
		}
		
		$app = JFactory::getApplication();
		
		$view = $app->input->get('view');

		if ($view != 'article' && !($view == 'featured' && $app->input->getInt('id', false))) {
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

		$group_id = $this->getGroupId($item);
		if (!$group_id) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/components/com_content/helpers/route.php');
		
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_content.article',
				'name'	=> $item->title,
				'link'	=> ContentHelperRoute::getArticleRoute($item->id.':'.$item->alias, $item->catid.':'.$item->category_alias),
				'id'	=> $item->id
		)
		);

		return $review->getFullReview();
	}
	
	public function getGroupId($item) {
		$item_params = new Registry($item->attribs);
		if ($item_params->get('djrv_enabled') === '0') {
			return false;
		}
		
		$group_id = null;
		$category = JCategories::getInstance('content')->get($item->catid);
		if ($category) {
			$cat_params = new Registry($category->params);
			$cat_enabled = $cat_params->get('djrv_enabled', true);
			
			if (!$cat_enabled && !$item_params->get('djrv_enabled')) {
				return false;
			}
			
			$group_id = $cat_params->get('djrv_group_id');
		}
		
		return $group_id ? $group_id : $this->params->get('rating_group', false);
	}
	
	public function onContentPrepare($context, &$item, $params, $offset) {
	
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
		$query->where('o.object_type = '.$db->quote('com_content.article').' AND o.entry_id='.(int)$id);
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
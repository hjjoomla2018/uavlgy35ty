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

class plgContentDJReviewsVM extends JPlugin {

	public function __construct(&$subject, $config = array())
	{
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
		if (!in_array($context, array('com_virtuemart.productdetails'))) {
			return false;
		}

		$app = JFactory::getApplication();
		
		if (!$app->isSite()) {
			return false;
		}
		
		$view = $app->input->get('view');

		if ((int)$this->params->get('enable_listing', 1) == 0 && $view != 'productdetails') {
			return false;
		}
		
		$excluded_categories = $this->params->get('exclude_categories', array());
		if (in_array($item->virtuemart_category_id, $excluded_categories)) {
			return false;
		}

		$excluded_articles = explode(',', $this->params->get('exclude_articles', ''));
		if (in_array($item->virtuemart_product_id, $excluded_articles)) {
			return false;
		}
		
		$group_id = $this->params->get('rating_group', false);
		if (!$group_id) {
			return false;
		}
        
       

		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_virtuemart.product',
				'name'	=> $item->product_name,
				'link'	=> $item->canonical,
				'id'	=> $item->virtuemart_product_id
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
		if (!in_array($context, array('com_virtuemart.productdetails'))) {
            return false;
        }
		
		$app = JFactory::getApplication();
		
		$view = $app->input->get('view');

		if ($view != 'productdetails') {
			return false;
		}
		
		$excluded_categories = $this->params->get('exclude_categories', array());
		if (in_array($item->virtuemart_category_id, $excluded_categories)) {
			return false;
		}
		
		$excluded_articles = explode(',', $this->params->get('exclude_articles', ''));
		if (in_array($item->virtuemart_product_id, $excluded_articles)) {
			return false;
		}

		$group_id = $this->params->get('rating_group', false);
		if (!$group_id) {
			return false;
		}
        
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		
		$review = DJReviewsAPI::getInstance(array(
				'group' => $group_id,
				'type'  => 'com_virtuemart.product',
				'name'	=> $item->product_name,
				'link'  => $item->canonical,
				'id'	=> $item->virtuemart_product_id
		)
		);

		return $review->getFullReview();
	}
	
	public function onContentPrepare($context, &$item, $params, $offset) {
	}
}
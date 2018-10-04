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

class plgDJReviewsContent extends JPlugin {
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}
	
	public function onObjectPrepareName($object_context) {
		$name = false;
		switch ($object_context) {
			case 'com_content'				: $name = 'Articles'; break;
			case 'com_content.article'		: $name = 'Aricle'; break;
			default							: break;
		}
		
		return $name;
	}
	
	public function onObjectPrepare($object_context, &$object) {
		if ($object_context !='com_content.article') return;

		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$nullDate = $db->getNullDate();
		$date	= JFactory::getDate();
		$now = $date->toSql();
		
		
		$query = $db->getQuery(true);
		$query->select('a.id, a.title, a.alias, a.introtext, a.fulltext, a.images, a.catid, a.created, c.title as category, c.alias as category_alias');
		$query->from('#__content AS a');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');
		
		$query->select('u.name AS author')->join('LEFT', '#__users AS u on u.id = a.created_by');
		
		$query->where('a.state = 1');
		$query->where('(a.publish_up = '.$db->quote($nullDate).' OR a.publish_up <= '.$db->quote($now).')');
		$query->where('(a.publish_down = '.$db->quote($nullDate).' OR a.publish_down >= '.$db->quote($now).')');
		$query->where('a.id ='. $object->entry_id);
		
		$db->setQuery($query,0,1);
		
		$article = $db->loadObject();
		
		if (!empty($article)) {
			require_once(JPath::clean(JPATH_ROOT.'/components/com_content/helpers/route.php'));
			
			$article->slug = $article->id .':'. $article->alias;
			$article->catslug = $article->catid .':'. $article->category_alias;
			$object->link = ContentHelperRoute::getArticleRoute($article->slug, $article->catslug);
			
			$images = new JRegistry($article->images);
			if($images->get('image_intro')) $article->image = $images->get('image_intro');
			else if($images->get('image_fulltext')) $article->image = $images->get('image_fulltext');
			else $article->image = $this->getImageFromText($article->introtext);
			// if no image found in article images and introtext then try fulltext
			if(!$article->image) $article->image = $this->getImageFromText($article->fulltext);
			if($article->image) {
				$object->_image = $article->image;
			}
			
			// Category URL
			$object->_additional_info = '<a class="djrv_article_category" href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($article->catslug)).'">'.$article->category.'</a>';
			
			$object->_additional_info .= '<span class="djrv_article_info">';
			if ($article->author) {
				$object->_additional_info .= '<span class="djrv_article_author">'.htmlspecialchars($article->author).'</span> | ';
			}
			$object->_additional_info .= '<span class="djrv_article_date">'.JHtml::_('date', $article->created, JText::_('DATE_FORMAT_LC3')).'</span>';
			$object->_additional_info .= '</span>';
		}
	}
	
	protected function getImageFromText(&$text, $remove = true)
	{
		$src = '';
		if(preg_match("/<img [^>]*src=\"([^\"]*)\"[^>]*>/", $text, $matches)){
			if($remove) $text = preg_replace("/<img[^>]*>/", '', $text);
			$src = $matches[1];
		}
	
		return $src;
	}
}
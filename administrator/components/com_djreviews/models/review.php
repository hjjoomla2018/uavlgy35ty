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

require_once(JPATH_ADMINISTRATOR.JPath::clean('/components/com_djreviews/lib/modeladmin.php'));

class DJReviewsModelReview extends DJReviewsModelAdmin
{
	protected $text_prefix = 'COM_DJREVIEWS';
	protected $form_name = 'review';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Reviews', $prefix = 'DJReviewsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djreviews.'.$this->form_name, $this->form_name, array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djreviews.edit.'.$this->form_name.'.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}
	
	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			if (!isset($item->rating_fields)) {

				$group_id = (empty($item->rating_group_id)) ? $this->getState('review.rating_group_id') : $item->rating_group_id;
				
				$query = $this->_db->getQuery(true);
				$query->select('f.*, i.rating as rating, ii.rating as text_rating');
				$query->from('#__djrevs_rating_fields as f');
				$query->join('INNER', '#__djrevs_fields_groups AS fg ON fg.field_id = f.id AND fg.group_id='.(int)$group_id);
				$query->join('LEFT', '#__djrevs_reviews_items as i ON i.field_id = f.id AND i.review_id = '.(int)$item->id);
				$query->join('LEFT', '#__djrevs_reviews_items_text as ii ON ii.field_id = f.id AND ii.review_id = '.(int)$item->id);
				//$query->where('f.group_id='.(int)$group_id.' AND f.published = 1');
				$query->where('f.published = 1');
				$query->order('fg.ordering ASC');

				$this->_db->setQuery($query);
				$item->rating_fields = $this->_db->loadObjectList('id'); 
				
				foreach($item->rating_fields as $k=>$v) {
					if (trim($item->rating_fields[$k]->label) == '') {
						$item->rating_fields[$k]->label = $item->rating_fields[$k]->name;
					}
					
					if ($v->type == 'rating' || !$v->type) {
						$item->rating_fields[$k]->rating = $v->rating;
					} else if ($v->text_rating) {
						$item->rating_fields[$k]->rating = $v->text_rating;
					}
				}
			}
			
			return $item;
		}
		return false;
	}

	protected function _prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}
	
	public function publish(&$pks, $value = 1) {
		
		$published = parent::publish($pks, $value);
		
		if (!$published) {
			return false;
		}
		
		// mark to recalculate
		if (count($pks)) {
			$query = ' UPDATE #__djrevs_reviews '
					.' SET recalculate = 2 '
					.' WHERE id IN ('.implode(',', $pks).')'
			;

			$this->_db->setQuery($query);
			$this->_db->query();
		}

		return true;
	}

	public function delete(&$cid) {
		
		if (count($cid)) {
			// mark other reviews to be recalculated first
			$query = ' UPDATE #__djrevs_reviews AS r '
					.' INNER JOIN (SELECT DISTINCT object_id FROM #__djrevs_reviews WHERE id IN ('.implode(',', $cid).')) as r2 ON r2.object_id = r.object_id'
					.' SET r.recalculate = 2 '
			;
			$this->_db->setQuery($query);
			$this->_db->query();
			
			$this->_db->setQuery('DELETE FROM #__djrevs_reviews_items WHERE review_id IN ('.implode(',', $cid).')');
			if ($this->_db->query() == false) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		DJReviewsAPI::recalculate();
		
		$deleted = parent::delete($cid);
		
		return $deleted;
		
	}
}
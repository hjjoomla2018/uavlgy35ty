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

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'modeladmin.php');

class DJReviewsModelGroup extends DJReviewsModelAdmin
{
	protected $text_prefix = 'COM_DJREVIEWS';
	protected $form_name = 'group';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Groups', $prefix = 'DJReviewsTable', $config = array())
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

	protected function _prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}

	public function delete(&$cid) {
		if (count( $cid ))
		{
			$cids = implode(',', $cid);

			$this->_db->setQuery("SELECT COUNT(*) FROM #__djrevs_rating_fields WHERE group_id IN ( ".$cids." )");
			if ($this->_db->loadResult() > 0) {
				$this->setError(JText::_('COM_DJREVIEWS_ERROR_RECORDS_HAVE_ITEMS_GROUP'));
				return false;
			}
		}
		
		return parent::delete($cid);
	}
	
	public function assign($group_id, $cid, $ordering, $listed_cid) {
		if (is_array($cid) && count($listed_cid) > 0 && !empty($group_id)) {
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query->delete('#__djrevs_fields_groups');
			$query->where('group_id='.$group_id.' AND field_id IN ('.implode(',', $listed_cid).')');
			$db->setQuery($query);
			
			if (!$db->execute()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
				
			if (count($cid) > 0 && count($ordering)) {
				$last = 0;
				
				$query = $db->getQuery(true);
				$query->insert('#__djrevs_fields_groups');
				$query->columns(array('group_id', 'field_id', 'ordering'));
				
				foreach($ordering as $index => $order) {
					if (isset($cid[$index])) {
						if ($order <= $last) {
							$order = $last + 1;
						}
						$last = $order;
						$query->values($group_id.', '.$cid[$index].', '.$order);
					}
				}
				
				$db->setQuery($query);
				if (!$db->execute()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
			}
			
			$query = $db->getQuery(true);
			$query->update('#__djrevs_reviews');
			$query->set('recalculate=1');
			$query->where('rating_group_id='.$group_id);
							;
			$db->setQuery($query);
			$db->execute();
			
			return true;
		} else {
			return false;
		}
	}
}
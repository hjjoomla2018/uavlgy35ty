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

class DJReviewsTableFields extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djrevs_rating_fields', 'id', $db);
	}
	function bind($array, $ignore = '')
	{
		return parent::bind($array, $ignore);
	}
	public function check() {
		/*if (empty($this->group_id)) {
			$this->setError(JText::_('COM_DJREVIEWS_MISSING_TABLE_ATTR').': group_id');
			return false;
		}*/
		
		return true;
	}
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();

		if (!$this->id) {
			if (!intval($this->created)) {
				
				$this->created = $date->toSql();
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
		}	
		
		$table = JTable::getInstance('Fields', 'DJReviewsTable');
		
		if ($table->load(array('name'=>$this->name)) && $app->input->get('task') == 'save2copy') {
			$this->name .= ' (copy)';
		}
		
		return parent::store($updateNulls);
	}
}
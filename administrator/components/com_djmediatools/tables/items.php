<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class DJMediatoolsTableItems extends JTable
{
	public function __construct(&$db) {
		parent::__construct('#__djmt_items', 'id', $db);
	}

	function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}
		
		if(empty($array['alias'])) {
			$array['alias'] = $array['title'];
		}
		$array['alias'] = JFilterOutput::stringURLSafe($array['alias']);
		if(trim(str_replace('-','',$array['alias'])) == '') {
			$array['alias'] = JFactory::getDate()->format("Y-m-d-H-i-s");
		}
		
		return parent::bind($array, $ignore);
	}
	
	
	public function store($updateNulls = false)
	{		
		/*
		$table = JTable::getInstance('Items', 'DJMediatoolsTable');
		if ($table->load(array('alias'=>$this->alias,'catid'=>$this->catid)) && ($table->id != $this->id || $this->id==0)) {
			$this->setError(JText::_('COM_DJMEDIATOOLS_ERROR_UNIQUE_ALIAS'));
			return false;
		}
		*/
		$isNew = ($this->id==0 ? true : false);
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if ($isNew) {
			if (!intval($this->created)) {
				$this->created = $date->toSql();
			}
		
			if ($user->id > 0 && !$user->guest) {
				if (empty($this->created_by)) {
					$this->created_by = $user->get('id');
				}
			}
		}
		
		$success = parent::store($updateNulls);
		if($isNew && $success && JRequest::getVar('view') == 'item') {
			$this->reorder('catid = '.$this->catid);
		}
		return $success;
	}
	
}

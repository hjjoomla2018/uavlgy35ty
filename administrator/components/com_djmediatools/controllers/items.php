<?php
/**
 * @version 1.0
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
 
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

class DJMediatoolsControllerItems extends JControllerAdmin
{
	public function getModel($name = 'Item', $prefix = 'DJMediatoolsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
	
	public function import(){
		
		$app = JFactory::getApplication();
		
		$db = JFactory::getDBO();
		
		$query="SELECT * FROM #__categories WHERE extension='com_djimageslider' ORDER BY id";
		$db->setQuery($query);
		
		$cats = $db->loadAssocList('id');
		
		$query = "SELECT * FROM #__djimageslider ORDER BY catid, ordering";
		$db->setQuery($query);
		
		$items = $db->loadAssocList('id');
		
		if(!count($cats) || !count($items)) {
			$this->setRedirect(JRoute::_('index.php?option=com_djmediatools&view=categories', false), JText::_('COM_DJMEDIATOOLS_NOTHING_TO_IMPORT'), 'notice');
			return false;
		}
		
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_djmediatools/tables');
		$table = JTable::getInstance('Categories','DJMediatoolsTable');
		
		$row = array();
		$row['parent_id'] = 0;
		$row['source'] = 'component';
		$row['title'] = JText::_('COM_DJMEDIATOOLS_IMPORTED_SLIDES_CATEGORY_NAME');
		$row['published'] = 1;
		$row['ordering'] = 100;
		
		if(!$table->save($row, 'parent_id')) {
			$this->setRedirect(JRoute::_('index.php?option=com_djmediatools&view=categories', false), JText::_('COM_DJMEDIATOOLS_IMPORT_FAILD_WITH_ERROR').': '.$table->getError(), 'error');
			return false;
		}
		//$table->reorder('parent_id = '.$table->parent_id);
		
		$parent = $table->id;
		$current = 0;
		$catid = 0;
		
		$itemtable = JTable::getInstance('Items','DJMediatoolsTable');
		
		foreach($items as $item){
			
			$itemtable->reset();
			
			if($current != $item['catid']) {
				
				if($catid) $itemtable->reorder('catid='.$catid);
				
				$current = $item['catid'];				
				$cats[$current]['id'] = 0;
				$cats[$current]['parent_id'] = $parent;
				$cats[$current]['source'] = 'component';
				$cats[$current]['ordering'] = $cats[$current]['lft'];
				$table->reset();
				$table->save($cats[$current]);
				$catid = $table->id;
			}
			
			$item['id'] = 0;
			$item['catid'] = $catid;
			$itemtable->save($item);
			
		}
		
		$table->reorder('parent_id = '.$parent);
			
		$this->setRedirect(JRoute::_('index.php?option=com_djmediatools&view=categories&filter_parent='.$parent, false), JText::_('COM_DJMEDIATOOLS_IMPORT_COMPLETED'));
	} 
}
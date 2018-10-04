<?php
/**
* @version $Id:$
* @package DJ-Catalog2
* @copyright Copyright (C) 2010 Blue Constant Media LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer $Author:$ Michal Olczyk - michal.olczyk@design-joomla.eu
*
*
*
** the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ-Catalog2 is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ-Catalog2. If not, see <http://www.gnu.org/licenses/>.
*
*/
defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class DJCatalog2ModelCompare extends JModelList {

	public function getItems() {
		return Djcatalog2HelperCompare::getItems();
	}
	
	public function getComparable() {
		$db = JFactory::getDbo();
		
		//$db->setQuery('SHOW COLUMNS FROM #__djc2_items');
		//$columns = $db->loadColumn(0);
		
		$columns = array();
		
		$query = $db->getQuery(true);
		$query->select('*')->from('#__djc2_items_extra_fields')->where('published=1')->where('comparable=1')->order('ordering asc');
		$db->setQuery($query);
		
		return $db->loadObjectList();
		
		/*$extra_fields = $db->loadColumn();
		
		foreach($extra_fields as $field) {
			$columns[] = '_ef_'.$field;
		}
		
		return $columns;*/
	}
	
	function getAttributes($all = false) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		//$query->select('f.*, group_concat(fo.id order by fo.ordering asc separator \'|\') as options');
		$query->select('f.*, g.name as group_name, g.label as group_label, g.id as fgroup_id');
		$query->from('#__djc2_items_extra_fields as f');
		//$query->join('LEFT', '#__djc2_items_extra_fields_options as fo ON fo.field_id=f.id');
		$query->join('LEFT', '#__djc2_items_extra_fields_groups as g ON g.id=f.group_id');
		
		if ($all) {
			$query->where('f.published = 1');
		} else {
			$query->where('(f.visibility = 2 or f.visibility = 3) and f.published = 1');
		}
		
		$query->where('f.comparable=0');
		
		//$query->group('f.id');
		$query->order('IFNULL(g.ordering,0) asc, g.ordering asc, f.ordering asc');
		$db->setQuery($query);
		$attributes = $db->loadObjectList('id');

		$optQuery = $db->getQuery(true);
		$optQuery->select('o.id, o.field_id');
		$optQuery->from('#__djc2_items_extra_fields_options AS o');
		$optQuery->order('o.field_id asc, o.ordering asc');
		
		$db->setQuery($optQuery);
		$options = $db->loadObjectList();
		$attributeOptions = array();
		
		foreach($options as $k=>$v) {
			if (!isset($options[$v->field_id])) {
				$attributeOptions[$v->field_id] = array();
			}
			$attributeOptions[$v->field_id][] = $v->id;
		}
		
		foreach ($attributes as $k=>$v) {
			if (isset($attributeOptions[$k])) {
				$attributes[$k]->options = implode('|', $attributeOptions[$k]);
			} else {
				$attributes[$k]->options = '';
			}
		}

		return $attributes;
	}
	
}
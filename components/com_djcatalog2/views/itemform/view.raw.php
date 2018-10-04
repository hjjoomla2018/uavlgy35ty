<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class Djcatalog2ViewItemform extends JViewLegacy {
	
	protected $itemId;
	protected $groupId;
	
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$this->itemId = (int)$app->input->getInt('itemId',0);
		$groupIds = $app->input->getString('groupId','');

		$groupIds = explode(',',$groupIds);
		if (!empty($groupIds)) {
			JArrayHelper::toInteger($groupIds);
			$groupIds = array_unique($groupIds);
		}
		
		$groupIds[] = 0;
		
		$this->groupId = $groupIds;

		$db = JFactory::getDbo();
		
		if (!empty($this->groupId)){
			$query = $db->getQuery(true);
			$query->select('f.*');
			$query->from('#__djc2_items_extra_fields AS f');
			$query->select('CASE '
						.'WHEN (f.type=\'text\' OR f.type=\'textarea\' OR f.type=\'html\') ' 
						.'THEN vt.value '
						.'WHEN (f.type=\'calendar\') ' 
						.'THEN vd.value '
						.'WHEN (f.type=\'checkbox\' OR f.type=\'select\' OR f.type=\'multiselect\' OR f.type=\'radio\' OR f.type=\'color\' OR f.type=\'multicolor\') '
						.'THEN GROUP_CONCAT(vi.value SEPARATOR \'|\')'
						.'ELSE "" END AS field_value');
			$query->join('LEFT','#__djc2_items_extra_fields_values_text AS vt ON f.id=vt.field_id AND vt.item_id='.(int)$this->itemId);
			$query->join('LEFT','#__djc2_items_extra_fields_values_int AS vi ON f.id=vi.field_id AND vi.item_id='.(int)$this->itemId);
			$query->join('LEFT','#__djc2_items_extra_fields_values_date AS vd ON f.id=vd.field_id AND vd.item_id='.(int)$this->itemId);
			//$query->where('f.group_id='.(int)$this->groupId.' OR f.group_id=0');
			$query->where('f.group_id IN ('.implode(',', $this->groupId).')');
			$query->group('f.id');
			$query->order('f.group_id asc, f.ordering asc');
			//echo str_replace('#_', 'jos', (string)$query);die();
			$db->setQuery($query);
			$this->fields = ($db->loadObjectList('id'));

			if (count($this->fields)) {
				$fieldIds = array_keys($this->fields);
				$db->setQuery('SELECT * FROM #__djc2_items_extra_fields_options WHERE field_id IN ('.implode(',', $fieldIds).') ORDER BY field_id ASC, ordering ASC');
				$optionList = $db->loadObjectList();
				
				foreach($this->fields as $field_id => $field) {
					foreach ($optionList as $optionRow) {
						if ($optionRow->field_id == $field_id) {
							if (empty($field->optionlist)) {
								$this->fields[$field_id]->optionlist = array();
							}
							$this->fields[$field_id]->optionlist[] = $optionRow;
						}
					}
				}
			} else {
				echo JText::_('COM_DJCATALOG2_NO_FIELDS_IN_GROUP');
				return;
			}
		} else {
			//echo JText::_('COM_DJCATALOG2_CHOOSE_FIELDGROUP_FIRST');
			return;
		}
		
		parent::display($tpl);
	}
	
}
?>
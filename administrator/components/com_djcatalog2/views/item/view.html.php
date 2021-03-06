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

class Djcatalog2ViewItem extends JViewLegacy {
	protected $state;
	protected $item;
	protected $form;
	
	protected $itemId;
	protected $groupId;
	
	public function display($tpl = null)
	{
		$layout = $this->getLayout();
		
		if ($layout == 'extrafields') {
			return $this->displayExtraFields($tpl);
		}
		
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		
		$this->cart_attributes	= $this->get('CartAttributes');
		$this->customisations	= $this->get('Customisations');
		
		//$this->fields =  $this->get('Fields');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
// 		$document = JFactory::getDocument();
// 		JHTML::_('behavior.framework');
// 		JHTML::_('behavior.calendar');
//         $document->addScript(JURI::root() . "administrator/components/com_djcatalog2/views/item/item.js");
// 		$document->addScript(JURI::root() . "components/com_djcatalog2/assets/nicEdit/nicEdit.js");
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}
        
		$this->addToolbar();
		parent::display($tpl);
	}
	
	public function displayExtraFields($tpl = null) {
		$app = JFactory::getApplication();
		$this->itemId = $app->input->getInt('itemId',0);
		$this->groupId = $app->input->getInt('groupId',0);
		
		$db = JFactory::getDbo();
		
		if ($this->groupId >= 0){
			$query = $db->getQuery(true);
			$query->select('f.*');
			$query->from('#__djc2_items_extra_fields AS f');
			$query->select('CASE '
					.'WHEN (f.type=\'text\' OR f.type=\'textarea\' OR f.type=\'html\') '
					.'THEN vt.value '
					.'WHEN (f.type=\'calendar\') '
					.'THEN vd.value '
					.'WHEN (f.type=\'checkbox\' OR f.type=\'select\' OR f.type=\'radio\') '
					.'THEN GROUP_CONCAT(vi.value SEPARATOR \'|\')'
					.'ELSE "" END AS field_value');
			$query->join('LEFT','#__djc2_items_extra_fields_values_text AS vt ON f.id=vt.field_id AND vt.item_id='.(int)$this->itemId);
			$query->join('LEFT','#__djc2_items_extra_fields_values_int AS vi ON f.id=vi.field_id AND vi.item_id='.(int)$this->itemId);
			$query->join('LEFT','#__djc2_items_extra_fields_values_date AS vd ON f.id=vd.field_id AND vd.item_id='.(int)$this->itemId);
			$query->where('f.group_id='.(int)$this->groupId.' OR f.group_id=0');
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
			echo JText::_('COM_DJCATALOG2_CHOOSE_FIELDGROUP_FIRST');
			return;
		}
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}
		
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);

		$text = $isNew ? JText::_( 'COM_DJCATALOG2_NEW' ) : JText::_( 'COM_DJCATALOG2_EDIT' );
		JToolBarHelper::title(   JText::_( 'COM_DJCATALOG2_ITEM' ).': <small><small>[ ' . $text.' ]</small></small>', 'generic.png' );
		
		JToolBarHelper::apply('item.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('item.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::custom('item.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		JToolBarHelper::custom('item.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('item.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
?>
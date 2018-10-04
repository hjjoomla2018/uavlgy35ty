<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die();
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldDjfieldgroup extends JFormField {
	
	protected $type = 'Djfieldgroup';
	
	protected function getInput()
	{
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$allowswitching = (isset($this->element['allowswitching']) && $this->element['allowswitching'] =='true') ? true: false; 
		$attr = '';
		
		$class = $this->element['class'] ? (string) $this->element['class'] : '';
		$class .= $this->element['required']=='true' ? ' required' : '';

		$attr .= 'class="'.$class.'"';
		$name = $this->name;
		if (isset($this->element['multiple']) && $this->element['multiple'] == 'true') {
			$attr .= ' multiple="true" size="10"';
		} else {
			// such situations should not happen, but if they do (it's possible) we need to assign at least one group of fields
			if (is_array($this->value)) {
				$newValue = 0;
				foreach ($this->value as $v) {
					if ($v > 0) {
						$newValue = $v;
						break;
					}
				}
				$this->value = $newValue;
			}
		}
		$db = JFactory::getDbo();
		$db->setQuery('SELECT id AS value, name AS text FROM #__djc2_items_extra_fields_groups ORDER BY text ASC');
		$groups = $db->loadObjectList();
		$options = array();
		$default_label = $this->element['required']=='true' ? JText::_('COM_DJCATALOG2_CHOOSE_FIELDGROUP') : JText::_('COM_DJCATALOG2_CONFIG_NONE');
		$default_value = $this->element['required']=='true' ? '' : '0';
		
        if (!isset($this->element['multiple']) || $this->element['multiple'] != 'true') {
            $options[] = JHTML::_('select.option', $default_value, '- '.$default_label.' -');
        }
        
		$selected = array();
		
		foreach ($groups as $group) {
			if ($group->value == $this->value) {
				$selected[] =$group->text;
			}
			$options[] = JHTML::_('select.option', $group->value, $group->text);
		}
		
		if ($this->value == null || $this->value=='' /*|| $this->value == 0*/ || $allowswitching) {
			$out = JHtml::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $this->value, $this->id);			
		} else {
			$out = '';
			if (is_array($this->value)) {
				foreach ($this->value as $val) {
					$out .= '<input type="hidden" name="'.$this->name.'" value="'.$val.'" />';
				}
			} else {
				$out .= '<input id="'.$this->id.'" type="hidden" name="'.str_replace('[]', '', $this->name).'" value="'.$this->value.'" />';
			}
				
			$out .= '<input type="text" value="'.(count($selected) ? implode(', ',$selected) : '- '.JText::_('COM_DJCATALOG2_CONFIG_NONE').' -').'"' .
				$size.' readonly="readonly" class="readonly" '.$maxLength.'/>';
		}
		
		return ($out);
		
	}
}
?>
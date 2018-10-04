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

class JFormFieldDjcunit extends JFormField {
	
	protected $type = 'Djcunit';
	
	protected function getInput()
	{
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
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
		
		$optionText = (!empty($this->element['option_text'])) ? trim($this->element['option_text']) : 'concat';
		$clause = 'CONCAT(name, " [", unit, "]") as text';
		if ($optionText == 'name') {
			$clause = 'name as text';
		} else if ($optionText == 'unit') {
			$clause = 'unit as text';
		}
		
		$db = JFactory::getDbo();
		$db->setQuery('SELECT id AS value, '.$clause.' FROM #__djc2_units ORDER BY is_default DESC, ordering ASC');
		$groups = $db->loadObjectList();
		$options = array();
		
		/*if (!isset($this->element['multiple']) || $this->element['multiple'] != 'true') {
			$options[] = JHTML::_('select.option', '' , JText::_('COM_DJCATALOG2_LIST_PLEASE_SELECT'));
		}*/
		
		$selected = array();
		
		foreach ($groups as $group) {
			if ($group->value == $this->value) {
				$selected[] =$group->text;
			}
			$options[] = JHTML::_('select.option', $group->value, $group->text);
		}
		
		$out = JHtml::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $this->value, $this->id);
		
		return ($out);
		
	}
}
?>
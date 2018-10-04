<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldDjfieldtype extends JFormField {
	
	protected $type = 'Djfieldtype';
	
	protected function getInput()
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		JHtmlJquery::framework(true);
		
		$type = (isset($this->element['source_type'])) ? $this->element['source_type'] : 'item';
		
		if ($type == 'item') {
			$document->addScript(JURI::root(true) . "/administrator/components/com_djcatalog2/models/fields/djfieldtype.js");
		} else if ($type == 'cart') {
			$document->addScript(JURI::root(true) . "/administrator/components/com_djcatalog2/models/fields/djcartfieldtype.js");
		}
        $js = array();
        
        //if ($this->value == 'html') $this->value = 'textarea';
        
        $initvalue = $this->value ? $this->value : 'empty';
        $js[] = 'jQuery(document).ready(function(){';
        if ($type == 'item') {
        	$js[] = 'window.Djfieldtype_'.$this->id.' = new Djfieldtype(\''.$initvalue.'\', \''.$this->id.'\', \''.$app->input->get('id',0, 'int').'\');';
        } else if ($type == 'cart') {
        	$js[] = 'window.Djcartfieldtype_'.$this->id.' = new Djcartfieldtype(\''.$initvalue.'\', \''.$this->id.'\', \''.$app->input->get('id',0, 'int').'\');';
        }
        $js[] = '});';
        
        $document->addScriptDeclaration(implode(PHP_EOL, $js));
        
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';

		$options = array();
		$options[] = JHTML::_('select.option', '', '- '.JText::_('COM_DJCATALOG2_FIELD_TYPE').' -');
		$options[] = JHTML::_('select.option', 'text', JText::_('COM_DJCATALOG2_FIELD_TYPE_TEXT'));
		$options[] = JHTML::_('select.option', 'textarea', JText::_('COM_DJCATALOG2_FIELD_TYPE_TEXTAREA'));
		$options[] = JHTML::_('select.option', 'calendar', JText::_('COM_DJCATALOG2_FIELD_TYPE_CALENDAR'));
		$options[] = JHTML::_('select.option', 'select', JText::_('COM_DJCATALOG2_FIELD_TYPE_SELECT'));
		$options[] = JHTML::_('select.option', 'radio', JText::_('COM_DJCATALOG2_FIELD_TYPE_RADIO'));
		$options[] = JHTML::_('select.option', 'checkbox', JText::_('COM_DJCATALOG2_FIELD_TYPE_CHECKBOX'));
		
		if ($type == 'item') {
			$options[] = JHTML::_('select.option', 'html', JText::_('COM_DJCATALOG2_FIELD_TYPE_HTML'));
			$options[] = JHTML::_('select.option', 'color', JText::_('COM_DJCATALOG2_FIELD_TYPE_COLOR'));
			$options[] = JHTML::_('select.option', 'multicolor', JText::_('COM_DJCATALOG2_FIELD_TYPE_MULTICOLOR'));
			$options[] = JHTML::_('select.option', 'multiselect', JText::_('COM_DJCATALOG2_FIELD_TYPE_MULTISELECT'));
		} else if ($type == 'cart') {
			$options[] = JHTML::_('select.option', 'file', JText::_('COM_DJCATALOG2_FIELD_TYPE_ATTACHMENT'));
		}
		
		if (!$this->value) {
			$out = JHtml::_('select.genericlist', $options, $this->name, 'class="inputbox required"', 'value', 'text', $this->value, $this->id);			
		} else {
				
			$out = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'" />';
				
			$out .= '<input type="text" id="'.$this->id.'"' .
				' value="'.JText::_('COM_DJCATALOG2_FIELD_TYPE_'.strtoupper($this->value)).'"' .
				$size.' readonly="readonly" class="readonly" '.$maxLength.'/>';
		}
		
		return ($out);
		
	}
}
?>
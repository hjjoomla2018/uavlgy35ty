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

class JFormFieldDjccountry extends JFormField {
	
	protected $type = 'Djccountry';
	
	protected function getInput()
	{
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$attr = '';
		
		$class = $this->element['class'] ? (string) $this->element['class'] : '';
		$class .= (isset($this->element['required']) && $this->element['required'] == 'true') ? ' required' : '';

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
		$query = $db->getQuery(true);
		$query->select('id AS value, country_name AS text');
		$query->from('#__djc2_countries');
		if (JFactory::getApplication()->isSite()) {
			$query->where('published=1');
		}
		$query->order('is_default DESC, country_name ASC');
		$db->setQuery($query);
		$countries = $db->loadObjectList();
		
		$options = array();
		$default_label = (isset($this->element['required']) && $this->element['required']) ? JText::_('COM_DJCATALOG2_CHOOSE_COUNTRY') : JText::_('COM_DJCATALOG2_CONFIG_NONE');
		$default_value = (isset($this->element['required']) && $this->element['required']) ? '' : '0';
		
        if (!isset($this->element['multiple']) || $this->element['multiple'] != 'true') {
            $options[] = JHtml::_('select.option', $default_value, '- '.$default_label.' -');
        }
        
		$selected = array();
		
		foreach ($countries as $country) {
			if ($country->value == $this->value) {
				$selected[] =$country->text;
			}
			$options[] = JHtml::_('select.option', $country->value, $country->text);
		}
		
		$out = JHtml::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $this->value, $this->id);
		
		return ($out);
		
	}
}
?>
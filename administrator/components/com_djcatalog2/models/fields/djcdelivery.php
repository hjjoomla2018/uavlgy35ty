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

class JFormFieldDjcdelivery extends JFormField {
	
	protected $type = 'Djcdelivery';
	
	protected function getInput()
	{
		$html = array();

		// Initialize some field attributes.
		$class     = !empty($this->class) ? ' class="radio ' . $this->class . '"' : ' class="radio"';
		$required  = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$readonly  = $this->readonly;

		// Start the radio field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . $required . $autofocus . $disabled . ' >';

		// Get the field options.
		$options = $this->getOptions();

		// Build the radio field output.
		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			$checked = ((string) $option->value == (string) $this->value || count($options) == 1) ? ' checked="checked"' : '';
			$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';

			$disabled = !empty($option->disable) || ($readonly && !$checked);

			$disabled = $disabled ? ' disabled' : '';

			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

			$html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '" value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $required . $onclick
				. $onchange . $disabled . ' data-shippment="'.(int)$option->shippment.'" '.$option->attributes.' />';

			$html[] = '<label for="' . $this->id . $i . '"' . $class . ' >'
				. JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) . '</label>';

			$required = '';
		}

		// End the radio field output.
		$html[] = '</fieldset>';
		
		return implode($html);
	}
	
	protected function getOptions()
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$options = array();
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__djc2_delivery_methods');
		if (!$app->isAdmin() && !$user->authorise('djcatalog2.salesman', 'com_djcatalog2')) {
			$user = JFactory::getUser();
			$userGroups = implode(',', $user->getAuthorisedViewLevels());
			
			$query->where('published=1');
			$query->where('access IN ('.$userGroups.')');
		}
		$query->order('ordering asc');
		
		$db->setQuery($query);
		$rows = $db->loadObjectList('id');

		$optOptions = array(
			'attr' => null,
			'disable' => false,
			'option.attr' => null,
			'option.disable' => 'disable',
			'option.key' => 'value',
			'option.label' => null,
			'option.text' => 'text'
		);
		
		foreach ($rows as $option)
		{
			$disabled = false;
			
			$optOptions['option.attr'] = 'attributes';
			$attr = array(
				'countries' => $option->countries ? explode(',', $option->countries) : array(),
				'postcodes' => $option->postcodes ? explode('|', $option->postcodes, 2) : array(),
			);
			
			$optOptions['attr'] = 'data-options=\''.json_encode($attr).'\'';
			
			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option->id, trim((string) $option->name), $optOptions, 'text',
				$disabled
			);

			// Set some option attributes.
			$tmp->class = 'djc_delivery_method';
			
			$tmp->shippment = $option->shipping_details;

			// Set some JavaScript option attributes.
			//$tmp->onclick = (string) $option['onclick'];
			//$tmp->onchange = (string) $option['onchange'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
?>
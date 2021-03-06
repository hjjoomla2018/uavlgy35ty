<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

$attribute = $this->attribute_cursor;
$item = $this->item_cursor;
$attribute_values = $this->attribute_values;
$attribute->field_value = '';

if (is_array($attribute_values) && isset($attribute_values[$attribute->id])) {
	$attribute->field_value = $attribute_values[$attribute->id];
}

$attribute->input_name = 'attribute['.$item->id.']['.$attribute->id.']';
$attribute->id .= '_'.$item->id;

$input = null;
$lblClass = (int)$attribute->required == 1 ? 'class="required"' : '';
$lblSfx = (int)$attribute->required == 1 ? '<span class="star">&nbsp;*</span>' : '';
switch ($attribute->type) {
	case 'text': {
		$class = (int)$attribute->required == 1 ? 'input input-medium inputbox required' : 'input input-medium inputbox';
		$class = 'class="'.$class.'"';
		$input = '
				<div class="control-label">
				<label for="attribute_'.$attribute->id.'" '.$lblClass.'>
				'.$attribute->name.$lblSfx.'
				</label></div>
				<div class="controls">
				<input size="40" '.$class.' id="attribute_'.$attribute->id.'" type="text" name="'.$attribute->input_name.'" value="'.htmlspecialchars($attribute->field_value).'" />
				</div>
				';
		break;
	}
	case 'textarea': 
	//case 'html': 
	{
		$class = (int)$attribute->required == 1 ? 'input input-medium inputbox textarea required' : 'input input-medium inputbox textarea';
		$class = 'class="'.$class.'"';
		$input = '
				<div class="control-label">
				<label for="attribute_'.$attribute->id.'" '.$lblClass.'>
				'.$attribute->name.$lblSfx.'
				</label></div>
				<div class="controls">
				<textarea '.$class.' cols="30" id="attribute_'.$attribute->id.'" name="'.$attribute->input_name.'">'.htmlspecialchars($attribute->field_value).'</textarea>
				</div>
				';
		break;
	}
	case 'select': {
		if (empty($attribute->optionlist)) break;
		$options = $attribute->optionlist;
		$optionList = '<option value="">---</option>';
		
		$class = (int)$attribute->required == 1 ? 'input inputbox required' : 'input inputbox';
		$class = 'class="'.$class.'"';
		
		foreach ($options as $option) {
			$selected = ($option->id == $attribute->field_value) ? 'selected="selected"' : '';
			$optionList .= '<option '.$selected.' value="'.$option->id.'">'.htmlspecialchars($option->value).'</option>';
		}
		$input = '
				<div class="control-label">
				<label for="attribute_'.$attribute->id.'" '.$lblClass.'>
				'.$attribute->name.$lblSfx.'
				</label></div>
				<div class="controls">
				<select '.$class.' id="attribute_'.$attribute->id.'" name="'.$attribute->input_name.'">'.$optionList.'</select>
				</div>
				';
		break;
	}
	case 'checkbox': {
		if (empty($attribute->optionlist)) break;
		$options = $attribute->optionlist;
		$optionList = null;
		//$values = explode('|', $attribute->field_value);
		$values = is_array($attribute->field_value) ? $attribute->field_value : array();
		
		$class = (int)$attribute->required == 1 ? 'checkbox checkboxes required' : 'checkbox checkboxes';
		$class = 'class="fltlft '.$class.'"';
		$i = 0;
		foreach ($options as $option) {
			$selected = (in_array($option->id, $values)) ? 'checked="checked"' : '';
			$optionList .= '
				<input id="attribute_'.$attribute->id.''.$i.'" type="checkbox" '.$selected.' name="'.$attribute->input_name.'[]" value="'.$option->id.'" />
				<label for="attribute_'.$attribute->id.''.$i.'">'.htmlspecialchars($option->value).'</label>
				';
			$i++;
		}
		$input = '
				<div class="control-label">
				<label for="attribute_'.$attribute->id.'" '.$lblClass.'>
				'.$attribute->name.$lblSfx.'
				</label></div>
				<div class="controls">
				<fieldset id="attribute_'.$attribute->id.'" '.$class.'>
					'.$optionList.'
				</fieldset>
				</div>
		';
		break;
	}
	case 'radio': {
		if (empty($attribute->optionlist)) break;
		$options = $attribute->optionlist;
		$optionList = null;
		
		$class = (int)$attribute->required == 1 ? 'radio required' : 'radio';
		$class = 'class="'.$class.'"';
		
		$i = 0;
		foreach ($options as $option) {
			$selected = ($option->id == $attribute->field_value) ? 'checked="checked"' : '';
			$optionList .= '
				<label for="attribute_'.$attribute->id.''.$i.'" for="attribute_'.$attribute->id.''.'-'.'-lbl">'.htmlspecialchars($option->value).'<input id="attribute_'.$attribute->id.''.($i).'" type="radio" '.$selected.' name="'.$attribute->input_name.'" value="'.$option->id.'" /></label>';
			$i++;
		}
		$input = '
				<div class="control-label">
				<label for="attribute_'.$attribute->id.'" '.$lblClass.'>
				'.$attribute->name.$lblSfx.'
				</label></div>
				<div class="controls">
				<fieldset id="attribute_'.$attribute->id.'" '.$class.'>
					'.$optionList.'
				</fieldset>
				</div>
		';
		break;
	}
	case 'calendar': {
		$class = (int)$attribute->required == 1 ? 'djc_calendar input inputbox required' : 'djc_calendar input inputbox';
		$class = 'class="'.$class.'"';
		/*$input = '
				<input '.$class.' size="40" id="attribute_'.$attribute->id.'" type="text" name="'.$attribute->input_name.'" value="'.htmlspecialchars($attribute->field_value).'" />
				'.JHtml::_('image', 'system/calendar.png', JText::_('JLIB_HTML_CALENDAR'), array('class' => 'calendar', 'id' => 'attribute_'.$attribute->id . '_img'), true).'
			';*/
		$input = '<div class="control-label"><label for="attribute_'.$attribute->id.'" '.$lblClass.'>
				'.$attribute->name.$lblSfx.'
				</label></div><div class="controls">'.JHtml::_('calendar', $attribute->field_value, $attribute->input_name, 'attribute_'.$attribute->id, '%Y-%m-%d', array('class'=>'input-medium')).'</div>';
		break;
	}
	default: break;
}

echo $input;
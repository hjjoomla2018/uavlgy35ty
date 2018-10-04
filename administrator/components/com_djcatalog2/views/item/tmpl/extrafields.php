<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$document = JFactory::getDocument();

if ($document instanceof JDocumentHTML) {
	if (!count(array_diff(ob_list_handlers(),array('default output handler')))) {
		ob_clean();
	}
}

$out = '<div class="adminformlist">';
foreach ($this->fields as $k=>$v) {
	$input = null;
	$lblClass = (int)$v->required == 1 ? 'class="required"' : '';
	$lblSfx = (int)$v->required == 1 ? '<span class="star">&nbsp;*</span>' : '';
	switch ($v->type) {
		case 'text': {
			$class = (int)$v->required == 1 ? 'input required' : 'input';
			$class = 'class="'.$class.'"';
			
			$input = '
					<div class="control-label">
						<label for="attribute_'.$v->id.'" '.$lblClass.'>
						'.$v->name . $lblSfx.'
						</label>
					</div>
					<div class="controls">
						<input size="40" id="attribute_'.$v->id.'" type="text" name="attribute['.$v->id.']" value="'.htmlspecialchars($v->field_value).'" '.$class.'/>
					</div>
				';
			break;
		}
		case 'textarea': {
			$class = (int)$v->required == 1 ? 'input required' : 'input';
			$class = 'class="'.$class.'"';
			
		//case 'html': 
		
			$input = '
					<div class="control-label">
						<label for="attribute_'.$v->id.'" '.$lblClass.'>
						'.$v->name . $lblSfx.'
						</label>
					</div>
					<div class="controls">
						<textarea rows="3" cols="30" id="attribute_'.$v->id.'" name="attribute['.$v->id.']" '.$class.'>'.htmlspecialchars($v->field_value).'</textarea>
					</div>
				';
			break;
		}
		case 'html': {
			if ($document instanceof JDocumentHTML) {
				$editor = JFactory::getEditor(null);
				$input = '
					<div class="control-label">
						<label for="attribute_'.$v->id.'" '.$lblClass.'>
							'.$v->name . $lblSfx.'
						</label>
					</div>
					<div class="controls">
						'.$editor->display( 'attribute['.$v->id.']', $v->field_value, '100%', '250', '0', '0',false, 'attribute_'.$v->id).'
					</div>
					';
			} else {
				$class = (int)$v->required == 1 ? 'nicEdit required' : 'nicEdit';
				$class = 'class="'.$class.'"';
				$input = '
					<div class="control-label">
						<label for="attribute_'.$v->id.'" '.$lblClass.'>
							'.$v->name . $lblSfx.'
						</label>
					</div>
					<div class="controls">
						<textarea '.$class.' style="height: 300px; width: 500px" rows="10" cols="40" id="attribute_'.$v->id.'" name="attribute['.$v->id.']">'.htmlspecialchars($v->field_value).'</textarea>
					</div>
					';
			}
			
			break;
			
		}
		/*case 'html': {
			$class = (int)$v->required == 1 ? 'nicEdit input-xxlarge required' : 'nicEdit input-xxlarge';
			$class = 'class="'.$class.'"';
			$input = '
					<div class="control-label">	
						<label for="attribute_'.$v->id.'" '.$lblClass.'>
							'.$v->name.'
						</label>
					</div>
					<div class="controls">
						<textarea '.$class.' style="min-width: 400px" rows="10" cols="40" id="attribute_'.$v->id.'" name="attribute['.$v->id.']">'.htmlspecialchars($v->field_value).'</textarea>
					</div>
					';
			break;
		}*/
		case 'select':
		case 'color': {
			if (empty($v->optionlist)) break;
			$options = $v->optionlist;
			$optionList = '<option value="">---</option>';
			
			$class = (int)$v->required == 1 ? 'input required' : 'input';
			$class = 'class="'.$class.'"';
			
			foreach ($options as $option) {
				$selected = ($option->id == $v->field_value) ? 'selected="selected"' : '';
				$optionList .= '<option '.$selected.' value="'.$option->id.'">'.htmlspecialchars($option->value).'</option>';
			}
			$input = '
					<div class="control-label">
						<label for="attribute_'.$v->id.'" '.$lblClass.'>'.$v->name . $lblSfx .'</label>
					</div>
					<div class="controls">
						<select id="attribute_'.$v->id.'" name="attribute['.$v->id.']" '.$class.'>'.$optionList.'</select>
					</div>
				';
			break;
		}
		case 'multiselect': {
			
			if (empty($v->optionlist)) break;
			$options = $v->optionlist;
			$optionList = null;
			$values = explode('|', $v->field_value);
			
			$class = (int)$v->required == 1 ? 'input required' : 'input';
			$class = 'class="'.$class.'"';
			
			foreach ($options as $option) {
				$selected = (in_array($option->id, $values)) ? 'selected="selected"' : '';
				$optionList .= '<option '.$selected.' value="'.$option->id.'">'.htmlspecialchars($option->value).'</option>';
			}
			
			$size = (count($options) >= 10) ? 10 : max(count($options), 1);
			
			$input = '
					<div class="control-label">
						<label for="attribute_'.$v->id.'" '.$lblClass.'>'.$v->name . $lblSfx .'</label>
					</div>
					<div class="controls">
						<select id="attribute_'.$v->id.'" name="attribute['.$v->id.'][]" '.$class.' multiple="true" size="'.$size.'">'.$optionList.'</select>
					</div>
				';
			break;
		}
		case 'checkbox':
		case 'multicolor': {
			if (empty($v->optionlist)) break;
			$options = $v->optionlist;
			$optionList = null;
			$values = explode('|', $v->field_value);
			
			$class = (int)$v->required == 1 ? 'checkboxes checkbox required' : 'checkbox checkboxes';
			$class = 'class="'.$class.'"';
			
			$i = 0;
			foreach ($options as $option) {
				$selected = (in_array($option->id, $values)) ? 'checked="checked"' : '';
				$optionList .= '
					<input id="attribute_'.$v->id.'-'.$i.'" type="checkbox" '.$selected.' name="attribute['.$v->id.'][]" value="'.$option->id.'" />
					<label for="attribute_'.$v->id.'-'.$i.'">'.htmlspecialchars($option->value).'</label>
					';
				$i++;
			}
			$input = '
					<div class="control-label">
						<label for="attribute_'.$v->id.'">'.$v->name . $lblSfx .'</label>
					</div>
					<div class="controls">
						<fieldset id="attribute_'.$v->id.'" '.$class.'>
							'.$optionList.'
						</fieldset>
					</div>
			';
			break;
		}
		case 'radio': {
			if (empty($v->optionlist)) break;
			$options = $v->optionlist;
			$optionList = null;
			
			$class = (int)$v->required == 1 ? 'radio required' : 'radio';
			$class = 'class="'.$class.'"';
			
			$i = 0;
			foreach ($options as $option) {
				$selected = ($option->id == $v->field_value) ? 'checked="checked"' : '';
				$optionList .= '
					<input id="attribute_'.$v->id.'-'.($i).'" type="radio" '.$selected.' name="attribute['.$v->id.']" value="'.$option->id.'" />
					<label for="attribute_'.$v->id.'-'.$i.'">'.htmlspecialchars($option->value).'</label>';
				$i++;
			}
			$input = '
					<div class="control-label">
						<label for="attribute_'.$v->id.'">'.$v->name. $lblSfx.'</label>
					</div>
					<div class="controls">
						<fieldset id="attribute_'.$v->id.'" '.$class.'>
							'.$optionList.'
						</fieldset>
					</div>
			';
			break;
		}
		case 'calendar': {
			$class = (int)$v->required == 1 ? 'djc_calendar input required' : 'djc_calendar input';
			$class = 'class="'.$class.'"';
			
			if ($v->field_value == '0000-00-00') {
				$v->field_value = '';
			}
			
			$input = '
				<div class="control-label">
					<label for="attribute_'.$v->id.'" '.$lblClass.'>
					'.$v->name . $lblSfx.'
					</label>
				</div>
				<div class="controls">
					'.DJCatalog2HtmlHelper::getCalendarInput($v->id, $v->field_value, $class).'		
				</div>
				';
			break;
		}
		default: break;
	}
	$out .= '<div class="control-group">'.$input.'</div>';
}

$out .= '</div>';
echo $out;

if ($document instanceof JDocumentHTML) {
	$app->close();
}

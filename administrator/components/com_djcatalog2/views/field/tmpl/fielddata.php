<?php
use Joomla\Registry\Registry;

/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

$out = '';

switch ($this->fieldtype) {
	case 'select':
	case 'multiselect':
	case 'radio':
	case 'checkbox': {
		$out .= '
		<div class="control-group">
			<div class="control-label">
			<label>'
			.JText::_('COM_DJCATALOG2_FIELD_TYPE_'.strtoupper($this->fieldtype))
			.' '
			.JText::_('COM_DJCATALOG2_FIELD_TYPE_OPTIONS').'</label>
			</div>
			<div class="controls">	
				<span class="btn" onclick="Djfieldtype_'.$this->suffix.'.appendOption();">
				'.JText::_('COM_DJCATALOG2_FIELD_TYPE_ADD_OPTION').'
				</span>
			</div>
		</div>';
		
		$out .= '<div class="clearfix"></div>
		 	<table class="table-condensed">
		 	<thead>
		 		<tr>
		 			<th>'.JText::_('COM_DJCATALOG2_FIELD_OPTION_NAME').'</th>
		 			<th>'.JText::_('COM_DJCATALOG2_FIELD_OPTION_POSITION').'</th>
		 		</tr>
		 	</thead>
		 	<tbody id="DjfieldOptions">';
		if ($this->fieldId > 0) {
			if (count($this->fieldoptions)) {
				foreach ($this->fieldoptions as $option) {
					$out .= '<tr>
						<td>
							 <input type="hidden" name="fieldtype[id][]" value="'.$option->id.'"/>
							 <input type="text" name="fieldtype[option][]" value="'.htmlspecialchars($option->value).'" class="input-medium required" />
						</td>
						<td>
							<input type="text" size="4" name="fieldtype[position][]" value="'.htmlspecialchars($option->ordering).'" class="input-mini" /><span class="btn button-x btn-mini">&nbsp;&nbsp;&minus;&nbsp;&nbsp;</span><span class="btn button-down btn-mini">&nbsp;&nbsp;&darr;&nbsp;&nbsp;</span><span class="btn button-up btn-mini">&nbsp;&nbsp;&nbsp;&uarr;&nbsp;&nbsp;&nbsp;</span>
						</td>
					 </tr>';
				}
			}
		}
		$out .='</tbody>
		</table>';
		break;
	}
	case 'color':
	case 'multicolor': {
		$out .= '
		<div class="control-group">
			<div class="control-label">
			<label>'
			.JText::_('COM_DJCATALOG2_FIELD_TYPE_'.strtoupper($this->fieldtype))
			.' '
				.JText::_('COM_DJCATALOG2_FIELD_TYPE_OPTIONS').'</label>
			</div>
			<div class="controls">
				<span class="btn" onclick="Djfieldtype_'.$this->suffix.'.appendOption();">
				'.JText::_('COM_DJCATALOG2_FIELD_TYPE_ADD_OPTION').'
				</span>
			</div>
		</div>';
					
		$out .= '<div class="clearfix"></div>
		 	<table class="table-condensed">
		 	<thead>
		 		<tr>
		 			<th>'.JText::_('COM_DJCATALOG2_FIELD_OPTION_NAME').'</th>
					<th>'.JText::_('COM_DJCATALOG2_FIELD_OPTION_COLOR_CODE').'</th>
					<th>'.JText::_('COM_DJCATALOG2_FIELD_OPTION_COLOR_FILE').'</th>
		 			<th>'.JText::_('COM_DJCATALOG2_FIELD_OPTION_POSITION').'</th>
		 		</tr>
		 	</thead>
		 	<tbody id="DjfieldOptions">';
 			if ($this->fieldId > 0) {
 				if (count($this->fieldoptions)) {
 					foreach ($this->fieldoptions as $option) {
 						$hexcode = '';
 						
 						$file_input = '<input type="hidden" name="fieldtype[file_name][]" value="" />';
 						$file_input_css = '';
 						if (!empty($option->params)) {
 							$params = new Registry($option->params);
 							$hexcode = $params->get('hexcode', '');
 							$file = $params->get('file_name', '');
 							
 							$file_input = '<div class="djc_field_opt_color_wrap" data-optionwrapper='.$option->id.'>';
 							
 							if ($file != '') {
 								
 								$file_input .= '<input type="hidden" name="fieldtype[file_name][]" value="'.$file.'" />';
 								$file_input .= '<img alt="" src="'.JUri::root().'/media/djcatalog2/images/colors/'.$file.'" />';
 								$file_input .= ' <button type="button" data-optionid="'.$option->id.'" class="btn btn-mini">'.JText::_('COM_DJCATALOG2_FILE_DELETE_LABEL').'</button>';
 								$file_input_css = 'display: none';
 							} else {
 								$file_input .= '<input type="hidden" name="fieldtype[file_name][]" value="" />';
 							}
 						}
 						$file_input .= '<input type="file" name="fieldtype[file][]" style="'.$file_input_css.'" />';
 						$file_input .= '</div>';
 						
 						$out .= '<tr>
						<td>
							 <input type="hidden" name="fieldtype[id][]" value="'.$option->id.'"/>
							 <input type="text" name="fieldtype[option][]" value="'.htmlspecialchars($option->value).'" class="input-medium required" />
						</td>
						<td><input type="text" name="fieldtype[hexcode][]" value="'.htmlspecialchars($hexcode).'" class="input-mini minicolors" /></td>
						<td>'.$file_input.'</td>
						<td>
							<input type="text" size="4" name="fieldtype[position][]" value="'.htmlspecialchars($option->ordering).'" class="input-mini" /><span class="btn button-x btn-mini">&nbsp;&nbsp;&minus;&nbsp;&nbsp;</span><span class="btn button-down btn-mini">&nbsp;&nbsp;&darr;&nbsp;&nbsp;</span><span class="btn button-up btn-mini">&nbsp;&nbsp;&nbsp;&uarr;&nbsp;&nbsp;&nbsp;</span>
						</td>
					 </tr>';
					}
		 		}
		 	}
		$out .='</tbody>
		</table>
		<script>
			jQuery(document).ready(function (){
				jQuery(".minicolors").each(function() {
					jQuery(this).minicolors({
						control: "hue",
						format: "hex",
						keywords: "",
						opacity: false,
						position: "default",
						theme: "bootstrap"
					});
				});

				jQuery("button[data-optionid]").click(function(){
					var target = jQuery(this);
					var optionId = target.attr("data-optionid");
					if (optionId) {
						target.css("display", "none");
						var wrapper = jQuery("div[data-optionwrapper=\'"+optionId+"\']");
						wrapper.find("input[name=\'fieldtype[file_name][]\']").val("");
						wrapper.find("img").css("display", "none");
						wrapper.find("input[type=\'file\']").css("display", "");
						wrapper.find("input[type=\'file\']").css("display", "");
					}
				});
			});
		</script>
		';
		break;
	}
	default: {
		break;
	}
}

echo $out;
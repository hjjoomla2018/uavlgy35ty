<?php
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
				<span class="btn" onclick="Djcartfieldtype_'.$this->suffix.'.appendOption();">
				'.JText::_('COM_DJCATALOG2_FIELD_TYPE_ADD_OPTION').'
				</span>
			</div>
		</div>'
		;
		
		$out .= '<div class="clearfix"></div>
			 	<table class="table-condensed">
			 	<thead>
			 		<tr>
			 			<th>'.JText::_('COM_DJCATALOG2_FIELD_OPTION_NAME').'</th>
			 			<th>'.JText::_('COM_DJCATALOG2_FIELD_OPTION_POSITION').'</th>
			 		</tr>
			 	</thead>
			 	<tbody id="DjfieldOptions">'
			 ;
		if ($this->fieldId > 0) {
			if (count($this->fieldoptions)) {
				foreach ($this->fieldoptions as $option) {
					$out .= '<tr>
						 <td>
							 <input type="hidden" name="fieldtype[id][]" value="'.$option->id.'"/>
							 <input type="text" size="30" name="fieldtype[option][]" value="'.htmlspecialchars($option->value).'" class="input-medium required" />
						 </td>
						 <td>
							 <input type="text" size="4" name="fieldtype[position][]" value="'.htmlspecialchars($option->ordering).'" class="input-mini" /><span class="btn button-x">&nbsp;&nbsp;&minus;&nbsp;&nbsp;</span><span class="btn button-down">&nbsp;&nbsp;&darr;&nbsp;&nbsp;</span><span class="btn button-up">&nbsp;&nbsp;&nbsp;&uarr;&nbsp;&nbsp;&nbsp;</span>
						 </td>
						 </tr>'
						 ;
				}
			}
		}
		$out .'</tbody>
			</table>';
		break;
	}
	default: {
		break;
	}
}

echo $out;
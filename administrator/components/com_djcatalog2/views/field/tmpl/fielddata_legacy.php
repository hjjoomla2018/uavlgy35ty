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
		$out .= '<span class="faux-label">'
		.JText::_('COM_DJCATALOG2_FIELD_TYPE_'.strtoupper($this->fieldtype))
		.' '
		.JText::_('COM_DJCATALOG2_FIELD_TYPE_OPTIONS').'</span>
		<div class="button2-left">
			<div class="blank">
				<span onclick="Djfieldtype_'.$this->suffix.'.appendOption();">
				'.JText::_('COM_DJCATALOG2_FIELD_TYPE_ADD_OPTION').'
				</span>
			</div>
		</div>'
		;
		
		$out .= '<div class="clr"></div>
			 	<table>
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
						 <input type="text" size="30" name="fieldtype[option][]" value="'.$option->value.'" class="inputbox required"/>
						 </td>
						 <td>
						 <input type="text" size="4" name="fieldtype[position][]" value="'.$option->ordering.'" class="inputbox"/>
						 <div class="button2-left"><div class="blank"><span class="button-x">&nbsp;&nbsp;&minus;&nbsp;&nbsp;</span></div></div>
						 <div class="button2-left"><div class="blank"><span class="button-down">&nbsp;&nbsp;&darr;&nbsp;&nbsp;</span></div></div>
                                 <div class="button2-left"><div class="blank"><span class="button-up">&nbsp;&nbsp;&nbsp;&uarr;&nbsp;&nbsp;&nbsp;</span></div></div>
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
<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die();
defined('JPATH_BASE') or die;
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldDJVideo extends JFormField {
	
	protected $type = 'DJVideo';
	
	protected function getInput()
	{	
		$doc = JFactory::getDocument();
		
		// Initialize some field attributes.
		$attr = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr.= $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$attr.= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr.= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$attr.= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		
		// Initialize JavaScript field attributes.
		JHtml::_('behavior.framework', true);
		$doc->addScript(JURI::root(true).'/administrator/components/com_djmediatools/models/fields/djvideo.js');
		$js = "
			var COM_DJMEDIATOOLS_CONFIRM_UPDATE_IMAGE_FIELD = '".JText::_('COM_DJMEDIATOOLS_CONFIRM_UPDATE_IMAGE_FIELD')."';
			var COM_DJMEDIATOOLS_CONFIRM_UPDATE_TITLE_FIELD = '".JText::_('COM_DJMEDIATOOLS_CONFIRM_UPDATE_TITLE_FIELD')."';
		";
		$doc->addScriptDeclaration($js);
		$thumb = ($this->element['thumb_field'] ? $this->formControl.'_'.(string) $this->element['thumb_field'] : '');
		$title = ($this->element['title_field'] ? $this->formControl.'_'.(string) $this->element['title_field'] : '');
		$callback = ($this->element['callback'] ? (string) $this->element['callback'] : 'null');
		
		$attr.= ' onpaste="setTimeout(function(){parseVideo(\''.$this->id.'\',\''.$thumb.'\',\''.$title.'\', '.$callback.')},0);"';
		$attr.= ' onclick="this.select();"';
		
		$preview = $this->value ? '<iframe src="'.$this->value.'" width="320" height="180" frameborder="0" allowfullscreen></iframe>' : '';
		
		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
				. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $attr . '/><div class="djvideo_preview" id="' . $this->id . '_preview">'.$preview.'</div>';
		
	}
}
?>
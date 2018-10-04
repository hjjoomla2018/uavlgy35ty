<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Supports a modal article picker.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class JFormFieldDJFolder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'DJFolder';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$root_folder = $this->element['root'] ? $this->element['root'] : '';
		$required = $this->element['required'] == 'true' ? true : false;
		
		// Initialize JavaScript field attributes.
		JHtml::_('behavior.framework', true);		
		$doc->addScript(JURI::root(true).'/administrator/components/com_djmediatools/models/fields/djfolder.js');

		// Setup variables for display.
		$html	= array();
		$html2	= array();
				
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			// The current user display field.
			$html[] = '<div class="fltlft">';
			$html[] = '  <input type="text" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'" readonly="readonly" size="35" data-root="'.$root_folder.'" placeholder="auto: '.$root_folder.'/id-alias" />';
			$html[] = '</div>';
	
			if(!$required) {
				$html[] = '	<button onclick="jInsertFieldValue(\'\', \''.$this->id.'\'); document.id(\''.$this->id.'_list\').slide(\'out\'); return false;">'.JText::_('JCLEAR').'</button>';
			}
			$html[] = '	<button id="'.$this->id.'_button" onclick="updateFolderList(\''.$this->id.'\'); return false;">'.JText::_('COM_DJMEDIATOOLS_SELECT').'</button>';
			$html[] = '<div class="clr"></div>';
			
			// The current user display field.
			$html2[] = '<div class="fltlft">';
			$html2[] = '  <input type="text" id="'.$this->id.'_add" value="" size="35"  placeholder="'.JText::_('COM_DJMEDIATOOLS_CREATE_FOLDER').'" />';
			$html2[] = '</div>';
			
			// The user create button.
			$html2[] = '<button id="'.$this->id.'_addbutton" onclick="addFolder(\''.$this->id.'\'); return false;">'.JText::_('COM_DJMEDIATOOLS_CREATE').'</button>';
			
			
		} else {
			$html[] = '<span class="input-append">';
			// The current user display field.
			$html[] = '  <input class="input-long" type="text" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'" readonly="readonly" size="35" data-root="'.$root_folder.'" placeholder="auto: '.$root_folder.'/id-alias" />';
			
			if(!$required) {
				$html[] = '	<button class="btn" onclick="jInsertFieldValue(\'\', \''.$this->id.'\'); document.id(\''.$this->id.'_list\').slide(\'out\'); return false;"><i class="icon-remove"></i></button>';
			}
			$html[] = '	<button id="'.$this->id.'_button" class="btn" onclick="updateFolderList(\''.$this->id.'\'); return false;">'.JText::_('COM_DJMEDIATOOLS_SELECT').'</button>';
			$html[] = '</span>';
			
			$html2[] = '<span class="input-append">';
			// The current user display field.
			$html2[] = '  <input class="input-medium" type="text" id="'.$this->id.'_add" value="" size="35" placeholder="'.JText::_('COM_DJMEDIATOOLS_CREATE_FOLDER').'" />';
				
			// The user select button.
			$html2[] = '	<button id="'.$this->id.'_addbutton" class="btn btn-primary" onclick="addFolder(\''.$this->id.'\'); return false;">'.JText::_('COM_DJMEDIATOOLS_CREATE').'</button>';
			$html2[] = '</span>';
		}
		
		$html[] = '<div id="'.$this->id.'_list" class="djfolder">';
		$html[] = '<div class="djfolderlist"></div>';
		$html[] = implode("\n", $html2);
		$html[] = '</div>';
		
		return implode("\n", $html);
	}
}

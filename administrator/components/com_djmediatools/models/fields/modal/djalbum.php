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
class JFormFieldModal_DJAlbum extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Modal_DJAlbum';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Load the modal behavior script.
		JHtml::_('behavior.modal');

		$lang = JFactory::getLanguage();
		$lang->load('com_djmediatools', JPATH_ADMINISTRATOR, 'en-GB', false, false);
		$lang->load('com_djmediatools', JPATH_ADMINISTRATOR . '/components/com_djmediatools', 'en-GB', false, false);
		$lang->load('com_djmediatools', JPATH_ADMINISTRATOR, null, true, false);
		$lang->load('com_djmediatools', JPATH_ADMINISTRATOR . '/components/com_djmediatools', null, true, false);
		
		// Build the script.
		$script = array();
		$script[] = '	function jSelectAlbum_'.$this->id.'(catid, img, title) {';
		$script[] = '		document.id("'.$this->id.'_id").value = catid;';
		$script[] = '		document.id("'.$this->id.'_name").value = title;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));


		// Setup variables for display.
		$html	= array();
		$link = 'index.php?option=com_djmediatools&amp;view=categories&amp;layout=modal&amp;tmpl=component&amp;f_name=jSelectAlbum_'.$this->id;

		$db	= JFactory::getDBO();
		$db->setQuery(
			'SELECT title' .
			' FROM #__djmt_albums' .
			' WHERE id = '.(int) $this->value
		);
		$title = $db->loadResult();

		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
		}

		if (empty($title)) {
			$title = JText::_('COM_DJMEDIATOOLS_SELECT_AN_ALBUM');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
		$options = '{handler: \'iframe\', size: {x: \'100%\', y: \'100%\'}, onOpen: function() { window.addEvent(\'resize\', function(){ this.resize({x: window.getSize().x - 100, y: window.getSize().y - 100}, true); }.bind(this) ); window.fireEvent(\'resize\'); }}';
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			// The current user display field.
			$html[] = '<div class="fltlft">';
			$html[] = '  <input type="text" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
			$html[] = '</div>';
	
			// The user select button.
			$html[] = '<div class="button2-left">';
			$html[] = '  <div class="blank">';
			$html[] = '	<a class="modal" title="'.JText::_('COM_DJMEDIATOOLS_SELECT_AN_ALBUM').'"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="'.$options.'">'.JText::_('COM_DJMEDIATOOLS_SELECT').'</a>';
			$html[] = '  </div>';
			$html[] = '</div>';
			
		} else {
			$html[] = '<span class="input-append">';
			// The current user display field.
			$html[] = '  <input class="input-medium" type="text" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
			
			// The user select button.
			$html[] = '	<a class="modal btn" title="'.JText::_('COM_DJMEDIATOOLS_SELECT_AN_ALBUM').'"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="'.$options.'">'.JText::_('COM_DJMEDIATOOLS_SELECT').'</a>';
			$html[] = '</span>';
		}
		
		// The active article id field.
		if (0 == (int)$this->value) {
			$value = '';
		} else {
			$value = (int)$this->value;
		}

		// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

		return implode("\n", $html);
	}
}

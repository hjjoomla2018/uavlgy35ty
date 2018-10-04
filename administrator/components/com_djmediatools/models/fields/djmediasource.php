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
 
defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');

/**
 * Supports an HTML select list of folder
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldDJMediasource extends JFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'DJMediasource';
	
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Once saved album can't change its source
		$disable = JRequest::getInt('id') ? true : false;
		
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ($disable)
		{
			$attr .= ' disabled="disabled"';
		}

		//$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		//$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= ' onchange="showPlgParams(this)"';
		$attr .= ' autocomplete="off"';
		
		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ($disable)
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		// Create a regular list.
		else
		{
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}
		$html[] = '<span class="djinlineinfo">'.JText::_('COM_DJMEDIATOOLS_SAVED_ALBUM_CANT_CHANGE_SOURCE').'</span>';
		//$html[] = $this->getPluginsParams();

		return implode($html);
	}
	
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Get the path in which to search for file options.
		$path = JPATH_SITE . DS . 'plugins' . DS . 'djmediatools';
		
		// Get a list of folders in the search path with the given filter.
		$folders = JFolder::folders($path);
		
		// Build the options list from the list of folders.
		if (is_array($folders))
		{
			$lang = JFactory::getLanguage();
			foreach ($folders as $folder)
			{				
				$source = JPATH_PLUGINS . '/djmediatools/' . $folder;
				$extension = 'plg_djmediatools_' . $folder;
				$lang->load($extension . '', JPATH_ADMINISTRATOR, 'en-GB', false, false);
				$lang->load($extension . '', $source, 'en-GB', false, false);
				$lang->load($extension . '', JPATH_ADMINISTRATOR, null, true, false);
				$lang->load($extension . '', $source, null, true, false);
				
				$options[] = JHtml::_('select.option', $folder, JText::_(strtoupper($extension).'_LABEL'));
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge($this->getOptionsList(), $options);

		return $options;
	}

	protected function getOptionsList()
	{
		// Initialize variables.
		$options = array();

		foreach ($this->element->children() as $option)
		{

			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option['value'],
				JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
				((string) $option['disabled'] == 'true')
			);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
	
}

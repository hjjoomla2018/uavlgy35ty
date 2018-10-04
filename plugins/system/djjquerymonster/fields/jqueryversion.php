<?php
/**
 * @version $Id: jqueryversion.php 4 2015-05-15 17:17:28Z szymon $
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
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

jimport('joomla.form.helper');

JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of folder
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldJqueryversion extends JFormFieldList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Jqueryversion';
	
	protected function getInput()
	{
		$html = parent::getInput();
		
		return $html;
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
		$options = parent::getOptions();

		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0', '>=')) { // Joomla!3+
			
			$joomla = array(JHtml::_('select.option', 'joomla', JText::_('PLG_DJJQUERYMONSTER_JOOMLA_JQUERY'), 'value', 'text'));
			
			$options = array_merge($joomla, $options);

			$this->value = isset($this->element['value']) ? (string) $this->element['value'] : 'joomla';
		}
		
		return $options;
	}	
}

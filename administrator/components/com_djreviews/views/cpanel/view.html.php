<?php
/**
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
jimport( 'joomla.application.categories');
jimport('joomla.html.pane');

class DJReviewsViewCpanel extends JViewLegacy
{
	// DJ-Reviews contains "views" in the class' name - it causes problems on J!2.5 when Joomla tries to guess tpl path using getName() method
	protected $_name = 'cpanel';
	
	function display($tpl = null)
	{
		//$model = $this->getModel();
		//$model->performChecks();
		
		JToolBarHelper::title( JText::_('COM_DJREVIEWS'));
		JToolBarHelper::preferences('com_djreviews', '450', '900');

		if (class_exists('JHtmlSidebar')){
			$this->sidebar = JHtmlSidebar::render();
		}
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}

		parent::display($tpl);
	}
}

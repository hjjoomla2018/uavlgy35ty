<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
jimport( 'joomla.application.categories');
jimport('joomla.html.pane');

class Djcatalog2ViewCpanel extends JViewLegacy
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		$model->performChecks();
		JToolBarHelper::title( JText::_('COM_DJCATALOG2_DJCATALOG'));
		JToolBarHelper::preferences('com_djcatalog2', '450', '900');
		
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

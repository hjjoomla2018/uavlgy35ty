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

class Djcatalog2ViewImport extends JViewLegacy
{
	function display($tpl = null)
	{
		
		$categories = Djc2Categories::getInstance();
		//$this->categories = $categories->getOptionList('- '.JText::_('JNONE').' -');
		$this->categories = $categories->getOptionList('- '.JText::_('JNONE').' -', false, null, false, array(), 0);
		
		$this->producers = $this->get('Producers');
		$this->users = $this->get('Users');
		$this->fieldgroups = $this->get('Fieldgroups');
		$this->acl = $this->get('ACL');
		
		$this->addToolbar();
		if (class_exists('JHtmlSidebar')){
			$this->sidebar = JHtmlSidebar::render();
		}
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}
		
		parent::display($tpl);
	}
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_DJCATALOG2_IMPORT'), 'generic.png');
		JToolBarHelper::preferences('com_djcatalog2', '450', '900');
		JToolBarHelper::help('import', true);
		
	}
}

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class Djcatalog2ViewCountries extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}		
		if (class_exists('JHtmlSidebar')){
            $this->sidebar = JHtmlSidebar::render();
        }
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_DJCATALOG2_COUNTRIES'), 'generic.png');
		JToolBarHelper::addNew('country.add','JTOOLBAR_NEW');
		JToolBarHelper::editList('country.edit','JTOOLBAR_EDIT');
		JToolBarHelper::custom('countries.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('countries.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::deleteList('', 'countries.delete','JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_djcatalog2', '450', '900');
		JToolBarHelper::divider();
	}
}

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

class Djcatalog2ViewCustomers extends JViewLegacy
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
		JToolBarHelper::title(JText::_('COM_DJCATALOG2_CUSTOMERS'), 'generic.png');
		JToolBarHelper::back('COM_DJCATALOG2_MANAGE_USER_ACCOUNTS', 'index.php?option=com_users');
		//JToolBarHelper::addNew('customer.add','JTOOLBAR_NEW');
		//JToolBarHelper::editList('customer.edit','COM_DJCATALOG2_EDIT_PROFILE');
		JToolBarHelper::editList('customer.edituser','COM_DJCATALOG2_EDIT_USER');
		JToolBarHelper::deleteList('', 'customers.delete','COM_DJCATALOG2_ERASE_CUSTOMER_INFO');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_djcatalog2', '450', '900');
		JToolBarHelper::divider();
	}
}

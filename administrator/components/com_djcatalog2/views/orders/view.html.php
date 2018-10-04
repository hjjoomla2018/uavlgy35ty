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

class Djcatalog2ViewOrders extends JViewLegacy
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
		
		if ($this->getLayout() == 'invcounters') {
			$this->counters = $this->get('Counters');
		} else {
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_DJCATALOG2_ORDERS'), 'generic.png');
		JToolBarHelper::addNew('order.add','JTOOLBAR_NEW');
		JToolBarHelper::editList('order.edit','JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('', 'orders.delete','JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		JToolbarHelper::modal('invcounters-modal', 'icon-edit', 'COM_DJCATALOG2_INV_COUNTERS');
		
		/*$export_icon = (version_compare(JVERSION, '3.0.0', '<')) ? 'export' : 'arrow-down';
		JToolBarHelper::custom('orders.invoices_filtered', $export_icon, $export_icon, 'COM_DJCATALOG2_EXPORT_FILTERED', false);
		JToolBarHelper::custom('orders.invoices_selected', $export_icon, $export_icon, 'COM_DJCATALOG2_EXPORT_SELECTED', true);
		JToolBarHelper::divider();
		*/
		
		JToolBarHelper::preferences('com_djcatalog2', '450', '900');
		JToolBarHelper::divider();
	}
}

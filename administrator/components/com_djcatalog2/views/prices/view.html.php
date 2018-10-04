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

class Djcatalog2ViewPrices extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->producers		= $this->get('Producers');
		
		$categories = Djc2Categories::getInstance();
		
		$this->categories = $categories->getOptionList('- '.JText::_('COM_DJCATALOG2_SELECT_CATEGORY').' -');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
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
		JToolBarHelper::title(JText::_('COM_DJCATALOG2_PRICES_AND_STOCK'), 'generic.png');

		JToolBarHelper::custom('items.set_prices', 'export.png', 'export_f2.png','COM_DJCATALOG2_UPDATE_PRICES', false);
		//JToolBarHelper::divider();
		JToolBarHelper::preferences('com_djcatalog2', '450', '900');
		JToolBarHelper::divider();
	}
}

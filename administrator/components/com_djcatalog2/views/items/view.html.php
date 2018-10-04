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

class Djcatalog2ViewItems extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->items		= array_values($this->get('Items'));
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->producers		= $this->get('Producers');
		
		$categories = Djc2Categories::getInstance();
		
		$this->categories = $categories->getOptionList('- '.JText::_('COM_DJCATALOG2_SELECT_CATEGORY').' -');
		
		if ($this->state->get('filter.parent') > 0) {
			$itemModel = JModelLegacy::getInstance('Item', 'DJCatalog2Model', array('ignore_request'=>true));
			$this->parent_item = $itemModel->getItem($this->state->get('filter.parent'));
		}
		
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
		$title = JText::_('COM_DJCATALOG2_ITEMS');
		if ($this->state->get('filter.parent') > 0) {
			$parentName = (!empty($this->parent_item->id)) ? $this->parent_item->name.' ['.$this->parent_item->id.']' : $this->state->get('filter.parent');
			$title .= ' - '.JText::_('COM_DJCATALOG2_PARENT_ITEM').': '.$parentName;
			$title .= ' <a class="btn button" href="'.JRoute::_('index.php?option=com_djcatalog2&view=items&filter_parent=0').'">'.JText::_('COM_DJCATALOG2_CHILD_ITEMS_GO_BACK').'</a>';
		}
		JToolBarHelper::title($title, 'generic.png');

		JToolBarHelper::addNew('item.add','JTOOLBAR_NEW');
		JToolBarHelper::editList('item.edit','JTOOLBAR_EDIT');
		//JToolBarHelper::custom('items.recreateThumbnails','move','move',JText::_('COM_DJCATALOG2_RECREATE_THUMBNAILS'),true,true);
		JToolBarHelper::divider();
		JToolBarHelper::custom('items.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('items.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::archiveList('items.archive');
		
		//if ($this->state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'items.delete','JTOOLBAR_DELETE');
		//}
		//else {
			JToolbarHelper::trash('items.trash');
		//}

		JToolBarHelper::divider();
		JToolBarHelper::custom('items.available', 'publish.png', 'publish_f2.png','COM_DJCATALOG2_MAKE_AVAILABLE', true);
		JToolBarHelper::custom('items.inavailable', 'unpublish.png', 'unpublish_f2.png', 'COM_DJCATALOG2_MAKE_INAVAILABLE', true);
		JToolBarHelper::divider();
		
		$export_icon = (version_compare(JVERSION, '3.0.0', '<')) ? 'export' : 'arrow-down';
		
		JToolBarHelper::custom('items.geocode', $export_icon, $export_icon, 'COM_DJCATALOG2_GEOCODE', true);
		
		JToolBarHelper::custom('items.export_filtered', $export_icon, $export_icon, 'COM_DJCATALOG2_EXPORT_FILTERED', false);
		JToolBarHelper::custom('items.export_selected', $export_icon, $export_icon, 'COM_DJCATALOG2_EXPORT_SELECTED', true);
		
		JToolBarHelper::divider();
		JToolbarHelper::modal('items-modal', 'icon-edit', 'JTOOLBAR_BATCH');
		
		JToolBarHelper::preferences('com_djcatalog2', '450', '900');
		JToolBarHelper::divider();
	}
}

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

class Djcatalog2ViewCategories extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->categories = Djc2Categories::getInstance();
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$this->ordering = array();
		foreach ($this->items as &$item) {
			if (!isset($this->ordering[$item->parent_id])){
				$this->ordering[$item->parent_id] = array();
			}
			$this->ordering[$item->parent_id][] = $item->id;
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
		JToolBarHelper::title(JText::_('COM_DJCATALOG2_CATEGORIES'), 'generic.png');

		JToolBarHelper::addNew('category.add','JTOOLBAR_NEW');

		JToolBarHelper::editList('category.edit','JTOOLBAR_EDIT');
		//JToolBarHelper::custom('categories.recreateThumbnails','move','move',JText::_('COM_DJCATALOG2_RECREATE_THUMBNAILS'),true,true);

		JToolBarHelper::divider();
		JToolBarHelper::custom('categories.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('categories.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);

		JToolBarHelper::deleteList('', 'categories.delete','JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		
		$export_icon = (version_compare(JVERSION, '3.0.0', '<')) ? 'export' : 'arrow-down';
		JToolBarHelper::custom('categories.export_filtered', $export_icon, $export_icon, 'COM_DJCATALOG2_EXPORT_FILTERED', false);
		JToolBarHelper::custom('categories.genAliases','publish','publish',JText::_('COM_DJCATALOG2_GENERATE_ALIASES'), true,true);
		JToolBarHelper::custom('categories.mergeAliases','publish','publish',JText::_('COM_DJCATALOG2_MERGE_ALIASES'), true,true);
		JToolBarHelper::custom('categories.genMergeAliases','publish','publish',JText::_('COM_DJCATALOG2_GEN_MERGE_ALIASES'), true,true);
		JToolBarHelper::divider();

		JToolBarHelper::preferences('com_djcatalog2', '450', '900');
		JToolBarHelper::divider();
	}
}

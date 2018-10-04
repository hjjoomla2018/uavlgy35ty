<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pagination');

class DJCatalog2ViewCompare extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/compare');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/compare');
		}
	}
	
	function display($tpl = null) {
		$app = JFactory::getApplication();
		
		$this->document = JFactory::getDocument();
		
		$model = $this->getModel();
		
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		
		$dispatcher	= JEventDispatcher::getInstance();
		$this->categories = Djc2Categories::getInstance(array('state'=>'1', 'access'=>$groups));
		
		
		
		$this->params = $params = Djcatalog2Helper::getParams();
		$this->items = $model->getItems();
		$this->comparable = $model->getComparable();
		$this->attributes = $model->getAttributes();
		
		if (count($this->items)) {
			JPluginHelper::importPlugin('djcatalog2');
			
			foreach ($this->items as $k=>$v) {
				$this->items[$k]->event = new stdClass();
				
				$resultsAfterTitle = $dispatcher->trigger('onAfterDJCatalog2DisplayTitle', array (&$this->items[$k], &$this->params, 0, 'items.'.$params->get('list_layout','items')));
				$this->items[$k]->event->afterDJCatalog2DisplayTitle = trim(implode("\n", $resultsAfterTitle));
				
				$resultsBeforeContent = $dispatcher->trigger('onBeforeDJCatalog2DisplayContent', array (&$this->items[$k], &$this->params, 0, 'items.'.$params->get('list_layout','items')));
				$this->items[$k]->event->beforeDJCatalog2DisplayContent = trim(implode("\n", $resultsBeforeContent));
				
				$resultsAfterContent = $dispatcher->trigger('onAfterDJCatalog2DisplayContent', array (&$this->items[$k], &$this->params, 0, 'items.'.$params->get('list_layout','items')));
				$this->items[$k]->event->afterDJCatalog2DisplayContent = trim(implode("\n", $resultsAfterContent));
			}
		}
		
		$this->_prepareDocument();
		
        parent::display($tpl);
	}
	
	protected function _prepareDocument() {
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;
		$heading		= null;

		$menu = $menus->getActive();
		
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		$title = $this->params->get('page_title', '');

		if ($menu && ($menu->query['option'] != 'com_djcatalog2' || $menu->query['view'] != 'compare')) {
			
			$title = JText::_('COM_DJCATALOG2_COMPARE_HEADING');
			
			$this->params->set('page_heading', $title);
			
			$path = array(array('title' => $title, 'link' => ''));
			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0)) {
			if ($app->getCfg('sitename_pagetitles', 0) == '2') {
				$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
			} else {
				$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
			}
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description')) 
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) 
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

}





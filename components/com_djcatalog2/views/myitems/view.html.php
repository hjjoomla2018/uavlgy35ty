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

class DJCatalog2ViewMyitems extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/myitems');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/myitems');
		}
	}
	
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		if ((bool)$user->guest) {
			$return_url = base64_encode(DJCatalogHelperRoute::getMyItemsRoute());
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
			return true;
		}
		
		JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models', 'DJCatalog2Model');
		$model = JModelLegacy::getInstance('Items', 'Djcatalog2Model', array('ignore_request'=>true));
		
		$this->state		= $model->getState();
		$this->params = Djcatalog2Helper::getParams();
		
		//$model->setState('list.start', $app->input->get('limitstart', 0));
		// TODO
		//$limitstart = $app->getUserStateFromRequest('com_djcatalog2.myitems.limitstart', 'limitstart', 0);
		$limitstart = $app->input->get('limitstart', 0);
		$model->setState('list.start', $limitstart);
		$model->setState('list.limit', 10);
		
		// state 0 means both published and unpublished
		$model->setState('filter.state', 0);
		
		$model->setState('filter.catalogue',false);
		
		if (!$user->authorise('core.edit', 'com_djcatalog2') || $this->params->get('fed_myitems_list', '0') == '0') {
			$model->setState('filter.owner', $user->id);
		}
		
		$ordering = $app->getUserStateFromRequest('com_djcatalog2.myitems.ordering', 'order', 'i.ordering');
		$model->setState('list.ordering', $ordering);
		
		$order_dir = $app->getUserStateFromRequest('com_djcatalog2.myitems.order_dir', 'dir', 'asc');
		$model->setState('list.direction', $order_dir);
		
		$search = urldecode($app->input->get( 'search','','string' ));
		$search				= JString::strtolower( $search );

		$model->setState('filter.search', $search);
		
		$this->items		= $model->getItems();
		$this->pagination	= $model->getPagination();
		
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
		$menu_query = (!empty($menu->query)) ? $menu->query : array();
		$option = (!empty($menu_query['option'])) ? $menu_query['option'] : null;
		$view = (!empty($menu_query['view'])) ? $menu_query['view'] : null;
		
		$id = (int) @$menu->query['cid'];
		
		if ($menu && $option == 'com_djcatalog2' && $view == 'myitems') {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->set('page_heading', JText::_('COM_DJCATALOG2_MY_ITEMS_HEADING'));
		}
		
		$title = ($option == 'com_djcatalog2' && $view == 'myitems') ? $this->params->get('page_title', '') : null;

		if (empty($title)) {
			$title = JText::_('COM_DJCATALOG2_MY_ITEMS_HEADING');
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





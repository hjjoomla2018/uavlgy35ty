<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pagination');

class DJCatalog2ViewArchived extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$app = JFactory::getApplication();
		$theme = DJCatalog2ThemeHelper::getThemeName();
		
		// Items view fallback
		$this->_addPath('template', JPATH_COMPONENT.  '/views/items/tmpl');
		$this->_addPath('template', JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_djcatalog2/items');
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/items');
		
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/items');
		}
		
		$this->_addPath('template', JPATH_COMPONENT.  '/views/archived/tmpl');
		$this->_addPath('template', JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_djcatalog2/archived');
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/archived');
		
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/archived');
		}
	}
	
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$view = $jinput->get('view');
		$document= JFactory::getDocument();
		
		$menus		= $app->getMenu('site');
		$menu  = $menus->getActive();
		
		$mOption = (empty($menu->query['option'])) ? null : $menu->query['option'];
		$mCatid = (empty($menu->query['cid'])) ? null : (int)$menu->query['cid'];
		$mProdid   = (empty($menu->query['pid'])) ? null : (int)$menu->query['pid'];
		
		$filter_catid		= $jinput->getInt('cid', null);
		if ($filter_catid === null && $mOption == 'com_djcatalog2' && $mCatid) {
			$filter_catid = $mCatid;
			$jinput->set('cid', $filter_catid);
		}
		
		$filter_producerid	= $jinput->get( 'pid',null,'string' );
		if ($filter_producerid === null && $mOption == 'com_djcatalog2' && $mProdid) {
			$filter_producerid = $mProdid;
			$jinput->set('pid', (int)$filter_producerid);
		}
		
		JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models', 'DJCatalog2Model');
		$model = JModelLegacy::getInstance('Items', 'Djcatalog2Model', array('ignore_request'=>true));
		
		$this->state		= $model->getState();
		$params = Djcatalog2Helper::getParams();
		
		$filter_order		= $jinput->get( 'order',$params->get('items_default_order','i.ordering'),'cmd' );
		$filter_order_Dir	= $jinput->get( 'dir',	$params->get('items_default_order_dir','asc'), 'word' );
		$search				= urldecode($jinput->get( 'search','','string' ));
		$search				= JString::strtolower( $search );
		
		$limitstart	= $jinput->get('limitstart', 0, 'int');
		$limit_items_show = $params->get('limit_items_show',10);
		
		$lists = array();
		
		if ($filter_order_Dir == '' || $filter_order_Dir == 'desc') {
			$lists['order_Dir'] = 'asc';
		} else {
			$lists['order_Dir'] = 'desc';
		}
		$lists['order'] = $filter_order;
		
		$categories = Djc2Categories::getInstance(array('state'=>'1'));
		
		// current category
		$category = $categories->get((int) $jinput->getInt('cid',0));
		if (($category && $category->id > 0 && $category->published == 0) || empty($category)) {
			throw new Exception(JText::_('COM_DJCATALOG2_PRODUCT_NOT_FOUND'), 404);
		}
		
		//$model->setState('list.start', $app->input->get('limitstart', 0));
		$model->setState('list.start', $limitstart);
		$model->setState('list.limit', 10);
		
		// state 2 means archived
		$model->setState('filter.state', 2);
		$model->setState('filter.category', (int)$filter_catid);
		$model->setState('filter.producer', (int)$filter_producerid);
		
		$model->setState('filter.catalogue',false);
		
		$model->setState('list.ordering', $filter_order);
		$model->setState('list.direction', $filter_order_Dir);
		
		$this->items		= $model->getItems();
		$this->pagination	= $model->getPagination();
		$this->params 		= $params;
		$this->lists 		= $lists;
		
		$this->attributes = $model->getAttributes();
		$this->column_attributes = $model->getFieldGroups();
		$this->sortables = $model->getSortables();
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
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
		$menu_query = (!empty($menu->query)) ? $menu->query : array();
		$option = (!empty($menu_query['option'])) ? $menu_query['option'] : null;
		$view = (!empty($menu_query['view'])) ? $menu_query['view'] : null;
		
		$id = (int) @$menu->query['cid'];
		
		if ($menu && $option == 'com_djcatalog2' && $view == 'archived') {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->set('page_heading', JText::_('COM_DJCATALOG2_ARCHIVED_ITEMS_HEADING'));
		}
		
		$title = ($option == 'com_djcatalog2' && $view == 'archived') ? $this->params->get('page_title', '') : null;

		if (empty($title)) {
			$title = JText::_('COM_DJCATALOG2_ARCHIVED_ITEMS_HEADING');
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





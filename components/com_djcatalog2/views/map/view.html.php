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

class DJCatalog2ViewMap extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/map');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/map');
		}
	}
	
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$user = JFactory::getUser();
		$categories = Djc2Categories::getInstance(array('state'=>'1'));
		$menus		= $app->getMenu('site');
		$menu  = $menus->getActive();
		
		JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models', 'DJCatalog2Model');
		$model = JModelLegacy::getInstance('Items', 'Djcatalog2Model', array('ignore_request'=>true));
		
		$mOption = (empty($menu->query['option'])) ? null : $menu->query['option'];
		$mCatid = (empty($menu->query['cid'])) ? null : (int)$menu->query['cid'];
		$mProdid   = (empty($menu->query['pid'])) ? null : (int)$menu->query['pid'];
		
		$filter_catid		= $jinput->getInt('cid', null);
		if ($filter_catid === null && $mOption == 'com_djcatalog2' && $mCatid) {
			$filter_catid = $mCatid;
			$jinput->set('cid', $filter_catid);
		}
		
		$category = $categories->get((int) $jinput->getInt('cid',0));
		
		$filter_producerid	= $jinput->get( 'pid',null,'string' );
		if ($filter_producerid === null && $mOption == 'com_djcatalog2' && $mProdid) {
			$filter_producerid = $mProdid;
			$jinput->set('pid', (int)$filter_producerid);
		}
		
		$search				= urldecode($jinput->get( 'mapsearch','','string' ));
		$search				= JString::strtolower( $search );
		
		$this->state		= $model->getState();
		$this->params = $params = Djcatalog2Helper::getParams();
		
		if ($filter_catid) {
			$model->setState('filter.category', (int)$filter_catid);
		}
		if ($filter_producerid) {
			$model->setState('filter.producer', (int)$filter_producerid);
		}
		if (JString::strlen($search) > 0) {
			$model->setState('filter.map.address', $search);
		} else {
			$search = '';
		}
		
		// state 0 means both published and unpublished
		$model->setState('filter.state', 1);
		
		$map_radius 		= $jinput->get( 'ms_radius', false, 'int' );
		$model->setState('filter.map.radius', $map_radius);
		
		$map_unit 		= $jinput->get( 'ms_unit', false, 'string' );
		$model->setState('filter.map.unit', $map_unit);
		
		$model->setState('filter.map', true);
		
		
		$model->setState('filter.catalogue',false);
		
		$ordering = 'i.ordering';// $app->getUserStateFromRequest('com_djcatalog2.myitems.ordering', 'order', 'i.ordering');
		$model->setState('list.ordering', $ordering);
		
		$order_dir = 'asc';//$app->getUserStateFromRequest('com_djcatalog2.myitems.order_dir', 'dir', 'asc');
		$model->setState('list.direction', $order_dir);
		
		$this->items		= $model->getItems();
		//$this->pagination	= $model->getPagination();
		
		$lists=array();
		
		$lists['search']= $search;
		
		// category filter
		$category_options = $categories->getOptionList('- '.JText::_('COM_DJCATALOG2_SELECT_CATEGORY').' -');
		
		if ($filter_catid > 0 && !empty($category)) {
			$category_path = $category->getPath();
			if (count($category_path) > 0) {
				if ($mCatid == 0) {
					$parent_category = $categories->get(0);
				} else {
					$parent_category = $categories->get((int)end($category_path));
				}
				if ($parent_category) {
					$childrenList = array($parent_category->id);
					$parent_category->makeChildrenList($childrenList);
					foreach ($category_options as $key => $option) {
						if (!in_array($option->value, $childrenList)) {
							unset($category_options[$key]);
						}
						if ($option->value == $parent_category->id) {
							$category_options[$key]->text = '- '.JText::_('COM_DJCATALOG2_SELECT_CATEGORY').' -';
						}
					}
				}
			}
		}
		
		$lists['categories'] = JHTML::_('select.genericlist', $category_options, 'cid', 'class="inputbox input"', 'value', 'text', $filter_catid);
		
		// producer filter
		$producers_first_option = new stdClass();
		$producers_first_option->id = '0';
		$producers_first_option->text = '- '.JText::_('COM_DJCATALOG2_SELECT_PRODUCER').' -';
		$producers_first_option->disable = false;
		$prodList = $model->getProducers();
		$producers = count($prodList) ? array_merge(array($producers_first_option),$prodList) : array($producers_first_option);
		$lists['producers'] = JHTML::_('select.genericlist', $producers, 'pid', 'class="inputbox input"', 'id', 'text', (int)$filter_producerid);
		
		$this->assignref('lists', $lists);
		
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
		
		if ($menu && $option == 'com_djcatalog2' && $view == 'map') {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->set('page_heading', JText::_('COM_DJCATALOG2_MAP_HEADING'));
		}
		
		$title = ($option == 'com_djcatalog2' && $view == 'map') ? $this->params->get('page_title', '') : null;

		if (empty($title)) {
			$title = JText::_('COM_DJCATALOG2_MAP_HEADING');
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





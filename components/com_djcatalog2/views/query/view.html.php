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

class DJCatalog2ViewQuery extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/query');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/query');
		}
	}
	
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$this->params = Djcatalog2Helper::getParams();
		$this->model = $this->getModel();
		
		if ($this->params->get('cart_query_enabled', '1') != '1') {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$auth = ($this->params->get('cart_query_registered', '1') == '1' && $user->guest) ? false : true;
		
		if (!$auth) {
			$return_url = base64_encode(DJCatalogHelperRoute::getQueryRoute());
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
			return true;
		}
		
		$cart_items = $app->getUserState('com_djcatalog2.cart.items', array());
		
		$this->basket = Djcatalog2HelperCart::getInstance();
		
		$this->items = $this->basket->getItems();
		
		if (empty($this->items)) {
			$app->redirect(JUri::base(), JText::_('COM_DJCATALOG2_CART_IS_EMPTY'));
			return true;
		}
		
		if (count($this->items)) {
			JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models', 'DJCatalog2Model');
			$itemsModel = JModelLegacy::getInstance('Items', 'Djcatalog2Model', array('ignore_request'=>true));
			$parents = array();
			foreach ($this->items as $item) {
				if ($item->parent_id > 0) {
					$parents[] = $item->parent_id;
				}
			}
			if (count($parents) > 0) {
				$state      = $itemsModel->getState();
				$itemsModel->setState('list.start', 0);
				$itemsModel->setState('list.limit', 0);
				$itemsModel->setState('filter.catalogue',false);
				$itemsModel->setState('list.ordering', 'i.name');
				$itemsModel->setState('list.direction', 'asc');
				$itemsModel->setState('filter.parent', '*');
				$itemsModel->setState('filter.state', '3');
		
				$itemsModel->setState('filter.item_ids', $parents);
		
				$parentItems = $itemsModel->getItems();
		
				foreach ($this->items as $id=>$item) {
					if ($item->parent_id > 0 && isset($parentItems[$item->parent_id])) {
						$this->items[$id]->parent =  $parentItems[$item->parent_id];
					} else {
						$this->items[$id]->parent =  false;
					}
				}
			}
		}
		
		$user_profile = Djcatalog2Helper::getUserProfile();
		$user = Djcatalog2Helper::getUser();
				
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$this->form = $this->get('Form');
		
		$data = JArrayHelper::fromObject($user_profile, false);
		$post_data = (array)$app->getUserState('com_djcatalog2.query.data', array());
		
		if (!empty($post_data)) {
			foreach($post_data as $k=>$v) {
				$data[$k] = $v;
			}
		}
		
		// TODO: dummy value/workaround
		//$data['customer_note'] = '-';
		$this->user_valid = $this->model->validate($this->form, array('djcatalog2profile' => $data), 'djcatalog2profile');
		
		if ($user_profile) {
			$name = array();
			if (!empty($user_profile->firstname)) {
				$name[] = $user_profile->firstname;
			}
			if (!empty($user_profile->lastname)) {
				$name[] = $user_profile->lastname;
			}
			
			$user_profile->_name = (count($name) > 0) ? implode(' ', $name) : null;
		}

		$this->user_profile = $user_profile;
		$this->user = $user;
		$this->total = $this->basket->getTotal();
		$this->product_total = $this->basket->getProductTotal();
		
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
		
		if ($menu && $option == 'com_djcatalog2' && $view == 'query') {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->set('page_heading', JText::_('COM_DJCATALOG2_QUERY_HEADING'));
		}
		
		$title = ($option == 'com_djcatalog2' && $view == 'query') ? $this->params->get('page_title', '') : null;

		if (empty($title)) {
			$title = JText::_('COM_DJCATALOG2_QUERY_HEADING');
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





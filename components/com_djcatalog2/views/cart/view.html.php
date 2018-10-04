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

class DJCatalog2ViewCart extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/cart');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/cart');
		}
	}
	
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$this->params = Djcatalog2Helper::getParams();
		
		/*
		$price_auth = ($this->params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;
		
		if (!$price_auth) {
			$return_url = base64_encode(DJCatalogHelperRoute::getCartRoute());
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
			return true;
		}*/
		
		$cart_items = $app->getUserState('com_djcatalog2.cart.items', array());
		
		$this->basket = Djcatalog2HelperCart::getInstance();
		//echo '<pre>';print_r($this->basket);echo '</pre>';
		$this->items = $this->basket->getItems();
		$this->total = $this->basket->getTotal();
		$this->product_total = $this->basket->getProductTotal();
		$this->product_old_total = $this->basket->getProductOldTotal();
		
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
        
		$this->_prepareDocument();
		
		if ($this->getLayout() == 'login') {
			if ($user->guest == false) {
				$app->redirect(JRoute::_(DJCatalog2HelperRoute::getCheckoutRoute(), false), 303);
				return;
			}
			
			$lang = JFactory::getLanguage();
			$lang->load('com_users');
			
			/*JForm::addFormPath(JPath::clean(JPATH_ROOT.'/components/com_users/models/forms'));
			JModelLegacy::addIncludePath(JPath::clean(JPATH_ROOT.'/components/com_users/models'), 'UsersModel');
			$registration_model = JModelLegacy::getInstance('Registration', 'UsersModel');
			
			$this->reg_data   = $registration_model->getData();
			$this->reg_form   = $registration_model->getForm();
			$this->reg_state  = $registration_model->getState();
			$this->reg_params = $this->reg_state->get('params');
			*/
			
			require_once JPATH_ROOT.'/components/com_users/helpers/route.php';
			$this->params->set('page_heading', JText::_('COM_DJCATALOG2_CART_LOGIN_HEADING'));
		}
        
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
		
		if ($menu && $option == 'com_djcatalog2' && $view == 'cart') {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->set('page_heading', JText::_('COM_DJCATALOG2_CART_HEADING'));
		}
		
		$title = ($option == 'com_djcatalog2' && $view == 'cart') ? $this->params->get('page_title', '') : null;

		if (empty($title)) {
			$title = JText::_('COM_DJCATALOG2_CART_HEADING');
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





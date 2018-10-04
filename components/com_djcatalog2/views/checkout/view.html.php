<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

use Joomla\Registry\Registry;

defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pagination');

class DJCatalog2ViewCheckout extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/checkout');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/checkout');
		}
	}
	
	public function display($tpl = null)
	{
		
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$this->params = Djcatalog2Helper::getParams();
		
		$model = JModelLegacy::getInstance('Order', 'Djcatalog2Model', array());
		$this->setModel($model, true);
		$this->model = $this->getModel();
		
		if ($this->params->get('cart_enabled', '1') != '1') {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$auth = ($this->params->get('cart_registered', '1') == '1' && $user->guest) ? false : true;
		
		if (!$auth) {
			$return_url = base64_encode(DJCatalogHelperRoute::getCheckoutRoute());
			//$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
			$app->redirect(JRoute::_(DJCatalog2HelperRoute::getCartRoute().'&layout=login', false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
			return true;
		}
		
		$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2') || $user->authorise('core.admin', 'com_djcatalog2');
		
		$cart_items = $app->getUserState('com_djcatalog2.cart.items', array());
		
		$this->basket = Djcatalog2HelperCart::getInstance();
		
		if (!$salesman) {
			foreach ($this->basket->items as $item) {
				if ($item->_prices['base']['display'] == 0.0 || ($item->_quantity > $item->stock && $item->onstock != 2) || $item->_quantity == 0) {
					//if (($qty > $item->stock && $item->onstock != 2) || !$item->onstock || $qty == 0) {
					$app->redirect(JRoute::_(DJCatalogHelperRoute::getCartRoute(), false), JText::sprintf('COM_DJCATALOG2_CHECKOUT_EMPTY_PRICES', JRoute::_('index.php?option=com_djcatalog2&task=cart.clearfree')));
					return true;
				}
			}
		}
		
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
		$postOrder = (array)$app->getUserState('com_djcatalog2.order.data', array());
		
		if (!empty($postOrder)) {
			foreach($postOrder as $k=>$v) {
				$data[$k] = $v;
			}
		}

		$this->user_valid = $this->model->validate($this->form, array('djcatalog2profile' => $data), 'djcatalog2profile');
		$this->billing_valid = $this->model->validate($this->form, array('djcatalog2billing' => $data), 'djcatalog2billing');
		
		$dispatcher = JEventDispatcher::getInstance();
		
		JPluginHelper::importPlugin('djcatalog2payment');
		JPluginHelper::importPlugin('djcatalog2delivery');
		
		$deliveryMethods = $model->getDeliveryMethods();
		$paymentMethods = $model->getPaymentMethods('*');
		
		$deliveryRes = array();
		$paymentRes = array();
		
		foreach ($deliveryMethods as &$deliveryObject) {
			$params = new Registry();
			$params->loadString($deliveryObject->params, 'JSON');
			$deliveryObject->params = $params;
			$deliveryRes[$deliveryObject->id] = $dispatcher->trigger('onDJC2CheckoutDetailsDisplay', array('com_djcatalog2.checkout.delivery', $deliveryObject));
		}
		unset($deliveryObject);
		
		foreach ($paymentMethods as &$paymentObject) {
			$params = new Registry();
			$params->loadString($paymentObject->params, 'JSON');
			$paymentObject->params = $params;
			$paymentRes[$paymentObject->id] = $dispatcher->trigger('onDJC2CheckoutDetailsDisplay', array('com_djcatalog2.checkout.payment', $paymentObject));
		}
		unset($paymentObject);
		
		$this->delivery_info = $deliveryRes;
		$this->payment_info = $paymentRes;
		
		$this->user_profile = $user_profile;
		$this->user = $user;
		$this->total = $this->basket->getTotal();
		$this->product_total = $this->basket->getProductTotal();
		$this->product_old_total = $this->basket->getProductOldTotal();
		
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
		
		if ($menu && $option == 'com_djcatalog2' && $view == 'checkout') {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->set('page_heading', JText::_('COM_DJCATALOG2_CHECKOUT_HEADING'));
		}
		
		$title = ($option == 'com_djcatalog2' && $view == 'checkout') ? $this->params->get('page_title', '') : null;
		
		if (empty($title)) {
			$title = JText::_('COM_DJCATALOG2_CHECKOUT_HEADING');
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





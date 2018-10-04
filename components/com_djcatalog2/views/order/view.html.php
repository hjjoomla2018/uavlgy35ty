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

class DJCatalog2ViewOrder extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/order');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/order');
		}
	}
	
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$this->params = Djcatalog2Helper::getParams();
		
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		
		if (empty($this->item)) {
			return JError::raiseError(404, JText::_('COM_DJCATALOG2_ORDER_NOT_FOUND'));
			return;
		}
		
		$token = $app->getUserStateFromRequest('com_djcatalog2.order.token', 'token', null, 'string');
		$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2');
		
		if ((strcmp($token, $this->item->token) !== 0 && !$salesman) || ($salesman && $user->id != $this->item->salesman_id) || $this->item->token ==  '') {
			if ( ($token && $user->guest) || ($user->id != $this->item->user_id && !$user->guest) ) {
				return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			} else if ($user->guest && !$token) {
				$return_url = base64_encode(DJCatalogHelperRoute::getOrderRoute($this->item->id));
				$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
				return true;
			}
		}
		
		$this->items = $this->item->items;
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$dispatcher = JEventDispatcher::getInstance();
		$db = JFactory::getDbo();
		
		JPluginHelper::importPlugin('djcatalog2payment');
		JPluginHelper::importPlugin('djcatalog2delivery');
		
		$deliveryObject = $paymentObject = $deliveryPlg = $paymentPlg = false;
		
		if (!empty($this->item->delivery_method_id)) {
			$db->setQuery('select * from #__djc2_delivery_methods where id='.(int)$this->item->delivery_method_id);
			if ($deliveryObject = $db->loadObject()) {
				$params = new JRegistry();
				$params->loadString($deliveryObject->params, 'JSON');
				$deliveryObject->params = $params;
				$deliveryPlg = JPluginHelper::getPlugin('djcatalog2delivery', $deliveryObject->plugin);
			}
		}
		
		if (!empty($this->item->payment_method_id)) {
			$db->setQuery('select * from #__djc2_payment_methods where id='.(int)$this->item->payment_method_id);
			if ($paymentObject = $db->loadObject()) {
				$params = new JRegistry();
				$params->loadString($paymentObject->params, 'JSON');
				$paymentObject->params = $params;
				$paymentPlg = JPluginHelper::getPlugin('djcatalog2payment', $paymentObject->plugin);
			}
		}
		
		
		$deliveryRes = (!empty($deliveryObject)) ? $dispatcher->trigger('onDJC2OrderDetailsDisplay', array('com_djcatalog2.order.delivery', $this->item, $deliveryObject)) : array();
		$paymentRes = (!empty($paymentObject)) ? $dispatcher->trigger('onDJC2OrderDetailsDisplay', array('com_djcatalog2.order.payment', $this->item, $paymentObject)) : array();
		
		$this->delivery_info = trim(implode("", $deliveryRes));
		$this->payment_info = trim(implode("", $paymentRes));
		
		$this->delivery_method = $deliveryObject;
		$this->payment_method = $paymentObject;
		
		if ($app->input->get('pdf') == '1' && $app->input->get('tmpl') == 'component' && $this->getLayout() == 'print') {
		
			if (JFile::exists(JPath::clean(JPATH_ROOT.'/libraries/dompdf/dompdf_config.inc.php')) == false) {
				throw new Exception('DOMPDF Libary is missing!');
			}
				
			$this->_preparePDF();
				
			$app->close();
			return true;
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
		
		$this->params->set('page_heading', JText::sprintf('COM_DJCATALOG2_ORDER_HEADING', $this->item->order_number));
		
		$title = JText::sprintf('COM_DJCATALOG2_ORDER_HEADING', $this->item->order_number);

		if ($app->getCfg('sitename_pagetitles', 0)) {
			if ($app->getCfg('sitename_pagetitles', 0) == '2') {
				$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
			} else {
				$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
			}
		}

		$this->document->setTitle($title);
	}
	
	protected function _preparePDF() {
		if (!defined('DOMPDF_ENABLE_REMOTE'))
		{
			define('DOMPDF_ENABLE_REMOTE', true);
		}
			
		$config = JFactory::getConfig();
		$document = JFactory::getDocument();
	
		$document->setMimeEncoding('application/pdf');
	
		if (!defined('DOMPDF_FONT_CACHE'))
		{
			define('DOMPDF_FONT_CACHE', $config->get('tmp_path'));
		}
	
		if (!defined('DOMPDF_DEFAULT_FONT'))
		{
			define('DOMPDF_DEFAULT_FONT', 'DejaVuSans');
		}
	
		require_once JPath::clean(JPATH_ROOT.'/libraries/dompdf/dompdf_config.inc.php');
	
		if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}
	
		$pdf =new DOMPDF();
	
		ob_start();
		parent::display(null);
		$body = ob_get_contents();
		ob_end_clean();
	
		$document->_scripts = array();
		$document->_script = array();
	
		$head = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'; //$document->getBuffer('head');
	
		$data = '<html><head>'.$head.'</head><body style="font-family: firefly, DejaVu Sans, sans-serif !important;">'.$body.'</body></html>';
	
		DJCatalog2HtmlHelper::setFullPaths($data);
	
		$pdf->load_html($data);
		$pdf->render();
		$pdf->stream(JFile::makeSafe($this->item->order_number) . '.pdf');
	}

}





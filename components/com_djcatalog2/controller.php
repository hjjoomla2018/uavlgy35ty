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
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

class DJCatalog2Controller extends JControllerLegacy
{

	function __construct($config = array())
	{
		parent::__construct($config);
		$lang = JFactory::GetLanguage();
		$lang->load('com_djcatalog2');
		$this->registerTask( 'modfp',  'getFrontpageXMLData' );
		$this->registerTask( 'search_reset',  'search' );
	}

	function display($cachable = true, $urlparams = null)
	{
		$app = JFactory::getApplication();
		
		$view = $app->input->get('view');
		$user = JFactory::getUser();
		
		$params = Djcatalog2Helper::getParams();
		$cached_views = (array)$params->get('cache_options', array('items','item','producers','producer','map'));
		
		$id = $app->input->getInt('id');
		
		if ($view == 'itemform' && !$this->checkEditId('com_djcatalog2.edit.itemform', $id)) {
			$app->redirect(JRoute::_(DJCatalogHelperRoute::getMyItemsRoute(), false), JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			return true;
			
		}
		
		$noncachable = array('itemform', 'myitems', 'cart', 'order', 'orders', 'query', 'checkout', 'query', 'questions', 'question', 'compare', 'orderform');
		
		if (in_array($view, $noncachable) || !in_array($view, $cached_views) || $user->id) {
			$cachable = false;
		} else if ($view == 'items' || $view == 'map') {
			$hasSearch = (bool)(
						$app->input->getString('search', '') != '' 
						|| $app->input->getString('mapsearch', '') != ''
						|| $app->input->getString('ind', '') != ''
						|| $app->input->getString('tag', '') != ''
						);
			$hasFilters = (bool) ($app->input->getString('cm', '') !== '');
			$hasSort = (bool)($app->input->getString('order', '') != '' || $app->input->getString('dir', '') != '');

			if ($hasSearch) {
				$cachable = false;
			}
			if ($hasFilters && !in_array('filters', $cached_views)) {
				$cachable = false;
			}
			if ($hasSort && !in_array('sort', $cached_views)) {
				$cachable = false;
			}
		}
		
		DJCatalog2ThemeHelper::setThemeAssets();
		
		$urlparams = array(
				'id' => 'STRING',
				'cid' => 'STRING',
				'pid' => 'STRING',
				'aid' => 'STRING',
				'search' => 'STRING',
				'task' => 'STRING',
				'order' => 'STRING',
				'dir' => 'STRING',
				'cm' => 'INT',
				'l' => 'STRING',
				'Itemid' => 'INT',
				'limit' => 'UINT', 
				'limitstart' => 'UINT',
				'start' => 'UINT',
				'lang' => 'CMD',
				'tmpl' => 'CMD',
				'ind' => 'RAW',
				'template' => 'STRING',
				'price_from' => 'STRING',
				'price_to' => 'STRING',
				'type' => 'STRING',
				'print' => 'INT',
				'pdf'	=> 'INT',
				'layout'=> 'STRING',
				'ms_unit' => 'STRING',
				'ms_radius' => 'INT',
				'mapsearch' => 'STRING',
				'eid'       => 'STRING',
				'ecid'      => 'STRING',
				'oid' 		=> 'INT',
				'plg'		=> 'STRING',
				'plgid'		=> 'INT',
				'billing'	=> 'INT',
				'error'		=> 'RAW',
				'success'	=> 'RAW',
				'view' => 'STRING',
				'layout' => 'STRING',
				'qid' => 'UINT',
				'token' => 'STRING',
				'tag' => 'STRING',
				'pic_only' => 'INT',
		);
		
		$db = JFactory::getDbo();
		$db->setQuery('select alias from #__djc2_items_extra_fields where type=\'checkbox\' or type=\'radio\' or type=\'select\'');
		$extra_fields = $db->loadColumn();
		if (count($extra_fields) > 0) {
			foreach($extra_fields as $extra_field) {
				$urlparams['f_'.$extra_field] = 'RAW';
				
				// stupid, stupid, stupid me
				$urlparams[str_replace('-', '_', 'f_'.$extra_field)] = 'RAW';
			}
		}

		parent::display($cachable, $urlparams);
	}
	
	function getFrontpageXMLData() {
		$model = $this->getModel('modfrontpage');
		$xml = $model->getXml();
		
		if (!count(array_diff(ob_list_handlers(), array('default output handler'))) || ob_get_length()) {
			@ob_clean();
		}
		
		if (!headers_sent()) {
			$document = JFactory::getDocument();
			header('Content-Type: \'text/xml\'; charset='.$document->_charset);
		}
		
		echo $xml;
		JFactory::getApplication()->close();
	}
	
	function search() {
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		//$post = JRequest::get('post');
		$post = $app->input->getMethod() == 'POST' ? $app->input->getArray($_POST) : $app->input->getArray($_GET);
		$params = array();
		foreach($post as $key => $value) {
			if ($key != 'task' && $key != 'option' && $key != 'view' && $key != 'cid' && $key != 'pid' && $key != 'Itemid') {
				if ($key == 'search') {
					$params[] = $key.'='.urlencode($value);
				}
				else if (is_array($value)) {
					foreach ($value as $k => $v) {
						if (is_numeric($k)) {
							$params[] = $key.'[]='.$v;
						} else {
							$params[] = $key.'['.$k.']='.$v;
						}
					}
				}
				else {
					$params[] = $key.'='.$value;
				}
			}
		}
		
		
		if (!array_key_exists('cm', $post) && $app->input->getCmd('task') != 'search_reset') {
			$params[] = 'cm=0';
		}
		
		$categoryId = $app->input->get( 'cid','0','string' );
		if (is_numeric($categoryId) && $categoryId > 0 && strstr($categoryId, ':') === false) {
			$query = $db->getQuery(true);
			$query->select('id, alias')->from('#__djc2_categories')->where('id='.(int)$categoryId);
			$db->setQuery($query);
			$category = $db->loadObject();
			if ($category) {
				$categoryId .= ':'.$category->alias;
			}
		}

		$producerId = $app->input->get( 'pid','0','string' );
		if (is_numeric($producerId) && $producerId > 0 && strstr($producerId, ':') === false) {
			$query = $db->getQuery(true);
			$query->select('id, alias')->from('#__djc2_producers')->where('id='.(int)$producerId);
			$db->setQuery($query);
			$producer = $db->loadObject();
			if ($producer) {
				$producerId .= ':'.$producer->alias;
			}
		}
		
		$menu = JFactory::getApplication('site')->getMenu('site');
		$uri = DJCatalogHelperRoute::getCategoryRoute( $categoryId, $producerId);
		if (strpos($uri,'?') === false ) {
			$get = (count($params)) ? '?'.implode('&',$params) : '';
		} else {
			$get = (count($params)) ? '&'.implode('&',$params) : '';
		}
		
		$cparams = JComponentHelper::getParams('com_djcatalog2');
		$jumpToSfx = $cparams->get('search_jump', true) ? '#tlb' : '';
		
		$app->redirect( JRoute::_($uri.$get, false).$jumpToSfx );
	}
	
	function producersearch() {
		$app = JFactory::getApplication();
		//$post = JRequest::get('post');
		$post = $app->input->getMethod() == 'POST' ? $app->input->getArray($_POST) : $app->input->getArray($_GET);
		$params = array();
		foreach($post as $key => $value) {
			if ($key != 'task' && $key != 'option' && $key != 'view' && $key != 'pid' && $key != 'cid' && $key != 'Itemid') {
				if ($key == 'search') {
					$params[] = $key.'='.urlencode($value);
				}
				else if (is_array($value)) {
					foreach ($value as $k => $v) {
						$params[] = $key.'[]='.$v;
					}
				}
				else {
					$params[] = $key.'='.$value;
				}
			}
		}
		
		$producer_id = $app->input->get( 'pid',null,'string' );
		if ((int)$producer_id == 0) {
			return $this->search();
		} else {
			if (strpos($producer_id, ':') === false) {
				$db = JFactory::getDbo();
				$db->setQuery('select alias from #__djc2_producers where id ='.(int)$producer_id);
				if ($alias = $db->loadResult()) {
					$producer_id = (int)$producer_id.':'.$alias;
				}
			}
		}
	
		$menu = JFactory::getApplication('site')->getMenu('site');
		$uri = DJCatalogHelperRoute::getProducerRoute( $producer_id);
		if (strpos($uri,'?') === false ) {
			$get = (count($params)) ? '?'.implode('&',$params) : '';
		} else {
			$get = (count($params)) ? '&'.implode('&',$params) : '';
		}
		
		$cparams = JComponentHelper::getParams('com_djcatalog2');
		$jumpToSfx = $cparams->get('search_jump', true) ? '#tlb' : '';
		
		$app->redirect( JRoute::_($uri.$get, false).$jumpToSfx );
	}
	
	function mapsearch() {
		$app = JFactory::getApplication();
		//$post = JRequest::get('post');
		$post = $app->input->getMethod() == 'POST' ? $app->input->getArray($_POST) : $app->input->getArray($_GET);
		$params = array();
		foreach($post as $key => $value) {
			if ($key != 'task' && $key != 'option' && $key != 'view' && $key != 'cid' && $key != 'pid' && $key != 'Itemid') {
				if ($key == 'search') {
					$params[] = $key.'='.urlencode($value);
				}
				else if (is_array($value)) {
					foreach ($value as $k => $v) {
						$params[] = $key.'[]='.$v;
					}
				}
				else {
					$params[] = $key.'='.$value;
				}
			}
		}
	
		$menu = JFactory::getApplication('site')->getMenu('site');
		//$uri = DJCatalogHelperRoute::getCategoryRoute( $app->input->get( 'cid','0','string' ), $app->input->get( 'pid',null,'string' ));
		$uri = 'index.php?option=com_djcatalog2&view=map';
		if ($app->input->get( 'cid', false) !== false) {
			$uri .= '&cid='.$app->input->getInt( 'cid', 0);
		}
		if ($app->input->get( 'pid', false) !== false) {
			$uri .= '&pid='.$app->input->getString( 'pid', '');
		}
		if ($app->input->get( 'Itemid', false) !== false) {
			$uri .= '&Itemid='.$app->input->getInt( 'Itemid', '');
		}
		
		if (strpos($uri,'?') === false ) {
			$get = (count($params)) ? '?'.implode('&',$params) : '';
		} else {
			$get = (count($params)) ? '&'.implode('&',$params) : '';
		}
		$cparams = JComponentHelper::getParams('com_djcatalog2');
		$jumpToSfx = $cparams->get('search_jump', true) ? '#tlb' : '';
		
		$app->redirect( JRoute::_($uri.$get, false).$jumpToSfx );
	}

	function download() {
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$db			= JFactory::getDbo();
		$file_id = 	$app->input->getInt('fid',0);
		
		$query = 'select i.created_by, f.access '.
				 'from #__djc2_items as i, '.
				 '#__djc2_files as f where f.item_id = i.id and f.id='.(int)$file_id
		;
		$db->setQuery($query);
		$file = $db->loadObject();
		$owner = $file->created_by;
		$access = $file->access;
		
		$groups	= $user->getAuthorisedViewLevels();

		$authorised = false;
		if ($user->id && $user->id == $owner) {
			$authorised = true;
		} else {
			if ($user->authorise('djcatalog2.filedownload', 'com_djcatalog2')) {
				$authorised = (bool)(in_array($access, $groups));
			}
		}
		
		if ($authorised !== true) {
			if ($user->guest) {
				$return = base64_encode(JUri::getInstance()->toString());
				$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return, false), JText::_('COM_DJCATALOG2_LOGIN_FIRST'));
				return true;
			} else {
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
				return false;
			}
		}

		if (!DJCatalog2FileHelper::getFile($file_id)){
			throw new Exception('', 404);
            return false;
        }
        
        // Close the application instead of returning from it.
        $app->close();
        //return true;
	}
	
	public function multiupload() {
		$app = JFactory::getApplication();
		// todo: secure upload from injections
		$user = JFactory::getUser();
		if (!$user->authorise('core.manage', 'com_djcatalog2') 
			&& !$user->authorise('core.create', 'com_djcatalog2') 
			&& !$user->authorise('core.edit', 'com_djcatalog2') 
			&& !$user->authorise('core.edit.own', 'com_djcatalog2')
			// not really protection but rather making sure that uploaded file is related to product customisation
			&& strpos($app->input->getString('upload_id'), 'customValues_') === false){
			$app = JFactory::getApplication();
			$app->setHeader('status', 403, true);
			$app->sendHeaders();
			
			echo '{"jsonrpc" : "2.0", "error" : {"code": 403, "message": "'.JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN').'"}}';
			
			$app->close();
			
		}
	
		DJCatalog2UploadHelper::upload();
	
		return true;
	}
	
	public function getStatesByCountry() {
		$app = JFactory::getApplication();
		$country = $app->input->getInt('country');
	
		$results = array();
	
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('cs.*')->from('#__djc2_countries_states AS cs')->where('cs.published=1')->order('cs.name asc');
	
		if ($country > 0) {
			$query->join('inner', '#__djc2_countries AS c ON c.id=cs.country_id AND c.published=1');
			$query->where('cs.country_id='.(int)$country);
		} else {
			$query->join('inner', '#__djc2_countries AS c ON c.id=cs.country_id AND c.is_default=1 AND c.published=1');
		}
	
		$db->setQuery($query);
		$results = $db->loadObjectList();
	
		echo json_encode($results);
		$app->close();
	}
	
	public function paymentProcess() {
		$app = JFactory::getApplication();
		$order_id = $app->input->getInt('oid');
		$plugin = $app->input->getString('plg');
		$plugin_id = $app->input->getInt('plgid');
		
		if (!$order_id) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_INVALID_REQUEST'), 400);
		}
		
		if (!$plugin || !$plugin_id) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_INVALID_REQUEST'), 400);
		}
		
		$model = JModelLegacy::getInstance('Order', 'DJCatalog2Model', array('ignore_request' => true));
		$order = $model->getItem($order_id);
		$paymentMethod = $model->getPaymentMethod($plugin_id);
		
		if ($order == false || empty($order) || empty($order->id) || $order->payment_method_id != $paymentMethod->id) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_ORDER_NOT_FOUND'), 404);
		}
		
		JPluginHelper::importPlugin('djcatalog2payment');
		$dispatcher = JEventDispatcher::getInstance();

		// payment processing 
		$paymentResponse = $dispatcher->trigger('onDJC2PaymentProcess', array('com_djcatalog2.order.payment', $order, $paymentMethod));
		
		$app->close();
	}
	
	public function paymentResponse() {
		$app = JFactory::getApplication();
		$plugin = $app->input->getString('plg');
		$plugin_id = $app->input->getInt('plgid');
		
		if (!$plugin || !$plugin_id) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_INVALID_REQUEST'), 400);
		}
		
		JPluginHelper::importPlugin('djcatalog2payment');
		$dispatcher = JEventDispatcher::getInstance();
		
		$model = JModelLegacy::getInstance('Order', 'DJCatalog2Model', array('ignore_request' => true));
		$paymentMethod = $model->getPaymentMethod($plugin_id);
		
		if (!$paymentMethod || $paymentMethod->plugin != $plugin) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_INVALID_REQUEST'), 400);
		}
		
		$order_id = false;
		
		// this should go through all the plugins and return order id
		$paymentDiscovery =  $dispatcher->trigger('onDJC2PaymentDiscover', array('com_djcatalog2.order.payment', $paymentMethod));
		foreach ($paymentDiscovery as $result) {
			if (!empty($result) && is_numeric($result)) {
				$order_id = (int)$result;
				break;
			}
		}

		if (!$order_id) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_ORDER_NOT_FOUND'), 404);
		}
		
		$order = $model->getItem($order_id);
		
		if ($order == false || empty($order) || empty($order->id) || $order->payment_method_id != $paymentMethod->id) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_ORDER_NOT_FOUND'), 404);
		}
		
		// payment response validation
		$paymentResponse = $dispatcher->trigger('onDJC2PaymentResponse', array('com_djcatalog2.order.payment', $order, $model, $paymentMethod));
		
		$app->close();
	}
	
	public function getUserData() {
		$app = JFactory::getApplication();
		$juser = JFactory::getUser();
		$salesman = $juser->authorise('djcatalog2.salesman', 'com_djcatalog2') || $juser->authorise('core.admin', 'com_djcatalog2');
		
		if (!$salesman) {
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}
		
		$user_id = $app->input->getInt('user_id');
		if (!$user_id) {
			throw new Exception(JText::_('COM_DJCATALOG2_ERROR_INVALID_REQUEST'), 400);
		}
		
		$user = Djcatalog2Helper::getUser($user_id);
		echo json_encode($user);
		$app->close();
		
	}
	
	public function user_login() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app    = JFactory::getApplication();
		$input  = $app->input;
		$method = $input->getMethod();
		
		// Populate the data array:
		$data = array();
		
		$data['return']    = base64_decode($app->input->post->get('return', '', 'BASE64'));
		$data['username']  = $input->$method->get('username', '', 'USERNAME');
		$data['password']  = $input->$method->get('password', '', 'RAW');
		$data['secretkey'] = $input->$method->get('secretkey', '', 'RAW');
		
		// Check for a simple menu item id
		if (is_numeric($data['return']))
		{
			if (JLanguageMultilang::isEnabled())
			{
		
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
				->select('language')
				->from($db->quoteName('#__menu'))
				->where('client_id = 0')
				->where('id =' . $data['return']);
		
				$db->setQuery($query);
		
				try
				{
					$language = $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					return;
				}
		
				if ($language !== '*')
				{
					$lang = '&lang=' . $language;
				}
				else
				{
					$lang = '';
				}
			}
			else
			{
				$lang = '';
			}
		
			$data['return'] = 'index.php?Itemid=' . $data['return'] . $lang;
		}
		else
		{
			// Don't redirect to an external URL.
			if (!JUri::isInternal($data['return']))
			{
				$data['return'] = '';
			}
		}
		
		// Set the return URL if empty.
		if (empty($data['return']))
		{
			$data['return'] = DJCatalog2HelperRoute::getCartRoute().'&layout=login';
		}
		
		// Set the return URL in the user state to allow modification by plugins
		$app->setUserState('users.login.form.return', $data['return']);
		
		// Get the log in options.
		$options = array();
		$options['remember'] = $this->input->getBool('remember', false);
		$options['return']   = $data['return'];
		
		// Get the log in credentials.
		$credentials = array();
		$credentials['username']  = $data['username'];
		$credentials['password']  = $data['password'];
		$credentials['secretkey'] = $data['secretkey'];
		
		// Perform the log in.
		if (true !== $app->login($credentials, $options))
		{
			// Login failed !
			// Clear user name, password and secret key before sending the login form back to the user.
			$data['remember'] = (int) $options['remember'];
			$data['username'] = '';
			$data['password'] = '';
			$data['secretkey'] = '';
			$app->setUserState('users.login.form.data', $data);
			$app->redirect(JRoute::_(DJCatalog2HelperRoute::getCartRoute().'&layout=login', false));
		}
		
		// Success
		if ($options['remember'] == true)
		{
			$app->setUserState('rememberLogin', true);
		}
		
		$app->setUserState('users.login.form.data', array());
		$app->redirect(JRoute::_($app->getUserState('users.login.form.return'), false));
	}
	
	public function user_register() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();
		$app->setUserState('com_djcatalog2.cart_registration', true);
		
		$link = 'index.php?option=com_users&view=registration&from_cart=1';
		
		$this->setRedirect(JRoute::_($link, false));
		return true; 
	}
	
	public function getFilterModules() {
		$app = JFactory::getApplication();
		
		$moduleIds = $app->input->get('module_id', array(), 'array');
		
		JArrayHelper::toInteger($moduleIds);
		
		if (empty($moduleIds)) {
			$app->close();
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__modules')->where('client_id=0')->where('id IN ('.implode(',', $moduleIds).')');
		$db->setQuery($query);
		
		$modules = $db->loadObjectList('id');
		
		if (empty($modules)) {
			$app->close();
		}
		
		JUri::getInstance()->setVar('task', null);
		$app->input->set('task', null);
		
		$output = '';
		foreach($modules as $module) {
			$output .= '<div data-filtersmodule="'.$module->id.'">' . JModuleHelper::renderModule($module) . '</div>';
		}
		
		echo '<div>'.$output.'</div>';
		$app->close();
		
		/*require_once (JPATH_BASE.DS.'modules'.DS.'mod_djc2filters'.DS.'helper.php');
		
		$lang = JFactory::getLanguage();
		
		$lang->load('mod_djc2filters', JPATH_BASE, null, false, true) ||
		$lang->load('mod_djc2filters', JPATH_BASE.'/modules/mod_djc2filters', null, false, true);
		
		$output = array();
		foreach($modules as $module) {
			$params = new Registry($module->params);
			$items = DJC2FiltersModuleHelper::getData($params);
			
			ob_start();
			require JModuleHelper::getLayoutPath('mod_djc2filters', $params->get('layout', 'default'));
			$output[] = ob_get_contents();
			ob_end_clean();
		}
		
		echo json_encode($output);
		*/
		$app->close();
	}
}
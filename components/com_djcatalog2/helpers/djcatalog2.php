<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_djcatalog2'.DS.'lib'.DS.'categories.php');

class Djcatalog2Helper {
	static $params = null;
	static $users = array();
	static $recentItems = array();
	static $languageLoaded = false;
	
	public static function getParams($reload = false) {
		if (!self::$params || $reload == true) {
			$app		= JFactory::getApplication();
			
			// our params
			$params = new Registry();
			
			// component's global params
			$cparams = JComponentHelper::getParams( 'com_djcatalog2' );
			
			// current params - all
			$aparams = $app->getParams();
			
			// curent params - djc2 only
			$mparams = $app->getParams('com_djcatalog2'); 
			
			// first let's use all current params
			$params->merge($aparams);
			
			// then override them with djc2 global settings - in case some other extension share's the same parameter name
			$params->merge($cparams);
			
			if ($app->input->getCmd('option') == 'com_djcatalog2') {
				// finally, override settings with current params, but only related to djc2.
				$params->merge($mparams);
			}
			
			// ...and then, override with category specific params
			$option = $app->input->get('option');
			$view = $app->input->get('view');

			if ($option = 'com_djcatalog2' && ($view = 'item' || $view = 'items' || $view = 'archived')) {
				
				$user	= JFactory::getUser();
				$groups	= $user->getAuthorisedViewLevels();
				
				$categories = Djc2Categories::getInstance(array('state' => '1', 'access' => $groups));
				$category = $categories->get((int) $app->input->get('cid',0,'int'));
				if (!empty($category)) {
					$catpath = array_reverse($category->getPath());
					foreach($catpath as $k=>$v) {
						$parentCat = $categories->get((int)$v);
						if (!empty($parentCat) && !empty($category->params)) {
							$catparams = new Registry($parentCat->params); 
							$params->merge($catparams);
						}
					}
				}
			}
			
			$listLayout = $app->input->get('l', $app->getUserState('com_djcatalog2.list_layout', null), 'cmd');
			if ($listLayout == 'items') {
				$app->setUserState('com_djcatalog2.list_layout', 'items');
				$params->set('list_layout', 'items');
			} else if ($listLayout == 'table') {
				$app->setUserState('com_djcatalog2.list_layout', 'table');
				$params->set('list_layout', 'table');
			}
			
			$catalogMode = $app->input->get('cm', null, 'int');
			$indexSearch = $app->input->get('ind', null, 'string');
			
			$globalSearch = urldecode($app->input->get( 'search','','string' ));
			$globalSearch = trim(JString::strtolower( $globalSearch ));
			if (substr($globalSearch,0,1) == '"' && substr($globalSearch, -1) == '"') { 
				$globalSearch = substr($globalSearch,1,-1);
			}
			if (strlen($globalSearch) > 0 && (strlen($globalSearch)) < 3 || strlen($globalSearch) > 20) {
				 $globalSearch = null;
			}
			if ($catalogMode === 0 || $globalSearch || $indexSearch) {
				$params->set('product_catalogue','0');
				// set 'filtering' variable in REQUEST
				// so we could hide for example sub-categories 
				// when searching/filtering is performed
				$app->input->set('filtering', true);
			}
			
			self::$params = $params;
		}
		return self::$params;
	}
	
	public static function loadComponentLanguage() {
		if (!self::$languageLoaded /*&& JFactory::getApplication()->input->getCmd('option') != 'com_djcatalog2'*/) {
			
			$lang = JFactory::getLanguage();
			
			if ($lang->getTag() != 'en-GB') {
				$lang->load('com_djcatalog2', JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djcatalog2'), 'en-GB', false, false);
				$lang->load('com_djcatalog2', JPATH_ADMINISTRATOR, 'en-GB', false, false);
				$lang->load('com_djcatalog2', JPath::clean(JPATH_ROOT.'/components/com_djcatalog2'), 'en-GB', false, false);
				$lang->load('com_djcatalog2', JPATH_ROOT, 'en-GB', false, false);
			}
			
			$lang->load('com_djcatalog2', JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djcatalog2'), null, true, false);
			$lang->load('com_djcatalog2', JPATH_ADMINISTRATOR, null, true, false);
			$lang->load('com_djcatalog2', JPath::clean(JPATH_ROOT.'/components/com_djcatalog2'), null, true, false);
			$lang->load('com_djcatalog2', JPATH_ROOT, null, true, false);
			
			self::$languageLoaded = true;
		}
	}
	
	public static function getUser($id = null) {
		$app = JFactory::getApplication();
		if ($id == null) {
			$juser = JFactory::getUser();
			$salesman = $juser->authorise('djcatalog2.salesman', 'com_djcatalog2');
			
			$id = ($salesman) ? $app->getUserState('com_djcatalog2.checkout.user_id', JFactory::getUser()->id) : JFactory::getUser()->id;
		}
		if (isset(self::$users[$id])) {
			return self::$users[$id];
		}
	
		$model_path = str_replace('/', DIRECTORY_SEPARATOR, '/components/com_users/models/profile.php');
		$route_path = str_replace('/', DIRECTORY_SEPARATOR, '/components/com_users/helpers/route.php');
		require_once JPATH_ROOT.$model_path;
		require_once JPATH_ROOT.$route_path;
	
		$user_model = JModelLegacy::getInstance('Profile', 'UsersModel', array('ignore_request'=>true));
		$user_model->setState('user.id', $id);
	
		$userData = $user_model->getData();
		
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('user');
		
		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array('com_users.profile', $userData));

		$db = JFactory::getDbo();
	
		$data = new stdClass();
		if (!empty($userData->djcatalog2profile)) {
			$data = $userData->djcatalog2profile;
			$data = JArrayHelper::toObject($data);
		}
	
		if (!isset($data->user_id)) {
			$data->user_id = $userData->id;
		}
		
		if (!isset($data->email)) {
			$data->email = $userData->email;
		}
	
		// define customer group
		if (!isset($data->customer_group_id)) {
			$data->customer_group_id = 0;
		}
		
		if (!isset($data->vat_id)) {
			$data->vat_id = '';
		}
	
		// define client type
		$params = JComponentHelper::getParams('com_djcatalog2');
		if (!isset($data->client_type) || ($data->client_type != 'R' && $data->client_type != 'W')) {
			$data->client_type = $params->get('default_client_type', 'R');
		}
		
		$task = $app->input->post->getCmd('task');
		
		$postData = $app->input->get('jform', array(), 'array');
		$postOrderData = (array)$app->getUserState('com_djcatalog2.order.data', array());
		
		/*if ($task == 'cart.confirm') {
			if (!empty($postData) && isset($postData['djcatalog2profile'])) {
				if (isset($postData['djcatalog2profile']['vat_id'])) {
					$data->vat_id = $postData['djcatalog2profile']['vat_id'];
				}
				if (isset($postData['djcatalog2profile']['country_id'])) {
					$data->country_id = $postData['djcatalog2profile']['country_id'];
				}
			}
		} else*/ if (isset($postOrderData['djcatalog2profile'])) {
			if (isset($postOrderData['djcatalog2profile']['vat_id'])) {
				$data->vat_id = $postOrderData['djcatalog2profile']['vat_id'];
			}
			if (isset($postOrderData['djcatalog2profile']['country_id'])) {
				$data->country_id = $postOrderData['djcatalog2profile']['country_id'];
			}
		}

		$country = false;
		
		if (!empty($data->country_id)) {
			$db->setQuery('select * from #__djc2_countries where id='.(int)$data->country_id);
			$country = $db->loadObject();
		}
		
		if (empty($country)) {
			$db->setQuery('select * from #__djc2_countries where is_default=1');
			$country = $db->loadObject();
			if ($country) {
				$data->country_id = $country->id;
				$data->country_name = $country->country_name;
				$data->country_3_code = $country->country_3_code;
				$data->country_2_code = $country->country_2_code;
				$data->country_eu = $country->is_eu;
			} else {
				$data->country_id = 0;
				$data->country_name = '*';
				$data->country_3_code = '';
				$data->country_2_code = '';
				$data->country_eu = false;
			}
		} else {
			$data->country_name = $country->country_name;
			$data->country_3_code = $country->country_3_code;
			$data->country_2_code = $country->country_2_code;
			$data->country_eu = $country->is_eu;
		}
		
		if ($data->vat_id && $data->country_eu) {
			$data->client_type = (self::isViesValid($data->vat_id, $data->country_2_code)) ? 'W' : 'R';
		}
		
		// define tax rules
		$tax_query = $db->getQuery(true);
		$tax_query->select('vrul.id as rule_id, vrul.name, vrat.*');
		$tax_query->from('#__djc2_vat_rules AS vrul');
		$tax_query->join('inner', '#__djc2_vat_rules_xref AS vx ON vx.rule_id = vrul.id');
        $tax_query->join('inner', '#__djc2_vat_rates AS vrat ON vx.rate_id = vrat.id');
	
		$tax_where = array();
	
        if ((int)$data->country_id > 0) {
        	//$tax_where[] = '(vrat.country_id='.(int)$data->country_id.' OR vrat.country_id=0)';
        	$tax_where[] = 'vrat.country_id='.(int)$data->country_id;
        } else {
            $tax_where[] = 'vrat.country_id=0';
        }
		$tax_where[] = '(vrat.client_type='.$db->quote('A').' OR vrat.client_type='.$db->quote($data->client_type).')';
	
		$tax_query->where($tax_where);
	
		$tax_query->order('vrat.client_type ASC, vrat.country_id ASC');
		$db->setQuery($tax_query);
	
		$rules = $db->loadObjectList();
        
		$data->tax_rules = array();
		foreach($rules as $rule) {
			if (!isset($data->tax_rules[$rule->rule_id])) {
				$data->tax_rules[$rule->rule_id] = 0;
			}
			$data->tax_rules[$rule->rule_id] = $rule->value;
		}
	
		$userData->djcatalog2profile = $data;
		
		self::$users[$id] = $userData;
		return self::$users[$id] ;
	}
	
	public static function getSecureToken($forceNew = false) {
		$app = JFactory::getApplication();
		$token = $app->getUserState('com_djcatalog2.secure_token', $app->input->cookie->get('djc2token', false));
		
		if ($token == false || $forceNew) {
			$token = static::createSecureToken();
		}
		
		$app->setUserState('com_djcatalog2.secure_token', $token);
		$app->input->cookie->set('djc2token', $token, (time() + (86400)), $app->get('cookie_path', '/'), $app->get('cookie_domain', ''));
		
		return $token;
	}
	
	public static function createSecureToken() {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$token = JApplicationHelper::getHash((int)$user->id . JUserHelper::genRandomPassword(8));
		
		return $token;
	}
	
	public static function isViesValid( $vatId, $countryCode )
	{
		$vatId = trim($vatId);
		$app = JFactory::getApplication();
		
		$codeAssoc = array(
			'AT' => 'AT',
			'BE' => 'BE',
			'BG' => 'BG',
			'HR' => 'HR',
			'CY' => 'CY',
			'CZ' => 'CZ',
			'DK' => 'DK',
			'EE' => 'EE',
			'FI' => 'FI',
			'FR' => 'FR',
			'DE' => 'DE',
			'GR' => 'EL',
			'HU' => 'HU',
			'IE' => 'IE',
			'IT' => 'IT',
			'LV' => 'LV',
			'LT' => 'LT',
			'LU' => 'LU',
			'MT' => 'MT',
			'NL' => 'NL',
			'PL' => 'PL',
			'PT' => 'PT',
			'RO' => 'RO',
			'SK' => 'SK',
			'SI' => 'SI',
			'ES' => 'ES',
			'SE' => 'SE',
			'GB' => 'GB'
		);
		
		$number = substr($vatId, 2);
		$country = $countryCode; //substr($vatId, 0, 2);
		if (isset($codeAssoc[$country])) {
			$country = $codeAssoc[$country];
		}
		
		$vatId = trim($country).$number;
		$hash = md5($countryCode.':'.$vatId);
		
		if ($app->getUserState('com_djcatalog2.vies_valid.'.$hash) == '-1') {
			return false;
		} else if ($app->getUserState('com_djcatalog2.vies_valid.'.$hash)) {
			return true;
		}
		
		$response = false;
		$soapOpts = array('connection_timeout' => 5);
		try {
			$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", $soapOpts);
			$response = $client->checkVat(array(
				'countryCode' => $country,
				'vatNumber' => $number
			));
		} catch(Exception $e) {
			sleep(2);
			try {
				$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", $soapOpts);
				$response = $client->checkVat(array(
					'countryCode' => $country,
					'vatNumber' => $number
				));
			} catch(Exception $e) {
				//$app->setUserState('com_djcatalog2.vies_valid.'.$vatId, '-1');
				$app->setUserState('com_djcatalog2.vies_valid.'.$hash, '-1');
				return false;
				//return $e->getMessage();
			}
		}
		if (empty($response)) {
			//$app->setUserState('com_djcatalog2.vies_valid.'.$vatId, null);
			$app->setUserState('com_djcatalog2.vies_valid.'.$hash, null);
			return null;
		}
		
		if (isset($response->valid)) {
			//$app->setUserState('com_djcatalog2.vies_valid.'.$vatId, (int)$response->valid);
			$app->setUserState('com_djcatalog2.vies_valid.'.$hash, (int)$response->valid);
			return (int)$response->valid;
		} else {
			//$app->setUserState('com_djcatalog2.vies_valid.'.$vatId, '-1');
			$app->setUserState('com_djcatalog2.vies_valid.'.$hash, '-1');
		}
		
		return false;
	}
	
	public static function getUserProfile($id = null) {
		$user = self::getUser($id);
		$data = array();
	
		if (!empty($user->djcatalog2profile)) {
			$data = $user->djcatalog2profile;
		}
	
		return $data;
	}
	
	public static function isDefaultLanguage() {
		$lang = JFactory::getLanguage();
		$default = JComponentHelper::getParams('com_languages')->get('site', $lang->getDefault());
		if (JString::strcmp($default, $lang->getTag()) === 0) {
			return true;
		}
		return false;
	}
	public static function getLangId(){
		$lang = JFactory::getLanguage();
		$db = JFactory::getDbo();
		$db->setQuery('select lang_id from #__languages where lang_code='.$db->quote($lang->getTag()));
	
		return $db->loadResult();
	}
	public static function isFalang() {
		return (bool)class_exists('plgSystemFalangdriver');
	}
	public static function getRecentItems() {
		if (empty(self::$recentItems)) {
			$app = JFactory::getApplication();
			$sessionItems = $app->getUserState('com_djcatalog2.items.recent', array());
			self::$recentItems = $sessionItems;
		}
		return self::$recentItems;
	}
	public static function pushRecentItem($id) {
		$app = JFactory::getApplication();
		$sessionItems = $app->getUserState('com_djcatalog2.items.recent', array());
		$sessionItems[] = (int)$id;
		self::$recentItems = array_values(array_unique($sessionItems));
		$app->setUserState('com_djcatalog2.items.recent', self::$recentItems);
		
		return true;
	}
	
	public static function getVendors($user_id = null) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT u.*, c.customer_id');
		$query->from('#__users AS u');
		$query->join('inner', '#__djc2_vendors AS v ON v.user_id=u.id');
		$query->join('left', '#__djc2_vendors_customers AS c ON c.vendor_id = v.id');
		if ($user_id) {
			$query->where('(c.customer_id IS NULL OR c.customer_id = '.(int)$user_id.')');
		} else {
			$query->where('c.customer_id IS NULL');
		}
		
		$db->setQuery($query);
		$vendors = $db->loadObjectList();
		
		if (empty($vendors)) {
			return array();
		}
		
		if (!$user_id) {
			return $vendors;
		}
		
		$general = array();
		$userAssigned = array();
		
		foreach($vendors as $vendor){
			if ($vendor->customer_id == $user_id) {
				$userAssigned[] = $vendor;
			} else {
				$general[] = $vendor;
			}
		}
		
		return (count($userAssigned) > 0) ? $userAssigned : $general;
	}
}
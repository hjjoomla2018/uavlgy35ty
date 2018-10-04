<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.utilities.date');

class plgUserDjcatalog2 extends JPlugin
{
	public static $component_language_loaded = false;
	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		
		/*$lang = JFactory::getLanguage();
		if (self::$component_language_loaded == false) {
			$lang = JFactory::getLanguage();
			if (JFactory::getApplication()->isSite()) {
				$lang->load('com_djcatalog2', JPATH_ROOT, 'en-GB', false, false);
				$lang->load('com_djcatalog2', JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2', 'en-GB', false, false);
				$lang->load('com_djcatalog2', JPATH_ROOT, null, true, false);
				$lang->load('com_djcatalog2', JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2', null, true, false);
			}
			else {
				$lang->load('com_djcatalog2', JPATH_ADMINISTRATOR, 'en-GB', false, false);
				$lang->load('com_djcatalog2', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2', 'en-GB', false, false);
				$lang->load('com_djcatalog2', JPATH_ADMINISTRATOR, null, true, false);
				$lang->load('com_djcatalog2', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2', null, true, false);
			}
			self::$component_language_loaded = true;
		}*/
		
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2'.DIRECTORY_SEPARATOR.'defines.djcatalog2.php');
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'route.php');
		require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'djcatalog2.php');
		
		Djcatalog2Helper::loadComponentLanguage();
		
	}
	
	function onContentPrepareData($context, $data)
	{
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}
		
		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->djcatalog2profile) and $userId > 0)
			{
				// Load the profile data from the database.
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('u.*, c.country_name, c.country_3_code, c.country_2_code, s.name as state_name');
				$query->from('#__djc2_users AS u');
				$query->join('left', '#__djc2_countries as c on c.id = u.country_id');
				$query->join('left', '#__djc2_countries_states as s on s.id = u.state_id');
				$query->where('user_id = '.(int) $userId);
				$db->setQuery($query);
				$results = $db->loadAssoc();
				
				// Check for a database error.
				if ($db->getErrorNum())
				{
					$this->_subject->setError($db->getErrorMsg());
					return false;
				}
				
				if (count($results)) {
					// Merge the profile data.
					$data->djcatalog2profile = array();
					$data->djcatalog2statements = array();
					foreach ($results as $k=>$v)
					{
						if ($k == 'gdpr_policy' || $k == 'gdpr_agreement' || $k == 'tos') {
							$data->djcatalog2statements[$k] = $v;
						} else {
							$data->djcatalog2profile[$k] = $v;
						}
					}	
					
				}
			}
		}
		
		if (!JHtml::isRegistered('users.country_id'))
		{
			
			JHtml::register('users.country_id', array(__CLASS__, 'country'));
		}
		if (!JHtml::isRegistered('users.state_id'))
		{
			JHtml::register('users.state_id', array(__CLASS__, 'country_state'));
		}
		
		if (!JHtml::isRegistered('users.www'))
		{
			JHtml::register('users.www', array(__CLASS__, 'www'));
		}
		
		if (!JHtml::isRegistered('users.user_id'))
		{
			JHtml::register('users.user_id', array(__CLASS__, 'user_id'));
		}
		if (!JHtml::isRegistered('users.gdpr_agreement'))
		{
			JHtml::register('users.gdpr_agreement', array(__CLASS__, 'yes_no_val'));
			JHtml::register('users.gdpr_policy', array(__CLASS__, 'yes_no_val'));
		}
		if (!JHtml::isRegistered('users.tos'))
		{
			JHtml::register('users.tos', array(__CLASS__, 'yes_no_val'));
		}

		return true;
	}

	function onContentPrepareForm($form, $data)
	{
		$app = JFactory::getApplication();
		
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}
		
		if (JFactory::getUser()->get('requireReset', 0)) {
			return true;
		}
		
		$params = $this->params;
		
		/*if ($app->isSite() && $form->getXML()  instanceof SimpleXMLElement) {
			$form->setFieldAttribute('name', 'type', 'hidden');
			$form->setFieldAttribute('name', 'required', null);
			$form->setFieldAttribute('name', 'validate', null);
			$form->setFieldAttribute('name', 'value', '');
			$form->setFieldAttribute('name', 'default', '');
			
			$document = JFactory::getDocument();
			$document->addScript(JURI::base() . "plugins/user/djcatalog2/forms/js/username.js");
			$document->addScriptDeclaration('
						window.addEvent("domready", function(){
							plguserdjc2.init();
						});
					');
			
		}*/

		// Add the registration fields to the form.
		JForm::addFormPath(JPath::clean(JPATH_ROOT.'/components/com_djcatalog2/models/forms'));

		$form->loadFile('userprofile', true);
		// ADMIN should not be required to fill-in all the fields
		$form->removeField('email', 'djcatalog2profile');
		
		$agreement_info = JText::sprintf('COM_DJCATALOG2_GDPR_AGREE', $app->get('sitename'));
		if (trim($params->get('gdpr_agreement_info')) != '') {
			$agreement_info = $params->get('gdpr_agreement_info');
		}
		$form->setFieldAttribute('gdpr_agreement', 'label', $agreement_info, 'djcatalog2statements');
		
		$agreement_info = JText::sprintf('COM_DJCATALOG2_GDPR_POLICY_AGREE', $app->get('sitename'));
		if (trim($params->get('gdpr_policy_info')) != '') {
			$agreement_info = $params->get('gdpr_policy_info');
		}
		$form->setFieldAttribute('gdpr_policy', 'label', $agreement_info, 'djcatalog2statements');
		
		$tos_link = $params->get('tos_link', '');
		$tos_link = JUri::isInternal($tos_link) ? JRoute::_($tos_link) : $tos_link;
		if ($tos_link) {
			$form->setFieldAttribute('tos', 'label', JText::sprintf('COM_DJCATALOG2_TOS_WITH_LINK', $tos_link), 'djcatalog2statements');
		}
		
		if ($app->isAdmin()) {
			$catalog_fields = $form->getFieldset('basicprofile');
			foreach ($catalog_fields as $field) {
				$form->setFieldAttribute($field->fieldname, 'required', null, 'djcatalog2profile');
				$class = $form->getFieldAttribute($field->fieldname, 'class', '', 'djcatalog2profile');
				$class = str_replace('required', '', $class);
				$form->setFieldAttribute($field->fieldname, 'class', $class, 'djcatalog2profile');
			}
			
			$statement_fields = $form->getFieldset('additional');
			foreach ($statement_fields as $field) {
				$form->setFieldAttribute($field->fieldname, 'required', null, 'djcatalog2statements');
				$class = $form->getFieldAttribute($field->fieldname, 'class', '', 'djcatalog2statements');
				$class = str_replace('required', '', $class);
				$form->setFieldAttribute($field->fieldname, 'class', $class, 'djcatalog2statements');
			}
		} else {
			$app = JFactory::getApplication();
			
			$form->removeField('customer_group_id', 'djcatalog2profile');
			$form->removeField('client_type', 'djcatalog2profile');
			
			$fields = array();
			
			$fields['djcatalog2profile'] = array('firstname', 'lastname', 'company', 'position', 'address', 'city', 'postcode', 'country_id', 'state_id', 'vat_id', 'phone', 'fax', 'www', 'customer_note');
			$fields['djcatalog2statements'] = array('tos', 'gdpr_policy', 'gdpr_agreement');
			
			foreach($fields as $group => $groupFields) {
				foreach($groupFields as $field) {
					if ($params->get('field_'.$field, false) === false) {
						continue;
					}
					if ($params->get('field_'.$field, '0') == '0') {
						$form->removeField($field, $group);
					} else {
						if ($params->get('field_'.$field, '0') == '2') {
							$form->setFieldAttribute($field, 'required', 'true', $group);
							$form->setFieldAttribute($field, 'class', $form->getFieldAttribute($field, 'class').' required', $group);
						} else {
							$form->setFieldAttribute($field, 'required', false, $group);
							
							$class = $form->getFieldAttribute($field, 'class', '', $group);
							$class = str_replace('required', '', $class);
							
							$form->setFieldAttribute($field, 'class', $class, $group);
						}
					}
				}
			}
		}
		
		$user = JFactory::getUser();
		$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2');
		
		//if (!$salesman || $name != 'com_users.profile' || $app->input->getCmd('layout', false)) {
			$form->removeField('user_id', 'djcatalog2profile');
		//}
		
		$form->removeGroup('djcatalog2delivery');
		$form->removeGroup('djcatalog2message');
		$form->removeGroup('djcatalog2captcha');
		$form->removeGroup('djcatalog2orderdetails');
		
		return true;
	}

	function onUserBeforeSave($oldData, $isNew, $data)
	{
		/*if (isset($data['djcatalog2profile']) && (count($data['djcatalog2profile'])))
		{
			if ($data->name == '---' || empty($data->name)) {
				$data->name = $data['djcatalog2profile']['firstname'].' '.$data['djcatalog2profile']['lastname'];
			}
		}*/
		//return false;
	}
	
	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');
		$user = JFactory::getUser($userId);
		$date = JFactory::getDate();
		$params = JComponentHelper::getParams('com_djcatalog2');
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$files = $app->input->files->get('jform', array());
		

		$company_name = '';
		
		/*$image_whitelist = explode(',', $image_types);
		foreach($image_whitelist as $key => $extension) {
			$image_whitelist[$key] = strtolower(trim($extension));
		}*/
		
		if ($userId && $result) {
			if (isset($data['djcatalog2profile']) && (count($data['djcatalog2profile'])))
			{
				try
				{
					
					$row = new stdClass();
					$data['djcatalog2profile']['user_id'] = $userId;
					foreach($data['djcatalog2profile'] as $column => $value) {
						$row->$column = $value;
					}
					
					$gdpr_policy = 0;
					if (isset($data['djcatalog2statements']) && !empty($data['djcatalog2statements']['gdpr_policy'])) {
						$gdpr_policy = 1;
					}
					
					$gdpr_agreement = 0;
					if (isset($data['djcatalog2statements']) && !empty($data['djcatalog2statements']['gdpr_agreement'])) {
						$gdpr_agreement = 1;
					}
					
					$tos_agreement = 0;
					if (isset($data['djcatalog2statements']) && !empty($data['djcatalog2statements']['tos'])) {
						$tos_agreement = 1;
					}
					
					$isempty = true;
					foreach ($row as $value) {
						if (!empty($value)) {
							$isempty = false;
							break;
						}
					}
					if ($isempty && $app->isAdmin()) {
						$this->onUserAfterDelete($user->getProperties(1), true, '');
					} else {
						$db->setQuery('SELECT * FROM #__djc2_users WHERE user_id = '.$userId);
						if ($djuser = $db->loadObject()) {
							$row->id = $djuser->id;
							if ($app->isSite()) {
								$row->customer_group_id = $djuser->customer_group_id;
								$row->client_type = $djuser->client_type;
								
								$row->gdpr_agreement = $djuser->gdpr_agreement ? $djuser->gdpr_agreement : $gdpr_agreement;
								$row->gdpr_policy = $djuser->gdpr_policy ? $djuser->gdpr_policy : $gdpr_policy;
								$row->tos = $djuser->tos ? $djuser->tos : $tos_agreement;
							} else {
								$row->gdpr_policy = $gdpr_policy;
								$row->gdpr_agreement = $gdpr_agreement;
								$row->tos = $tos_agreement;
							}
						} else {
							$db->setQuery('SELECT id FROM #__djc2_customer_groups WHERE is_default=1');
							$group_id = $db->loadResult();
							$row->customer_group_id = $group_id ? $group_id : 0;
							
							$client_type = $params->get('default_client_type', 'R');
							$row->client_type = ($client_type == 'R' || $client_type == 'W') ? $client_type : 'R';
							
							$row->gdpr_policy = $gdpr_policy;
							$row->gdpr_agreement = $gdpr_agreement;
							$row->tos = $tos_agreement;
						}
						
						$row->modified = $date->toSql();
						
						if ($row->id > 0) {
							if (!$db->updateObject('#__djc2_users', $row, 'id', true))
							{
								throw new Exception($db->getErrorMsg());
							}
						} else {
							if (!$db->insertObject('#__djc2_users', $row))
							{
								throw new Exception($db->getErrorMsg());
							}
						}				
						
						 
						if (($user->name == '---' || empty($user->name)) && !empty($row->firstname) && !empty($row->firstname)) {
							$new_name = $row->firstname.' '.$row->lastname;
							$user->set('name', $new_name);
							$db->setQuery('update #__users set name='.$db->quote($new_name).' where id='.(int)$userId);
							$db->query();
						}
						$company_name = $row->company;
					}
				}
				catch (JException $e)
				{
					$this->_subject->setError($e->getMessage());
					return false;
				}
			}
		}
		
		/*if ($app->isSite() && $isNew == false && $app->input->get('task') == 'save' && $app->input->get('option') == 'com_users') {
			$app->setUserState('com_users.edit.profile.redirect', JRoute::_(DJCatalogHelperRoute::getMyItemsRoute(), false));
		}*/
		
		$cart_registration = $app->getUserState('com_djcatalog2.cart_registration');
		$users_params = JComponentHelper::getParams('com_users');
		if ($app->isSite() && $isNew && $result && $cart_registration && $users_params->get('useractivation') == 0) {
			$credentials = array();
			$credentials['username'] = $data['username'];
			$credentials['password'] = $data['password1'];
			
			$options = array();
			$options['remember'] = true;
			
			$language 	= JFactory::getLanguage();
			$base_dir 	= JPATH_SITE . '/language';
			if(!$language->load('com_users', $base_dir, $language->getTag(), true))
			{
				$language->load('com_users', $base_dir, 'en-GB', true);
			}
			
			$sendpassword = $users_params->get('sendpassword', 1);
			
			$config = JFactory::getConfig();
			
			$user_data = $data;
			$user_data['fromname'] = $config->get('fromname');
			$user_data['mailfrom'] = $config->get('mailfrom');
			$user_data['sitename'] = $config->get('sitename');
			$user_data['siteurl'] = JUri::root();
			
			$emailSubject = JText::sprintf(
					'COM_USERS_EMAIL_ACCOUNT_DETAILS',
					$user_data['name'],
					$user_data['sitename']
					);
			
			$emailBody = '';
			if ($sendpassword)
			{
				$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_BODY',
						$user_data['name'],
						$user_data['sitename'],
						$user_data['siteurl'],
						$user_data['username'],
						$user_data['password_clear']
						);
			}
			else
			{
				$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_BODY_NOPW',
						$user_data['name'],
						$user_data['sitename'],
						$user_data['siteurl']
						);
			}
			
			JFactory::getMailer()->sendMail($user_data['mailfrom'], $user_data['fromname'], $user_data['email'], $emailSubject, $emailBody);
			
			$app->login($credentials, $options);
			
			$app->getUserState('com_djcatalog2.cart_registration', null);
			$app->setUserState('users.login.form.data', array());
			$app->redirect(JRoute::_(DJCatalog2HelperRoute::getCheckoutRoute(), false));
		}
		
		return true;
	}

	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');
		$db = JFactory::getDbo();
		if ($userId)
		{
			try
			{
				$db->setQuery('DELETE FROM #__djc2_users WHERE user_id = '.$userId);

				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
	/*
	private function _notifyAfterSave($item, $user)
	{
		$app		= JFactory::getApplication();
		$params 	= JComponentHelper::getParams('com_djcatalog2');
			
		$mailfrom	= $app->getCfg('mailfrom');
		$fromname	= $app->getCfg('fromname');
		$sitename	= $app->getCfg('sitename');
		$copytext 	= JText::sprintf('COM_DJCATALOG2_COPYTEXT_OF', $item->name, $sitename);
			
		$contact_list = $params->get('fed_notify_list', false);
		$recipient_list = array();
		if ($contact_list !== false) {
			$recipient_list = explode(PHP_EOL, $params->get('fed_notify_list', ''));
		}
			
		$list_is_empty = true;
		foreach ($recipient_list as $r) {
			if (strpos($r, '@') !== false) {
				$list_is_empty = false;
				break;
			}
		}
			
		if ($list_is_empty) {
			$recipient_list[] = $mailfrom;
		}
			
		$recipient_list = array_unique($recipient_list);
		
		$name = null;
		$email = null;
		$item_name = $item->name;
		$item_id = $item->id;
		
		$subject	= JText::_('COM_DJCATALOG2_NEW_PRODUCT_SUBMITTED_SUBJECT');
		$body = '';
		if ($user->guest) {
			$body = JText::sprintf('COM_DJCATALOG2_PRODUCT_SUBMITTED_BY_GUEST', $item_id, $item_name);
		} else {
			$name		= $user->name.' ('.$user->username.')';
			$email		= $user->email;
			$body = JText::sprintf('COM_DJCATALOG2_PRODUCT_SUBMITTED', $item_id, $item_name, $name, $email);
		}
		
		$body .= "\n\n".JURI::base().'administrator/index.php?option=com_djcatalog2&view=items&filter_search='.urlencode('id:'.$item_id);

		$mail = JFactory::getMailer();
	
		//$mail->addRecipient($mailfrom);
		foreach ($recipient_list as $recipient) {
			$mail->addRecipient(trim($recipient));
		}
		if ($user->guest == false) {
			$mail->addReplyTo(array($email, $name));
		}
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename.': '.$subject);
		$mail->setBody($body);
		$sent = $mail->Send();
	
		return $sent;
	}
	
	public static function image($value, $alt = '')
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			$directory = self::$images_path;
			$image_path = JURI::root().'/'.$directory.'/'.$value;
			
			return '<img src="'.$image_path.'" alt="'.htmlspecialchars($alt).'" />';
		}
	}*/
	
	public static function country($value)
	{
		if (empty($value) || !(int)$value)
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			$db = JFactory::getDbo();
			$db->setQuery('select country_name from #__djc2_countries where id ='.(int)$value);
			$country = $db->loadResult();
			
			return (empty($country)) ? JHtml::_('users.value', $value) : $country;
		}
	}
	
	public static function country_state($value)
	{
		if (empty($value) || !(int)$value)
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			$db = JFactory::getDbo();
			$db->setQuery('select name from #__djc2_countries_states where id ='.(int)$value);
			$state = $db->loadResult();
				
			return (empty($state)) ? JHtml::_('users.value', $value) : $state;
		}
	}
	
	public static function www($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			$value = htmlspecialchars($value);
			if (substr ($value, 0, 4) == "http")
			{
				return '<a href="'.$value.'" target="_blank">'.$value.'</a>';
			}
			else
			{
				return '<a href="http://'.$value.'" target="_blank">'.$value.'</a>';
			}
		}
	}
	
	public static function user_id($value)
	{
		$user = JFactory::getUser();
		$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2');
		
		if (empty($value) || !$salesman)
		{
			return '';//JHtml::_('users.value', $value);
		}
		else
		{
			$app = JFactory::getApplication();
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			$query->select('id')->from('#__djc2_vendors')->where('user_id='.(int)$user->id);
			$db->setQuery($query);
			$vendor_id = $db->loadResult();
			
			if (!$vendor_id){
				return '-';
			}
			
			$query = $db->getQuery(true);
			$query->select('u.id, u.name, u.username, u.email');
			$query->from('#__users as u');
			$query->join('inner', '(select customer_id from #__djc2_vendors_customers where vendor_id = '.(int)$vendor_id.') as vc ON vc.customer_id = u.id');
			$query->order('u.name');
			$db->setQuery($query);
			
			$users = $db->loadObjectList();
			if (empty($users)) {
				return '';
			}
			$active = $app->getUserState('com_djcatalog2.checkout.user_id', $user->id);
			
			$html = '<select onchange="DJC2CheckoutgetUserData(this.value);">';
			foreach($users as $user) {
				$selected = $active == $user->id ? 'selected="selected"' : '';
				$html .= '<option value="'.$user->id.'" '.$selected.'>'.$user->name.', '.$user->username.', '.$user->email.'</option>';
			}
			
			$html .= '</select>
					<script type="text/javascript">
					function DJC2CheckoutgetUserData (user_id) {
						window.location.href = "'.JUri::base(false).'index.php?option=com_djcatalog2&task=cart.selectUser&user_id=" + user_id;
					}
					</script>';
			
			return $html;
		}
	}
	
	public static function yes_no_val($value)
	{
		return $value ? JText::_('JYES') : JText::_('JNO');
	}
	
	/*public function onAfterInitialise() {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$users_params = JComponentHelper::getParams('com_users');
		$activation = $users_params->get('useractivation');
		$cart_registration = $app->getUserState('com_djcatalog2.cart_registration');
		
		$input = $app->input;
		
		if ($app->isSite() && $user->guest && $activation > 0 && $cart_registration) {
			if ($input->get('option') == 'com_users' && $input->get('view') == 'registration' && $input->get('layout') == 'complete') {
				$app->getUserState('com_djcatalog2.cart_registration', null);
				
				$msg = ($activation == 1) ? JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE') : JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY');
				$app->enqueueMessage($msg);
				$app->redirect(JRoute::_(DJCatalog2HelperRoute::getCartRoute().'&layout=login', false));
			}
		}
	}*/
}

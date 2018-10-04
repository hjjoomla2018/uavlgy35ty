<?php
/**
 * @version $Id: djreviews.php 18 2014-10-30 11:01:55Z michal $
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

class plgDjclassifiedsDJReviewssales extends JPlugin {
	
	protected $users = array();
	protected $adverts = array();
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	
	public function onAfterDJClassifiedsDisplayAdvertAuthor( &$item, $params, $view) {
		
		$app = JFactory::getApplication();
		
		if ($view != 'item' || !$item->user_id) {
			return false;
		}
		
		$group_id = $this->params->get('rating_group_author', false);
		if (!$group_id) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php');
		
		$uid_slug = $item->user_id.':'.DJClassifiedsSEO::getAliasName($item->username);
		
		$name = $item->username ? $item->username : $item->user_id;
		
		$review = DJReviewsAPI::getInstance(array(
			'group' => $group_id,
			'type'  => 'com_djclassifieds.buysell',
			'name'	=> $name,
			'link'	=> 'index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug,
			'id'	=> $item->user_id,
			'plugin' => 'djclassifieds.djreviewssales'
		)
			);
		
		return $review->getRatingAvg();
		
	}
	
	public function onAfterDJClassifiedsDisplayProfile( &$profile, $params, $view) {
		$app = JFactory::getApplication();
		
		if (!$profile['id']) {
			return false;
		}
		
		$group_id = $this->params->get('rating_group_author', false);
		if (!$group_id) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php');
		
		$uid_slug = $profile['id'].':'.DJClassifiedsSEO::getAliasName($profile['name']);
		
		$name = $profile['name'] ? $profile['name'] : $profile['id'];
		
		$review = DJReviewsAPI::getInstance(array(
			'group' => $group_id,
			'type'  => 'com_djclassifieds.buysell',
			'name'	=> $name,
			'link'	=> 'index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug,
			'id'	=> $profile['id'],
			'plugin' => 'djclassifieds.djreviewssales'
		)
			);
		
		
		$layout_prev = $review->getReviewsLayout('preview');
		if(strstr($layout_prev, 'djrv_single_review')){
			$btn = '<p><a href="#profile-allreviews" class="btn btn-primary">'.JText::_('PLG_DJCLASSIFIEDS_DJREVIEWSSALES_UI_SEE_ALL').'</a></p>';
		}
		
		
		return $review->getRating() .  $layout_prev. $btn /*. $review->getReviewsLayout('default')*/;
		
	}
	
	public function onAfterDJClassifiedsDisplayProfileItems( &$profile, $params, $view) {
		$app = JFactory::getApplication();
		
		if (!$profile['id']) {
			return false;
		}
		
		$group_id = $this->params->get('rating_group_author', false);
		if (!$group_id) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php');
		
		$uid_slug = $profile['id'].':'.DJClassifiedsSEO::getAliasName($profile['name']);
		
		$name = $profile['name'] ? $profile['name'] : $profile['id'];
		
		$review = DJReviewsAPI::getInstance(array(
			'group' => $group_id,
			'type'  => 'com_djclassifieds.buysell',
			'name'	=> $name,
			'link'	=> 'index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug,
			'id'	=> $profile['id'],
			'plugin' => 'djclassifieds.djreviewssales'
		)
			);
		
		return '<div id="profile-allreviews">'.$review->getReviewsLayout('default').'</div>' . $review->getForm();
	}
	
	public function onAfterDJClassifiedsDisplaySalesItem( &$order, $params, $view) {
		$app = JFactory::getApplication();
		
		$user = JFactory::getUser();
		if ($user->guest || $order->user_id == 0 || $order->item_id == 0) {
			return false;
		}
		
		$item = $this->getAdvertById($order->item_id);
		if (empty($item) || !$item->user_id) {
			return false;
		}
		
		$buyer = $this->getUserById($order->user_id);
		if (empty($buyer)) {
			return false;
		}
		
		$seller = $this->getUserById($item->user_id);
		if (empty($seller)) {
			return false;
		}
		
		$isSeller = (bool)($seller->id == $user->id);
		$isBuyer = (bool)($buyer->id == $user->id);
		
		if ((!$isSeller && !$isBuyer) || ($isSeller && $isBuyer)) {
			return false;
		}
		
		$userType = $isBuyer ? 'buyer' : 'seller';
		$profile = $isBuyer ? $seller : $buyer;
		
		if (!$this->canReviewOrder($order, $userType, $buyer, $seller, $user, $item)) {
			return JText::_('PLG_DJCLASSIFIEDS_DJREVIEWSSALES_UI_ALREADY_RATED');
		}
		
		//return '<pre>'.print_r($order,true).'</pre>';
		
		$group_id = $this->params->get('rating_group_author', false);
		if (!$group_id) {
			return false;
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php');
		
		$uid_slug = $profile->id.':'.DJClassifiedsSEO::getAliasName($profile->name);
		
		$name = $profile->name;
		
		$review = DJReviewsAPI::getInstance(array(
			'group' => $group_id,
			'type'  => 'com_djclassifieds.buysell',
			'name'	=> $name,
			'link'	=> 'index.php?option=com_djclassifieds&view=profile&uid='.$uid_slug,
			'id'	=> $profile->id,
			'plugin' => 'djclassifieds.djreviewssales'
		)
			);
		
		if ($review->initialise()) {
			$customParams = array('order_id' => $order->id);
			return '<a href="#" data-toggle="djreviewsform" data-objectid="'.$review->id.'" data-customparams=\''.json_encode($customParams).'\'>leave feedback</a>';
		}
	}
	
	protected function canReviewOrder($order, $userType, $buyer, $seller, $user, $item) {
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('*')->from('#__djrevs_plg_classifiedssales')->where('order_id='.(int)$order->id);
		$db->setQuery($query);
		
		$order_info = $db->loadObject();
		
		if (empty($order_info)) {
			$order_info = new stdClass();
			$order_info->id = null;
			$order_info->order_id = $order->id;
			$order_info->seller_id = $seller->id;
			$order_info->buyer_id = $buyer->id;
			$order_info->seller_rated = 0;
			$order_info->buyer_rated = 0;
			
			$db->insertObject('#__djrevs_plg_classifiedssales', $order_info, 'id');
			return true;
		}
		
		if ($order_info->seller_rated == 1 && $userType == 'seller') {
			return false;
		}
		
		if ($order_info->buyer_rated == 1 && $userType == 'buyer') {
			return false;
		}
		
		return true;
	}
	
	public function onDJReviewsAuthorise($data, $object, $action) {
		$plugin = $object->plugin;
		if ($plugin != 'djclassifieds.djreviewssales') {
			return true;
		}
		
		$user = JFactory::getUser();
		if ($user->guest) {
			return true;
		}
		
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		
		if ($action == 'create') {
			if ($object->object_type == 'com_djclassifieds.buysell') {
				if ((int)$object->entry_id == $user->id) {
					return false;
				}
				
				$custom = $app->input->get('custom', array(), 'array');
				$orderId = isset($custom['order_id']) ? $custom['order_id'] : false;
				if (!$orderId) {
					return false;
				}
				
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('*')->from('#__djcf_orders')->where('id='.(int)$orderId);
				$db->setQuery($query);
				
				$order = $db->loadObject();
				if (empty($order)) {
					return false;
				}
				
				$user = JFactory::getUser();
				if ($user->guest || $order->user_id == 0 || $order->item_id == 0) {
					return false;
				}
				
				$item = $this->getAdvertById($order->item_id);
				if (empty($item) || !$item->user_id) {
					return false;
				}
				
				$buyer = $this->getUserById($order->user_id);
				if (empty($buyer)) {
					return false;
				}
				
				$seller = $this->getUserById($item->user_id);
				if (empty($seller)) {
					return false;
				}
				
				$isSeller = (bool)($seller->id == $user->id);
				$isBuyer = (bool)($buyer->id == $user->id);
				
				if ((!$isSeller && !$isBuyer) || ($isSeller && $isBuyer)) {
					return false;
				}
				
				$userType = $isBuyer ? 'buyer' : 'seller';
				$profile = $isBuyer ? $seller : $buyer;
				
				if (!$this->canReviewOrder($order, $userType, $buyer, $seller, $user, $item)) {
					return false;
				}
				
				$app->setUserState('com_djreviews.plg_sales_user_type', $userType);
			}
		}
		
		return true;
	}
	
	public function onContentAfterSave($context, $table, $isNew, $data) {
		if ($context != 'com_djreviews.reviewform') {
			return;
		}
		
		if ($isNew) {
			$app = JFactory::getApplication();
			
			$custom = $app->input->get('custom', array(), 'array');
			$orderId = isset($custom['order_id']) ? $custom['order_id'] : false;
			if (!$orderId) {
				return;
			}
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update('#__djrevs_plg_classifiedssales');
			$userType = $app->getUserState('com_djreviews.plg_sales_user_type');
			
			if (!$userType) {
				return;
			}
			
			if ($userType == 'buyer') {
				$query->set('buyer_rated=1');
			} else {
				$query->set('seller_rated=1');
			}
			$query->where('order_id='.(int)$orderId);
			$db->setQuery($query);
			$db->execute();
		}
	}
	
	public function onDJReviewsEmailNotification($data, $object, $model) {
		$plugin = $object->plugin;
		if ($plugin != 'djclassifieds.djreviewssales') {
			return;
		}
		
		if ($this->params->get('notify_owners') != '1') {
			return false;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$email = null;
		
		if ($object->object_type == 'com_djclassifieds.buysell') {
			$query->select('u.email')->from('#__users AS u');
			$query->where('u.id='.(int)$object->entry_id);
			$db->setQuery($query);
			
			$email = $db->loadResult();
		}
		
		$subject = $data['id']== 0 ? JText::_('COM_DJREVIEWS_MAIL_REVIEW_ADDED') : JText::_('COM_DJREVIEWS_MAIL_REVIEW_CHANGED');
		
		$body = JText::_('COM_DJREVIEWS_MAIL_REVIEW_HEADING').' '.$data['user_name'].',<br /><br />';
		$body .= JText::_('COM_DJREVIEWS_RECEIVE_MESSAGE').'.<br /><br />';
		$body .= JText::_('COM_DJREVIEWS_AUTHOR').': ';
		//$body .= $data['user_name'].' ('.$data['user_login'].')' . '<br />';
		$body .= $data['user_name'].'<br /><br />';
		
		if (isset($data['title'])) {
			$body .= JText::_('COM_DJREVIEWS_TITLE').': ';
			$body .= $data['title'] . '<br /><br />';
		}
		if (isset($data['message'])) {
			$body .= JText::_('COM_DJREVIEWS_MESSAGE').': ';
			$body .= nl2br($data['message']) . '<br /><br />';
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		require_once JPath::clean(JPATH_ROOT.'/administrator/components/com_djclassifieds/lib/djseo.php');
		
		$profileLink = JRoute::_('index.php?option=com_djclassifieds&view=profile'.DJClassifiedsSEO::getUserProfileItemid(), false, (JUri::getInstance()->isSSL() ? 1 : -1));
		
		$body .= JText::_('COM_DJREVIEWS_EMAIL_LINK').':<br /><a href="'.$profileLink.'">'.$profileLink.'</a><br /><br />';
		$body .= JText::_('COM_DJREVIEWS_BEST_REGARDS').'<br />';
		$body .= 'Celtic Zone';
		
		$admin_body = $body;
		
		if (isset($data['published'])) {
			$admin_body .= '<br /><br />';
			if ($data['published'] == 0) {
				$admin_body .= JText::_('COM_DJREVIEWS_MAIL_REVIEW_NOT_PUBLISHED').'<br/>';
			} else {
				$admin_body .= JText::_('COM_DJREVIEWS_MAIL_REVIEW_PUBLISHED').'<br />';
			}
		}
		
		$reviewId = $model->getState('reviewform.id');
		$admin_body .= '<a href="'.JURI::base().'administrator/index.php?option=com_djreviews&amp;view=reviews&amp;filter_search='.urlencode('id:'.$reviewId).'">'.JText::_('COM_DJREVIEWS_MAIL_REVIEW_BACK_END_LINK').'</a>';
		
		$this->sendNotification($subject, $admin_body);
		$this->sendNotification($subject, $body, $email);
		
		return true;
	}
	
	/*public function onDJReviewsNotification($data, $object) {
	 $plugin = $object->plugin;
	 if ($plugin != 'djclassifieds.djreviewssales') {
	 return true;
	 }
	 
	 if ($this->params->get('notify_owners') != '1') {
	 return false;
	 }
	 
	 $db = JFactory::getDbo();
	 $query = $db->getQuery(true);
	 $email = null;
	 
	 if ($object->object_type == 'com_djclassifieds.buysell') {
	 $query->select('u.email')->from('#__users AS u');
	 $query->where('u.id='.(int)$object->entry_id);
	 $db->setQuery($query);
	 
	 $email = $db->loadResult();
	 }
	 
	 return $email;
	 }*/
	
	protected function getUserById($user_id) {
		if (!isset($this->users[$user_id])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__users')->where('id='.(int)$user_id);
			$db->setQuery($query);
			
			$this->users[$user_id] = $db->loadObject();
		}
		return $this->users[$user_id];
	}
	
	protected function getAdvertById($item_id) {
		if (!isset($this->adverts[$item_id])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djcf_items')->where('id='.(int)$item_id);
			$db->setQuery($query);
			
			$this->adverts[$item_id] = $db->loadObject();
		}
		return $this->adverts[$item_id];
	}
	
	protected function sendNotification($subject, $body, $recipient = null)
	{
		$app		= JFactory::getApplication();
		$params 	= JComponentHelper::getParams('com_djreviews');
		$config 	= JFactory::getConfig();
		
		$mailfrom	= $config->get('mailfrom');
		$fromname	= $config->get('fromname');
		$sitename	= $config->get('sitename');
		
		$contact_list = $params->get('contact_list', false);
		$recipient_list = array();
		if (is_null($recipient)) {
			if ($contact_list !== false) {
				$recipient_list = explode(PHP_EOL, $params->get('contact_list', ''));
			}
		} else {
			$recipient_list[] = $recipient;
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
		
		$mail = JFactory::getMailer();
		
		foreach ($recipient_list as $recipient) {
			$mail->addRecipient(trim($recipient));
		}
		
		$mail->setSender(array($mailfrom, $fromname));
		
		$mail->setSubject($subject. ': '. $sitename);
		$mail->setBody($body);
		$mail->isHtml(true);
		$sent = $mail->Send();
		
		return $sent;
	}
	
}
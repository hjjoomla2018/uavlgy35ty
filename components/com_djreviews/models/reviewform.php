<?php
/**
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

use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djreviews/models/review.php');

jimport('joomla.application.component.helper');

class DJReviewsModelReviewForm extends DJReviewsModelReview
{
	public $object = null;
	public $userRating = array();
	
	public function __construct($config = array()) {
		parent::__construct($config);
		JForm::addFormPath(__DIR__.JPath::clean('/forms'));
	}
	
	protected function populateState()
	{
		parent::populateState();
	
		$app = JFactory::getApplication();
	
		$object_id = $app->input->getInt('object_id');
		$this->setState('review.object_id', $object_id);
		if($object_id > 0){
			$db = JFactory::getDbo();
			$db->setQuery("SELECT * FROM #__djrevs_objects WHERE id=".$object_id );
			$object = $db->loadObject();
			if($object){
				$this->setState('review.rating_group_id',$object->rating_group_id);
				$this->setState('review.object_type', $object->object_type);
			}
		}
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
	
		// Get the form.
		$form = $this->loadForm('com_djreviews.'.$this->form_name, $this->form_name, array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		
		$rating_group = $this->getState('review.rating_group_id');
		if ($rating_group) {
			$form->setValue('rating_group_id', null, $rating_group);
		}
		
		$object_id = $this->getState('review.object_id');
		if ($object_id) {
			$form->setValue('object_id', null, $object_id);
		}
		
		$object_type = $this->getState('review.object_type');
		if ($object_type) {
			$form->setValue('object_type', null, $object_type);
		}
		
		$user = JFactory::getUser();
		if ($user->id > 0) {
			$form->setValue('created_by', null, $user->id);
			$form->setValue('user_name', null, $user->name);
			$form->setValue('user_login', null, $user->username);
			$form->setValue('email', null, $user->email);
		}
		
		if (!$rating_group) {
			$formData = $app->input->get('jform', array(), 'array');
			if (!empty($formData) && isset($formData['rating_group_id'])) {
				$rating_group = (int)$formData['rating_group_id'];
			}
		}
		
		//if ($rating_group > 0) {
			$params = DJReviewsHelper::getParams($rating_group);
			
			$requireTitle = (int)$params->get('title', 2);
			$requireMessage = (int)$params->get('message', 2);
			if ($requireTitle == 2) {
				$form->setFieldAttribute('title', 'required', 'true');
				$form->setFieldAttribute('title', 'class', $form->getFieldAttribute('title', 'class').' required');
			} else if ($requireTitle == 0) {
				$form->removeField('title');
			}
			if ($requireMessage == 2) {
				$form->setFieldAttribute('message', 'required', 'true');
				$form->setFieldAttribute('message', 'class', $form->getFieldAttribute('message', 'class').' required');
			} else if ($requireMessage == 0) {
				$form->removeField('message');
			}
		//}
		
		return $form;
	}
	
	public function validate($form, $data, $group = null) {
		$user = JFactory::getUser();
		$params  = DJReviewsHelper::getParams((int)$data['rating_group_id']);
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		
		if ($this->validateCaptcha() == false) {
			$this->setError(JText::_('COM_DJREVIEWS_CAPTCHA_ERROR'));
			return false;
		}
		
		if (isset($data['id']) && $data['id'] > 0) {
			// if the review has been edited we don't change it's state
			unset($data['published']);
			
			if ($item = $this->getItem($data['id'])) {
				$data['created_by'] = $item->created_by;
				$data['user_name'] = $item->user_name;
				$data['user_login'] = $item->user_login;
				$data['email'] = $item->email;
			}
			
		} else {
			if ($user->authorise('review.autopublish', 'com_djreviews') || $user->authorise('core.edit.state', 'com_djreviews')){
				$data['published'] = 1;
			} else {
				$data['published'] = 0;
			}
			
			if ($user->id > 0) {
				$data['created_by'] = $user->id;
				$data['user_name'] = $user->name;
				$data['user_login'] = $user->username;
				$data['email'] = $user->email;
			}
		}
		
		$canRate = true;
		$userRating = $this->getUserRating((int)$data['object_id']);
		foreach ($userRating as $userRate) {
			if ($userRate->avg_rate > 0 && (int)$data['id'] != $userRate->id && (int)$params->get('multiple_rating', 0) == 0) {
				$canRate = false;
			}
		}
		
		$canSubmit = true;
		if (!$canRate && ((int)$params->get('message', 2) == 0 || ((int)$params->get('followup', 1) == 0 && (int)$params->get('multiple_rating', 0) == 0))) {
			$canSubmit = false;
		}
		
		if (!$canSubmit) {
			$this->setError(JText::_('COM_DJREVIEWS_CANNOT_ADD_MORE_REVIEWS'));
			return false;
		}
		
		if ($canRate) {
			if (isset($data['rating_group_id'])) {
				$group_id = (int)$data['rating_group_id'];
				if ($group_id == 0) {
					$this->setError(JText::_('COM_DJREVIEWS_ERROR_FORM_MALFORMED'));
					return false;
				}
					
				//$db->setQuery('SELECT * FROM #__djrevs_rating_fields WHERE group_id = '.(int)$data['rating_group_id'].' AND required=1');
				$query = $db->getQuery(true);
				$query->select('f.*');
				$query->from('#__djrevs_rating_fields AS f');
				$query->join('INNER', '#__djrevs_fields_groups AS fg ON fg.field_id = f.id AND fg.group_id = '.(int)$group_id);
				$query->where('f.required = 1');
				$db->setQuery($query);
				
				$fields = $db->loadObjectList('id');
					
				if (count($fields) && empty($data['rating'])) {
					$this->setError(JText::_('COM_DJREVIEWS_ERROR_FORM_MALFORMED'));
					return false;
				}
					
				$allFieldsValid = true;
				foreach ($fields as $field) {
					if (!array_key_exists($field->id, $data['rating']) || empty($data['rating'][$field->id])) {
						$allFieldsValid = false;
						break;
					}
				}
				
				$query->clear('where');
				$query->where('f.regular_exp != "" AND f.regular_exp IS NOT NULL');
				$db->setQuery($query);
				
				$fields = $db->loadObjectList('id');
				foreach ($fields as $field) {
					if (!empty($data['rating'][$field->id])) {
						if ($field->regular_exp && !preg_match(chr(1) . $field->regular_exp . chr(1), $data['rating'][$field->id])) {
							$allFieldsValid = false;
						}
					}
				}
					
				if (!$allFieldsValid) {
					$this->setError(JText::_('COM_DJREVIEWS_ERROR_RATING_IS_MISSING'));
					return false;
				}
					
			}
		} else {
			if (!empty($data['rating'])) {
				$this->setError(JText::_('COM_DJREVIEWS_ERROR_YOU_HAVE_ALREADY_RATED'));
				return false;
			}
		}
		
		$data['title'] = empty($data['title']) ? '' : $data['title'];
		$data['message'] = empty($data['message']) ? '' : $data['message'];
		
		$title_splited = explode(' ', $data['title']);
		$msg_splited = explode(' ', $data['message']);
		if($params->get('word_blacklist',"")){
			$word_blacklist = explode(',', $params->get('word_blacklist',""));
			$word_whitelist = explode(',', $params->get('word_whitelist',""));
			
			foreach($msg_splited as &$word){
					foreach($word_blacklist as $bl){
						$bl = trim(JString::strtolower($bl));
						if (!$bl) {
							continue;
						}
						$tmpWord = JString::strtolower($word);
						if (strstr($tmpWord, $bl)){
							$bl_found =1;
							foreach($word_whitelist as $wl){
								$wl = trim(JString::strtolower($wl));
								if ($wl && strstr($tmpWord, $wl)){
									$bl_found = 0;
									break;
								}
							}
							if ($bl_found){
								$stars_l = JString::strlen($word);
								$stars = '';
								for($i=0; $i < JString::strlen($bl); $i++){
									$stars .= '*';
								} 
								$word = JString::str_ireplace($bl, $stars, $word);
							}
						}
					}
			}
			unset($word);
			
			foreach($title_splited as &$word){
				foreach($word_blacklist as $bl){
					$bl = trim(JString::strtolower($bl));
					if (!$bl) {
						continue;
					}
					$tmpWord = JString::strtolower($word);
					if (strstr($tmpWord, $bl)){
						$bl_found =1;
						foreach($word_whitelist as $wl){
							$wl = trim(JString::strtolower($wl));
							if ($wl && strstr($tmpWord, $wl)){
								$bl_found = 0;
								break;
							}
						}
						if ($bl_found){
							$stars_l = JString::strlen($word);
							$stars = '';
							for($i=0; $i < JString::strlen($bl); $i++){
								$stars .= '*';
							}
							$word = JString::str_ireplace($bl, $stars, $word);
						}
					}
				}
			}
			unset($word);
			
			$data['message'] = implode(' ', $msg_splited);
			$data['title'] = implode(' ', $title_splited);
		}

		return parent::validate($form, $data, $group);
	}
	
	protected function preprocessForm(JForm $form, $data, $group = 'content') {
	}
	
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
	
		return (bool)($user->authorise('core.delete', 'com_djreviews') 
				|| ($user->authorise('review.delete.own', 'com_djreviews') && $user->id == $record->created_by));
	}
	
	public function getObject($id = false){
		if (empty($this->object)) {
			$db  = JFactory::getDbo();
			$app = JFactory::getApplication();
			$object_id = 0;
			if ($id !== false) {
				$object_id = $id;
			} else {
				$object_id = $this->getState('review.object_id');
				
				if(!$object_id && $app->input->getInt('object_id') > 0){
					$object_id = $app->input->getInt('object_id');
				}
				
				if (!$object_id && $this->getState('reviewform.id') > 0) {
					if ($item = $this->getItem()) {
						$object_id = $item->object_id;
					}
				}
			}
		
			if ($object_id) {
				$query = $db->getQuery(true);
				$query->select('ro.* ');
				$query->from('#__djrevs_objects ro');
				$query->where('ro.id='.(int)$object_id);
				$db->setQuery($query);
				$this->object = $db->loadObject();
			}
		}
		
		return $this->object;
	}
	
	public function getUserRating($object_id = false) {
		$object = $this->getObject($object_id);
		$user = JFactory::getUser();
		
		if (empty($this->userRating)) {
			if (!empty($object)) {
				$db = JFactory::getDbo();
					
				if ($user->guest) {
					$ip = '';
					$client  = @$_SERVER['HTTP_CLIENT_IP'];
					$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
					$remote  = $_SERVER['REMOTE_ADDR'];
						
					if(filter_var($client, FILTER_VALIDATE_IP)) {
						$ip = $client;
					} elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
						$ip = $forward;
					} else {
						$ip = $remote;
					}
					if ($ip) {
						$db->setQuery('SELECT * FROM #__djrevs_reviews WHERE object_id = '.(int)$object->id.' AND created_by=0 AND ip = '.$db->quote($ip).' ORDER BY created DESC');
						$this->userRating = $db->loadObjectList();
					}
				} else {
					$db->setQuery('SELECT * FROM #__djrevs_reviews WHERE object_id = '.(int)$object->id.' AND created_by = '.(int)$user->id.' ORDER BY created DESC');
					$this->userRating = $db->loadObjectList();
				}
			}
		}
		
		return $this->userRating;
	}
	
	private function validateCaptcha(){
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		
		$captcha_key = '';
		if (JPluginHelper::isEnabled('captcha', 'recaptcha') && $user->guest) {
			$recaptcha =  JPluginHelper::getPlugin('captcha', 'recaptcha');
			$recaptcha_params = $recaptcha->params;
			if (!is_object($recaptcha_params) && $recaptcha_params !='') {
				$recaptcha_params = new Registry($recaptcha_params);
			}
		
			if ($recaptcha_params->get('public_key') && $recaptcha_params->get('private_key')) {
				$captcha_key = $recaptcha_params->get('private_key');
			}
		}
		
		if ($user->guest && $captcha_key) {
			$recaptcha_response = $this->getRecaptchaResponse($captcha_key, $app->input->server->get('REMOTE_ADDR', '', 'string'), $app->input->get('g-recaptcha-response', '', 'string'));
			if ($recaptcha_response == false) {
				return false;
			}
		}
		
		return true;
	}
	
	private function getRecaptchaResponse($privatekey, $remoteip, $response)
	{
		require_once JPath::clean(JPATH_ROOT.'/plugins/captcha/recaptcha/recaptchalib.php');

		$reCaptcha = new JReCaptcha($privatekey);
		$response  = $reCaptcha->verifyResponse($remoteip, $response);

		if ( !isset($response->success) || !$response->success)
		{
			return false;
		}
	
		return true;
	}
	
}
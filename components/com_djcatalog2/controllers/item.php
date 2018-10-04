<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class Djcatalog2ControllerItem extends JControllerForm
{
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	public function contact()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('item');
		$params = JComponentHelper::getParams('com_djcatalog2');
		$slug	= $app->input->getString('id');
		$id		= (int)$slug;
		$layout = $app->input->getCmd('layout');
		$layoutSfx = ($layout == 'contact') ? '&layout=contact&tmpl=component' : '';

		// Get the data from POST
		$data = $app->input->get('jform', array(), 'array');

		$item = $model->getItem($id);

		// Check for a valid session cookie
		if(JFactory::getSession()->getState() != 'active'){
			JError::raiseWarning(403, JText::_('COM_CONTACT_SESSION_INVALID'));

			// Save the data in the session.
			$app->setUserState('com_djcatalog2.contact.data', $data);

			// Redirect back to the contact form.
			$this->setRedirect(JRoute::_('index.php?option=com_djcatalog2&view=item&id='.$slug.'&cid='.$item->catslug.$layoutSfx, false).'#contactform');
			return false;
		}

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		
		$validate = $model->validate($form, $data);

		if ($validate === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();
			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_djcatalog2.contact.data', $data);

			// Redirect back to the contact form.
			$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getItemRoute($slug, $item->catslug).$layoutSfx, false).'#contactform');
			return false;
		}

		// Send the email
		$sent = $this->_sendEmail($data, $item);

		// Set the success message if it was a success
		if (!($sent instanceof Exception)) {
			$msg = JText::_('COM_DJCATALOG2_EMAIL_THANKS');
			/*if ($layout == 'contact') {
				$msg .= '
					<script>
					jQuery(document).ready(function(){
						var isIframe = false;
						try {
							isIframe = window.self !== window.top;
						} catch (e) {
							isIframe = true;
						}
						setTimeout(function(){
							if (isIframe) {
								jQuery(window.parent.document).find("button.mfp-close").trigger("click");
							} else {
								window.location.href="'.JRoute::_(DJCatalogHelperRoute::getItemRoute($slug, $item->catslug), false).'";
							}
						}, 2000);
					});
					</script>';
			}*/
		} else {
			$msg = '' ;
		}

		// Flush the data from the session
		$app->setUserState('com_djcatalog2.contact.data', null);
		
		$layoutSfx .= ($layoutSfx) ? '&success=1' : '';
		$this->setRedirect(JRoute::_(DJCatalogHelperRoute::getItemRoute($slug, $item->catslug).$layoutSfx, false), $msg);

		return true;
	}

	private function _sendEmail($data, $item)
	{
		$app		= JFactory::getApplication();
		$params 	= JComponentHelper::getParams('com_djcatalog2');
		$db 		= JFactory::getDbo();
		/*if ($contact->email_to == '' && $contact->user_id != 0) {
			$contact_user = JUser::getInstance($contact->user_id);
			$contact->email_to = $contact_user->get('email');
		}*/
		
		$mailfrom	= $app->getCfg('mailfrom');
		$fromname	= $app->getCfg('fromname');
		$sitename	= $app->getCfg('sitename');
		$copytext 	= JText::sprintf('COM_DJCATALOG2_COPYTEXT_OF', $item->name, $sitename);
		
		$contact_list = $params->get('contact_list', false);
		$recipient_list = array();
		if ($contact_list !== false) {
			$recipient_list = explode(PHP_EOL, $params->get('contact_list', ''));
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
		
		$recipient_settings = $params->get('fed_contact', 0);
		//$owner = JFactory::getUser($item->created_by);
		
		$query = $db->getQuery(true);
		$query->select('u.email, i.email as alt_email');
		$query->from('#__djc2_items AS i');
		$query->join('LEFT', '#__users AS u ON i.created_by = u.id');
		$query->where('i.id = '.$item->id);
		$db->setQuery($query);
		
		$owner = $db->loadObject();
		
		$owner_email = null;
		if ($owner && ($owner->email || $owner->alt_email)) {
			$owner_email = ($owner->alt_email != '') ? $owner->alt_email : $owner->email;
		}
		
		if ((int)$recipient_settings == 1 && trim($owner_email) != '') {
			$recipient_list = array(trim($owner_email));
		} else if ((int)$recipient_settings == 2) {
			$recipient_list[] = trim($owner_email);
		}
		
		$recipient_list = array_unique($recipient_list);

		$name		= $data['contact_name'];
		$email		= $data['contact_email'];
		$subject	= $data['contact_subject'];
		$body		= $data['contact_message'];
		
		$additional_data = array();
		if (!empty($data['contact_company_name'])){
			$additional_data[] = array(
					'label' => JText::_('COM_DJCATALOG2_CONTACT_COMPANY_NAME_LABEL'),
					'value'	=> $data['contact_company_name']
			);
		}
		if (!empty($data['contact_phone'])){
			$additional_data[] = array(
					'label' => JText::_('COM_DJCATALOG2_CONTACT_PHONE_LABEL'),
					'value'	=> $data['contact_phone']
			);
		}
		if (!empty($data['contact_street'])){
			$additional_data[] = array(
					'label' => JText::_('COM_DJCATALOG2_CONTACT_STREET_LABEL'),
					'value'	=> $data['contact_street']
			);
		}
		if (!empty($data['contact_city'])){
			$additional_data[] = array(
					'label' => JText::_('COM_DJCATALOG2_CONTACT_CITY_LABEL'),
					'value'	=> $data['contact_city']
			);
		}
		if (!empty($data['contact_zip'])){
			$additional_data[] = array(
					'label' => JText::_('COM_DJCATALOG2_CONTACT_ZIP_LABEL'),
					'value'	=> $data['contact_zip']
			);
		}
		if (!empty($data['contact_country'])){
			$query = $db->getQuery(true)->select('country_name')->from('#__djc2_countries')->where('id = '.(int)$data['contact_country']);
			$db->setQuery($query);
			$result = $db->loadResult();
			if ($result) {
				$data['contact_country'] = $result;
				$additional_data[] = array(
						'label' => JText::_('COM_DJCATALOG2_CONTACT_COUNTRY_LABEL'),
						'value'	=> $data['contact_country']
				);
			}
		}
		if (!empty($data['contact_state'])){
			$query = $db->getQuery(true)->select('name')->from('#__djc2_countries_states')->where('id = '.(int)$data['contact_state']);
			$db->setQuery($query);
			$result = $db->loadResult();
			if ($result) {
				$data['contact_state'] = $result;
				$additional_data[] = array(
						'label' => JText::_('COM_DJCATALOG2_CONTACT_STATE_LABEL'),
						'value'	=> $data['contact_state']
				);
			}
		}
		
		if (!empty($data['contact_gdpr_policy'])){
			$policy_info = JText::sprintf('COM_DJCATALOG2_GDPR_POLICY_AGREE', $app->get('sitename'));
			if (trim($params->get('contact_gdpr_policy_info_field')) != '') {
				$policy_info = $params->get('contact_gdpr_policy_info_field');
			}
			$additional_data[] = array(
				'label' => $policy_info,
				'value'	=> JText::_('JYES')
			);
		}
		
		if (!empty($data['contact_gdpr_agreement'])){
			$agreement_info = JText::sprintf('COM_DJCATALOG2_GDPR_AGREE', $app->get('sitename'));
			if (trim($params->get('contact_gdpr_agreement_info_field')) != '') {
				$agreement_info = $params->get('contact_gdpr_agreement_info_field');
			}
			$additional_data[] = array(
				'label' => $agreement_info,
				'value'	=> JText::_('JYES')
			);
		}
		
		if (count($additional_data) > 0) {
			$body .= "<br /><br />".JText::_('COM_DJCATALOG2_CONTACT_ADDITIONAL_DATA');
			foreach ($additional_data as $k=>$v) {
				$body .= "<br />".$v['label'].': '.$v['value'];
			}
		}
		
		$itemLink = JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug), false, -1);

		// Prepare email body
		$prefix = JText::sprintf('COM_DJCATALOG2_ENQUIRY_TEXT', JURI::base(), $item->name);
		$body	= $prefix." ".$name.' <a href="mailo:'.$email.'">'.$email.'</a>'."<br /><br />".stripslashes($body)."<br /><br />" . '<a href="'.$itemLink.'">'.$itemLink.'</a>';
		//$body = strip_tags($body);
		$mail = JFactory::getMailer();

		/*if ((int) $recipient_settings == 1) {
			$mail->addRecipient($owner_email);
		} else {
			$mail->addRecipient($mailfrom);
			foreach ($recipient_list as $recipient) {
				$mail->addBCC(trim($recipient));
			}
		}*/
		
		$mail->addReplyTo($email, $name);
		
		if ((int)$params->get('contact_sender', 0) == 1) {
			$mail->setSender(array($email, $name));
		} else {
			$mail->setSender(array($mailfrom, $fromname));
		}
		
		$mail->setSubject($sitename.': '.$subject);
		$mail->isHtml(true);
		$mail->setBody('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $body . '</body></html>');
		
		$sent = false;
		
		foreach($recipient_list as $recipient) {
			$mail->clearAddresses();
			$mail->addRecipient(trim($recipient));
			$sent = $sent || $mail->Send();
		}
		
		//If we are supposed to copy the sender, do so.

		// check whether email copy function activated
		if ( array_key_exists('contact_email_copy', $data)  ) {
			$copytext		= JText::sprintf('COM_DJCATALOG2_COPYTEXT_OF', $item->name, $sitename);
			$copytext		.= "<br /><br />".$body;
			$copysubject	= JText::sprintf('COM_DJCATALOG2_COPYSUBJECT_OF', $subject);

			$mail = JFactory::getMailer();
			$mail->addRecipient($email);
			$mail->addReplyTo($email, $name);
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($copysubject);
			$mail->setBody('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $copytext . '</body></html>');
			$mail->isHtml(true);
			$sent = $mail->Send();
		}

		return $sent;
	}
	
	public function addToCompare() {
		
		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('item');
		$params = JComponentHelper::getParams('com_djcatalog2');
		$id		= $app->input->getInt('item_id');
		$ajax 	= (bool)($app->input->getInt('ajax')==1);
		$return = $app->input->getBase64('return');
		
		$item = $model->getItem($id);
		
		if (!$id || empty($item)) {
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);
			
			if ($ajax) {
				$app->close();
			} else {
				return false;
			}
		}
		
		$return = ($return) ? base64_decode($return) : DJCatalog2HelperRoute::getItemRoute($item->slug, $item->catslug);
		
		$ajaxOutput = array('error'=>false, 'message'=>'', 'items' => array());
		
		if (Djcatalog2HelperCompare::canAdd() == false) {
			$limit = (int)$params->get('compare_limit', 4);
			
			if ($ajax) {
				$ajaxOutput['error'] = true;
				$ajaxOutput['message'] = JText::sprintf('COM_DJCATALOG2_COMPARE_LIMIT_REACHED', $limit);
				$ajaxOutput['items'] = Djcatalog2HelperCompare::getItems();
				echo json_encode($ajaxOutput);
				$app->close();
			} else {
				$this->setMessage(JText::sprintf('COM_DJCATALOG2_COMPARE_LIMIT_REACHED', $limit), 'warning');
				$this->setRedirect(JRoute::_($return, false));
				return true;
			}
		}
		
		
		Djcatalog2HelperCompare::add($id);
		
		if ($ajax) {
			$ajaxOutput['message'] = JText::_('COM_DJCATALOG2_COMPARE_ADDED');
			$ajaxOutput['items'] = Djcatalog2HelperCompare::getItems();
			echo json_encode($ajaxOutput);
			$app->close();
		} else {
			$this->setRedirect(JRoute::_($return, false));
			return true;
		}
		
		return false;
	}
	
	public function removeFromCompare() {
		
		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('item');
		$params = JComponentHelper::getParams('com_djcatalog2');
		$id		= $app->input->getInt('item_id');
		$ajax 	= (bool)($app->input->getInt('ajax')==1);
		$return = $app->input->getBase64('return');
		
		$item = $model->getItem($id);
		
		if (!$id || empty($item)) {
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);
			
			if ($ajax) {
				$app->close();
			} else {
				return false;
			}
		}
		
		$return = ($return) ? base64_decode($return) : DJCatalog2HelperRoute::getItemRoute($item->slug, $item->catslug);
		
		Djcatalog2HelperCompare::remove($id);
		
		$ajaxOutput = array('error'=>false, 'message'=>'', 'items' => array());
		
		if ($ajax) {
			$ajaxOutput['message'] = JText::_('COM_DJCATALOG2_COMPARE_REMOVED');
			$ajaxOutput['items'] = Djcatalog2HelperCompare::getItems();
			echo json_encode($ajaxOutput);
			$app->close();
		} else {
			$this->setRedirect(JRoute::_($return, false));
			return true;
		}
		
		return false;
	}
	
	public function getProductsToCompare() {
		$app = JFactory::getApplication();
		$idsOnly = $app->input->getInt('idsOnly',1) == 1;
		$items = $idsOnly ? Djcatalog2HelperCompare::getItemIds() : Djcatalog2HelperCompare::getItems();
		
		echo json_encode($items);
		$app->close();
		
		return true;
	}
}

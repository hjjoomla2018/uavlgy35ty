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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.utilities.string');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once(JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djreviews/lib/controllerform.php'));

class DJReviewsBaseControllerReview extends DJReviewsControllerForm {

	public $controller_type = null;

	function __construct($config = array())
	{
		$this->view_list = '';
		$this->view_item = '';

		parent::__construct($config);

		$this->unregisterTask('save2copy');
	}

	public function add()
	{
		$app = JFactory::getApplication();
		$context = "$this->option.edit.$this->context";

		// Access check.
		if (!$this->allowAdd())
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			return false;
		}

		return true;
	}

	public function edit($key = null, $urlVar = null)
	{
		$app   = JFactory::getApplication();
		$model = $this->getModel();

		$table = $model->getTable();
		$recordId   = $app->input->getInt('id', 0);
		$context = "$this->option.edit.$this->context";

		$key = 'id';

		//$checkin = property_exists($table, 'checked_out');

		// Access check.
		if (!$this->allowEdit(array($key => $recordId), $key))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			return false;
		}

		// Skipping checking-in

		// Attempt to check-out the new record for editing and redirect.
		/*if ($checkin && !$model->checkout($recordId))
		{
		throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError()));
		return false;
		}
		else
		{
		// Check-out succeeded, push the new record id into the session.
		$this->holdEditId($context, $recordId);
		$app->setUserState($context . '.data', null);

		return true;
		}*/

		return true;
	}
	
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
	
		// Get items to remove from the request.
		$id = JFactory::getApplication()->input->getInt('id', 0);
		$item = null;
		
		if (!$id)
		{
			JLog::add(JText::_('COM_DJREVIEWS_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
			return false;
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
			
			$item = $model->getItem($id);
	
			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
				
			$cid = array($id);
			JArrayHelper::toInteger($cid);
	
			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(JText::_('COM_DJREVIEWS_ITEM_DELETED'));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		DJReviewsAPI::recalculate();
		
		$this->removeAltaUserPoints($item);
		
		return true;
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowAdd($data = array())
	{
		$user = JFactory::getUser();

		$auth =  (bool)($user->authorise('review.create', $this->option) || $user->authorise('core.create', $this->option));
		
		if (!$auth) {
			return false;
		}
		
		//if (!$user->authorise('core.create', $this->option)) {
			// trigger the plugin for additional checks
			$object		= $this->getModel()->getObject($data['object_id']);
			if (!$object) {
				return false;
			}
			
			if (trim($object->plugin) == '') {
				return $auth;
			}
			
			$plugin_parts = explode('.', $object->plugin, 2);
			JPluginHelper::importPlugin($plugin_parts[0], $plugin_parts[1]);
			
			$dispatcher = JEventDispatcher::getInstance();
			$result = $dispatcher->trigger('onDJReviewsAuthorise', array($data, $object, 'create'));
			if (in_array(false, $result, true))
			{
				return false;
			}
		//}
		
		return $auth;
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id') {
		// Initialise variables.
		$recordId	= (int) isset($data[$key]) ? $data[$key] : 0;
		$user		= JFactory::getUser();
		$userId		= $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', $this->option)) {
			return true;
		}

		$ownerId	= (int) isset($data['created_by']) ? $data['created_by'] : 0;
		if (empty($ownerId) && $recordId) {
			// Need to do a lookup from the model.
			$record		= $this->getModel()->getItem($recordId);

			if (empty($record)) {
				return false;
			}

			$ownerId = $record->created_by;
		}

		return (bool) ($ownerId == $userId && $user->authorise('review.edit.own', $this->option));
	}

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowSave($data, $key = 'id')
	{
		$recordId = isset($data[$key]) ? $data[$key] : '0';

		$object_id = (isset($data['object_id'])) ? (int)$data['object_id'] : false;
		$rating_group_id= (isset($data['rating_group_id'])) ? (int)$data['rating_group_id'] : false;
		$object_type= (isset($data['object_type'])) ? $data['object_type'] : false;

		if (!$object_id || !$rating_group_id || !$object_type) {
			return false;
		}

		$db = JFactory::getDbo();
		$db->setQuery('select * from #__djrevs_objects WHERE id='.(int)$object_id);
		$object = $db->loadObject();

		if (!$object) {
			return false;
		}

		if ($object->rating_group_id != $rating_group_id) {
			return false;
		}
		if ($object->object_type != $object_type) {
			return false;
		}

		if ($recordId)
		{
			return $this->allowEdit($data, $key);
		}
		else
		{
			return $this->allowAdd($data);
		}
	}

	public function getModel($name = 'ReviewForm', $prefix = 'DJReviewsModel', $config = array('ignore_request' => true))
	{
		if (empty($name))
		{
			$name = $this->context;
		}

		return parent::getModel($name, $prefix, $config);
	}

	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		$model = $this->getModel();
		$table = $model->getTable();
		$data  = $app->input->post->get('jform', array(), 'array');

		$context = "$this->option.edit.$this->context";
		$task = $this->getTask();
		$checkin = property_exists($table, 'checked_out');

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $app->input->getInt($urlVar);

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			return false;
		}

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, true);

		if (!$form)
		{
			$this->setError($model->getError());

			return false;
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();
			
			$messages = array();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$this->setError($errors[$i]->getMessage());
				}
				else
				{
					$this->setError($errors[$i]);
				}
			}
			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($validData))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			return false;


		}
		// Save succeeded, so check-in the record.
		if ($checkin && $model->checkin($validData[$key]) === false)
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Check-in failed, so go back to the record and display a notice.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			
			return false;
		}

		$this->setMessage(JText::_('COM_DJREVIEWS_REVIEW_SUCCESSFULLY_ADDED'));

		$this->releaseEditId($context, $recordId);
		$app->setUserState($context . '.data', null);

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/api.php');
		DJReviewsAPI::recalculate();

		return true;
	}
	
	protected function _postSaveHook($model, $validData = array()) {
		$params = JComponentHelper::getParams('com_djreviews');
		
		$db = JFactory::getDbo();
		$db->setQuery('SELECT * FROM #__djrevs_objects WHERE id='.(int)$validData['object_id']);
		$object = $db->loadObject();
		
		$notificationSent = false;
		if (!empty($object) && trim($object->plugin) != '') {
			$plugin_parts = explode('.', $object->plugin, 2);
			JPluginHelper::importPlugin($plugin_parts[0], $plugin_parts[1]);
			
			$dispatcher = JEventDispatcher::getInstance();
			$results = $dispatcher->trigger('onDJReviewsEmailNotification', array($validData, $object, $model));
			foreach ($results as $r) {
				if ($r === true) {
					$notificationSent = true;
					break;
				}
			}
		}
		
		if (!$notificationSent) {
			$subject = $validData['id']== 0 ? JText::_('COM_DJREVIEWS_MAIL_REVIEW_ADDED') : JText::_('COM_DJREVIEWS_MAIL_REVIEW_CHANGED');
			
			$body = JText::_('COM_DJREVIEWS_MAIL_REVIEW_HEADING').'<br /><br />';
			
			$body .= JText::_('COM_DJREVIEWS_AUTHOR').':<br />';
			$body .= $validData['user_name'].' ('.$validData['user_login'].')' . '<br /><br />';
			
			$body .= JText::_('COM_DJREVIEWS_EMAIL').':<br />';
			$body .= $validData['email'] . '<br /><br />';
			
			if (isset($validData['title'])) {
				$body .= JText::_('COM_DJREVIEWS_TITLE').':<br />';
				$body .= $validData['title'] . '<br /><br />';
			}
			if (isset($validData['message'])) {
				$body .= JText::_('COM_DJREVIEWS_MESSAGE').':<br />';
				$body .= $validData['message'] . '<br /><br />';
			}
			
			if (!empty($object)) {
				$body .= JText::_('COM_DJREVIEWS_OBJECT').':<br />';
				$body .= $object->name . '<br /><br />';
				
				$body .= JText::_('COM_DJREVIEWS_OBJECT_LINK').':<br />';
				$body .= '<a href="'.JRoute::_($object->link, true, 2).'">'.JRoute::_($object->link, true, 2).'</a>' . '<br /><br />';
			}
			
			
			if ($params->get('email_notifications', 1)) {
				$admin_body = $body;
				
				if (isset($validData['published'])) {
					if ($validData['published'] == 0) {
						$admin_body .= JText::_('COM_DJREVIEWS_MAIL_REVIEW_NOT_PUBLISHED').'<br/>';
					} else {
						$admin_body .= JText::_('COM_DJREVIEWS_MAIL_REVIEW_PUBLISHED').'<br />';
					}
				}
				
				$admin_body .= '<a href="'.JURI::base().'administrator/index.php?option=com_djreviews&amp;view=reviews&amp;filter_search='.urlencode('id:'.$model->getState('reviewform.id')).'">'.JText::_('COM_DJREVIEWS_MAIL_REVIEW_BACK_END_LINK').'</a>';
				$this->sendNotification($subject, $admin_body);
			}
			
			
			if (!empty($object) && trim($object->plugin) != '') {
				$plugin_parts = explode('.', $object->plugin, 2);
				JPluginHelper::importPlugin($plugin_parts[0], $plugin_parts[1]);
				
				$dispatcher = JEventDispatcher::getInstance();
				$results = $dispatcher->trigger('onDJReviewsNotification', array($validData, $object, $model));
				foreach ($results as $r) {
					if ($r != '' && strpos($r, '@') !== false) {
						$this->sendNotification($subject, $body, $r);
						break;
					}
				}
			}
		}
			
		$this->addAltaUserPoints($validData, $object);
	}
	
	public function report() {
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
	
		die('NOT SUPPORTED YET');
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
			
		$mail->setSubject($subject. ' : '. $sitename);
		$mail->setBody($body);
		$mail->isHtml(true);
		$sent = $mail->Send();
	
		return $sent;
	}
	
	protected function addAltaUserPoints($data, $object) {
		$user = JFactory::getUser();
		if (!$user->id || $user->guest || $data['id'] > 0 || !$data['created_by']) {
			return;
		}
		
		$aup	= JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_altauserpoints' . DIRECTORY_SEPARATOR . 'helper.php';
		if ( JFile::exists( $aup ) )
		{
			require_once( $aup );
			$aupid = AltaUserPointsHelper::getAnyUserReferreID( (int)$data['created_by'] );
			AltaUserPointsHelper::newpoints('plgaup_djreviews_post_review', $aupid);
		}
	}
	
	protected function removeAltaUserPoints($item) {
		$user = JFactory::getUser();
		if (!$user->id || $user->guest || !$item->created_by) {
			return;
		}
	
		$aup	= JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_altauserpoints' . DIRECTORY_SEPARATOR . 'helper.php';
		if ( JFile::exists( $aup ) )
		{
			require_once( $aup );
			$aupid = AltaUserPointsHelper::getAnyUserReferreID( (int)$item->created_by );
			AltaUserPointsHelper::newpoints('plgaup_djreviews_delete_review', $aupid);
			
		}
	}
}
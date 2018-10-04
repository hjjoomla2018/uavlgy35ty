<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

use Joomla\Registry\Registry;

// No direct access.
defined('_JEXEC') or die;


class Djcatalog2ModelQuestionform extends JModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Quotes', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.questionform', 'questionform', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();
	
		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);
	
			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
		}
	
		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		
		$item = JArrayHelper::toObject($properties, 'JObject');
		
		if (!is_array($item->items)) {
			if (isset($item->id)) {
				$this->_db->setQuery('SELECT * FROM #__djc2_quote_items WHERE quote_id=\''.$item->id.'\'');
				$item->items = $this->_db->loadObjectList();
			} else {
				$item->items = array();
			}
		}

		if (property_exists($item, 'params'))
		{
			$registry = new Registry();
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}
	
		return $item;
	}
	

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.query.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}

	public function delete(&$cid) {
		if (parent::delete($cid)) {
			
			$cids = implode(',', $cid);
			
			$this->_db->setQuery("delete from #__djc2_quote_items WHERE quote_id IN ( ".$cids." )");
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		return false;
	}
	
	public function save($data) {
		
		$new_status = $data['status'];
		$old_status = false;
		
		if ((int)$data['id'] > 0) {
			$table = $this->getTable();
			$table->load((int)$data['id']);
			$old_status = $table->status;
		}
		
		if (!parent::save($data)) {
			return false;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__djc2_quote_items')->where('quote_id='.$data['id']);
		$db->setQuery($query);
		$data['items'] = $db->loadAssocList();
		return $this->_sendEmail($data);
		
		return true;
	}
	
	private function _sendEmail($order)
	{
		require_once JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'html.php';
		require_once JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'djcatalog2.php';
		
		$app		= JFactory::getApplication();
		$params 	= JComponentHelper::getParams('com_djcatalog2');
		$config 	= JFactory::getConfig();
		
		$mailfrom	= $config->get('mailfrom');
		$fromname	= $config->get('fromname');
		$sitename	= $config->get('sitename');
		
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
		
		$recipient_list = array_unique($recipient_list);
		
		$subject	= JText::sprintf('COM_DJCATALOG2_EMAIL_QUOTE_UPDATE_SUBJECT', $sitename);

		$client_body = DJCatalog2HtmlHelper::getEmailTemplate($order, 'quote_update');
		$admin_body = DJCatalog2HtmlHelper::getEmailTemplate($order, 'admin.quote_update');
		
		// Send an email to customer
		$mail = JFactory::getMailer();
		
		foreach ($recipient_list as $recipient) {
			$mail->addRecipient(trim($recipient));
		}
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($admin_body);
		$mail->isHtml(true);
		$mail_sent = $mail->Send();
		
		// Send an email to customer
		$mail = JFactory::getMailer();
		$mail->addRecipient($order['email']);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($client_body);
		$mail->isHtml(true);
		$mail_sent = $mail->Send();
		
		return $mail_sent;
	}
}
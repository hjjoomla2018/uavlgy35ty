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

class Djcatalog2ModelOrderform extends JModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';
	public $invoiceStatuses = array('C', 'P', 'F');
	
	
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	public function getTable($type = 'Orders', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		
		// Get the form.
		$form = $this->loadForm('com_djcatalog2.order', 'order', array('control' => 'jform', 'load_data' => $loadData));
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
				$this->_db->setQuery('SELECT * FROM #__djc2_order_items WHERE order_id=\''.$item->id.'\'');
				$item->items = $this->_db->loadObjectList();
			} else {
				$item->items = array();
			}
		}
		
		if (property_exists($item, 'params'))
		{
			$registry = new Registry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}
		
		return $item;
	}
	
	
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.order.data', array());
		
		if (empty($data)) {
			$data = $this->getItem();
		}
		
		return $data;
	}
	
	protected function prepareTable($table)
	{
		$db = JFactory::getDbo();
		
		if (empty($table->order_number)) {
			$db->setQuery('select max(order_number) from #__djc2_orders');
			$current = (int)$db->loadResult();
			$table->order_number = $current + 1;
		}
	}
	
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}
	
	public function delete(&$cid) {
		if (parent::delete($cid)) {
			
			$cids = implode(',', $cid);
			
			$this->_db->setQuery("delete from #__djc2_order_items WHERE order_id IN ( ".$cids." )");
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
		
		$id = $this->getState($this->getName() . '.id');
		$table = $this->getTable();
		$db = JFactory::getDbo();
		
		if ($table->load((int)$id) && $table->status != $old_status && !empty($table->email)) {
			
			if (in_array($table->status, $this->invoiceStatuses) && empty($table->invoice_number)) {
				$date = JFactory::getDate();
				
				$invoiceDate = (empty($table->invoice_date) || $table->invoice_date == '0000-00-00 00:00:00') ? $date->toSql(true) : $table->invoice_date;
				$paymentDate = (empty($table->payment_date) || $table->payment_date == '0000-00-00 00:00:00') ? $date->toSql(true) : $table->payment_date;
				$serviceDate = (empty($table->service_date) || $table->service_date == '0000-00-00 00:00:00') ? $date->toSql(true) : $table->service_date;
				
				//$invoiceNumber = $this->getNextInvoiceNumber($date);
				require_once JPATH_ROOT.'/components/com_djcatalog2/helpers/invoice.php';
				$invoiceCounter = 0;
				$invoiceNumber = DJCatalog2HelperInvoice::getNext($invoiceCounter, $date);
				
				$query = $db->getQuery(true);
				$query->update('#__djc2_orders');
				$query->set('invoice_counter='.$invoiceCounter);
				$query->set('invoice_number='.$db->quote($invoiceNumber));
				$query->set('invoice_date='.$db->quote($invoiceDate));
				$query->set('payment_date='.$db->quote($paymentDate));
				$query->set('service_date='.$db->quote($serviceDate));
				$query->where('id='.(int)$table->id);
				$db->setQuery($query);
				
				if (!$db->execute()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
				
				DJCatalog2HelperInvoice::update($invoiceCounter, $date);
				
				$table->invoice_counter = $invoiceCounter;
				$table->invoice_number = $invoiceNumber;
				$table->invoice_date = $invoiceDate;
				$table->payment_date = $paymentDate;
				$table->service_date = $serviceDate;
			}
			
			$data = $table->getProperties();
			foreach ($data['items'] as $k=>$v) {
				$data['items'][$k] = JArrayHelper::fromObject($v);
			}
			
			return $this->_sendEmail($data);
		}
		
		return true;
	}
	public function set_status($id, $value, $notify = true) {
		
		$date = JFactory::getDate();
		$db = JFactory::getDbo();
		
		$db->setQuery('update #__djc2_orders SET status='.$db->quote($value[0]).' WHERE id='.(int)$id);
		
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		
		$table = $this->getTable('Orders');
		
		if ($table->load((int)$id)) {
			if (in_array($table->status, $this->invoiceStatuses) && empty($table->invoice_number)) {
				$invoiceDate = (empty($table->invoice_date) || $table->invoice_date == '0000-00-00 00:00:00') ? $date->toSql(true) : $table->invoice_date;
				$paymentDate = (empty($table->payment_date) || $table->payment_date == '0000-00-00 00:00:00') ? $date->toSql(true) : $table->payment_date;
				$serviceDate = (empty($table->service_date) || $table->service_date == '0000-00-00 00:00:00') ? $date->toSql(true) : $table->service_date;
				
				require_once JPATH_ROOT.'/components/com_djcatalog2/helpers/invoice.php';
				
				$invoiceCounter = 0;
				$invoiceNumber = DJCatalog2HelperInvoice::getNext($invoiceCounter, $date);
				
				$query = $db->getQuery(true);
				$query->update('#__djc2_orders');
				$query->set('status='.$db->quote($value));
				$query->set('invoice_counter='.$invoiceCounter);
				$query->set('invoice_number='.$db->quote($invoiceNumber));
				$query->set('invoice_date='.$db->quote($invoiceDate));
				$query->set('payment_date='.$db->quote($paymentDate));
				$query->set('service_date='.$db->quote($serviceDate));
				$query->where('id='.(int)$table->id);
				$db->setQuery($query);
				
				if (!$db->execute()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
				
				DJCatalog2HelperInvoice::update($invoiceCounter, $date);
				
				$table->invoice_counter = $invoiceCounter;
				$table->invoice_number = $invoiceNumber;
				$table->invoice_date = $invoiceDate;
				$table->payment_date = $paymentDate;
				$table->service_date = $serviceDate;
			}
			
			$data = $table->getProperties();
			foreach ($data['items'] as $k=>$v) {
				$data['items'][$k] = JArrayHelper::fromObject($v);
			}
			
			if ($notify) {
				return $this->_sendEmail($data);
			}
		}
		
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
		
		$subject	= JText::sprintf('COM_DJCATALOG2_EMAIL_NEW_STATUS_SUBJECT', $order['order_number'], $sitename);
		
		$client_body = DJCatalog2HtmlHelper::getEmailTemplate($order, 'order_status');
		
		// Send an email to customer
		$mail = JFactory::getMailer();
		
		//$mail->addRecipient($mailfrom);
		$mail->addRecipient($order['email']);
		
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($client_body);
		$mail->isHtml(true);
		$mail_sent = $mail->Send();
		
		return $mail_sent;
	}
}
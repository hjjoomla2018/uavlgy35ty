<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.application.component.controlleradmin');


class Djcatalog2ControllerOrders extends JControllerAdmin
{
	public function getModel($name = 'Order', $prefix = 'Djcatalog2Model', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	public function change_status (){
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$statuses = JFactory::getApplication()->input->get('status_change', array(), 'array');
		$notifications = JFactory::getApplication()->input->get('status_notify', array(), 'array');
		
		if (empty($cid) || empty($statuses))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);
				
			$cid = $cid[0];
				
			$value = (isset($statuses[$cid])) ? $statuses[$cid] : false;
			$notify = (isset($notifications[$cid])) ? (bool)$statuses[$cid] : false;
				
			if (!$value) {
				JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
			} else {
				// Get the model.
				$model = $this->getModel();
				
				// Publish the items.
				try
				{
					$model->set_status($cid, $value, $notify);
				
					$this->setMessage(JText::_('COM_DJCATALOG2_STATUS_CHANGED'));
				}
				catch (Exception $e)
				{
					$this->setMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
				}
			}
		}
		$extension = JFactory::getApplication()->input->get('extension');
		$extensionURL = ($extension) ? '&extension=' . $extension : '';
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
		
	}
	
	protected function importTCPDF() {
		$libfile = JPATH_LIBRARIES . '/tcpdf/tcpdf.php';
		if (JFile::exists($libfile) == false) {
			return false;
		}
		
		require_once $libfile;
		return true;
	}
	
	public function invoices_selected() {
		if (!JSession::checkToken('post') && !JSession::checkToken('get')) jexit( 'Invalid Token' );
	
		$app = JFactory::getApplication();
	
		// Get items to publish from the request.
		$cid    = $app->input->get('cid', array(), 'array');

		if (empty($cid)) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		}
		else {
			if (count($cid) == 1) {
				$db = JFactory::getDbo();
				$db->setQuery('select invoice_number from #__djc2_orders where id = '.(int)$cid[0]);
				$filename = $db->loadResult();
				if (!empty($filename)) {
					$filename = JFile::makeSafe(str_replace('/','-',$filename)).'.pdf';
					if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'djcatalog2'.DS.'export'.DS.'invoices'.DS.$filename)) {
						JFile::delete(JPATH_ROOT.DS.'media'.DS.'djcatalog2'.DS.'export'.DS.'invoices'.DS.$filename);
					}
					$app->input->set('export_file', $filename);
				}
			}
			return $this->invoices_filtered($cid);
		}
	}
	
	public function proforma_selected() {
		if (!JSession::checkToken('post') && !JSession::checkToken('get')) jexit( 'Invalid Token' );
		
		$app = JFactory::getApplication();
		
		// Get items to publish from the request.
		$cid    = $app->input->get('cid', array(), 'array');
		
		if (empty($cid)) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		}
		else {
			if (count($cid) == 1) {
				$db = JFactory::getDbo();
				$db->setQuery('select order_number from #__djc2_orders where id = '.(int)$cid[0]);
				$filename = $db->loadResult();
				if (!empty($filename)) {
					$filename = JFile::makeSafe(str_replace('/','-', 'proforma-'.$filename)).'.pdf';
					if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'djcatalog2'.DS.'export'.DS.'invoices'.DS.$filename)) {
						JFile::delete(JPATH_ROOT.DS.'media'.DS.'djcatalog2'.DS.'export'.DS.'invoices'.DS.$filename);
					}
					$app->input->set('export_file', $filename);
				}
			}
			return $this->invoices_filtered($cid);
		}
	}
	
	public function invoices_filtered($cid = array()) {
		if (!JSession::checkToken('post') && !JSession::checkToken('get')) jexit( 'Invalid Token' );
		
		if (!$this->importTCPDF()) {
			$this->setRedirect( 'index.php?option=com_djcatalog2&view=orders', JText::_('COM_DJCATALOG2_TCPDF_LIB_IS_MISSING'), 'error' );
			return false;
		}

		error_reporting(0);
		@ini_set('display_errors', 0);
	
		$app = JFactory::getApplication();
		$task   = $this->getTask();
	
		$path = JPATH_ROOT.DS.'media'.DS.'djcatalog2'.DS.'export'.DS.'invoices';
		if (!JFolder::exists($path)) {
			JFolder::create($path);
		}
	
		if (!is_writable($path)) {
			$this->setRedirect( 'index.php?option=com_djcatalog2&view=orders', JText::_('COM_DJCATALOG2_FOLDER_NOT_WRITABLE').' '.$path, 'error' );
			return false;
		}
		
		$layout = ($task == 'proforma_selected') ? 'proforma' : 'invoice';
	
		$model = $this->getModel('Orders');

		$start = $app->input->getInt('start', 0);
		$limit = 10;
	
		$model->setState('list.limit', $limit);
		$model->setState('list.start', $start);
		if ($layout != 'proforma') {
			$model->setState('filter.invoice', true);
		}
		
		$date_from = $model->getUserStateFromRequest('com_djcatalog2.orders.filter.date_from', 'filter_date_from');
		$model->setState('filter.date_from', $date_from);
		
		$date_to = $model->getUserStateFromRequest('com_djcatalog2.orders.filter.date_to', 'filter_date_to');
		$model->setState('filter.date_to', $date_to);
		
		if (($task == 'invoices_selected' || $task == 'proforma_selected') && count($cid) > 0) {
			$limit = $start = 0;
			JArrayHelper::toInteger($cid);
	
			$model->setState('list.limit', 0);
			$model->setState('list.start', 0);
	
			$model->setState('filter.ids', implode(',',$cid));
		}
	
		$items =  $model->getItems();
		
		if (empty($items)) {
			$this->setRedirect( 'index.php?option=com_djcatalog2&view=orders', JText::_('COM_DJCATALOG2_LIST_IS_EMPTY').' '.$path, 'error' );
			return false;
		}
	
		$filename = $app->input->get('export_file', 'invoice-export-'.date("Y-m-d_H-i-s").'.pdf', 'raw');
	
		//$pdf = new DJCatalog2InvoiceFPDI('P', 'mm', 'A4', true, 'UTF-8', false);
	
		$pdf = new TCPDF();
		
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->setFontSubsetting(true);
	
		$pdf->SetFont('freesans', '', 9, '', true);
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(true);
		$pdf->SetFooterMargin('15px');
	
		$pageCount = 0;
		if (JFile::exists($path.DS.$filename)) {
			$pageCount = $pdf->setSourceFile($path.DS.$filename);
			if ($pageCount > 0) {
				for ($i = 1; $i <= $pageCount; $i++) {
					//$pdf->importPage($i);
					$pdf->AddPage();
					$tplIdx = $pdf->importPage($i);
					$pdf->useTemplate($tplIdx);
					$pdf->endPage();
				}
			}
		}
	
		$db = JFactory::getDbo();
	
		foreach ($items as $id => $item) {
	
			$db->setQuery('select * from #__djc2_order_items where order_id='.$item->id);
			$items[$id]->items = $db->loadObjectList();
			
			$pdf->AddPage();
			$pdf->_intCurPage = 1;
			$pdf->_intFootNo = $item->invoice_number; 
			$html = DJCatalog2HtmlHelper::getThemeLayout($item, $layout, 'pdf');
			$pdf->writeHTML($html, true, false, true, false, '');
	
		}
	
		$pdf->Output($path.DS.$filename, 'F');
	
		$pagination = $model->getPagination();
	
		if ($pagination->get('pages.total') > $pagination->get('pages.current')) {
			header("refresh: 0; url=".JURI::base().'index.php?option=com_djcatalog2&task=orders.invoices_filtered&start='.($start+$limit).'&export_file='.$filename.'&'.JSession::getFormToken().'=1');
			echo '<p>'.$pagination->get('pages.current').' / '.$pagination->get('pages.total').'</p>';
		} else {
			//header("refresh: 0; url=".JURI::base().'index.php?option=com_djcatalog2&view=items');
			$file_link = '<a href="'.JRoute::_('index.php?option=com_djcatalog2&task=download_file&path='.base64_encode('media/djcatalog2/export/invoices/'.$filename)).'">'.$filename.'</a>';
			$this->setRedirect( 'index.php?option=com_djcatalog2&view=orders', JText::_('COM_DJCATALOG2_EXPORT_SUCCESFULL').' '.$file_link );
		}
	
		return true;
	}
	
	public function save_counters(){
		if (!JSession::checkToken('post') && !JSession::checkToken('get')) jexit( 'Invalid Token' );
		
		$app = JFactory::getApplication();
		$input = $app->input;
		
		$counters = $input->get('djc_counters', array(), 'array');
		
		$total = count($counters);
		if (!$total) {
			$this->setRedirect( 'index.php?option=com_djcatalog2&view=orders&layout=invcounters&tmpl=component', JText::_('COM_DJCATALOG2_COUNTERS_NOTHING_TO_UPDATE'), 'warning');
			return false;
		}
		
		require_once JPATH_ROOT.'/components/com_djcatalog2/helpers/invoice.php';
		$done = 0;
		foreach($counters as $year => $value) {
			if (is_numeric($value) && (int)$value >= 0) {
				DJCatalog2HelperInvoice::updateYear((int)$value, $year);
				$done++;
			} else {
				$app->enqueueMessage(JText::sprintf('COM_DJCATALOG2_COUNTERS_INVALID_FOR_YEAR', $year), 'warning');
			}
		}
		
		$this->setRedirect( 'index.php?option=com_djcatalog2&view=orders&layout=invcounters&tmpl=component', JText::_('COM_DJCATALOG2_COUNTERS_UPDATED'));
		return true;
	}
}
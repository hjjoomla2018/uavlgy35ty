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

//jimport('joomla.plugin.plugin');

class plgDjcatalog2paymentStandard extends JPlugin {
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	protected function isAllowed($plgInfo, $type = 'djcatalog2payment') {
		if (empty($plgInfo)) return  false;
		return (bool)($plgInfo->plugin == $this->_name && $this->_type == $type);
	}
	
	public function onContentPrepareForm($form, $data) {
		if ($form->getName() != 'com_djcatalog2.payment') {
			return ;
		}
		
		$plugin = '';
		
		if (!empty($data) && !empty($data->plugin)) {
			$plugin = $data->plugin;
		} else {
			$jform = JFactory::getApplication()->input->get('jform', array(), 'array');
			if (!empty($jform) && isset($jform['plugin'])) {
				$plugin = $jform['plugin'];
			}
		}
		
		if ($plugin != $this->_name) {
			return true;
		}
		
		return $form->loadFile(dirname(__FILE__).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'configuration.xml', false);
	}
	
	public function onDJC2BeforeSaveOrder($context, $table, $isNew, $plgInfo) {
		if ($context != 'com_djcatalog2.checkout.payment' || !$this->isAllowed($plgInfo)) {
			return;
		}
		
		if ($plgInfo->params->get('proforma')) {
			$days = (int)$plgInfo->params->get('paymentDate', 3);
			$days = max(0, $days);
			$date = JFactory::getDate((time() + (86400 * $days)));
			$table->payment_date = $date->toSql(true);
		}
		
	}
	
	public function onDJC2AfterSaveOrder($context, $table, $isNew, $plgInfo) {
		if ($context != 'com_djcatalog2.checkout.payment' || !$this->isAllowed($plgInfo)) {
			return;
		}
	}
	
	public function onDJC2CheckoutDetailsDisplay($context, $plgInfo) {
		if ($context != 'com_djcatalog2.checkout.payment' || !$this->isAllowed($plgInfo)) {
			return;
		}
		
		$html = '';
		if (trim(strip_tags($plgInfo->description)) != '' ) {
			$html = '<h4>'.$plgInfo->name.'</h4>';
			$html .= $plgInfo->description;
		}
		return $html;
	}
	
	public function onDJC2OrderDetailsDisplay($context, $order, $plgInfo) {
		if ($context != 'com_djcatalog2.order.payment' || !$this->isAllowed($plgInfo)) {
			return;
		}
        
        if ($order->status != 'A' && $order->status != 'N') {
            return;
        }
		
		$html = '';
		if (trim(strip_tags($plgInfo->description)) != '' || $plgInfo->params->get('proforma')) {
			$html = '<h4>'.$plgInfo->name.'</h4>';
			$html .= $plgInfo->description;
            
			if ($plgInfo->params->get('proforma') && $this->importTCPDF(false)) {
                $paymentUrl = JRoute::_('index.php?option=com_djcatalog2&task=paymentProcess&oid='.$order->id.'&plg='.$plgInfo->plugin.'&plgid='.$plgInfo->id);
                $html .= '<a href="'.$paymentUrl.'" class="button btn proformabtn">'.JText::_('PLG_DJCATALOG2PAYMENT_STANDARD_PROFORMA_BTN').'</a>';
            }
		}
        
		return $html;
	}
    
    public function onDJC2PaymentProcess($context, $order, $plgInfo) {
        if ($context != 'com_djcatalog2.order.payment' || !$this->isAllowed($plgInfo)) {
            return;
        }
        
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        if ($user->id != $order->user_id || $user->guest) {
        	$token = $app->getUserStateFromRequest('com_djcatalog2.order.token', 'token', null, 'string');
        	if ((strcmp($token, $order->token) !== 0) || $order->token ==  '') {
        		return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
        	}
        }
        
        if (!$this->importTCPDF(true)) {
        	return;
        }
        
        $filename = $order->order_number.'-proforma.pdf';
        
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->setFontSubsetting(true);
        
        $pdf->SetFont('freesans', '', 9, '', true);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(true);
        $pdf->SetFooterMargin('15px');
        
        $pdf->AddPage();
        $pdf->_intCurPage = 1;
        $pdf->_intFootNo = '';
        $html = DJCatalog2HtmlHelper::getThemeLayout($order, 'proforma', 'pdf');
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $pdf->Output($filename, 'D');
        $app->close();
    }
    
    protected function getProforma($data, $plgInfo) {
    	return DJCatalog2HtmlHelper::getThemeLayout($data, 'proforma', 'pdf');
    }
    
    protected function importTCPDF($require = false) {
    	$libfile = JPATH_LIBRARIES . '/tcpdf/tcpdf.php';
    	if (JFile::exists($libfile) == false) {
    		return false;
    	}
    	
    	if ($require) {
    		require_once $libfile;
    	}
    	
    	return true;
    }
}



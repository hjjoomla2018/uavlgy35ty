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

class plgDjcatalog2paymentPaypal extends JPlugin {
	
	protected $currency;
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
        $cparams = JComponentHelper::getParams('com_djcatalog2');
        $this->currency = $cparams->get('cart_currency', 'USD');
		$this->debug = $this->params->get('debug', 0);
		$this->loadLanguage();
		
		require_once JPATH_ROOT.'/plugins/djcatalog2payment/paypal/lib/paypal_ipn.php';
	}
	
	protected function isAllowed($plgInfo, $type = 'djcatalog2payment') {
		return (bool)($plgInfo->plugin == $this->_name && $this->_type == $type);
	}
	
	public function onContentPrepareForm($form, $data) {
		if ($form->getName() != 'com_djcatalog2.payment') {
			return;
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
        
		$app = JFactory::getApplication();
		
        $message = '';
        if ($app->input->get('view') == 'order' && $app->input->get('error')) {
            $message = JText::_('PLG_DJCATALOG2PAYMENT_PAYPAL_ERROR_TEXT');
        } else if ($app->input->get('success') == '1') {
            $message = JText::_('PLG_DJCATALOG2PAYMENT_PAYPAL_RETURN_TEXT');
        }
        
        if ($order->status != 'A' && $order->status != 'N') {
            return;
        }
		
		$paymentUrl = JRoute::_('index.php?option=com_djcatalog2&task=paymentProcess&oid='.$order->id.'&plg='.$plgInfo->plugin.'&plgid='.$plgInfo->id);
		
		$html = '<div class="paypalinfo">';
		
		if ($message) {
            $html .= '<p class="paypalalert">'.$message.'</p>';
		} else if ($app->input->get('finished') == '1' && $plgInfo->params->get('autoredir', '1') == '1') {
        	$app->redirect(JRoute::_('index.php?option=com_djcatalog2&task=paymentProcess&oid='.$order->id.'&plg='.$plgInfo->plugin.'&plgid='.$plgInfo->id, false), 302);
		} 
		
        
		$html .= '<p class="paypalmessage">';
        $html .= JText::_('PLG_DJCATALOG2PAYMENT_PAYPAY_INFO_TEXT');
        $html .= '</p>';
        $html .= '<p class="paypalbutton">';
        $html .= '<a href="'.$paymentUrl.'" class="paypalbtn btn btn-primary">'.JText::_('PLG_DJCATALOG2PAYMENT_PAYPAL_BTN').'</a>';
        $html .= '</p>';
		$html .= '</div>';
		
		return $html;
	}
    
    public function onDJC2PaymentProcess($context, $order, $plgInfo) {
        if ($context != 'com_djcatalog2.order.payment' || !$this->isAllowed($plgInfo)) {
            return;
        }
        
        $emailId = $plgInfo->params->get('email_id');
		$testMode = $plgInfo->params->get('test_mode');
		
		if (empty($emailId)) {
			throw new Exception(JText::_('PLG_DJCATALOG2PAYMENT_PAYPAL_ERROR_MISSING_PARAMS'), 400);
		}
		
		$app = JFactory::getApplication();
		$websiteName = $app->get('sitename', JURI::base());
		
		
		
		$return = JRoute::_(DJCatalogHelperRoute::getOrderRoute($order->id).'&success=1', false, (JUri::getInstance()->isSSL() ? 1 : -1));
		$cancel_return = JRoute::_(DJCatalogHelperRoute::getOrderRoute($order->id).'&error=1', false, (JUri::getInstance()->isSSL() ? 1 : -1));
		$notify_url = JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&task=paymentResponse&plg='.$plgInfo->plugin.'&plgid='.$plgInfo->id.'&oid='.$order->id, false, (JUri::getInstance()->isSSL() ? 1 : -1));
		
		$cancel_return .= '&ts=' . time();
		$notify_url .= '&ts=' . time();
		$return .= '&ts=' . time();
		
		
		$description = JText::sprintf('PLG_DJCATALOG2PAYMENT_PAYPAL_ORDER_DESCRIPTION', $order->order_number, $websiteName);
		$currency = (!empty($order->currency)) ? $order->currency : $plgInfo->params->get('currency_code', $this->currency);;
		
		$amount = $order->grand_total;
		
		$urlpaypal="";
		if ($testMode){
			$urlpaypal="https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$urlpaypal="https://www.paypal.com/cgi-bin/webscr";
		}
		
		$html  = '<!doctype html><html lang="en-US"><head><meta charset="utf-8"><title>'.JText::_('COM_DJCATALOG2_PAYMENT_REDIRECTION').'</title></head>';
		$html .= '<body><div style="margin: auto; text-align: center;">';
		$html .= '<form method="post" action="'.$urlpaypal.'" name="paypalform">';
		$html .= '<p>'.JText::_('COM_DJCATALOG2_PAYMENT_REDIRECTION').'</p>';
		$html .= '<input type="hidden" name="cmd" value="_xclick" />';
		$html .= '<input id="custom" type="hidden" name="custom" value="'.$order->id.'" />';
		$html .= '<input type="hidden" name="business" value="'.$emailId.'" />';
		$html .= '<input type="hidden" name="currency_code" value="'.$currency.'" />';
		$html .= '<input type="hidden" name="item_name" value="'.$description.'" />';
		$html .= '<input type="hidden" name="amount" value="'.$amount.'" />';
		$html .= '<input type="hidden" name="charset" value="utf-8" />';
		$html .= '<input type="hidden" name="cancel_return" value="'.($cancel_return).'" />';
		$html .= '<input type="hidden" name="notify_url" value="'.($notify_url).'" />';
		$html .= '<input type="hidden" name="return" value="'.($return).'" />';
		$html .= '<noscript><input type="submit"  value="' . JText::_('COM_DJCATALOG2_PAYMENT_REDIRECTION_BUTTON') . '" /></noscript>';
		$html .= '</form></div>';
		
		$html .= ' <script type="text/javascript">';
		$html .= ' setTimeout(function(){ document.paypalform.submit(); }, 500);';
		$html .= ' </script></body></html>';
		
		echo $html;
		
		$app->close();
    }
    
	public function onDJC2PaymentDiscover($context, $plgInfo) {
		if ($context != 'com_djcatalog2.order.payment' || !$this->isAllowed($plgInfo)) {
			return;
		}
		
		$this->log('[onDJC2PaymentDiscover] '.$plgInfo->plugin);
		
		$app = JFactory::getApplication();
		$oid = $app->input->getInt('oid');
		if ($oid > 0) {
			
			$this->log('[onDJC2PaymentDiscover] order id: '.$oid);
			
			return $oid;
		}
	}
	
	public function onDJC2PaymentResponse($context, $order, $model, $plgInfo) {
		if ($context != 'com_djcatalog2.order.payment' || !$this->isAllowed($plgInfo)) {
			return;
		}
		
		$this->log('[onDJC2PaymentResponse] '.$plgInfo->plugin);
		
		$emailId = $plgInfo->params->get('email_id');
		$testMode = $plgInfo->params->get('test_mode');
		
		$app = JFactory::getApplication();
		
		if (empty($emailId)) {
			throw new Exception(JText::_('PLG_DJCATALOG2PAYMENT_PAYPAL_ERROR_MISSING_PARAMS'), 400);
		}
		
		$paypal_info = $_POST;
		
		$this->log('[onDJC2PaymentResponse] POST DATA init: ');
		$this->log(print_r($paypal_info, true));
		
		$paypal_ipn = new paypal_ipn($paypal_info);
		
		$this->log('[onDJC2PaymentResponse] paypal_ipn after init: ');
		$this->log($paypal_ipn);
		
		foreach ($paypal_ipn->paypal_post_vars as $key=>$value)
		{
			if (getType($key)=="string")
			{
				eval("\$$key=\$value;");
			}
		}
		
		$this->log('[onDJC2PaymentResponse] paypal_ipn before send response: ');
		$this->log($paypal_ipn);
		
		$paypal_ipn->send_response($testMode);
		
		$this->log('[onDJC2PaymentResponse] paypal_ipn after send response: ');
		$this->log($paypal_ipn);
		
		if (!$paypal_ipn->is_verified())
		{
			$this->log('[onDJC2PaymentResponse] failed verification');
			$app->close();
		}
		
		if(floatval($app->input->getString('mc_gross')) != floatval($order->grand_total)){
			$this->log('[onDJC2PaymentResponse] failed verification price fraud');
			$this->log('Price Paypal '.$app->input->getString('mc_gross'));
			$this->log('Price Order '.$order->grand_total);
			$app->close();
		}
		
		
		$status = $paypal_ipn->get_payment_status();
		//$txn_id=$paypal_ipn->paypal_post_vars['txn_id'];
		
		$this->log('[onDJC2PaymentResponse] payment status: '.$status);
		
		if(($status=='Completed') || ($status=='Pending' && $testMode==1)){
			
			$this->log('[onDJC2PaymentResponse] order status: '.$order->status);
			
			// confirm the order unless it has already been rejected or confirmed
			if ($order->status == 'A' || $order->status == 'N') {
				
				$this->log('[onDJC2PaymentResponse] payment status changed');
				
				$model->changeStatus($order, 'P', true, true, JText::_('PLG_DJCATALOG2PAYMENT_PAYPAL_PAYMENT_COMPLETED')); 
			}
		}
		
		$app->close();
	}

	private function log($msg) {
			
		if(!$this->debug) return;
		
		$fp = fopen(__DIR__.'/lib/logs.txt', 'a');
		fwrite($fp, print_r($msg, true));
		fwrite($fp, "\n");
		fclose($fp);
	}
}



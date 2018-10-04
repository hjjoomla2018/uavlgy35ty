<?php 

if(!class_exists('paypal_ipn')) {

class paypal_ipn
{
	var $paypal_post_vars;
	var $paypal_response;
	var $timeout;
	var $error_email;
	function __construct($paypal_post_vars) {
		$this->paypal_post_vars = $paypal_post_vars;
		$this->timeout = 120;
	}
	function send_response($account_type)
	{
		$fp  = '';
		if($account_type == 1)
		{
			$fp = @fsockopen( "www.sandbox.paypal.com", 80, $errno, $errstr, 120 );
		}else {
			//$fp = @fsockopen( "www.paypal.com", 80, $errno, $errstr, 120 );
			$fp = @fsockopen( "ssl://www.paypal.com", 443, $errno, $errstr, 30 );
		}
		if (!$fp) {
			$this->error_out("PHP fsockopen() error: " . $errstr , "");
		} else {
			foreach($this->paypal_post_vars AS $key => $value) {
				if (@get_magic_quotes_gpc()) {
					$value = stripslashes($value);
				}
				$values[] = "$key" . "=" . urlencode($value);
			}
			$response = @implode("&", $values);
			$response .= "&cmd=_notify-validate";
			fputs( $fp, "POST /cgi-bin/webscr HTTP/1.0\r\n" );
			fputs( $fp, "Content-type: application/x-www-form-urlencoded\r\n" );
			fputs( $fp, "Content-length: " . strlen($response) . "\r\n\n" );
			fputs( $fp, "$response\n\r" );
			fputs( $fp, "\r\n" );
			$this->send_time = time();
			$this->paypal_response = "";

			while (!feof($fp)) {
				$this->paypal_response .= fgets( $fp, 1024 );

				if ($this->send_time < time() - $this->timeout) {
					$this->error_out("Timed out waiting for a response from PayPal. ($this->timeout seconds)" , "");
				}
			}
			fclose( $fp );
		}
	}
	function is_verified() {
		if( strstr($this->paypal_response,"VERIFIED") )
			return true;
		else
			$this->error_out("PAYPAL response: " . $this->paypal_response);
			return false;
	}
	function get_payment_status() {
		return $this->paypal_post_vars['payment_status'];
	}
	function error_out($message)
	{
		//$fp = fopen(__DIR__.'/logs.txt', 'a');
		fwrite($fp, $message);
		fclose($fp);
	}
}


}

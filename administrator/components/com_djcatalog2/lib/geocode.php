<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access.
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );

class DJCatalog2Geocode {
	
	static private $url = "https://maps.google.com/maps/api/geocode/json?sensor=false";

    public static function getLocation($address){
    	$params = JComponentHelper::getParams('com_djcatalog2');
    	$apiKey = $params->get('gm_api_key_server') ? 'key='.$params->get('gm_api_key_server') : '';
    	
        $url = self::$url."&address=".urlencode($address);
        
        if ($apiKey) {
        	$url .= '&' . $apiKey;
        }
        
        $resp_json = self::curl_file_get_contents($url);
        $resp = json_decode($resp_json, true);
        if($resp['status']='OK' && isset($resp['results'][0])){
            return $resp['results'][0]['geometry']['location'];
        }else{
            return false;
        }
    }
    
    public static function getLocationPostCode($post_code, $country=''){
    	$params = JComponentHelper::getParams('com_djcatalog2');
    	$apiKey = $params->get('gm_api_key_server') ? 'key='.$params->get('gm_api_key_server') : '';
    	
    	//$post_code = str_ireplace(array(' ','-'), array('',''), $post_code);
    	$url_zip = '';
    	if($country){
    		$url_zip = '&address='.urlencode($country);
    	}
    	$url = self::$url.$url_zip."&components=postal_code:".urlencode($post_code);
    	
    	if ($apiKey) {
    		$url .= '&' . $apiKey;
    	}
    	 
    	$resp_json = self::curl_file_get_contents($url);
    	$resp = json_decode($resp_json, true);
    	if($resp['status']='OK' && isset($resp['results'][0])){
    		return $resp['results'][0]['geometry']['location'];
    	}else{
    		return false;
    	}
    }


    private static function curl_file_get_contents($URL){
    	if (!in_array('curl', get_loaded_extensions())) {
    		return false;
    	}
        
    	$c = curl_init();
        
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($c, CURLOPT_URL, $URL);
        curl_setopt($c, CURLOPT_REFERER, JURI::root(false));
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        
        $contents = curl_exec ($c);
        
        if(curl_errno($c)) {
        	curl_close ($c);
        	return false;
        }
        
        curl_close ($c);

        return (empty($contents)) ? false : $contents;
    }
}

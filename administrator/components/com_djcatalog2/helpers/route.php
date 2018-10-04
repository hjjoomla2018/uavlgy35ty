<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access'); 

require_once JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_djcatalog2'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'route.php';

// usage: echo DJCatalog2HelperSiteRoute::buildRoute('getItemRoute', array('1:test', '1:test'), '&layout=promotion#plan-3', true);

class DJCatalog2HelperSiteRoute extends DJCatalog2HelperRoute {
	
	public static function buildRoute($function, $args = array(), $params = '', $xhtml = true) {
		$liveSite = substr(JUri::root(), 0, -1);
		$app    = JApplication::getInstance('Site');
		$router = $app->getRouter();
		$routed = self::call(array('DJCatalog2HelperRoute', $function), $args);
		$url = $router->build($liveSite .'/'. $routed . $params)->toString();
		
		$uri = JUri::getInstance();
		$substitute = $uri->toString(array('scheme', 'host', 'port'));
		
		$link = str_replace($liveSite . '/administrator', $substitute, $url);
		
		$link = preg_replace('/\s/u', '%20', $link);
		
		return ($xhtml) ? htmlspecialchars($link) : $link;
	}
	
	protected static function call($function, $args) {
		if (!is_callable($function)) {
			throw new InvalidArgumentException('Function not supported', 500);
		}
		$temp = array();
		foreach ($args as &$arg) {
			$temp[] = &$arg;
		}
		return call_user_func_array($function, $temp);
	}
}

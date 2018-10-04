<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'defines.djcatalog2.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'djcatalog2.php');

class DJCatalog2ThemeHelper {
	static $themeSet = null;
	static $themeName = null;
	
	public static function setThemeAssets() {
		if (!isset(self::$themeSet)) {
			$app = JFactory::getApplication();
			$document = JFactory::getDocument();
			$params = Djcatalog2Helper::getParams(); //$app->getParams('com_djcatalog2');
			
			$theme = self::getThemeName();
			
			JHtml::_('jquery.framework');
			
			if ($params->get('theme_bootstrap_css', false)) {
				JHtml::_('bootstrap.loadCss', true, JFactory::getDocument()->direction);
			}
			if ($params->get('theme_bootstrap_framework', true)) {
				JHtml::_('bootstrap.framework');
			}
			
			$document->addScriptDeclaration('
				window.DJC2BaseUrl = "'.JUri::base(true).'";
			');
			
			//JHTML::_('behavior.framework', true);
			//JHTML::_('behavior.modal');
			
			$lightboxes = array('slimbox', 'picbox', 'magnific');
			//$lightboxType = $params->get('lightbox_type', 'magnific');
			
			$lightboxType = 'magnific';
			
			//$slimboxJs = JUri::root(true).'/components/com_djcatalog2/assets/slimbox-1.8/js/slimbox.js';
			//$slimboxCss = JUri::root(true).'/components/com_djcatalog2/assets/slimbox-1.8/css/slimbox.css';
			
			//$lightboxes['slimbox']['css'] = $slimboxCss;
			//$lightboxes['slimbox']['js'] = $slimboxJs;
			
			//$picboxJs = JUri::root(true).'/components/com_djcatalog2/assets/picbox/js/picbox.js';
			//$picboxCss = JUri::root(true).'/components/com_djcatalog2/assets/picbox/css/picbox.css';
			
			//$lightboxes['picbox']['css'] = $picboxCss;
			//$lightboxes['picbox']['js'] = $picboxJs;
			
			$magnificJs = JUri::root(true).'/media/djextensions/magnific/magnific.js';
			$magnificCss = JUri::root(true).'/media/djextensions/magnific/magnific.css';
				
			$lightboxes['magnific']['css'] = $magnificCss;
			$lightboxes['magnific']['js'] = $magnificJs;
			
			$document->addStyleSheet($lightboxes[$lightboxType]['css']);
			$document->addScript($lightboxes[$lightboxType]['js']);
			
			if ($lightboxType == 'magnific') {
				$document->addScript(JUri::root(true).'/components/com_djcatalog2/assets/magnific/magnific-init.js');
			}
			
			$isRTL = false;
			if ($document->direction=='rtl'){
				$isRTL = true;
			} else if (isset($_COOKIE["jmfdirection"])){
				if ($_COOKIE["jmfdirection"]=='rtl'){
					$isRTL = true;
				}
			} else if (isset($_COOKIE["djdirection"])){
				if ($_COOKIE["djdirection"]=='rtl'){
					$isRTL = true;
				}
			}
			
			if ($params->get('theme_css', '1') == '1') {
				$css_suffix = ($isRTL) ? '.rtl' : '';
					
				$theme_css_url = JUri::root(true).'/components/com_djcatalog2/themes/default/css/theme'.$css_suffix.'.css';
				$theme_responsive_url = JUri::root(true).'/components/com_djcatalog2/themes/default/css/responsive'.$css_suffix.'.css';
				
				if (JFile::exists( DJCATCOMPONENTPATH.DS.'themes'.DS.$theme.DS.'css'.DS.'theme'.$css_suffix.'.css' )) {
					$theme_css_url = JUri::root(true).'/components/com_djcatalog2/themes/'.$theme.'/css/theme'.$css_suffix.'.css';
				}
				
				$document->addStyleSheet($theme_css_url);
					
				if ($params->get('theme_responsive', '1') == '1') {
					if (JFile::exists( DJCATCOMPONENTPATH.DS.'themes'.DS.$theme.DS.'css'.DS.'responsive'.$css_suffix.'.css' )) {
						$theme_responsive_url = JUri::root(true).'/components/com_djcatalog2/themes/'.$theme.'/css/responsive'.$css_suffix.'.css';
					}
					
					$document->addStyleSheet($theme_responsive_url);
				}
			}
			
			//$theme_js_file = $jquery ? 'theme.jquery.js' : 'theme.js';
			$theme_js_file = 'theme.js';
			$theme_js_url = JUri::root(true).'/components/com_djcatalog2/themes/default/js/'.$theme_js_file;
			
			if (JFile::exists( DJCATCOMPONENTPATH.DS.'themes'.DS.$theme.DS.'js'.DS.$theme_js_file )) {
				$theme_js_url = JUri::root(true).'/components/com_djcatalog2/themes/'.$theme.'/js/'.$theme_js_file;
			}
			
			$document->addScript($theme_js_url);
			
			$theme_class_file = DJCATCOMPONENTPATH.DS.'themes'.DS.'default'.DS.'theme.php';
			$theme_class = 'Djcatalog2Theme';
			
			if (JFile::exists( DJCATCOMPONENTPATH.DS.'themes'.DS.$theme.DS.'theme.php' )) {
				$theme_class_file = DJCATCOMPONENTPATH.DS.'themes'.DS.$theme.DS.'theme.php';
			}
			
			if (!class_exists($theme_class)) {
				if (file_exists($theme_class_file)) {
					require_once $theme_class_file;
				}
			}
			if (class_exists($theme_class)) {
				$themeClass = new $theme_class();
				if (method_exists($themeClass, 'setStyles')) {
					$themeClass->setStyles($params);				
				}
			}
			self::$themeSet = true;
		}
	}
	public static function getThemeImage($filename) {
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		//$params = $app->getParams('com_djcatalog2');
		$params = Djcatalog2Helper::getParams();
		$theme = $params->get('theme','bootstrapped');
		if (JFile::exists(DJCATCOMPONENTPATH.DS.'themes'.DS.$theme.DS.'images'.DS.$filename)) {
			return JUri::root(true).'/components/com_djcatalog2/themes/'.$theme.'/images/'.$filename;
		} else if (JFile::exists(DJCATCOMPONENTPATH.DS.'themes'.DS.'default'.DS.'images'.DS.$filename)) {
			return JUri::root(true).'/components/com_djcatalog2/themes/default/images/'.$filename;
		} else {
			return '';
		}
	}
	public static function getThemeName() {
		if (!self::$themeName) {
			$app = JFactory::getApplication();
			$document = JFactory::getDocument();
			//$params = $app->getParams('com_djcatalog2');
			$params = Djcatalog2Helper::getParams();
			self::$themeName = $params->get('theme','bootstrapped');
		}
		return self::$themeName;
	}
}
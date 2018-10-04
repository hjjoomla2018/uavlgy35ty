<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined('_JEXEC') or die('Restricted access');

class DJCatalog2HtmlHelper {
	
	public static $fieldLabels = array();
	
	public static function trimText($text, $length = 0) {
		$nohtml = strip_tags($text);
		if ($length > 0) {
			if(strlen($nohtml) > $length)
				return self::_substr($nohtml,$length);
		}
		return $nohtml;
	}
	
	private static function _substr($str, $length, $minword = 3)
	{
		$sub = '';
		$len = 0;
	
		foreach (explode(' ', $str) as $word)
		{
			$part = (($sub != '') ? ' ' : '') . $word;
			$temp = $sub . $part;
				
			if (strlen($temp) >= $length)
			{
				break;
			}
				
			$sub .= $part;
			$len += strlen($part);
				
			if (strlen($word) > $minword && strlen($sub) >= $length)
			{
				break;
			}
		}
		return $sub . (($len < strlen($str) && $sub != '') ? '...' : '');
	}
	
	public static function formatPrice($price, &$params) {
		$price_decimal_separator = null;
		$price_thousands_separator = null;
		
		switch($params->get('thousand_separator',0)) {
			case 0: $price_thousands_separator=''; break;
			case 1: $price_thousands_separator=' '; break;
			case 2: $price_thousands_separator='\''; break;
			case 3: $price_thousands_separator=','; break;
			case 4: $price_thousands_separator='.'; break;
			default: $price_thousands_separator=''; break;
		}
		
		switch($params->get('decimal_separator',0)) {
			case 0: $price_decimal_separator=','; break;
			case 1: $price_decimal_separator='.'; break;
			default: $price_decimal_separator=','; break;
		}
		
		$priceHtml = '<span class="djc_price_value">'.number_format($price, $params->get('decimals',2), $price_decimal_separator, $price_thousands_separator).'</span>';
		$unitHtml = '<span class="djc_price_unit">'.$params->get('price_unit').'</span>';
		
		return $params->get('unit_side') == '1' ? $priceHtml.' '.$unitHtml : $unitHtml.$priceHtml;
	}
	
	public static function orderDirImage ($order_current, $order='i.ordering', $dir='asc') {
		if ($dir == 'desc') $dir='asc';
		else $dir = 'desc';
		if ($order_current == $order) {
			return '<img class="djcat_order_dir" alt="'.$dir.'" src="'.DJCatalog2ThemeHelper::getThemeImage($dir.'.png').'" />';			
		}
		else {
			return '';
		}
	}
	
	public static function getEmailTemplate($data, $templatename) {
		require_once JPATH_ROOT.'/components/com_djcatalog2/assets/emogrifier/Emogrifier.php';
		
		$params = JComponentHelper::getParams('com_djcatalog2');
		$theme = $params->get('theme','default');
		
		$css_file = JPATH_ROOT.'/components/com_djcatalog2/themes/default/css/emails.css';
		
		if ($theme && $theme != 'default' && JFile::exists(JPATH_ROOT.'/components/com_djcatalog2/themes/'.$theme.'/css/emails.css')) {
			$css_file = JPATH_ROOT.'/components/com_djcatalog2/themes/'.$theme.'/css/emails.css';
		}
		
		$html = self::getThemeLayout($data, $templatename, 'email');
		$css = JFile::read($css_file);
		
		$emogrifier = new \Pelago\Emogrifier();
		
		$emogrifier->setHtml($html);
		$emogrifier->setCss($css);
		
		$mergedHtml = $emogrifier->emogrify();
		
		return $mergedHtml;
	}
	
	public static function getThemeLayout($data, $templatename, $type) {
		$params = JComponentHelper::getParams('com_djcatalog2');
		$theme = $params->get('theme','default');
	
		$theme_location = $default_location = JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'themes'.DS.'default'.DS.$type.DS.$templatename.'.php';
	
		if ($theme && $theme != 'default') {
			$theme_location = JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'themes'.DS.$theme.DS.$type.DS.$templatename.'.php';
		}
	
		$template = JFile::exists($theme_location) ? $theme_location : $default_location;
	
	
		if (JFile::exists($template)) {
			ob_start();
			include($template);
			$out = ob_get_contents();
			ob_end_clean();
			return $out;
		}
		else {
			return false;
		}
	}
	
	public static function getCustomFieldValue($alias, &$item, $unset = null, $separator = ', ', $wrapper = null, $plugin = false) {
		$cfName = '_ef_'.$alias;
		$retVal = null;
	
		if (!empty($item->$cfName)) {
			$retVal = $item->$cfName;
			if (is_array($item->$cfName)) {
				if ($separator == 'li') {
					$list = '<ul>';
					foreach($retVal as $value) {
						$list .= '<li>'.$value.'</li>';
					}
					$list .= '</ul>';
					$retVal = $list;
				} else {
					$retVal = implode($separator, $retVal);
				}
			}
				
			if ($wrapper) {
				$wrap = '<'.$wrapper.' class="djc_cf_wrap djc_cf_wrap-'.$alias.'">';
				$wrap .= $retVal;
				$wrap .= '</'.$wrapper.'>';
	
				$retVal = $wrap;
			}
				
			if ($plugin) {
				$retVal = JHtml::_('content.prepare', $retVal, null, 'com_djcatalog2.custom_field');
			}
				
			if ($unset) {
				unset($item->$cfName);
			}
		}
	
		return $retVal;
	}
	
	public static function getCustomFieldLabel($alias, $type = null) {
		$alias = trim($alias);
	
		if (!$alias) {
			return false;
		}
		if (!isset(self::$fieldLabels[$alias])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)->select('*')->from('#__djc2_items_extra_fields')->where('published=1 AND alias LIKE '.$db->quote($db->escape($alias)));
			$db->setQuery($query);
			$field = $db->loadObject();
				
			self::$fieldLabels[$alias] = $field;
		}
	
		$retVal = null;
	
		if (self::$fieldLabels[$alias]) {
			$attribute = self::$fieldLabels[$alias];
				
			$retVal = '<span class="djc_attribute-label">'.htmlspecialchars($attribute->name).'</span>';
				
			if (!($type && $type !='text') && !empty($attribute->imagelabel)) {
				$imgLabel = '<img class="djc_attribute-imglabel" alt="'.htmlspecialchars($attribute->name).'" src="'.JURI::base().$attribute->imagelabel.'" />';
	
				if ($type == 'image') {
					$retVal = $imgLabel;
				} else {
					$retVal = $imgLabel . $retVal;
				}
			}
		}
	
		return $retVal;
	}
	
	public static function initCalendarScripts() {
		$version = new JVersion;
		if (!version_compare($version->getShortVersion(), '3.7.0', '<')) {
			/** new Calendar setup **/
			$tag       = JFactory::getLanguage()->getTag();
			$calendar  = JFactory::getLanguage()->getCalendar();
			$direction = strtolower(JFactory::getDocument()->getDirection());
				
			$localesPath = 'system/fields/calendar-locales/en.js';
			if (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower($tag) . '.js'))
			{
				$localesPath = 'system/fields/calendar-locales/' . strtolower($tag) . '.js';
			}
			elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js'))
			{
				$localesPath = 'system/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js';
			}
			$cssFileExt = ($direction === 'rtl') ? '-rtl.css' : '.css';
			// Load polyfills for older IE
			JHtml::_('behavior.polyfill', array('event', 'classlist', 'map'), 'lte IE 11');
			// The static assets for the calendar
			JHtml::_('script', $localesPath, false, true, false, false, true);
			JHtml::_('script', 'system/fields/calendar-locales/date/gregorian/date-helper.min.js', false, true, false, false, true);
			JHtml::_('script', 'system/fields/calendar.min.js', false, true, false, false, true);
			JHtml::_('stylesheet', 'system/fields/calendar' . $cssFileExt, array(), true);
		}
	}
	
	public static function getCalendarInput($id, $value, $class) {
		$out = '';
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.7.0', '<')) {
			$out = '
			<input '.$class.' size="40" id="attribute_'.$id.'" type="text" name="attribute['.$id.']" value="'.htmlspecialchars($value).'" />
			<button class="button btn" id="attribute_'.$id.'_img"><i class="icon-calendar"></i></button>
			';
		} else {
			$out  = '
			<div class="field-calendar">
					<div class="input-append">
						<input '.$class.' size="40" id="attribute_'.$id.'"  type="text" name="attribute['.$id.']" value="'.htmlspecialchars($value).'" data-alt-value="'.htmlspecialchars($value).'"  autocomplete="off" />
						<button type="button" class="btn btn-secondary"
							id="attribute_'.$id.'_btn"
							data-inputfield="attribute_'.$id.'"
							data-dayformat="%Y-%m-%d"
							data-button="attribute_'.$id.'_btn"
							data-firstday="0"
							data-weekend="0,6"
							data-today-btn="1"
							data-week-numbers="0"
							data-show-time="0"
							data-show-others="1"
							data-time-24="24"
							data-only-months-nav="0"
						><span class="icon-calendar"></span></button>
					</div>
				</div>
			</div>
			';
		}
		
		return $out;
	}
	
	public static function setFullPaths(&$data)
	{
		$data = str_replace('xmlns=', 'ns=', $data);
	
		$doc = new DOMDocument();
		$doc->loadHTML($data);
	
		libxml_use_internal_errors(true);
	
		$allow_fopen = @ini_get('allow_url_fopen');
		$remote_urls = (empty($allow_fopen) || $allow_fopen == 'Off') ? false : true;
	
		try
		{
			//$ok = new SimpleXMLElement($sxml);
			$ok = simplexml_import_dom($doc);
			if ($ok)
			{
				$uri = JUri::getInstance();
				//$base = JURI::root(false);
				$base = $uri->getScheme() . '://' . $uri->getHost();
	
				$imgs = $ok->xpath('//img');
				foreach ($imgs as &$img) {
					if (!strstr($img['src'], $base)) {
						if ($remote_urls) {
							$img['src'] = $base . $img['src'];
						}
					} else if (!$remote_urls){
						$img['src'] = str_replace($base.'/', '', $img['src']);
					}
	
					if (strpos($img['src'], '/') == 0 && !$remote_urls) {
						$img['src'] = substr($img['src'], 1);
					}
					$img['src'] = str_replace(' ', '%20', $img['src']);
				}
				//links
				$as = $ok->xpath('//a');
				foreach ($as as &$a)
				{
					if (!strstr($a['href'], $base) && !strstr($a['href'], '://'))
					{
						$a['href'] = $base . $a['href'];
					}
				}
	
				// css files.
				$links = $ok->xpath('//link');
				foreach ($links as &$link)
				{
					if ($link['rel'] == 'stylesheet' && !strstr($link['href'], $base))
					{
						$link['href'] = $base . $link['href'];
					}
				}
				$data = $ok->asXML();
			}
		} catch (Exception $err)
		{
			$errors = libxml_get_errors();
			if (JDEBUG)
			{
				echo "<pre>";print_r($errors);echo "</pre>";
				exit;
			}
		}
	
	}
	
}

?>
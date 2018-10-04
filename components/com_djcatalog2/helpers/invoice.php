<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access');

class DJCatalog2HelperInvoice {
	public static function getNext(&$invoiceCounter, $date = false) {
		$params = JComponentHelper::getParams('com_djcatalog2');
		$formatTpl = $params->get('cart_invoice_format', '{no}/{year}');
		
		if (!$date) {
			$date = JFactory::getDate();
		}
		
		$count = static::getCandidate($date);
		
		$number = str_pad($count, 6, '0', STR_PAD_LEFT);
		$format = str_replace('{no}', $number, $formatTpl);
		$format = str_replace('{year}', $date->format('Y'), $format);
		$format = str_replace('{month}', $date->format('m'), $format);
		$format = str_replace('{day}', $date->format('d'), $format);
		
		$invoiceCounter = $count;
		
		return $format;
	}
	
	public static function getCandidate($date) {
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('counter')->from('#__djc2_inv_counters');
		$query->where('year = '.$db->quote($date->format('Y')));
		$db->setQuery($query);
		
		$count = $db->loadResult();
		
		if (!is_numeric($count) && !$count) {
			$count = 0;
			$query = $db->getQuery(true);
			$query->insert('#__djc2_inv_counters');
			$query->columns(array('year', 'counter'));
			$query->values($db->quote($date->format('Y')).', 0');
			$db->setQuery($query);
			$db->execute();
		}
		
		$count++;
		return $count;
	}
	
	public static function update($counter, $date) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__djc2_inv_counters');
		$query->set('counter='.($counter));
		$query->where('year='.$db->quote($date->format('Y')));
		$db->setQuery($query);
		return $db->execute();
	}
	
	public static function updateYear($counter, $year) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__djc2_inv_counters');
		$query->set('counter='.($counter));
		$query->where('year='.$db->quote((int)$year));
		$db->setQuery($query);
		return $db->execute();
	}
}
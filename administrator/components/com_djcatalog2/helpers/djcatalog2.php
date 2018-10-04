<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access
defined('_JEXEC') or die;

class Djcatalog2AdminHelper
{
	public static function addSubmenu($vName = 'cpanel')
	{
		$app = JFactory::getApplication();
		$version = new JVersion;
		$catalog_views = array('cpanel', 'items', 'categories', 'producers', 'fieldgroups', 'fields', 'thumbs','import');
		$customer_views = array('customers', 'customergroups');
		$order_views = array('orders');
		$price_views = array('prices','taxrates','taxrules','vatrates','vatrules');

		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_CPANEL'), 'index.php?option=com_djcatalog2&view=cpanel', $vName=='cpanel');
		
		JHtmlSidebar::addEntry('<div class="divider"></div>', '', false);
		
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_ITEMS'), 'index.php?option=com_djcatalog2&view=items', $vName=='items');
		//if (in_array($vName, $catalog_views)) {
			JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_CATEGORIES'), 'index.php?option=com_djcatalog2&view=categories', $vName=='categories');
			JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_PRODUCERS'), 'index.php?option=com_djcatalog2&view=producers', $vName=='producers');
			JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_FIELDGROUPS'), 'index.php?option=com_djcatalog2&view=fieldgroups', $vName=='fieldgroups');
			JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_LABELS'), 'index.php?option=com_djcatalog2&view=labels', $vName=='labels');
			JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_FIELDS'), 'index.php?option=com_djcatalog2&view=fields', $vName=='fields');
		//}
			JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_PRODUCT_CUSTOMISATIONS'), 'index.php?option=com_djcatalog2&view=customisations', $vName=='customisations');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_CART_FIELDS'), 'index.php?option=com_djcatalog2&view=cartfields', $vName=='cartfields');
		
		JHtmlSidebar::addEntry('<div class="divider"></div>', '', false);
		
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_QUERIES'), 'index.php?option=com_djcatalog2&view=queries', $vName=='queries');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_ORDERS'), 'index.php?option=com_djcatalog2&view=orders', $vName=='orders');
		
		JHtmlSidebar::addEntry('<div class="divider"></div>', '', false);
		
		
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_CUSTOMERS'), 'index.php?option=com_djcatalog2&view=customers', $vName=='customers');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_CUSTOMERGROUPS'), 'index.php?option=com_djcatalog2&view=customergroups', $vName=='customergroups');
		
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_VENDORS'), 'index.php?option=com_djcatalog2&view=vendors', $vName=='vendors');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_COUPONS'), 'index.php?option=com_djcatalog2&view=coupons', $vName=='coupons');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_DELIVERIES'), 'index.php?option=com_djcatalog2&view=deliveries', $vName=='deliveries');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_PAYMENTS'), 'index.php?option=com_djcatalog2&view=payments', $vName=='payments');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_COUNTRIES'), 'index.php?option=com_djcatalog2&view=countries', $vName=='countries');
		
		
		JHtmlSidebar::addEntry('<div class="divider"></div>', '', false);
		
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_VAT_RATES'), 'index.php?option=com_djcatalog2&view=vatrates', $vName=='vatrates');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_VAT_RULES'), 'index.php?option=com_djcatalog2&view=vatrules', $vName=='vatrules');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_PRICES_AND_STOCK'), 'index.php?option=com_djcatalog2&view=prices', $vName=='prices');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_UNITS'), 'index.php?option=com_djcatalog2&view=units', $vName=='units');
		
		JHtmlSidebar::addEntry('<div class="divider"></div>', '', false);
		
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_IMAGES_MANAGER'), 'index.php?option=com_djcatalog2&view=thumbs', $vName=='thumbs');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_IMPORT_CONFIGS'), 'index.php?option=com_djcatalog2&view=importconfigs', $vName=='importconfigs');
		JHtmlSidebar::addEntry(JText::_('COM_DJCATALOG2_IMPORT'), 'index.php?option=com_djcatalog2&view=import', $vName=='import');
	}

	public static function getActions($asset = null, $assetId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if ( !$asset) {
			$assetName = 'com_djcatalog2';
		} else if ($assetId != 0){
			$assetName = 'com_djcatalog2.'.$asset.$assetId;
		} else {
			$assetName = 'com_djcatalog2.'.$asset;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);
		
		$actions = array(
			'catalog2.admin','core.admin'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
}

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT.'/components/com_djcatalog2/helpers/price.php';

$show_tax 		= (int)$cparams->get('price_display_tax', 0);

$prices = Djcatalog2HelperPrice::getPrices($item->final_price, $item->price, $item->tax_rule_id, false, $cparams);

if ($prices['display'] != $prices['old_display'] ) {
	if ($cparams->get('show_old_price', '1') == '1') {
		echo '<span class="djc_price_label">'.JText::_('COM_DJCATALOG2_PRICE').': </span>';
		?><span class="djc_price_old"><?php 
			echo DJCatalog2HtmlHelper::formatPrice($prices['old_display'], $cparams); 
		?></span>
		<span class="djc_price_new"><?php 
			echo DJCatalog2HtmlHelper::formatPrice($prices['display'], $cparams);
		?></span><?php 
	} else {
		echo JText::_('COM_DJCATALOG2_PRICE').': ';
		?><span class="djc_price_normal djc_price_new"><?php 
		echo DJCatalog2HtmlHelper::formatPrice($prices['display'], $cparams);
		?></span><?php 
	}
} else {
	echo '<span class="djc_price_label">'.JText::_('COM_DJCATALOG2_PRICE').': </span>';
	?><span class="djc_price_normal djc_price_new"><?php 
	echo DJCatalog2HtmlHelper::formatPrice($prices['display'], $cparams);
	?></span><?php } 

if ($prices['display2nd'] !== false && $show_tax) {
	echo '<span class="djc_price_without_tax">'.JText::sprintf('COM_DJCATALOG2_PRICE_WITHANDWITHOUT_TAX', DJCatalog2HtmlHelper::formatPrice($prices['display2nd'], $cparams, false), DJCatalog2HtmlHelper::formatPrice($prices['tax'], $cparams, false)).'</span>';
} else if ($show_tax) {
	echo '<span class="djc_price_with_tax">'.JText::sprintf('COM_DJCATALOG2_PRICE_WITH_TAX', DJCatalog2HtmlHelper::formatPrice($prices['tax'], $cparams, false)).'</span>';
}
	
?>

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access');

$show_tax 		= (int)$this->params->get('price_display_tax', 0);

$prices = Djcatalog2HelperPrice::getPrices($this->item_cursor->final_price, $this->item_cursor->price, $this->item_cursor->tax_rule_id, false, $this->params);


if ($prices['display'] != $prices['old_display'] ) {
	if ($this->params->get('show_old_price', '1') == '1') {
		//echo JText::_('COM_DJCATALOG2_PRICE').': ';
		?><span class="djc_price_old"><?php 
			echo DJCatalog2HtmlHelper::formatPrice($prices['old_display'], $this->params); 
		?></span>&nbsp;<span class="djc_price_new"><?php 
			echo DJCatalog2HtmlHelper::formatPrice($prices['display'], $this->params);
		?></span><?php 
	} else {
		//echo JText::_('COM_DJCATALOG2_PRICE').': ';
		?><span><?php 
		echo DJCatalog2HtmlHelper::formatPrice($prices['display'], $this->params);
		?></span><?php 
	}
} else {
	//echo JText::_('COM_DJCATALOG2_PRICE').': ';
	?><span><?php 
	echo DJCatalog2HtmlHelper::formatPrice($prices['display'], $this->params);
	?></span><?php } 

if ($prices['display2nd'] !== false && $show_tax) {
	echo JText::sprintf('COM_DJCATALOG2_PRICE_WITHANDWITHOUT_TAX', DJCatalog2HtmlHelper::formatPrice($prices['display2nd'], $this->params, false), DJCatalog2HtmlHelper::formatPrice($prices['tax'], $this->params, false));
} else if ($show_tax) {
	echo JText::sprintf('COM_DJCATALOG2_PRICE_WITH_TAX', DJCatalog2HtmlHelper::formatPrice($prices['tax'], $this->params, false));
}
	
?>

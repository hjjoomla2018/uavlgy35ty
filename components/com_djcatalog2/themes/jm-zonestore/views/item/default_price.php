<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$item = $this->item_cursor;

?>
<?php 

$show_tax = (int)$this->params->get('price_display_tax', 0);
$prices = Djcatalog2HelperPrice::getPrices($item->final_price, $item->price, $item->tax_rule_id, false, $this->params);

if ($prices['display'] != $prices['old_display'] ) {
	if ($this->params->get('show_old_price_item', '1') == '1') {

		?><span class="djc_price_wrap">
		<?php echo '<span class="djc_price_label">'.JText::_('COM_DJCATALOG2_LIST_PRICE').'</span>'; ?>
			<span class="djc_price_old"><?php 
			echo DJCatalog2HtmlHelper::formatPrice($prices['old_display'], $this->params); 
		?></span><span class="djc_price_break"></span>
		<?php echo '<span class="djc_price_label">'.JText::_('COM_DJCATALOG2_PRICE_NEW').'</span>'; ?>
		<span class="djc_price_new"><?php 
			echo DJCatalog2HtmlHelper::formatPrice($prices['display'], $this->params);
		?></span><?php 

		if ($prices['display2nd'] !== false && $show_tax) {
			echo '<span class="djc_price_without_tax">'.JText::sprintf('COM_DJCATALOG2_PRICE_WITHANDWITHOUT_TAX', DJCatalog2HtmlHelper::formatPrice($prices['display2nd'], $this->params, false), DJCatalog2HtmlHelper::formatPrice($prices['tax'], $this->params, false)).'';
		} else if ($show_tax) {
			echo '<span class="djc_price_with_tax">'.JText::sprintf('COM_DJCATALOG2_PRICE_WITH_TAX', DJCatalog2HtmlHelper::formatPrice($prices['tax'], $this->params, false)).'';
		} ?>
		</span> <?php
	} else {
		echo '<span class="djc_price_label">'.JText::_('COM_DJCATALOG2_PRICE').'</span>';
		?><span class="djc_price_wrap"><span class="djc_price_normal djc_price_new"><?php 
		echo DJCatalog2HtmlHelper::formatPrice($prices['display'], $this->params);
		?></span><?php 

		if ($prices['display2nd'] !== false && $show_tax) {
			echo '<span class="djc_price_without_tax">'.JText::sprintf('COM_DJCATALOG2_PRICE_WITHANDWITHOUT_TAX', DJCatalog2HtmlHelper::formatPrice($prices['display2nd'], $this->params, false), DJCatalog2HtmlHelper::formatPrice($prices['tax'], $this->params, false)).'';
		} else if ($show_tax) {
			echo '<span class="djc_price_with_tax">'.JText::sprintf('COM_DJCATALOG2_PRICE_WITH_TAX', DJCatalog2HtmlHelper::formatPrice($prices['tax'], $this->params, false)).'';
		} ?>
		</span> <?php
	}
} else {
	echo '<span class="djc_price_label">'.JText::_('COM_DJCATALOG2_PRICE').'</span>';
	?><span class="djc_price_wrap"><span class="djc_price_normal djc_price_new"><?php 
	echo DJCatalog2HtmlHelper::formatPrice($prices['display'], $this->params);
	?></span><?php 

	if ($prices['display2nd'] !== false && $show_tax) {
		echo '<span class="djc_price_without_tax">'.JText::sprintf('COM_DJCATALOG2_PRICE_WITHANDWITHOUT_TAX', DJCatalog2HtmlHelper::formatPrice($prices['display2nd'], $this->params, false), DJCatalog2HtmlHelper::formatPrice($prices['tax'], $this->params, false)).'';
	} else if ($show_tax) {
		echo '<span class="djc_price_with_tax">'.JText::sprintf('COM_DJCATALOG2_PRICE_WITH_TAX', DJCatalog2HtmlHelper::formatPrice($prices['tax'], $this->params, false)).'';
	} ?>
	</span> <?php
} 

?>
<meta itemprop="price" content="<?php echo $prices['display']; ?>" />
<meta itemprop="priceCurrency" content="<?php echo $this->params->get('cart_currency', ''); ?>" />

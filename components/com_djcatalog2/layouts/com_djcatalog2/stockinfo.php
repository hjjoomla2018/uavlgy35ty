<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access'); 

$item = $displayData['item'];
$type = $displayData['type'];
$params = $displayData['params'];
$onStock = (bool)( $item->available && ( ($item->onstock == 1 && $item->stock > 0) || $item->onstock == 2) );

if (!$params->get('cart_enabled') || !($item->price > 0.0000)) {
	return;
}
if ($type == '1' && $onStock) {
	return;
}
$hasVariants = (bool)(isset($item->combo_count) && $item->combo_count);
?>
<?php if ($onStock) { ?>
	<?php if ($type == '2' || $item->onstock == 2 || $hasVariants) { ?>
		<span class="djc_stock_info djc_in_stock"><?php echo JText::_('COM_DJCATALOG2_PRODUCT_IN_STOCK');?></span>
	<?php } else { ?>
		<span class="djc_stock_info djc_in_stock"><?php echo JText::sprintf('COM_DJCATALOG2_PRODUCT_IN_STOCK_QTY', (float)$item->stock);?></span>
	<?php } ?>
<?php } else { ?>
	<span class="djc_stock_info djc_out_stock"><?php echo JText::_('COM_DJCATALOG2_PRODUCT_OUT_OF_STOCK');?></span>
<?php }
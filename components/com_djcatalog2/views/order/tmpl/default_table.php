<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');
$user = JFactory::getUser();

?>

<table width="100%" cellpadding="0" cellspacing="0"
	class="djc_cart_table djc_order_items_table jlist-table category table-striped table"
	id="djc_order_items_table">
	<thead>
		<tr>
			<th class="djc_thead djc_th_title"><?php echo JText::_('COM_DJCATALOG2_CART_NAME'); ?>
			</th>
			<th class="djc_thead djc_th_qty"><?php echo JText::_('COM_DJCATALOG2_QUANTITY'); ?>
			</th>
			<th class="djc_thead djc_th_price djc_th_price_net"><?php echo JText::_('COM_DJCATALOG2_NET_VALUE'); ?>
			</th>
			<th class="djc_thead djc_th_price djc_th_price_tax"><?php echo JText::_('COM_DJCATALOG2_TAX'); ?>
			</th>
			<th class="djc_thead djc_th_price djc_th_price_gross"><?php echo JText::_('COM_DJCATALOG2_TOTAL_VALUE'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="2" class="djc_ft_total_label"><?php echo JText::_('COM_DJCATALOG2_CART_FOOTER_TOTAL'); ?>
			</td>
			<td><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->total, $this->params)?>
			</td>
			<td><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->tax, $this->params)?>
			</td>
			<td><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->grand_total, $this->params)?>
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php
		$k = 1;
		foreach($this->items as $item) {
		$k = 1 - $k;
		?>
		<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k;?>">
			<td class="djc_td_title"><?php 
			echo $item->item_name;
			?>
			</td>
			<td class="djc_td_qty" nowrap="nowrap"><?php echo DJCatalog2HelperQuantity::formatAmount($item->quantity, $this->params).' '.$item->unit; ?>
			</td>
			<td class="djc_td_price djc_td_price_net" nowrap="nowrap"><?php echo DJCatalog2HtmlHelper::formatPrice($item->cost, $this->params, false)?>
			</td>
			<td class="djc_td_price djc_td_price_tax" nowrap="nowrap"><?php echo DJCatalog2HtmlHelper::formatPrice($item->tax, $this->params, false)?>
			</td>
			<td class="djc_td_price djc_td_price_gross" nowrap="nowrap"><?php echo DJCatalog2HtmlHelper::formatPrice($item->total, $this->params, false)?>
			</td>
		</tr>
		<?php } ?>
		
		<?php 
		if ($this->item->delivery_method) {
			$k = 1 - $k;
		?>
		<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k;?>"><td colspan="5"></td></tr>
		<?php $k = 1 - $k; ?>
		<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k;?>">
			<td class="djc_td_title"><?php 
			echo '<em>' . JText::_('COM_DJCATALOG2_DELIVERY_METHOD') . ':</em> ' . $this->item->delivery_method;
			?>
			</td>
			<td class="djc_td_qty" nowrap="nowrap">1</td>
			<td class="djc_td_price djc_td_price_net" nowrap="nowrap"><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->delivery_price, $this->params, false)?>
			</td>
			<td class="djc_td_price djc_td_price_tax" nowrap="nowrap"><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->delivery_tax, $this->params, false)?>
			</td>
			<td class="djc_td_price djc_td_price_gross" nowrap="nowrap"><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->delivery_total, $this->params, false)?>
			</td>
		</tr>
		<?php } ?>
		
		<?php 
		if ($this->item->payment_method) {
			$k = 1 - $k;
		?>
		<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k;?>"><td colspan="5"></td></tr>
		<?php $k = 1 - $k; ?>
		<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k;?>">
			<td class="djc_td_title"><?php 
			echo '<em>' . JText::_('COM_DJCATALOG2_PAYMENT_METHOD') . ':</em> ' . $this->item->payment_method;
			?>
			</td>
			<td class="djc_td_qty" nowrap="nowrap">1</td>
			<td class="djc_td_price djc_td_price_net" nowrap="nowrap"><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->payment_price, $this->params, false)?>
			</td>
			<td class="djc_td_price djc_td_price_tax" nowrap="nowrap"><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->payment_tax, $this->params, false)?>
			</td>
			<td class="djc_td_price djc_td_price_gross" nowrap="nowrap"><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->payment_total, $this->params, false)?>
			</td>
		</tr>
		<?php } ?>
		<?php $k = 1 - $k; ?>
		<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k;?>"><td colspan="5"></td></tr>
		
	</tbody>
</table>

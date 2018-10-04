<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

?>
<table width="100%" cellpadding="0" cellspacing="0" class="djc_orders_table jlist-table category table table-condensed" id="djc_orders_table">
	<thead>
		<tr>
			<th class="djc_thead djc_thead_order_no">
				<?php echo JText::_('COM_DJCATALOG2_ORDER_NUMBER'); ?>
			</th>
			<th class="djc_thead djc_thead_order_date">
				<?php echo JText::_('COM_DJCATALOG2_ORDER_DATE'); ?>
			</th>
			<th class="djc_thead djc_thead_order_status">
				<?php echo JText::_('COM_DJCATALOG2_ORDER_STATUS'); ?>
			</th>
			<th class="djc_thead djc_thead_order_total">
				<?php echo JText::_('COM_DJCATALOG2_ORDER_FINAL_PRICE'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$k = 1;
		foreach($this->items as $item) {
			$k = 1 - $k; 
			$order_url = JRoute::_(DJCatalogHelperRoute::getOrderRoute($item->id));
		?>
			<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k;?>">
				<td class="djc_td_order_no">
					<a href="<?php echo $order_url;?>"><strong><?php echo str_pad($item->order_number, 5, '0', STR_PAD_LEFT); ?></strong></a>
				</td>
				<td class="djc_td_order_date">
					<a href="<?php echo $order_url;?>"><?php echo JHtml::_('date', $item->created_date, 'd-m-Y'); ?></a>
				</td>
				<td class="djc_td_order_status">
					<?php echo JText::_('COM_DJCATALOG2_ORDER_STATUS_'.$item->status); ?>
				</td>
				<td class="djc_td_order_total">
					<?php echo DJCatalog2HtmlHelper::formatPrice($item->grand_total, $this->params); ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
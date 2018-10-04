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

$price_auth = ($this->params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;
$show_prices = (bool)($price_auth && (int)$this->params->get('cart_show_prices', 0) == 1 && $this->item->grand_total > 0.0);

?>

<table width="100%" cellpadding="0" cellspacing="0"
	class="djc_cart_table djc_question_items_table jlist-table category table-striped table"
	id="djc_question_items_table">
	<thead>
		<tr>
			<th class="djc_thead djc_th_title"><?php echo JText::_('COM_DJCATALOG2_CART_NAME'); ?>
			</th>
			<th class="djc_thead djc_th_qty"><?php echo JText::_('COM_DJCATALOG2_QUANTITY'); ?>
			</th>
			<?php if ($show_prices) {?>
			<th class="djc_thead djc_th_price djc_th_price_gross"><?php echo JText::_('COM_DJCATALOG2_TOTAL_VALUE'); ?>
			</th>
			<?php } ?>
		</tr>
	</thead>
	<?php if ($show_prices) {?>
	<tfoot>
		<tr>
			<td colspan="2" class="djc_ft_total_label"><?php echo JText::_('COM_DJCATALOG2_CART_FOOTER_TOTAL'); ?>
			</td>
			<td>
				<?php echo DJCatalog2HtmlHelper::formatPrice($this->item->grand_total, $this->params)?>
			</td>
		</tr>
	</tfoot>
	<?php } ?>
	<tbody>
		<?php
		$k = 1;
		foreach($this->items as $item){
		$k = 1 - $k;
		?>
		<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k;?>">
			<td class="djc_td_title"><?php 
			echo $item->item_name;
			?>
			</td>
			<td class="djc_td_qty" nowrap="nowrap"><?php echo (int)$item->quantity; ?>
			</td>
			<?php if ($show_prices) {?>
			<td class="djc_td_price djc_td_price_gross" nowrap="nowrap"><?php echo ($item->total > 0.0) ? DJCatalog2HtmlHelper::formatPrice($item->total, $this->params, false) : '-'; ?>
			</td>
			<?php } ?>
		</tr>
		<?php if (trim($item->additional_info) != '' && $item->item_type == 'item') { ?>
			<?php $data = json_decode($item->additional_info); ?>
				<?php if ($data) {?>
				<tr>
					<td colspan="<?php echo $show_prices ? 3:2; ?>">
						<?php 
						$attrs = array();
						foreach($data as $label => $value) {
							$attrs[] = '<strong>'.$label.'</strong>: <span>'.($value ? $value : '---').'</span>';
						} 
						echo implode(' | ', $attrs);
						?>
					</td>
				</tr>
			<?php } ?>
		<?php } ?>
		<?php } ?>
	</tbody>
</table>

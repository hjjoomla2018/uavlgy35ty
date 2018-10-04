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
$return_url = base64_encode(JUri::getInstance()->__toString());

$price_auth = ($this->params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;
$show_prices = (bool)($price_auth && (int)$this->params->get('cart_show_prices', 0) == 1 && $this->total['gross'] > 0.0);

$tbl_class = ($show_prices) ? 'djc_cart_table withprices' : 'djc_cart_table noprices';
?>
<table width="100%" cellpadding="0" cellspacing="0" class="<?php echo $tbl_class; ?>  jlist-table table-condensed table category" id="djc_cart_table">
	<thead>
		<tr>
			<th class="djc_thead djc_th_title">
				<?php echo JText::_('COM_DJCATALOG2_CART_NAME'); ?>
	        </th>
	        <th class="djc_thead djc_th_qty" colspan="2">
				<?php echo JText::_('COM_DJCATALOG2_QUANTITY'); ?>
	        </th>
	        <?php if ($show_prices) { ?>
	        <?php /* ?>
	        <th class="djc_thead djc_th_price djc_th_price_net">
				<?php echo JText::_('COM_DJCATALOG2_NET_VALUE'); ?>
	        </th>
	        <th class="djc_thead djc_th_price djc_th_price_tax">
				<?php echo JText::_('COM_DJCATALOG2_TAX'); ?>
	        </th>
	        <?php */ ?>
	        <th class="djc_thead djc_th_price djc_th_price_gross">
				<?php echo JText::_('COM_DJCATALOG2_TOTAL_VALUE'); ?>
	        </th>
	        <?php } ?>
	    </tr>
	</thead>
	<?php if ($show_prices) { ?>
	<tfoot>
		<tr>
			<td colspan="3" class="djc_ft_total_label">
				<?php echo JText::_('COM_DJCATALOG2_CART_FOOTER_TOTAL'); ?>
			</td>
			<?php /* ?>
			<td>
				<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_total['net'], $this->params)?>
			</td>
			<td>
				<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_total['tax'], $this->params)?>
			</td>
			<?php */ ?>
			<td>
				<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_total['gross'], $this->params); ?>
			</td>
		</tr>
	</tfoot>
	<?php } ?>
    <tbody>
        <?php
	$k = 1;
	$itemsImages = array();
	foreach($this->items as $item){
		$k = 1 - $k;
		
		if (!empty($item->parent)) {
			if (!$item->item_image && $item->parent->item_image) {
				$item->item_image = $item->parent->item_image;
				$item->image_caption = $item->parent->image_caption;
				$item->image_path = $item->parent->image_path;
				$item->image_fullpath = $item->parent->image_fullpath;
			}
			$item->name = $item->parent->name . ' ['.$item->name.']';
			$item->slug = $item->parent_id.':'.$item->parent->alias;
		}
		if ($item->sku && $this->params->get('cart_display_sku', 1) == '1') {
			$item->name = $item->name . '<small class="djc_sku"> (#' . $item->sku.')</small>';
		}
		?>
        <tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k; if ($item->featured == 1) echo ' featured_item'; ?>">
            <td class="djc_td_title">
            <?php if ($item->item_image) { ?>
	        	<span class="djc_image">
					<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)); ?>"><img class="img-polaroid" alt="<?php echo $item->image_caption; ?>" src="<?php echo DJCatalog2ImageHelper::getImageUrl($item->image_fullpath,'small'); ?>"/></a>
	        	</span>
			<?php } ?>
			<strong><a href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)); ?>"><?php echo $item->name; ?></a></strong>
            </td>
            <td class="djc_td_update_qty" nowrap="nowrap">
            	<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute());?>" method="post" class="form-horizontal form">
            		<input type="text" name="quantity" class="input input-mini inputbox djc_qty_input" value="<?php echo (int)$item->_quantity; ?>" />
            		<input type="submit" class="btn djc_update_qty_btn" value="<?php echo JText::_('COM_DJCATALOG2_UPDATE_QTY_BUTTON'); ?>" title="<?php echo JText::_('COM_DJCATALOG2_UPDATE_QTY_BUTTON_TITLE'); ?>" />
            		<input type="hidden" name="task" value="cart.update"/>
            		<input type="hidden" name="return" value="<?php echo $return_url; ?>"/>
            		<input type="hidden" name="item_id" value="<?php echo (int)$item->id; ?>"/>
            		<?php echo JHtml::_( 'form.token' ); ?>
            	</form>
            </td>
            <td class="djc_td_cart_remove" nowrap="nowrap">
            	<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute());?>" method="post" class="form-horizontal form">
            		<input type="submit" class="btn djc_cart_remove_btn" value="<?php echo JText::_('COM_DJCATALOG2_CART_REMOVE_BUTTON'); ?>" title="<?php echo JText::_('COM_DJCATALOG2_CART_REMOVE_BUTTON_TITLE'); ?>" />
            		<input type="hidden" name="task" value="cart.remove"/>
            		<input type="hidden" name="return" value="<?php echo $return_url; ?>"/>
            		<input type="hidden" name="item_id" value="<?php echo (int)$item->id; ?>"/>
            		<?php echo JHtml::_( 'form.token' ); ?>
            	</form>
            </td>
            <?php if ($show_prices) { ?>
            <?php /*?>
            <td class="djc_td_price djc_td_price_net" nowrap="nowrap">
            	<?php echo DJCatalog2HtmlHelper::formatPrice($item->_prices['total']['net'], $this->params, false)?>
            </td>
            <td class="djc_td_price djc_td_price_tax" nowrap="nowrap">
            	<?php echo DJCatalog2HtmlHelper::formatPrice($item->_prices['total']['tax'], $this->params, false)?>
            </td>
            <?php */ ?>
            <td class="djc_td_price djc_td_price_gross" nowrap="nowrap">
            	<?php echo ($item->_prices['total']['gross'] > 0.0) ? DJCatalog2HtmlHelper::formatPrice($item->_prices['total']['gross'], $this->params, false) : '-';?>
            </td>
            <?php } ?>
        </tr>
	<?php } ?>
	</tbody>
</table>
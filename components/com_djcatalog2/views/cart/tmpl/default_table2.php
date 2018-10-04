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
$show_prices = (bool)($price_auth && ((int)$this->params->get('cart_show_prices', 0) == 1 || $this->params->get('cart_enabled', '1') == '1') && $this->total['gross'] > 0.0);
$show_vat = $this->params->get('cart_show_vat', 1) && $show_prices;

$show_discount = (bool)($show_prices && (($this->product_old_total['gross'] > 0.0 && $this->product_total['gross'] != $this->product_old_total['gross'])));

$tbl_class = ($show_prices) ? 'djc_cart_table withprices' : 'djc_cart_table noprices';

$attributes = $this->basket->getAttributes();

//$col_span_btns = 3;
//$col_span_price = 2;
$col_span_btns = 2;
$col_span_price = 1;
if ($show_prices) {
	if ($show_vat) {
		$col_span_btns += 3;
	} else {
		$col_span_btns++;
	}
	$col_span_price++;
}

$net_prices = (bool)((int)$this->params->get('price_including_tax', 1) == 0);
$salesman = false;//$user->authorise('djcatalog2.salesman', 'com_djcatalog2');

if (count($attributes) > 0) {
	$tbl_class .= ' has_attributes';
	//$col_span_btns += count($attributes);
	//$col_span_price += count($attributes);
	$col_span_btns++;
	$col_span_price++;
}
if ($salesman) {
	$col_span_btns++;
	$col_span_price++;
}


?>
<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute());?>" method="post" class="form-horizontal form">
<table width="100%" cellpadding="0" cellspacing="0" class="<?php echo $tbl_class; ?>  jlist-table table-condensed table category" id="djc_cart_table">
	<thead>
		<tr>
			<th class="djc_thead djc_th_title">
				<?php echo JText::_('COM_DJCATALOG2_CART_NAME'); ?>
			</th>
			<th class="djc_thead djc_th_qty" <?php /*?>colspan="2"<?php */ ?>>
				<?php echo JText::_('COM_DJCATALOG2_QUANTITY'); ?>
			</th>
			<?php if (count($attributes) > 0) {?>
				<?php /*foreach ($attributes as $attribute) {?>
				<th class="djc_thead djc_attribute">
					<?php echo $attribute->name; ?>
				</th>
				<?php }*/ ?>
				<th class="djc_thead djc_attributes">
					<?php echo JText::_('COM_DJCATALOG2_CUSTOM_ATTRIBUTES'); ?>
				</th>
			<?php } ?>
			<?php if ($salesman) {?>
			<th class="djc_thead djc_th_basecost">
				<?php echo ($net_prices) ? JText::_('COM_DJCATALOG2_BASE_COST_NET') : JText::_('COM_DJCATALOG2_BASE_COST_BRU')?>
			</th>
			<?php } ?>
			<?php if ($show_prices || $salesman) { ?>
				<?php if ($show_vat) {?>
				<th class="djc_thead djc_th_price djc_th_price_net">
					<?php echo JText::_('COM_DJCATALOG2_NET_VALUE'); ?>
				</th>
				<th class="djc_thead djc_th_price djc_th_price_tax">
					<?php echo JText::_('COM_DJCATALOG2_TAX'); ?>
				</th>
				<?php } ?>
			<th class="djc_thead djc_th_price djc_th_price_gross">
				<?php echo JText::_('COM_DJCATALOG2_TOTAL_VALUE'); ?>
			</th>
			<?php } ?>
		</tr>
	</thead>
	<tfoot>
		<?php if ($show_prices || $salesman) { ?>
			<?php if($show_discount) { ?>
			<tr class="djc_cart_foot djc_cart_foot_old_price">
				<td class="djc_ft_total_label" colspan="<?php echo $col_span_price;?>" >
					<?php echo JText::_('COM_DJCATALOG2_CART_FOOTER_OLD_TOTAL'); ?>
				</td>
				<?php if ($show_vat) {?>
				<td>
					<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_old_total['net'], $this->params)?>
				</td>
				<td>
					<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_old_total['tax'], $this->params)?>
				</td>
				<?php } ?>
				<td>
					<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_old_total['net'], $this->params); ?>
				</td>
			</tr>
			<tr class="djc_cart_foot djc_cart_foot_discount">
				<td class="djc_ft_total_label" colspan="<?php echo $col_span_price;?>" >
					<?php 
					if ($this->basket->coupon) {
					    echo JText::_('COM_DJCATALOG2_CART_FOOTER_COUPON_DISCOUNT');
	                    
					    if($this->basket->coupon->type == 'percent') {
	                        echo ' ('. (float)$this->basket->coupon->value . '%)';
	                    } else {
	                        echo ' ('. DJCatalog2HtmlHelper::formatPrice($this->basket->coupon->value, $this->params) .')';
	                    }
					}
					?>
				</td>
				<?php if ($show_vat) {?>
				<td>
					<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_old_total['net'] - $this->product_total['net'], $this->params)?>
				</td>
				<td>
					<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_old_total['tax'] - $this->product_total['tax'], $this->params)?>
				</td>
				<?php } ?>
				<td>
					<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_old_total['net'] - $this->product_total['net'], $this->params); ?>
				</td>
			</tr>
			<?php } ?>
		<tr class="djc_cart_foot">
			<td colspan="<?php echo $col_span_price;?>" class="djc_ft_total_label">
				<?php echo JText::_('COM_DJCATALOG2_CART_FOOTER_TOTAL'); ?>
			</td>
			<?php if ($show_vat) {?>
				<td>
					<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_total['net'], $this->params)?>
				</td>
				<td>
					<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_total['tax'], $this->params)?>
				</td>
			<?php } ?>
			<td>
				<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_total['gross'], $this->params); ?>
			</td>
		</tr>
		<?php } ?>
		<tr class="djc_cart_buttons">
			<td colspan="<?php echo $col_span_btns;?>">
				<input type="submit" class="btn" value="<?php echo JText::_('COM_DJCATALOG2_CART_UPDATE_BUTTON'); ?>" />
				<input type="hidden" name="task" value="cart.update_batch"/>
				<input type="hidden" name="return" value="<?php echo $return_url; ?>" />
				<a class="btn" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=cart.clear&'.JSession::getFormToken().'=1');?>"><?php echo JText::_('COM_DJCATALOG2_CART_CLEAR_BUTTON'); ?></a>
				<?php echo JHtml::_( 'form.token' ); ?>
			</td>
		</tr>
	</tfoot>
	<tbody id="djc_cart_contents">
		<?php
	$k = 1;
	$itemsImages = array();
	foreach($this->items as $item){
		$k = 1 - $k;
		$item->_customisations = $this->basket->getCustomisations($item->_sid);
		$this->item_cursor = $item;
		
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
		
		$item->_link = DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug);
		$item->_popuplink = DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug, null, 'preview').'&tmpl=component';
		
		if ($item->sku && $this->params->get('cart_display_sku', 1) == '1') {
			$item->name = $item->name . '<small class="djc_sku"> (#' . $item->sku.')</small>';
		}
		
		?>
		<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k; if ($item->featured == 1) echo ' featured_item'; ?>">
			<td class="djc_td_title">
			<?php if ($item->item_image) { ?>
				<?php /*?><span class="djc_image">
					<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)); ?>"><img class="img-polaroid" alt="<?php echo $item->image_caption; ?>" src="<?php echo DJCatalog2ImageHelper::getImageUrl($item->image_fullpath,'small'); ?>"/></a>
				</span><?php */ ?>
				<?php 
				$variant = 'link';
				$layout = new JLayoutFile('com_djcatalog2.listimage', null, array('component'=> 'com_djcatalog2'));
				$imageData = array(	'item' => &$item, 
									'type' => 'item', 
									'size' => 'small', 
									'variant' => $variant, 
									'hover_img' => false,
									'context' => 'com_djcatalog2.items.cart', 
									'params' => &$this->params);
				echo $layout->render($imageData);
				?>
			<?php } ?>
			<strong><a href="<?php echo JRoute::_($item->_link); ?>"><?php echo $item->name; ?></a></strong>
		   
			<?php if (!empty($item->_combination)) {?>
				<ul class="djc_combination_info">
				<?php foreach($item->_combination->fields as $comboField) { ?>
					<li><?php echo $comboField->field_name.': <strong>'.$comboField->field_value.'</strong>'; ?></li>
				<?php } ?>
				</ul>
			<?php } ?>
			</td>
			<td class="djc_td_update_qty" nowrap="nowrap">
				<?php 
				$unit = DJCatalog2HelperQuantity::getUnit($item->unit_id); 
				$btnOpts = array(
					'name' => 'quantity['.$item->_sid.']', 
					'value' => $item->_quantity, 
					'allow_empty' => true, 
					'show_box' => true, 
					//'remove_button' => '<a class="btn" href="'.JRoute::_('index.php?option=com_djcatalog2&task=cart.remove&sid='.$item->_sid).'">'.JText::_('COM_DJCATALOG2_CART_REMOVE_BUTTON_TITLE').'</a>'
					'remove_button' => '<a class="btn btn-danger" href="'.JRoute::_('index.php?option=com_djcatalog2&task=cart.remove&sid='.$item->_sid).'" title="'.JText::_('COM_DJCATALOG2_CART_REMOVE_BUTTON_TITLE').'">&times;</a>'
				);
				echo DJCatalog2HelperQuantity::renderInput($unit, $item, $btnOpts);
				?>
			</td>
			<?php /*?>
			<td class="djc_td_cart_remove" nowrap="nowrap">
				<a class="btn djc_cart_remove_btn" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=cart.remove&sid='.$item->_sid); ?>"><?php echo JText::_('COM_DJCATALOG2_CART_REMOVE_BUTTON_TITLE'); ?></a>
			</td><?php */ ?>
			
			<?php if (count($attributes) > 0) { ?>
				<td class="djc_td_cart_attribute form-vertical">
				<?php foreach ($attributes as $attribute) {?>
				
					<?php 
					$this->attribute_cursor = clone $attribute;
					$this->attribute_values = $this->basket->getItemAttributes($item);
					echo $this->loadTemplate('itemattribute'); 
					?>
				<?php } ?>
				</td>
			<?php } ?>
			
			<?php if ($salesman) {?>
			<td>
				<?php if ($net_prices) {?>
					<input type="text" name="price[<?php echo $item->_sid; ?>]" value="<?php echo $item->_prices['base']['net']; ?>" class="input input-mini inputbox djc_price_input" />
				<?php } else { ?>
					<input type="text" name="price[<?php echo $item->_sid; ?>]" value="<?php echo $item->_prices['base']['gross']; ?>" class="input input-mini inputbox djc_price_input" />
				<?php } ?>
			</td>
			<?php } ?>
			<?php if ($show_prices || $salesman) { ?>
				<?php if ($show_vat) {?>
					<td class="djc_td_price djc_td_price_net" nowrap="nowrap">
						<?php echo DJCatalog2HtmlHelper::formatPrice($item->_prices['total']['net'], $this->params, false)?>
					</td>
					<td class="djc_td_price djc_td_price_tax" nowrap="nowrap">
						<?php echo DJCatalog2HtmlHelper::formatPrice($item->_prices['total']['tax'], $this->params, false)?>
					</td>
				<?php } ?>
				<td class="djc_td_price djc_td_price_gross" nowrap="nowrap">
					<?php echo ($item->_prices['total']['gross'] > 0.0) ? DJCatalog2HtmlHelper::formatPrice($item->_prices['total']['gross'], $this->params, false) : '-';?>
				</td>
			<?php } ?>
		</tr>
		
		<?php if (count($item->_customisations) > 0) { ?>
			<?php foreach($item->_customisations as $customOption) { ?>
			<tr class="cat-list-row<?php echo $k;?> djc_custom_row<?php echo $k;?> djc_child_custom_row">
				<td class="djc_td_title"  colspan="<?php echo $col_span_price; ?>">
					<div class="djc_customisation_info">
						<strong><?php echo $customOption->name; ?></strong>
						<?php if (count($customOption->data)) { ?>
							<ul class="djc_customistation_data">
								<?php foreach($customOption->data as $inputParam) {?>
									<li>
										<?php if ($inputParam['type'] == 'text') {?>
											<?php echo $inputParam['name'].': <strong>'.$inputParam['value'].'</strong>'; ?>
										<?php } else if ($inputParam['type'] == 'file') {?>
											<?php echo $inputParam['name']; ?>:
											<?php if ($inputParam['value'] != '') {?>
												<?php $jsonFiles = json_decode($inputParam['value'], true); ?>
												<?php foreach ($jsonFiles as $jsonFile) {
													$ext = JFile::getExt($jsonFile['fullname']);
													if ($ext == 'jpg' || $ext == 'png' || $ext == 'svg' || $ext == 'gif') { ?>
														<br /><span class="djc_customisation_file" style="width: 64px; height: 64px; display: inline-block; background-repeat: no-repeat; background-size: cover; background-image: url('<?php echo $jsonFile['url']; ?>')"></span>
													<?php } else { ?>
														<br /><span><?php echo '['.strtoupper($ext).'] '.$jsonFile['caption']; ?></span>
													<?php } ?>
												<?php } ?>
											<?php } ?>
										<?php } ?>
									</li>
								<?php } ?>
							</ul>
						<?php } ?>
					</div>
				</td>
				
				<?php if ($show_prices || $salesman) { ?>
					<?php if ($show_vat) {?>
						<td class="djc_td_price djc_td_price_net" nowrap="nowrap">
							<?php echo DJCatalog2HtmlHelper::formatPrice($customOption->_prices['total']['net'], $this->params, false)?>
						</td>
						<td class="djc_td_price djc_td_price_tax" nowrap="nowrap">
							<?php echo DJCatalog2HtmlHelper::formatPrice($customOption->_prices['total']['tax'], $this->params, false)?>
						</td>
					<?php } ?>
					<td class="djc_td_price djc_td_price_gross" nowrap="nowrap">
						<?php echo ($item->_prices['total']['gross'] > 0.0) ? DJCatalog2HtmlHelper::formatPrice($customOption->_prices['total']['gross'], $this->params, false) : '-';?>
					</td>
				<?php } ?>
			</tr>
			<?php } ?>
		<?php } ?>
	<?php } ?>
	
	<?php if ($cartCustoms = $this->basket->getCustomisations(0)) { ?>
		<?php foreach($cartCustoms as $customOption) { ?>
		<?php $k = 1 - $k; ?>
		<tr class="cat-list-row<?php echo $k;?> djc_custom_row<?php echo $k;?> djc_common_custom_row">
			<td class="djc_td_title" colspan="<?php echo $col_span_price-1; ?>">
				<div class="djc_customisation_info">
					<strong><?php echo $customOption->name; ?></strong>
						<?php if (count($customOption->data)) { ?>
							<ul class="djc_customistation_data">
								<?php foreach($customOption->data as $inputParam) {?>
									<li>
										<?php if ($inputParam['type'] == 'text') {?>
											<?php echo $inputParam['name'].': <strong>'.$inputParam['value'].'</strong>'; ?>
										<?php } else if ($inputParam['type'] == 'file') {?>
											<?php echo $inputParam['name']; ?>:
											<?php if ($inputParam['value'] != '') {?>
											<?php $jsonFiles = json_decode($inputParam['value'], true); ?>
											<?php foreach ($jsonFiles as $jsonFile) {?>
												<br /><span class="djc_customisation_file" style="width: 64px; height: 64px; display: inline-block; background-repeat: no-repeat; background-size: cover; background-image: url('<?php echo $jsonFile['url']; ?>')"></span>
											<?php } ?>
											<?php } ?>
										<?php } ?>
									</li>
								<?php } ?>
							</ul>
						<?php } ?>
				</div>
			</td>
			
			<td class="djc_td_cart_remove" nowrap="nowrap">
				<?php /* ?><a class="btn djc_cart_remove_btn" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=cart.remove&sid=0'); ?>"><?php echo JText::_('COM_DJCATALOG2_CART_REMOVE_BUTTON_TITLE'); ?></a><?php */ ?>
				<a class="btn btn-danger" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=cart.remove&sid=0'); ?>">&times;</a>
			</td>
			
			<?php if ($show_prices || $salesman) { ?>
				<?php if ($show_vat) {?>
					<td class="djc_td_price djc_td_price_net" nowrap="nowrap">
						<?php echo DJCatalog2HtmlHelper::formatPrice($customOption->_prices['total']['net'], $this->params, false)?>
					</td>
					<td class="djc_td_price djc_td_price_tax" nowrap="nowrap">
						<?php echo DJCatalog2HtmlHelper::formatPrice($customOption->_prices['total']['tax'], $this->params, false)?>
					</td>
				<?php } ?>
				<td class="djc_td_price djc_td_price_gross" nowrap="nowrap">
					<?php echo ($item->_prices['total']['gross'] > 0.0) ? DJCatalog2HtmlHelper::formatPrice($customOption->_prices['total']['gross'], $this->params, false) : '-';?>
				</td>
			<?php } ?>
		</tr>
		<?php } ?>
	<?php } ?>
	</tbody>
</table>
</form>

<script>
(function($){
	$(document).ready(function(){
		var cart = $('#djc_cart_contents');
		var inputs = cart.find('select, textarea, input[type="text"], input[type="radio"], input[type="checkbox"]');

		inputs.change(function(){
			$(this).addClass('input-modified');
		});
		
		var form_btns = $('.djc_query_btn, .djc_checkout_btn');
		form_btns.click(function(){
			var has_error = cart.find('.input-modified').length > 0;
			if (has_error) {
				return confirm('<?php echo JText::_('COM_DJCATALOG2_CART_CHANGED_CONFIRM'); ?>');
			}
		});
		
	});
})(jQuery);

</script>

<?php if ($salesman) {?>
<script type="text/javascript">
<!--

(function($){
	$(document).ready(function(){
		var djItemPriceInputs = $('input.djc_price_input');
		if (djItemPriceInputs.length > 0) {
			djItemPriceInputs.each(function(){
				var el = $(this);
				el.on('keyup change click', function(){
					djValidatePrice(el);
				});
			});
		}
		
		var form_btns = $('#djcatalog .djc_cart_actions form input[type="submit"]');
		form_btns.click(function(){
			var has_error = false;
			djItemPriceInputs.parents('tr').each(function(){
				if ($(this).hasClass('error')) {
					has_error = true;
				}
			});
			if (has_error) {
				return confirm('<?php echo JText::_('COM_DJCATALOG2_CART_CHANGED_CONFIRM'); ?>');
			}
		});
		
	});
})(jQuery);

function djValidatePrice(priceInput) {

	var price = jQuery(priceInput).attr('value');
	
	// valid format
	var valid_price = new RegExp(/^(\d+|\d+\.\d+)$/);
	
	// comma instead of dot
	var wrong_decimal = new RegExp(/\,/g);
	
	// non allowed characters
	var restricted = new RegExp(/[^\d+\.]/g);
	
	// replace comma with a dot
	price = price.replace(wrong_decimal, ".");
	
	if (valid_price.test(price) == false) {
		// remove illegal chars
		price = price.replace(restricted, '');
	}
	
	if (valid_price.test(price) == false) {
		// too many dots in here
		parts = price.split('.');
		if (parts.length > 2 ) {
			price = parts[0] + '.' + parts[1];
		}
	}
	
	jQuery(priceInput).attr('value', price);
	djHighlightRow(priceInput);
}

function djHighlightRow(priceInput) {
	var row = jQuery(priceInput).parents('tr').first();
	row.addClass('error');
}

//-->
</script>
<?php } ?>


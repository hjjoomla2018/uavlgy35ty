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

$attributes = $this->basket->getAttributes();

$show_vat = $this->params->get('cart_show_vat', 1);

$tbl_class = ($show_prices) ? 'djc_cart_table withprices' : 'djc_cart_table noprices';

$col_span_price = 0;
if ($show_prices) {
	$col_span_price++;
	
	if ($show_vat) {
		$col_span_price += 2;
	}
}

$net_prices = (bool)((int)$this->params->get('price_including_tax', 1) == 0);
$salesman = false;//$user->authorise('djcatalog2.salesman', 'com_djcatalog2');

if (count($attributes) > 0) {
	$tbl_class .= ' has_attributes';
	$col_span_price += count($attributes);
}

?>
<table width="100%" cellpadding="0" cellspacing="0" class="<?php echo $tbl_class; ?> jlist-table category table-condensed table" id="djc_cart_checkout_table">
	<thead>
		<tr>
			<th class="djc_thead djc_th_title" colspan="2">
				<?php echo JText::_('COM_DJCATALOG2_CART_NAME'); ?>
	        </th>
	        <th class="djc_thead djc_th_qty">
				<?php echo JText::_('COM_DJCATALOG2_QUANTITY'); ?>
	        </th>
	        <?php if (count($attributes) > 0) {?>
	        	<?php foreach ($attributes as $attribute) {?>
	        	<th class="djc_thead djc_attribute">
	        		<?php echo $attribute->name; ?>
	        	</th>
	        	<?php } ?>
	        <?php } ?>
	        <?php if ($show_prices) {?>
		        <?php if ($show_vat) {?>
			        <th class="djc_thead djc_th_price djc_th_price_net">
						<?php echo JText::_('COM_DJCATALOG2_NET_VALUE'); ?>
			        </th>
			        <th class="djc_thead djc_th_price djc_th_price_tax">
						<?php echo JText::_('COM_DJCATALOG2_TAX'); ?>
			        </th>
			        <th class="djc_thead djc_th_price djc_th_price_gross">
						<?php echo JText::_('COM_DJCATALOG2_TOTAL_VALUE'); ?>
		        	</th>
		        <?php } ?>
	        <?php } ?>
	    </tr>
	</thead>
	<tfoot>
		<?php if ($show_prices) {?>
		<tr class="djc_cart_foot">
			<td colspan="<?php echo $col_span_price; ?>" class="djc_ft_total_label">
				<?php echo JText::_('COM_DJCATALOG2_CART_FOOTER_TOTAL'); ?>
			</td>
			<?php if ($show_prices) {?>
				<?php if ($show_vat) {?>
					<td>
						<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_total['net'], $this->params)?>
					</td>
					<td>
						<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_total['tax'], $this->params)?>
					</td>
				<?php } ?>
				<td>
					<?php echo DJCatalog2HtmlHelper::formatPrice($this->product_total['gross'], $this->params)?>
				</td>
			<?php } ?>
		</tr>
		<?php } ?>
	</tfoot>
    <tbody>
        <?php
	$k = 1;
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
            <td class="djc_td_image">
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
            </td>
			<td class="djc_td_title">
           		<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)); ?>"><?php 
		        	echo $item->name;
		        ?>
		        </a>
		        
		        <?php if (!empty($item->_combination)) {?>
	           		<ul class="djc_combination_info inine list-inline">
	           		<?php foreach($item->_combination->fields as $comboField) { ?>
	           			<li><?php echo $comboField->field_name.': <strong>'.$comboField->field_value.'</strong>'; ?></li>
	           		<?php } ?>
	           		</ul>
	           	<?php } ?>
	           	
	           	<?php if (count($item->_customisations) > 0) {?>
		           	<div class="djc_customisation_info">
		           	<?php foreach($item->_customisations as $customOption) { ?>
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
		           	<?php } ?>
		           	</div>
	           	<?php } ?>
            </td>
            <td class="djc_td_update_qty" nowrap="nowrap">
            	<?php echo (int)$item->_quantity; ?>
            </td>
            <?php if (count($attributes) > 0) {?>
	        	<?php foreach ($attributes as $attribute) {?>
	        	<td class="djc_td_cart_attribute">
	        		<?php 
	        		$this->attribute_cursor = clone $attribute;
	        		$this->attribute_values = $this->basket->getItemAttributes($item, true);
	        		echo $this->loadTemplate('itemattribute'); 
	        		?>
	        	</td>
	        	<?php } ?>
	        <?php } ?>
	        <?php if ($show_prices) {?>
		        <?php if ($show_vat) {?>
		            <td class="djc_td_price djc_td_price_net" nowrap="nowrap">
		            	<?php echo DJCatalog2HtmlHelper::formatPrice($item->_prices['total']['net'], $this->params, false)?>
		            </td>
		            <td class="djc_td_price djc_td_price_tax" nowrap="nowrap">
		            	<?php echo DJCatalog2HtmlHelper::formatPrice($item->_prices['total']['tax'], $this->params, false)?>
		            </td>
		        <?php } ?>
	            <td class="djc_td_price djc_td_price_gross" nowrap="nowrap">
	            	<?php echo DJCatalog2HtmlHelper::formatPrice($item->_prices['total']['gross'], $this->params, false)?>
	            </td>
            <?php } ?>
        </tr>
	<?php } ?>
	
	<?php if ($cartCustoms = $this->basket->getCustomisations(0)) { ?>
		<?php foreach($cartCustoms as $customOption) { ?>
		<tr>
			<td class="djc_td_title" colspan="<?php echo $col_span_price; ?>">
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
			
			<?php if ($show_prices) {?>
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
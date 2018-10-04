<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');
$user		= JFactory::getUser();
$price_auth = ($this->params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;

$show_additional_data = false;
if ($this->params->get('items_show_attributes_variants', '1') == '1') {
	foreach ($this->childrenAttributes as $attribute) {
		$show_additional_data = (bool)($show_additional_data || (int)$attribute->separate_column != 1);
	}
}

?>
<table width="100%" cellpadding="0" cellspacing="0" class="djc_items_table djc_variants_table jlist-table category table table-condensed" id="djc_variants_table">
	<thead>
		<tr>
			<?php if ((int)$this->params->get('items_show_image_variants', 1)) { ?>
				<th class="djc_thead djc_th_image">&nbsp;</th>
			<?php } ?>
				<th class="djc_thead djc_th_title" nowrap="nowrap">
					<?php echo JText::_('COM_DJCATALOG2_NAME'); ?>
		        </th>
		    <?php if ((int)$this->params->get('items_show_sku_variants', '1') > 0 ) {?>
				<th class="djc_thead djc_th_sku" nowrap="nowrap">
					<?php echo JText::_('COM_DJCATALOG2_SKU'); ?>
		        </th>
	        <?php } ?>
			<?php if ($this->params->get('items_show_intro_variants')) {?>
                <th class="djc_thead djc_th_intro" nowrap="nowrap">
                    <?php echo JText::_('COM_DJCATALOG2_DESCRIPTION'); ?>
                </th>
			<?php } ?>
			<?php if ($price_auth && $this->params->get('items_show_price_variants') > 0) { ?>
	                <th class="djc_thead djc_th_price" nowrap="nowrap">
	                	<?php echo JText::_('COM_DJCATALOG2_PRICE'); ?>
	                </th>
			<?php } ?>
			
			<?php if (!empty($this->childrenColumns)) { ?>
				<?php foreach ($this->childrenColumns as $column) { ?>
					<th class="djc_thead djc_th_attributes djc_th_attribute_<?php echo $column->alias; ?>">
						<?php 
						echo $this->escape($column->name); 
						unset($this->childrenAttributes[$column->id]);
						?>
					</th>
				<?php } ?>
			<?php } ?>
			<?php if ($show_additional_data) { ?>
				<?php if (count($this->childrenAttributes)) { ?>
		            <th class="djc_thead djc_th_attribute" nowrap="nowrap">
	                	<?php echo JText::_('COM_DJCATALOG2_CUSTOM_ATTRIBUTES'); ?>
	                </th>
	            <?php } ?>
			<?php } ?>
			<?php if ($this->params->get('items_show_cart_button_variants', '1') == 1 && ($this->params->get('cart_enabled', false) || $this->params->get('cart_query_enabled', 1) )) { ?>
				<th class="djc_thead djc_th_addtocart_cell">
				</th>
			<?php } ?>
	            </tr>
            </thead>
            <tbody>
        <?php
	$k = 1;
	$itemsImages = array();
	foreach($this->children as $item){
		$k = 1 - $k;
		
		if ($item->parent_id > 0) {
			if (!$item->item_image) {
				if (!isset($itemsImages[$item->parent_id])) {
					$itemsImages[$item->parent_id] = DJCatalog2ImageHelper::getImages('item', $item->parent_id);
				}
				if (!empty($itemsImages[$item->parent_id]) && isset($itemsImages[$item->parent_id][0])) {
					$item->item_image = $itemsImages[$item->parent_id][0]->fullname;
					$item->image_caption = $itemsImages[$item->parent_id][0]->caption;
					$item->image_path = $itemsImages[$item->parent_id][0]->path;
					$item->image_fullpath = $itemsImages[$item->parent_id][0]->fullpath;
				}
			}
			//$item->slug = $item->parent_id.':'.$item->alias;
		}
		$item->_link = JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug));
		
		$this->item_cursor = $item;
		
		?>
        <tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k; if ($item->featured == 1) echo ' featured_item'; ?>">
            <?php if ((int)$this->params->get('items_show_image_variants', 1)) { ?>
	            <td class="djc_image">
	                <?php if ($item->item_image) { ?>
		        	<div class="djc_image_in">
						<a rel="djimagebox-djitemvariant" class="djimagebox" title="<?php echo $item->image_caption; ?>" href="<?php echo DJCatalog2ImageHelper::getImageUrl($item->image_fullpath,'fullscreen'); ?>"><img class="img-polaroid" alt="<?php echo $item->image_caption; ?>" src="<?php echo DJCatalog2ImageHelper::getImageUrl($item->image_fullpath,'small'); ?>"/></a>
		        	</div>
				<?php } ?>
	            </td>
            <?php } ?>
				<td class="djc_td_title">
	           		<?php echo $this->escape($item->name); ?>
	            </td>
	            <?php if ((int)$this->params->get('items_show_sku_variants', '1') > 0 ) {?>
	            <td class="djc_td_sku">
	           		<?php echo $this->escape($item->sku); ?>
	            </td>
	            <?php } ?>
		<?php if ($this->params->get('items_show_intro_variants')) {?>
		<td class="djc_introtext">
			<?php if ($this->params->get('items_intro_length_variants') > 0 && $this->params->get('items_intro_trunc_variants') == '1') {
					echo DJCatalog2HtmlHelper::trimText($item->intro_desc, $this->params->get('items_intro_length_variants'));
				}
				else {
					echo $item->intro_desc; 
				}
			?>
		 </td>
		<?php } ?>
		<?php if ($price_auth && $this->params->get('items_show_price_variants') > 0) { ?>
            <td class="djc_price">
                <?php if ($item->price > 0.0) { ?>
	                <?php if ($item->price != $item->final_price ) { ?>
	                	<?php if ($this->params->get('show_old_price', '1') == '1') {?>
	        				<span class="djc_price_old"><?php echo DJCatalog2HtmlHelper::formatPrice($item->price, $this->params); ?></span><br /><span class="djc_price_new"><?php echo DJCatalog2HtmlHelper::formatPrice($item->final_price, $this->params); ?></span>
						<?php } else { ?>
							<span><?php echo DJCatalog2HtmlHelper::formatPrice($item->final_price, $this->params); ?></span>
						<?php } ?>
					<?php } else { ?>
						<span><?php echo DJCatalog2HtmlHelper::formatPrice($item->price, $this->params); ?></span>
					<?php } ?>
				<?php } ?>
            </td>
		<?php } ?>
		<?php if (!empty($this->childrenColumns)) { ?>
			<?php foreach ($this->childrenColumns as $column) { ?>
				<td class="djc_td_attribute djc_td_attribute_<?php echo $column->alias; ?>">
					<?php 
						$this->attribute_cursor = $column;
						$attributes_body = $this->loadTemplate('column_attributes');
						echo JHtml::_('content.prepare', $attributes_body, $this->params, 'com_djcatalog2.children.extra_fields');
					?>
				</td>
			<?php } ?>
		<?php } ?>
		<?php if ($show_additional_data) { ?>
			<?php 
			if (count($this->childrenAttributes) > 0) { ?>
				<td class="djc_attributes">
				<?php
				$attributes_body = '';
				foreach ($this->childrenAttributes as $attribute) {
						$this->attribute_cursor = $attribute;

						$attributes_body .= $this->loadTemplate('attributes');
					}
				?>
				<?php if ($attributes_body != '') { ?>
					<div class="djc_attributes">
						<table class="table table-condensed">
						<?php echo JHtml::_('content.prepare', $attributes_body, $this->params, 'com_djcatalog2.children.extra_fields'); ?>
						</table>
					</div>
					<?php } ?>
				</td>
				<?php } ?>
		<?php } ?>
		<?php if ($this->params->get('items_show_cart_button_variants', '1') == 1 && ($this->params->get('cart_enabled', false) || $this->params->get('cart_query_enabled', 1) )) { ?>
			<td class="djc_addtocart_cell">
			<?php if ((int)$item->available == 1) {
				echo $this->loadTemplate('addtocart');
			}?>
			</td>
		<?php } ?>
        </tr>
	<?php } ?>
	</tbody>
</table>
<?php $this->item_cursor = $this->item; ?>

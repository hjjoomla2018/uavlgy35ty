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

$show_location = (bool)((int)$this->params->get('show_location_details', true) > 0);
if ($show_location) {
	$show_location = false;
	$location_fields = array('location_country', 'location_city', 'location_address', 'location_postcode', 'location_phone', 'location_mobile', 'location_fax', 'location_website', 'location_email');
	foreach($location_fields as $param) {
		if ($this->params->get($param, '1') == '1' || $this->params->get($param, '1') == '2') {
			$show_location = true; 
			break;
		}
	}
}

$show_additional_data = false;
if ($this->params->get('items_show_attributes', '1') == '1') {
	foreach ($this->attributes as $attribute) {
		$show_additional_data = (bool)($show_additional_data || (int)$attribute->separate_column != 1);
	}
}

$producer_modals = array();
$multi_form = false;

?>
<div class="djc_table_wrap">
	<table width="100%" cellpadding="0" cellspacing="0" class="djc_items_table jlist-table category table table-condensed" id="djc_items_table">
		<thead>
			<tr>
				<?php if ((int)$this->params->get('image_link_item') != -1) { ?>
					<th class="djc_thead djc_th_image">&nbsp;</th>
				<?php } ?>
				<?php if ((int)$this->params->get('show_item_name','1') > 0 ) {?>
					<th class="djc_thead djc_th_title" nowrap="nowrap">
						<?php echo JText::_('COM_DJCATALOG2_NAME'); ?>
					</th>
				<?php } ?>
				<?php if ((int)$this->params->get('show_sku','1') > 0 ) {?>
					<th class="djc_thead djc_th_sku" nowrap="nowrap">
						<?php echo JText::_('COM_DJCATALOG2_SKU'); ?>
					</th>
				<?php } ?>
				<?php if ($this->params->get('items_show_intro')) {?>
					<th class="djc_thead djc_th_intro" nowrap="nowrap">
						<?php echo JText::_('COM_DJCATALOG2_DESCRIPTION'); ?>
					</th>
				<?php } ?>
				<?php if ($this->params->get('show_category_name') > 0) { ?>
					<th class="djc_thead djc_th_category" nowrap="nowrap">
						<?php echo JText::_('COM_DJCATALOG2_CATEGORY'); ?>
					</th>
				<?php } ?>
				<?php if ($this->params->get('show_producer_name') > 0) { ?>
					<th class="djc_thead djc_th_producer" nowrap="nowrap">
						<?php echo JText::_('COM_DJCATALOG2_PRODUCER'); ?>
					</th>
				<?php } ?>
				<?php if ($price_auth && $this->params->get('show_price') > 0) { ?>
						<th class="djc_thead djc_th_price" nowrap="nowrap">
							<?php echo JText::_('COM_DJCATALOG2_PRICE'); ?>
						</th>
				<?php } ?>
				
				<?php if( $show_location) { ?>
					<?php if ((int) $this->params->get('location_table_combine', '1') == '1') { ?>
						<th class="djc_thead djc_th_location">
							<?php echo JText::_('COM_DJCATALOG2_LOCATION'); ?>
						</th>				
					<?php } else { ?>
						<?php if ($this->params->get('location_country', 1) == '1' || $this->params->get('location_country', 1) == '2') {?>
						<th class="djc_thead djc_th_country">
							<?php echo JText::_('COM_DJCATALOG2_UP_COUNTRY'); ?>
						</th>
						<?php } ?>
						<?php if ($this->params->get('location_city', 1) == '1' || $this->params->get('location_city', 1) == '2') {?>
						<th class="djc_thead djc_th_city">
							<?php echo JText::_('COM_DJCATALOG2_UP_CITY'); ?>
						</th>
						<?php } ?>
						<?php if ($this->params->get('location_address', 1) == '1' || $this->params->get('location_address', 1) == '2') {?>
					   	<th class="djc_thead djc_th_street">
							<?php echo JText::_('COM_DJCATALOG2_UP_ADDRESS'); ?>
						 </th>
						 <?php } ?>
						<?php if ($this->params->get('location_postcode', 1) == '1' || $this->params->get('location_postcode', 1) == '2') {?>
						 <th class="djc_thead djc_th_postcode">
							 <?php echo JText::_('COM_DJCATALOG2_UP_POSTCODE'); ?>
						 </th>
						 <?php } ?>
						<?php if ($this->params->get('location_phone', 1) == '1' || $this->params->get('location_phone', 1) == '2') {?>
						 <th class="djc_thead djc_th_phone">
							 <?php echo JText::_('COM_DJCATALOG2_UP_PHONE'); ?>
						 </th>
						 <?php } ?>
						 <?php if ($this->params->get('location_mobile', 1) == '1' || $this->params->get('location_mobile', 1) == '2') {?>
						 <th class="djc_thead djc_th_mobile">
							 <?php echo JText::_('COM_DJCATALOG2_UP_MOBILE'); ?>
						 </th>
						 <?php } ?>
						 <?php if ($this->params->get('location_fax', 1) == '1' || $this->params->get('location_fax', 1) == '2') {?>
						 <th class="djc_thead djc_th_fax">
							 <?php echo JText::_('COM_DJCATALOG2_UP_FAX'); ?>
						 </th>
						 <?php } ?>
						 <?php if ($this->params->get('location_website', 1) == '1' || $this->params->get('location_website', 1) == '2') {?>
						 <th class="djc_thead djc_th_website">
							 <?php echo JText::_('COM_DJCATALOG2_UP_WEBSITE'); ?>
						 </th>
						 <?php } ?>
						 <?php if ($this->params->get('location_email', 1) == '1' || $this->params->get('location_email', 1) == '2') {?>
						 <th class="djc_thead djc_th_email">
							 <?php echo JText::_('COM_DJCATALOG2_UP_EMAIL'); ?>
						 </th>
						 <?php } ?>
					<?php } ?>
				<?php } ?>
				
				<?php if ($this->params->get('items_show_attributes', '1') && !empty($this->column_attributes)) { ?>
					<?php foreach ($this->column_attributes as $column) { ?>
						<th class="djc_thead djc_th_attributes djc_th_attribute_<?php echo $column->alias; ?>">
							<?php 
							echo $this->escape($column->name); 
							unset($this->attributes[$column->id]);
							?>
						</th>
					<?php } ?>
				<?php } ?>
				<?php if ($show_additional_data) { ?>
					<?php if (count($this->attributes)) { ?>
						<th class="djc_thead djc_th_attribute" nowrap="nowrap">
							<?php echo JText::_('COM_DJCATALOG2_CUSTOM_ATTRIBUTES'); ?>
						</th>
					<?php } ?>
				<?php } ?>
				<?php if ((int)$this->params->get('items_show_cart_button', 1) > 0 && ($this->params->get('cart_enabled', false) || $this->params->get('cart_query_enabled', 1) )) { ?>
					<th class="djc_thead djc_th_addtocart_cell">
					</th>
				<?php } ?>
					</tr>
				</thead>
				<tbody>
			<?php
		$k = 1;
		foreach($this->items as $item){
			$k = 1 - $k;
			
			$itemLink = DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug);
			if ($this->params->get('seo_advanced', 0) == 0 && (int)($this->item->catslug)) {
				$itemLink = DJCatalogHelperRoute::getItemRoute($item->slug, $this->item->catslug);
			}
			$popupLink = DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug, null, 'preview').'&tmpl=component';
			if ($this->params->get('seo_advanced', 0) == 0 && (int)($this->item->catslug)) {
				$popupLink = DJCatalogHelperRoute::getItemRoute($item->slug, $this->item->catslug, null, 'preview').'&tmpl=component';
			}
			$item->_link = $itemLink;
			$item->_popuplink = $popupLink;
			
			$item->_images = ($this->params->get('image_hover_item', 0) == 1) ? DJCatalog2ImageHelper::getImages('item', $item->id, true) : array();
			
			$this->item_cursor = $item;
			
			?>
			<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k; if ($item->featured == 1) echo ' featured_item'; ?>">
				<?php if ((int)$this->params->get('image_link_item') != -1) { ?>
					<td class="djc_td_image">
						<?php 
						$variant = 'img';
						$imgLink = (int)$this->params->get('image_link_item', 0);
						if ($imgLink == 0) {
							$variant = 'link';
						} else if ($imgLink == 1) {
							$variant = 'popup';
						} else if ($imgLink == 2) {
							$variant = 'preview';
						}
						$layout = new JLayoutFile('com_djcatalog2.listimage', null, array('component'=> 'com_djcatalog2'));
						$imageData = array(	'item' => &$item, 
											'type' => 'item', 
											'size' => 'small', 
											'variant' => $variant, 
											'hover_img' => $this->params->get('image_hover_item', 0) == 1,
											'context' => 'com_djcatalog2.items.table', 
											'params' => &$this->params);
						echo $layout->render($imageData);
						?> 
					</td>
				<?php } ?>
				<?php if ((int)$this->params->get('show_item_name','1') > 0 ) { ?>
					<td class="djc_td_title">
				   		<?php 
						if ((int)$this->params->get('show_item_name','1') == 2 ) {
							echo $item->name;
						} else if ((int)$this->params->get('show_item_name','1') == 3) { ?>
							<a class="djc_item_preview_link" href="<?php echo JRoute::_($item->_popuplink); ?>"><?php echo $this->escape($item->name); ?></a>
						<?php } else { ?>
							<a href="<?php echo JRoute::_($item->_link); ?>"><?php echo $this->escape($item->name); ?></a>
						<?php } ?>
						<?php if ($item->featured == 1) { 
							echo '<img class="djc_featured_image" alt="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" title="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" src="'.DJCatalog2ThemeHelper::getThemeImage('featured.png').'" />';
						}?>
						
						<?php if ($this->params->get('items_show_labels') == '1' || $this->params->get('items_show_labels') == '2') {
							echo $this->loadTemplate('items_labels'); 
						}?>
						
						<?php if(!empty($item->event->afterDJCatalog2DisplayTitle)) { ?>
						<div class="djc_post_title">
							<?php echo $item->event->afterDJCatalog2DisplayTitle; ?>
						</div>
						<?php } ?>
						
						<?php if ($this->params->get('compare_limit', 4) > 1 && $this->params->get('item_compare', false)) {?>
						<?php 
						$enabled = (true) ? false : true; 
						$checked = $enabled ? 'checked="checked"' : '';
						?>
						<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=items');?>" method="post" class="djc_form_compare"  style="display: none">
							<div class="checkbox">
								<label for="<?php echo 'djc_compare-'.$item->id; ?>">
								<input id="<?php echo 'djc_compare-'.$item->id; ?>" type="checkbox" name="item_id_chk" value="<?php echo $item->id; ?>" class="djc_compare_checkbox" <?php echo $checked; ?>/> 
								<?php echo JText::_('COM_DJCATALOG2_COMPARE_LBL');?></label>
							</div>
							<input type="hidden" name="item_id" value="<?php echo $item->id; ?>" />
							<input type="hidden" name="task" value="<?php echo $enabled ? 'item.removeFromCompare' : 'item.addToCompare'; ?>" />
							<noscript><button type="submit" class="btn"><?php echo JText::_('COM_DJCATALOG2_BTN_CONFIRM'); ?></button></noscript>
							
						</form>
					<?php } ?>
					</td>
				<?php } ?>
				<?php if ((int)$this->params->get('show_sku','1') > 0 ) {?>
					<td class="djc_td_sku">
						<?php echo trim($item->sku); ?>
					</td>
				<?php } ?>
			<?php if ($this->params->get('items_show_intro')) {?>
			<td class="djc_introtext">
				<?php if ($this->params->get('items_intro_length') > 0 && $this->params->get('items_intro_trunc') == '1') {
						echo DJCatalog2HtmlHelper::trimText($item->intro_desc, $this->params->get('items_intro_length'));
					}
					else {
						echo JHtml::_('content.prepare', $item->intro_desc, $this->params, 'com_djcatalog2.items.intro_desc');
					}
				?>
			 </td>
			<?php } ?>
			<?php if ($this->params->get('show_category_name') > 0 && $item->publish_category) { ?>
					<td class="djc_category" >
						<?php 
							if ($this->params->get('show_category_name') == 2) {
								?><span><?php echo $item->category; ?></span> 
							<?php }
							else {
								?><a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute($item->catslug)) ;?>"><span class="djcat_category"><?php echo $item->category; ?></span></a> 
							<?php } ?>
					</td>
				<?php } ?>
				<?php if ($this->params->get('show_producer_name') > 0) { ?>
					<td class="djc_producer">
					<?php if ($item->publish_producer && $item->producer) { ?>
						<?php 
							if ($this->params->get('show_producer_name') == 2 && $item->producer) { ?>
								<span><?php echo $item->producer;?></span>
							<?php } else if(($this->params->get('show_producer_name') == 3 && $item->producer)) { ?>
								<?php /*?><a class="modal" rel="{handler: 'iframe', size: {x: 800, y: 600}}" href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug).'&tmpl=component'); ?>"><span class="djcat_producer"><?php echo $item->producer; ?></span></a><?php */ ?> 
								<a data-toggle="modal" data-target="#djc_producer_modal-<?php echo $item->producer_id; ?>" href="#djc_producer_modal-<?php echo $item->producer_id; ?>"><span><?php echo $item->producer; ?></span></a>
								<?php $producer_modals[$item->producer_id] = array('title'=> $item->producer, 'url' => JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug).'&tmpl=component')); ?>
							<?php } else if ($item->producer){ ?>
								<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug)); ?>"><span class="djcat_producer"><?php echo $item->producer; ?></span></a>
							<?php } ?>
						<?php } ?>
					</td>
				<?php } ?>
			<?php if ($price_auth && $this->params->get('show_price') > 0) { ?>
	            <td class="djc_price">
	                <?php if ($item->price > 0.0) { ?>
		                <?php echo $this->loadTemplate('price'); ?>
					<?php } ?>
	            </td>
			<?php } ?>
			<?php if( $show_location) { ?>
				<?php if ((int) $this->params->get('location_table_combine', '1') == '1') { ?>
					<td class="djc_location">
						<?php
						$address = array();
						 
						if (($this->params->get('location_address', 1) == '1' || $this->params->get('location_address', 1) == '2') && $item->address) {
							$address[] = $item->address;
						}
						if (($this->params->get('location_postcode', 1) == '1' || $this->params->get('location_postcode', 1) == '2') && $item->postcode) {
							$address[] = $item->postcode;
						}
						if (($this->params->get('location_city', 1) == '1' || $this->params->get('location_city', 1) == '2') && $item->city) {
							$address[] = $item->city;
						}
						if (($this->params->get('location_country', 1) == '1' || $this->params->get('location_country', 1) == '2') && $item->country_name) {
							$address[] = $item->country_name;
						}
						
						if (count($address)) { ?>
						<p class="djc_address"><?php echo implode(', ', $address); ?></p>
						<?php }
						
						$contact = array();
						
						if (($this->params->get('location_phone', 1) == '1' || $this->params->get('location_phone', 1) == '2') && $item->phone) {
							$contact[] = JText::_('COM_DJCATALOG2_UP_PHONE').': <span>'.$item->phone.'</span>';
						}
						if (($this->params->get('location_mobile', 1) == '1' || $this->params->get('location_mobile', 1) == '2') && $item->mobile) {
							$contact[] = JText::_('COM_DJCATALOG2_UP_MOBILE').': <span>'.$item->mobile.'</span>';
						}
						if (($this->params->get('location_fax', 1) == '1' || $this->params->get('location_fax', 1) == '2') && $item->fax) {
							$contact[] = JText::_('COM_DJCATALOG2_UP_FAX').': <span>'.$item->fax.'</span>';
						}
						if (($this->params->get('location_website', 1) == '1' || $this->params->get('location_website', 1) == '2') && $item->website) {
							$item->website = (strpos($item->website, 'http') === 0) ? $item->website : 'http://'.$item->website;
							$item->website = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', htmlspecialchars($item->website));
							$contact[] = JText::_('COM_DJCATALOG2_UP_WEBSITE').': <span>'.$item->website.'</span>';
						}
						if (($this->params->get('location_email', 1) == '1' || $this->params->get('location_email', 1) == '2') && $item->email) {
							$item->email = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', htmlspecialchars($item->email));
							$contact[] = JText::_('COM_DJCATALOG2_UP_EMAIL').': <span>'.$item->email.'</span>';
						}
						
						if (count($contact)) { ?>
						<p class="djc_contact"><?php echo implode('<br />', $contact);?></p>
						<?php } ?>
					</td>
				<?php } else { ?>
					<?php if ($this->params->get('location_country', 1) == '1' || $this->params->get('location_country', 1) == '2') {?>
					<td class="djc_country">
						<?php echo $item->country_name; ?>
					</td>
					<?php } ?>
					<?php if ($this->params->get('location_city', 1) == '1' || $this->params->get('location_city', 1) == '2') {?>
					<td class="djc_city">
						<?php echo $item->city; ?>
					</td>
					<?php } ?>
					<?php if ($this->params->get('location_address', 1) == '1' || $this->params->get('location_address', 1) == '2') {?>
					<td class="djc_address">
						<?php echo $item->address; ?>
					</td>
					<?php } ?>
					<?php if ($this->params->get('location_postcode', 1) == '1' || $this->params->get('location_postcode', 1) == '2') {?>
					<td class="djc_postcode">
						<?php echo $item->postcode; ?>
					</td>
					<?php } ?>
					<?php if ($this->params->get('location_phone', 1) == '1' || $this->params->get('location_phone', 1) == '2') {?>
					<td class="djc_phone">
						<?php echo $item->phone; ?>
					</td>
					<?php } ?>
					<?php if ($this->params->get('location_mobile', 1) == '1' || $this->params->get('location_mobile', 1) == '2') {?>
					<td class="djc_mobile">
						<?php echo $item->mobile; ?>
					</td>
					<?php } ?>
					<?php if ($this->params->get('location_fax', 1) == '1' || $this->params->get('location_fax', 1) == '2') {?>
					<td class="djc_fax">
						<?php echo $item->fax; ?>
					</td>
					<?php } ?>
					<?php if ($this->params->get('location_website', 1) == '1' || $this->params->get('location_website', 1) == '2') {?>
					<td class="djc_website">
					<?php if (!empty($item->website)) { ?>
						<?php $item->website = (strpos($item->website, 'http') === 0) ? $item->website : 'http://'.$item->website; ?>
							<?php echo preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', $item->website); ?>
						<?php } ?>
					</td>
					<?php } ?>
					<?php if ($this->params->get('location_email', 1) == '1' || $this->params->get('location_email', 1) == '2') { ?>
					<td class="djc_email">
						<?php if (!empty($item->email)) { ?>
							<?php echo preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', $item->email); ?>
					   	<?php } ?>
					</td>
					<?php } ?>
				<?php } ?>
			<?php } ?>
			<?php if ($this->params->get('items_show_attributes', '1') && !empty($this->column_attributes) && true) { ?>
				<?php foreach ($this->column_attributes as $column) { ?>
					<td class="djc_td_attribute djc_td_attribute_<?php echo $column->alias; ?>">
						<?php 
							$this->attribute_cursor = $column;
							$attributes_body = $this->loadTemplate('column_attributes');
							echo JHtml::_('content.prepare', $attributes_body, $this->params, 'com_djcatalog2.items.extra_fields');
						?>
					</td>
				<?php } ?>
			<?php } ?>
			<?php if ($show_additional_data) { ?>
				<?php 
				if (count($this->attributes) > 0) { ?>
					<td class="djc_attributes">
					<?php 
					if ($this->params->get('items_show_attributes', '1')) {
						$layout = new JLayoutFile('com_djcatalog2.attributestable', null, array('component'=> 'com_djcatalog2'));
						echo $layout->render(array('item' => $item, 'attributes' => $this->attributes, 'context' => 'com_djcatalog2.items.extra_fields', 'params' => $this->params));
					} 
					?>
					</td>
					<?php } ?>
			<?php } ?>
			<?php if ((int)$this->params->get('items_show_cart_button', 1) > 0 && ($this->params->get('cart_enabled', false) || $this->params->get('cart_query_enabled', 1) )) { ?>
				<td class="djc_addtocart_cell">
				<?php if ((int)$item->available == 1) {
					//echo $this->loadTemplate('addtocart');
					$multi_form = $multi_form || (bool)($this->params->get('items_show_cart_button') == 2);
					//$layout = new JLayoutFile('com_djcatalog2.addtocart', JPATH_ROOT.'/components/com_djcatalog2/layouts');
					$layout = new JLayoutFile('com_djcatalog2.addtocart', null, array('component'=> 'com_djcatalog2'));
					echo $layout->render(array('item' => $item, 'context' => 'com_djcatalog2.items.addtocart', 'params' => $this->params, 'multi_form' => (bool)($this->params->get('items_show_cart_button') == 2)));
				}?>
				</td>
			<?php } ?>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>

<?php if ($multi_form) {?>
<div class="row-fluid">
	<div class="span12">
		<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute());?>" method="post" class="djc_addtocart_submitter">
			<input type="submit" class="btn" value="<?php echo JText::_('COM_DJCATALOG2_ADD_TO_CART'); ?>" disabled="disabled" />
			<input type="hidden" name="task" value="cart.update_batch" />
			<input type="hidden" name="option" value="com_djcatalog2" />
			<?php echo JHtml::_( 'form.token' ); ?>
		</form>
	</div>
</div>
<?php } ?>

<?php if (count($producer_modals) > 0) {?>
	<?php foreach($producer_modals as $pid => $producer) {?>
		<?php echo JHtmlBootstrap::renderModal('djc_producer_modal-'.$pid, array('height' => '600px', 'url' => $producer['url'], 'title'=> $producer['title'])); ?>
	<?php } ?>
<?php } ?>

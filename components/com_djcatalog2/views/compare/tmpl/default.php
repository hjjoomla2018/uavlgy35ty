<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

//$columns = (count($this->comparable) > 0) ? count($this->items)+1 : count($this->items);
$columns = count($this->items) + 1;

$nullDate = JFactory::getDbo()->getNullDate();
$user		= JFactory::getUser();
$price_auth = ($this->params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;

?>

<?php if ($this->params->get( 'show_page_heading', 1)) { ?>
<h1
	class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php } ?>
<div id="djcatalog"
	class="djc_compareview<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">
	
	<?php /*foreach ($this->items as $item) {?>
		<?php echo $item->name?><br />
	<?php }*/ ?>
	
	<?php if (count($this->items) > 0) {?>
	
	<?php foreach($this->items as $idx => $item) {
		$itemLink = DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug);
		$this->items[$idx]->_link = $itemLink;
		$this->items[$idx]->_images = ($this->params->get('compare_image_hover_item', 0) == 1) ? DJCatalog2ImageHelper::getImages('item', $item->id, true) : array();
		
	} ?>
	
	<table class="djc_compare_items table table-striped">
		<colgroup class="djc_compare_colgroup djc_compare_colgroup-<?php echo count($this->items); ?>">
			<col class="djc_compare_col djc_compare_col_first" />
			<?php for ($i=0; $i < count($this->items); $i++ ) {?>
				<col class="djc_compare_col djc_compare_col_item" style="width: <?php echo 100/$columns; ?>%; "/>
			<?php } ?>			
		</colgroup>
		<?php /* ?>
		<thead>
			<tr>
				<?php if (count($this->comparable) > 0) {?>
				<th class="djc_compare_heading djc_compare_heading_label" style="width: <?php echo 100/$columns; ?>%;"></th>
				<?php } ?>
				<?php foreach($this->items as $item) { ?>
					<th class="djc_compare_heading" style="width: <?php echo 100/$columns; ?>%;">
						<?php 
						$this->item_cursor = $item;
						echo $this->loadTemplate('item');
						?>
					</th>
				<?php } ?>
					
			</tr>
		</thead>
		<?php */ ?>
		<tbody>
			<?php if ((int)$this->params->get('compare_show_item_name','1') > 0 ) {?>
			<tr>
				<td class="djc_compare_item_label">
					<?php echo JText::_('COM_DJCATALOG2_NAME'); ?>
				</td>
				<?php foreach($this->items as $item) { ?>
					<td class="djc_compare_item_value">
						<div class="djc_title">
							<h3>
							<?php 
							if ((int)$this->params->get('compare_show_item_name','1') == 2 ) {
								echo $item->name;
							} else { ?>
								<a href="<?php echo JRoute::_($item->_link); ?>"><?php echo $this->escape($item->name); ?></a>
							<?php } ?>
							</h3>
						</div>
					
						<?php if(!empty($item->event->afterDJCatalog2DisplayTitle)) { ?>
						<div class="djc_post_title">
							<?php echo $item->event->afterDJCatalog2DisplayTitle; ?>
						</div>
						<?php } ?>
					</td>
				<?php } ?>
			</tr>
			<?php } ?>
			
			<?php if ((int)$this->params->get('compare_show_sku', 1) == 1) { ?>
			<tr>
				<td class="djc_compare_item_label">
					<?php echo JText::_('COM_DJCATALOG2_SKU'); ?>
				</td>
				<?php foreach($this->items as $item) { ?>
					<td class="djc_compare_item_value">
						<?php if ($item->sku != '') {?>
							<span><?php echo trim($item->sku); ?></span>
						<?php } ?>
					</td>
				<?php } ?>
			</tr>
			<?php } ?>
			
			<?php if ((int)$this->params->get('compare_image_link_item', 0) != -1) { ?>
			<tr>
				<td class="djc_compare_item_label"><?php echo JText::_('COM_DJCATALOG2_IMAGE'); ?></td>
				<?php foreach($this->items as $item) { ?>
					<td class="djc_compare_item_value">
						<?php if ($item->item_image) {?>
						<?php $hoverEffect = (bool) ($this->params->get('compare_image_hover_item', 0) == 1 && count($item->_images) > 1) ; ?>
						<div class="djc_image <?php if ($hoverEffect) echo 'djc_hover_image'; ?>">
							<?php if ((int)$this->params->get('compare_image_link_item', 0) == 1) { ?>
								<a rel="djimagebox-djitem" class="djimagebox" title="<?php echo $item->image_caption; ?>" href="<?php echo DJCatalog2ImageHelper::getImageUrl($item->image_fullpath,'fullscreen'); ?>">
									<img class="img-polaroid" alt="<?php echo $item->image_caption; ?>" src="<?php echo DJCatalog2ImageHelper::getImageUrl($item->image_fullpath,'medium'); ?>"/>
									<?php if ($hoverEffect) {?>
										<img class="img-polaroid" alt="<?php echo $item->_images[1]->caption; ?>" src="<?php echo $item->_images[1]->medium; ?>"/>
									<?php } ?>
								</a>
							<?php } else { ?>
								<a href="<?php echo JRoute::_($item->_link); ?>">
									<img class="img-polaroid" alt="<?php echo $item->image_caption; ?>" src="<?php echo DJCatalog2ImageHelper::getImageUrl($item->image_fullpath,'medium'); ?>"/>
									<?php if ($hoverEffect) {?>
										<img class="img-polaroid" alt="<?php echo $item->_images[1]->caption; ?>" src="<?php echo $item->_images[1]->medium; ?>"/>
									<?php } ?>
								</a>
							<?php } ?>
						</div>
						<?php } ?>
					</td>
				<?php } ?>
			</tr>
		<?php } ?>
		
		<?php if ($price_auth && $this->params->get('compare_show_price') > 0) { ?>
		<tr>
			<td class="djc_compare_item_label">
				<?php echo JText::_('COM_DJCATALOG2_PRICE'); ?>
			</td>
			<?php foreach($this->items as $item) { ?>
				<td class="djc_compare_item_value">
					<?php if ($this->params->get('compare_show_price') == 2 || ( $this->params->get('compare_show_price') == 1 && $item->price > 0.0)) { ?>
						<?php $this->item_cursor = $item; ?>
						<div class="djc_price">
							<?php echo $this->loadTemplate('item_price'); ?>
						</div>
					<?php } ?>
				</td>
			<?php } ?>
		</tr>
		<?php } ?>
		
		<?php if ($this->params->get('compare_items_show_cart_button', '1') == 1) { ?>
		<tr>
			<td class="djc_compare_item_label">
			</td>
			<?php foreach($this->items as $item) { ?>
				<?php $this->item_cursor = $item; ?>
				<td class="djc_compare_item_value">
					<?php if ((int)$item->available == 1) { 
						$layout = new JLayoutFile('com_djcatalog2.addtocart', null, array('component'=> 'com_djcatalog2'));
						echo $layout->render(array('item' => $item, 'context' => 'com_djcatalog2.compare.addtocart', 'params' => $this->params));
					} ?>
				</td>
			<?php } ?>
		</tr>
		<?php } ?>
		
		<?php if ($this->params->get('compare_show_category_name') > 0) { ?>
		<tr>
			<td class="djc_compare_item_label"><?php echo JText::_('COM_DJCATALOG2_CATEGORY'); ?></td>
			<?php foreach($this->items as $item) { ?>
				<td class="djc_compare_item_value">
					<?php if ($item->publish_category) { ?>
						<?php 
						if ($this->params->get('compare_show_category_name') == 2) { ?>
							<span><?php echo $item->category; ?></span> 
						<?php }
						else { ?>
							<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute($item->catslug));?>">
								<span><?php echo $item->category; ?></span>
							</a> 
						<?php } ?>
				<?php } ?>
				</td>
			<?php } ?>
		</tr>
		<?php } ?>
		
		<?php if ($this->params->get('compare_show_producer_name') > 0) { ?>
		<tr>
			<td class="djc_compare_item_label"><?php echo JText::_('COM_DJCATALOG2_PRODUCER'); ?></td>
			<?php foreach($this->items as $item) { ?>
				<td class="djc_compare_item_value">
					<?php if ($item->producer && $item->publish_producer) { ?>
					<?php if ($this->params->get('compare_show_producer_name') == 2) { ?>
						<span><?php echo $item->producer;?></span>
					<?php } else { ?>
						<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug)); ?>">
							<span><?php echo $item->producer; ?></span>
						</a> 
					<?php } ?>
					<?php if ($this->params->get('compare_show_producers_items', 1)) { ?>
						<a class="djc_producer_items_link btn btn-mini" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&cm=0&pid='.$item->prodslug); ?>"><span><?php echo JText::_('COM_DJCATALOG2_SHOW_PRODUCERS_ITEMS'); ?></span></a>
					<?php } ?>
				<?php } ?>
				</td>
			<?php } ?>
		</tr>
		<?php } ?>
		
		<?php if ($this->params->get('compare_items_show_intro')) { ?>
		<tr>
			<td class="djc_compare_item_label"><?php echo JText::_('COM_DJCATALOG2_DESCRIPTION'); ?></td>
			<?php foreach($this->items as $item) { ?>
				<td class="djc_compare_item_value">
					<div class="djc_introtext">
						<?php if ($this->params->get('compare_items_intro_length') > 0  && $this->params->get('compare_items_intro_trunc') == '1') {
								?><p><?php echo DJCatalog2HtmlHelper::trimText($item->intro_desc, $this->params->get('compare_items_intro_length'));?></p><?php
							}
							else {
								echo JHtml::_('content.prepare', $item->intro_desc, $this->params, 'com_djcatalog2.items.intro_desc');
							}
						?>
					</div>
				</td>
			<?php } ?>
		</tr>
		<?php } ?>
		
		<?php /*if ($this->params->get('compare_items_show_attributes', '1') && count($this->attributes) > 0) { ?>
		<tr>
			<td class="djc_compare_item_label"><?php echo JText::_('COM_DJCATALOG2_CUSTOM_ATTRIBUTES'); ?></td>
			<?php foreach($this->items as $item) { ?>
				<td class="djc_compare_item_value">
				<?php 
				$attributes_body = '';
				foreach ($this->attributes as $attribute) {
					$this->attribute_cursor = $attribute;
					$this->item_cursor = $item;
					$attributes_body .= $this->loadTemplate('item_attributes');
				}
				?>
				<?php if ($attributes_body != '') { ?>
					<div class="djc_attributes">
						<table class="table table-condensed">
						<?php echo JHtml::_('content.prepare', $attributes_body, $this->params, 'com_djcatalog2.items.extra_fields'); ?>
						</table>
					</div>
				<?php } ?>
				</td>
			<?php } ?>
		</tr>
		<?php }*/ ?>
			
		<?php if (count($this->comparable) > 0) { ?>
			<?php foreach ($this->comparable as $comparable) { ?>
				<?php 
				
				$attr = '_ef_'.$comparable->alias;
				
				$found = false; 
				foreach ($this->items as $item) {
					if (!empty($item->$attr)) {
						$found = true;
						break;
					}
				} ?>
				
				<?php if ($found) { ?>
					<tr>
						<td class="djc_compare_label">
						<?php 
						if ($comparable->imagelabel != '') {
							echo '<img class="djc_attribute-imglabel" alt="'.htmlspecialchars($comparable->name).'" src="'.JUri::base().$comparable->imagelabel.'" />';
						} else {
							echo '<span class="djc_attribute-label">'.htmlspecialchars($comparable->name).'</span>';
						}
						?>
						</td>
						<?php foreach ($this->items as $item) {?>
							<td class="djc_compare_item">
								<?php 
								$this->attribute_cursor = $comparable;
								$this->item_cursor = $item;
								echo $this->loadTemplate('compare_attribute');
								?>
							</td>
						<?php } ?>
					</tr>
				<?php } ?>
			<?php } ?>
		<?php } ?>
		
		<?php if( (int)$this->params->get('compare_show_location_details', true) > 0) { ?>
		<tr>
			<td class="djc_compare_item_label">
				<?php echo JText::_('COM_DJCATALOG2_LOCATION'); ?>
			</td>
			<?php foreach($this->items as $item) { ?>
				<td class="djc_compare_item_value">
					<div class="djc_location">
						<?php
						$address = array();
						 
						if (($this->params->get('compare_location_address', 1) == '1' || $this->params->get('compare_location_address', 1) == '3') && $item->address) {
							$address[] = $item->address;
						}
						if (($this->params->get('compare_location_postcode', 1) == '1' || $this->params->get('compare_location_postcode', 1) == '3') && $item->postcode) {
							$address[] = $item->postcode;
						}
						if (($this->params->get('compare_location_city', 1) == '1' || $this->params->get('compare_location_city', 1) == '3') && $item->city) {
							$address[] = $item->city;
						}
						if (($this->params->get('compare_location_country', 1) == '1' || $this->params->get('compare_location_country', 1) == '3') && $item->country_name) {
							$address[] = $item->country_name;
						}
						
						if (count($address)) { ?>
						<p class="djc_address"><?php echo implode(', ', $address); ?></p>
						<?php }
						
						$contact = array();
						
						if (($this->params->get('compare_location_phone', 1) == '1' || $this->params->get('compare_location_phone', 1) == '3') && $item->phone) {
							$contact[] = JText::_('COM_DJCATALOG2_UP_PHONE').': <span>'.$item->phone.'</span>';
						}
						if (($this->params->get('compare_location_mobile', 1) == '1' || $this->params->get('compare_location_mobile', 1) == '3') && $item->mobile) {
							$contact[] = JText::_('COM_DJCATALOG2_UP_MOBILE').': <span>'.$item->mobile.'</span>';
						}
						if (($this->params->get('compare_location_fax', 1) == '1' || $this->params->get('compare_location_fax', 1) == '3') && $item->fax) {
							$contact[] = JText::_('COM_DJCATALOG2_UP_FAX').': <span>'.$item->fax.'</span>';
						}
						if (($this->params->get('compare_location_website', 1) == '1' || $this->params->get('compare_location_website', 1) == '3') && $item->website) {
							$item->website = (strpos($item->website, 'http') === 0) ? $item->website : 'http://'.$item->website;
							$item->website = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', htmlspecialchars($item->website));
							$contact[] = JText::_('COM_DJCATALOG2_UP_WEBSITE').': <span>'.$item->website.'</span>';
						}
						if (($this->params->get('compare_location_email', 1) == '1' || $this->params->get('compare_location_email', 1) == '3') && $item->email) {
							$item->email = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', htmlspecialchars($item->email));
							$contact[] = JText::_('COM_DJCATALOG2_UP_EMAIL').': <span>'.$item->email.'</span>';
						}
						
						if (count($contact)) { ?>
						<p class="djc_contact"><?php echo implode('<br />', $contact);?></p>
						<?php } ?>
					</div>
				</td>
			<?php } ?>
		</tr>
		<?php } ?>
		
		<tr>
			<td class="djc_compare_item_label"></td>
			<?php foreach($this->items as $item) { ?>
				<?php $this->item_cursor = $item; ?>
				<td class="djc_compare_item_value">
					<?php if ((int)$this->params->get('compare_show_author', 0) > 0 && $item->author) { ?>
						<div class="djc_author">
							<?php echo JText::_('COM_DJCATALOG2_CREATED_BY').': '; ?>
							<?php if ((int)$this->params->get('compare_show_author_item') == 1 && $item->created_by) {?>
								<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&aid='.$item->created_by.':'.JApplication::stringURLSafe($item->author));?>"><span><?php echo $item->author; ?></span></a>
							<?php } else {?>
								<span><?php echo $item->author; ?></span>
							<?php } ?>
						</div>
					<?php } ?>
					
					<?php if ((int)$this->params->get('compare_show_date', 0) == 1 && $item->created != $nullDate) { ?>
						<div class="djc_date djc_created_date">
							<?php echo JText::_('COM_DJCATALOG2_CREATED_ON').': '; ?>
							<span><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></span>
						</div>
					<?php } ?>
					
					<?php if ((int)$this->params->get('compare_show_publishdate', 0) == 1 && $item->publish_up != $nullDate) { ?>
						<div class="djc_date djc_publish_date">
							<?php echo JText::_('COM_DJCATALOG2_PUBLISHED_ON').': '; ?>
							<span><?php echo JHtml::_('date', $item->publish_up, JText::_('DATE_FORMAT_LC3')); ?></span>
						</div>
					<?php } ?>
					
					<?php if ((int)$this->params->get('compare_show_hits', 0) == 1) { ?>
						<div class="djc_hits">
							<?php echo JText::_('COM_DJCATALOG2_HITS').': '; ?>
							<span><?php echo $item->hits; ?></span>
						</div>
					<?php } ?>
					
					<form action="<?php echo JRoute::_(DJCatalog2HelperRoute::getComparisonRoute()); ?>" method="post" class="djc_remove_compare_form">
						<input type="hidden" name="item_id" value="<?php echo $item->id; ?>" />
						<input type="hidden" name="option" value="com_djcatalog2" />
						<input type="hidden" name="task" value="item.removeFromCompare" />
						<input type="hidden" name="return" value="<?php echo base64_encode(DJCatalog2HelperRoute::getComparisonRoute()); ?>" />
						<button type="submit" class="btn" title="<?php echo JText::_('COM_DJCATALOG2_COMPARE_REMOVE_BTN'); ?>">&times;</button>
					</form>
				</td>
			<?php } ?>
		</tr>
		</tbody>
	</table>
	<?php } else { ?>
		<p class="alert alert-info text-center"><?php echo JText::_('COM_DJCATALOG2_NO_ITEMS_TO_COMPARE');?><br /><a href="<?php echo JUri::root(); ?>" class="btn"><?php echo JText::_('COM_DJCATALOG2_NO_ITEMS_TO_COMPARE_BTN'); ?></a></p>
	<?php } ?>
	<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
	?>
</div>


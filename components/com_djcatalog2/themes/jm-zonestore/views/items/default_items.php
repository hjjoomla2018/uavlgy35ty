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
?>

<?php
$k = 0; 
$i = 1; 
$col_count = $this->params->get('items_columns',2);
$col_width = ((100/$col_count)-0.01);

$nullDate = JFactory::getDbo()->getNullDate();

$producer_modals = array();

$multi_form = false;

foreach ($this->items as $item) {
	$itemLink = DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug);
	if ($this->params->get('seo_advanced', 0) == 0 && (int)($this->item->catslug)) {
		$itemLink = DJCatalogHelperRoute::getItemRoute($item->slug, $this->item->catslug);
	}
	$popupLink = DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug, null, 'preview').'&tmpl=component';
	if ($this->params->get('seo_advanced', 0) == 0 && (int)($this->item->catslug)) {
		$popupLink = DJCatalogHelperRoute::getItemRoute($item->slug, $this->item->catslug, null, 'preview').'&tmpl=component';
	}
	
	$contactLink = DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug, null, 'contact').'&tmpl=component';
	if ($this->params->get('seo_advanced', 0) == 0 && (int)($this->item->catslug)) {
		$contactLink = DJCatalogHelperRoute::getItemRoute($item->slug, $this->item->catslug, null, 'contact').'&tmpl=component';
	}
	
	$item->_link = $itemLink;
	$item->_popuplink = $popupLink;
	$item->_contactlink = $contactLink;
	
	$item->_images = ($this->params->get('image_hover_item', 0) == 1) ? DJCatalog2ImageHelper::getImages('item', $item->id, true) : array();
	
	$this->item_cursor = $item;
	
	$newrow_open = $newrow_close = false;
	if ($k % $col_count == 0) $newrow_open = true;
	if (($k+1) % $col_count == 0 || count($this->items) <= $k+1) $newrow_close = true;
			
	$rowClassName = 'djc_clearfix djc_item_row djc_item_row';
	if ($k == 0) $rowClassName .= '_first';
	if (count($this->items) <= ($k + $this->params->get('items_columns',2))) $rowClassName .= '_last';
	
	$colClassName ='djc_item_col';
	if ($k % $col_count == 0) { $colClassName .= '_first'; }
	else if (($k+1) % $col_count == 0) { $colClassName .= '_last'; }
	else {$colClassName .= '_'.($k % $col_count);}
	$k++;
	
	if ($newrow_open) { $i = 1 - $i; ?>
	<div class="<?php echo $rowClassName.'_'.$i; ?> djc2_cols_<?php echo $col_count ?>">
	<?php }
	?>
		<div class="djc_item pull_left <?php echo $colClassName; if ($item->featured == 1) echo ' featured_item'; ?>" style="width:<?php echo $col_width; ?>%">
		<div class="djc_item_bg">
		<div class="djc_item_in djc_clearfix">
		<?php if ($item->featured == 1) { 
			echo '<img class="djc_featured_image" alt="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" title="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" src="'.DJCatalog2ThemeHelper::getThemeImage('featured.png').'" />';
		}?>
			<?php $hoverEffect = (bool) ($this->params->get('image_hover_item', 0) == 1 && count($item->_images) > 1) ; ?>

			<div class="djc_image_wrapper <?php if ($hoverEffect) echo 'djc_hover_image'; ?>">
			<?php if ((int)$this->params->get('image_link_item', 0) != -1) { ?>
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
				                       'size' => 'medium',
				                       'variant' => $variant,
				                       'hover_img' => $this->params->get('image_hover_item', 0) == 1,
				                       'context' => 'com_djcatalog2.items.list',
				                       'params' => &$this->params);
				echo $layout->render($imageData);
				?>
			<?php } ?>
			<?php if ($this->params->get('showreadmore_item') || $this->params->get('show_preview_item')  /*|| $item->params->get('show_contact_form')*/) { ?>
				<div class="djc_readon">
					<?php if ($this->params->get('show_preview_item')) {?>
						<a class="btn btn-light djc_item_preview" href="<?php echo JRoute::_($item->_popuplink); ?>"><?php echo JText::sprintf('COM_DJCATALOG2_READMORE_PREVIEW'); ?></a>
					<?php } ?>
					<?php if ($this->params->get('showreadmore_item')) {?>
						<a class="btn" href="<?php echo JRoute::_($item->_link); ?>"><?php echo JText::sprintf('COM_DJCATALOG2_READMORE'); ?></a>
					<?php } ?>
					<?php /*if ($item->params->get('show_contact_form')) { ?>
				<a class="btn djc_item_contact" href="<?php echo JRoute::_($item->_contactlink); ?>"><?php echo JText::_('COM_DJCATALOG2_CONTACT_FORM_OPEN'); ?></a>
				<?php }*/ ?>
				</div>
			<?php } ?>
			</div>
		<?php if ((int)$this->params->get('show_item_name','1') > 0 ) {?>
		<div class="djc_title">
			<h3>
			<?php 
			if ((int)$this->params->get('show_item_name','1') == 2 ) {
				echo $item->name;
			} else if ((int)$this->params->get('show_item_name','1') == 3) { ?>
				<a class="djc_item_preview_link" href="<?php echo JRoute::_($item->_popuplink); ?>"><?php echo $this->escape($item->name); ?></a>
			<?php } else { ?>
				<a href="<?php echo JRoute::_($item->_link); ?>"><?php echo $this->escape($item->name); ?></a>
			<?php } ?>
			</h3>
		</div>

		<?php } ?>
		
		<?php if ($this->params->get('items_show_labels', 0) == '1' || $this->params->get('items_show_labels', 0) == '3') {
			echo $this->loadTemplate('items_labels'); 
		}?>
		<div class="djc_description">
			<div class="djc_item_info">
				<?php if ($this->params->get('show_category_name') > 0 && $item->publish_category) { ?>
				<div class="djc_category_info">
					<?php 
					if ($this->params->get('show_category_name') == 2) {
						//echo JText::_('COM_DJCATALOG2_CATEGORY').': '?>
						<span><?php echo $item->category; ?></span>
					<?php }
					else {
						//echo JText::_('COM_DJCATALOG2_CATEGORY').': ';?>
						<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute($item->catslug));?>">
							<span><?php echo $item->category; ?></span>
						</a> 
					<?php } ?>
				</div>
				<?php } ?>
				<?php if(!empty($item->event->afterDJCatalog2DisplayTitle)) { ?>
					<div class="djc_post_title">
						<?php echo $item->event->afterDJCatalog2DisplayTitle; ?>
					</div>
				<?php } ?>

				<div class="djc_price_stock">
					<?php
					if ($price_auth && ($this->params->get('show_price') == 2 || ( $this->params->get('show_price') == 1 && $item->price > 0.0))) {
						?>
						<div class="djc_price">
							<?php echo $this->loadTemplate('price'); ?>
						</div>
					<?php } ?>

					<?php
					$stockInfo = $this->params->get('show_outstock_info', 0);
					if ((int)$stockInfo > 0) {
						$stockData = array('item' => $item, 'type' => $stockInfo, 'params' => $this->params);
						$layout = new JLayoutFile('com_djcatalog2.stockinfo', null, array('component'=> 'com_djcatalog2'));
						?>
						<p class="djc_stock"><?php echo $layout->render($stockData); ?></p>
					<?php } ?>
				</div>

				<?php if ($this->params->get('show_producer_name') > 0 && $item->producer && $item->publish_producer) { ?>
				<div class="djc_producer_info">
					<?php if ($this->params->get('show_producer_name') == 2) { ?>
						<?php echo JText::_('COM_DJCATALOG2_PRODUCER').': '; ?>
						<span><?php echo $item->producer;?></span>
					<?php /*} else if(($this->params->get('show_producer_name') == 3)) { ?>
						<?php echo JText::_('COM_DJCATALOG2_PRODUCER').': ';?>
						<a class="modal" rel="{handler: 'iframe', size: {x: 800, y: 600}}" href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug).'&tmpl=component'); ?>">
							<span><?php echo $item->producer; ?></span>
						</a>
					<?php } else {*/ ?>
					<?php } else if(($this->params->get('show_producer_name') == 3)) { ?>
						<?php echo JText::_('COM_DJCATALOG2_PRODUCER').': ';?>
						<a data-toggle="modal" data-target="#djc_producer_modal-<?php echo $item->producer_id; ?>" href="#djc_producer_modal-<?php echo $item->producer_id; ?>" data-href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug).'&tmpl=component'); ?>" data-modaltitle="<?php echo $this->escape($item->producer); ?>">
							<span><?php echo $item->producer; ?></span>
						</a>
						<?php $producer_modals[$item->producer_id] = array('title'=> $item->producer, 'url' => JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug).'&tmpl=component')); ?>
					<?php } else { ?>
						<?php echo JText::_('COM_DJCATALOG2_PRODUCER').': ';?>
						<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug)); ?>">
							<span><?php echo $item->producer; ?></span>
						</a> 
					<?php } ?>
					<?php if ($this->params->get('show_producers_items', 1)) { ?>
						<a class="djc_producer_items_link btn btn-mini" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&cm=0&pid='.$item->prodslug); ?>"><span><?php echo JText::_('COM_DJCATALOG2_SHOW_PRODUCERS_ITEMS'); ?></span></a>
					<?php } ?>
				</div>
				<?php } ?>
				
				<?php if ((int)$this->params->get('show_sku', 1) == 1 && $item->sku != '') { ?>
					<div class="djc_sku">
						<?php echo JText::_('COM_DJCATALOG2_SKU').': '; ?>
						<span><?php echo trim($item->sku); ?></span>
					</div>
				<?php } ?>
				
				<?php if ((int)$this->params->get('show_author', 0) > 0 && $item->author) { ?>
					<div class="djc_author">
						<?php echo JText::_('COM_DJCATALOG2_CREATED_BY').': '; ?>
						<?php if ((int)$this->params->get('show_author_item') == 1 && $item->created_by) {?>
							<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&aid='.$item->created_by.':'.JApplication::stringURLSafe($item->author));?>"><span><?php echo $item->author; ?></span></a>
						<?php } else {?>
							<span><?php echo $item->author; ?></span>
						<?php } ?>
					</div>
				<?php } ?>
				
				<?php if ((int)$this->params->get('show_date', 0) == 1 && $item->created != $nullDate) { ?>
					<div class="djc_date djc_created_date">
						<?php echo JText::_('COM_DJCATALOG2_CREATED_ON').': '; ?>
						<span><?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')); ?></span>
					</div>
				<?php } ?>
				
				<?php if ((int)$this->params->get('show_publishdate', 0) == 1 && $item->publish_up != $nullDate) { ?>
					<div class="djc_date djc_publish_date">
						<?php echo JText::_('COM_DJCATALOG2_PUBLISHED_ON').': '; ?>
						<span><?php echo JHtml::_('date', $item->publish_up, JText::_('DATE_FORMAT_LC3')); ?></span>
					</div>
				<?php } ?>
				
				<?php if ((int)$this->params->get('show_hits', 0) == 1) { ?>
					<div class="djc_hits">
						<?php echo JText::_('COM_DJCATALOG2_HITS').': '; ?>
						<span><?php echo $item->hits; ?></span>
					</div>
				<?php } ?>
				
				<?php if( (int)$this->params->get('show_location_details', true) > 0) { ?>
					<div class="djc_location">
						<?php
						$address = array();
						 
						if (($this->params->get('location_address', 1) == '1' || $this->params->get('location_address', 1) == '3') && $item->address) {
							$address[] = $item->address;
						}
						if (($this->params->get('location_postcode', 1) == '1' || $this->params->get('location_postcode', 1) == '3') && $item->postcode) {
							$address[] = $item->postcode;
						}
						if (($this->params->get('location_city', 1) == '1' || $this->params->get('location_city', 1) == '3') && $item->city) {
							$address[] = $item->city;
						}
						if (($this->params->get('location_country', 1) == '1' || $this->params->get('location_country', 1) == '3') && $item->country_name) {
							$address[] = $item->country_name;
						}
						
						if (count($address)) { ?>
						<p class="djc_address"><?php echo implode(', ', $address); ?></p>
						<?php }
						
						$contact = array();
						
						if (($this->params->get('location_phone', 1) == '1' || $this->params->get('location_phone', 1) == '3') && $item->phone) {
							$contact[] = JText::_('COM_DJCATALOG2_UP_PHONE').': <span>'.$item->phone.'</span>';
						}
						if (($this->params->get('location_mobile', 1) == '1' || $this->params->get('location_mobile', 1) == '3') && $item->mobile) {
							$contact[] = JText::_('COM_DJCATALOG2_UP_MOBILE').': <span>'.$item->mobile.'</span>';
						}
						if (($this->params->get('location_fax', 1) == '1' || $this->params->get('location_fax', 1) == '3') && $item->fax) {
							$contact[] = JText::_('COM_DJCATALOG2_UP_FAX').': <span>'.$item->fax.'</span>';
						}
						if (($this->params->get('location_website', 1) == '1' || $this->params->get('location_website', 1) == '3') && $item->website) {
							$item->website = (strpos($item->website, 'http') === 0) ? $item->website : 'http://'.$item->website;
							$item->website = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', htmlspecialchars($item->website));
							$contact[] = JText::_('COM_DJCATALOG2_UP_WEBSITE').': <span>'.$item->website.'</span>';
						}
						if (($this->params->get('location_email', 1) == '1' || $this->params->get('location_email', 1) == '3') && $item->email) {
							$item->email = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', htmlspecialchars($item->email));
							$contact[] = JText::_('COM_DJCATALOG2_UP_EMAIL').': <span>'.$item->email.'</span>';
						}
						
						if (count($contact)) { ?>
						<p class="djc_contact"><?php echo implode('<br />', $contact);?></p>
						<?php } ?>
					</div>
				<?php } ?>
			
			</div>
			
			<?php if ($this->params->get('items_show_intro')) { ?>
			<div class="djc_introtext">
				<?php if ($this->params->get('items_intro_length') > 0  && $this->params->get('items_intro_trunc') == '1') {
						?><p><?php echo DJCatalog2HtmlHelper::trimText($item->intro_desc, $this->params->get('items_intro_length'));?></p><?php
					}
					else {
						echo JHtml::_('content.prepare', $item->intro_desc, $this->params, 'com_djcatalog2.items.intro_desc');
					}
				?>
			</div>
			<?php } ?>

			<?php
			if ($this->params->get('items_show_attributes', '1')) {
				$layout = new JLayoutFile('com_djcatalog2.attributestable', null, array('component'=> 'com_djcatalog2'));
				echo $layout->render(array('item' => $item, 'attributes' => $this->attributes, 'context' => 'com_djcatalog2.items.extra_fields', 'params' => $this->params));
			}
			?>
		</div>
			<?php if ((int)$item->available == 1 && (int)$this->params->get('items_show_cart_button', '1') > 0) {
				//echo $this->loadTemplate('addtocart');
				$multi_form = $multi_form || (bool)($this->params->get('items_show_cart_button') == 2);
				$layout = new JLayoutFile('com_djcatalog2.addtocart', null, array('component'=> 'com_djcatalog2'));
				echo $layout->render(array('item' => $item, 'context' => 'com_djcatalog2.items.addtocart', 'params' => $this->params, 'multi_form' => (bool)($this->params->get('items_show_cart_button') == 2)));
			}?>

			<?php if ($hoverEffect) : ?>

			<?php if ($this->params->get('showreadmore_item') || $this->params->get('show_preview_item')  /*|| $item->params->get('show_contact_form')*/) { ?>
				<div class="djc_readon">
					<?php if ($this->params->get('show_preview_item')) {?>
						<a class="btn btn-light djc_item_preview" href="<?php echo JRoute::_($item->_popuplink); ?>"><?php echo JText::_('COM_DJCATALOG2_READMORE_PREVIEW'); ?></a>
					<?php } ?>
					<?php if ($this->params->get('showreadmore_item')) {?>
					<a class="btn" href="<?php echo JRoute::_($item->_link); ?>"><?php echo JText::_('COM_DJCATALOG2_READMORE'); ?></a>
					<?php } ?>
					<?php /*if ($item->params->get('show_contact_form')) { ?>
					<a class="btn djc_item_contact" href="<?php echo JRoute::_($item->_contactlink); ?>"><?php echo JText::_('COM_DJCATALOG2_CONTACT_FORM_OPEN'); ?></a>
					<?php }*/ ?>
				</div>
			<?php } ?>

			<?php endif; ?>

			<?php if ($this->params->get('compare_limit', 4) > 1 && $this->params->get('item_compare', false)) {?>
				<?php
				$enabled = (true) ? false : true;
				$checked = $enabled ? 'checked="checked"' : '';
				?>
				<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=items');?>" method="post" class="djc_form_compare"  style="display: none">
					<div class="checkbox"><label for="<?php echo 'djc_compare-'.$item->id; ?>">
							<input id="<?php echo 'djc_compare-'.$item->id; ?>" type="checkbox" name="item_id_chk" value="<?php echo $item->id; ?>" class="djc_compare_checkbox" <?php echo $checked; ?>/>
							<?php echo JText::_('COM_DJCATALOG2_COMPARE_LBL');?></label>
					</div>
					<input type="hidden" name="item_id" value="<?php echo $item->id; ?>" />
					<input type="hidden" name="task" value="<?php echo $enabled ? 'item.removeFromCompare' : 'item.addToCompare'; ?>" />
					<noscript><button type="submit" class="btn"><?php echo JText::_('COM_DJCATALOG2_BTN_CONFIRM'); ?></button></noscript>
				</form>
			<?php } ?>

		 </div>
 	</div>
	<div class="djc_clear"></div>
	</div>
	<?php if ($newrow_close) { ?>
		</div>
	<?php } ?>
<?php } ?>

<?php if ($multi_form) {?>
	<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute());?>" method="post" class="djc_addtocart_submitter">
		<input type="submit" class="btn" value="<?php echo JText::_('COM_DJCATALOG2_ADD_TO_CART'); ?>" disabled="disabled" />
		<input type="hidden" name="task" value="cart.update_batch"/>
		<input type="hidden" name="option" value="com_djcatalog2" />
		<?php echo JHtml::_( 'form.token' ); ?>
	</form>
<?php } ?>

<?php if (count($producer_modals) > 0) {?>
	<?php foreach($producer_modals as $pid => $producer) {?>
		<?php echo JHtmlBootstrap::renderModal('djc_producer_modal-'.$pid, array('height' => '600px', 'url' => $producer['url'], 'title'=> $producer['title'])); ?>
	<?php } ?>
<?php } ?>


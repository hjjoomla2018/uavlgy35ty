<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access');
$user		= JFactory::getUser();
$price_auth = ($this->params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;
$edit_auth = ($user->authorise('core.edit', 'com_djcatalog2') || ($user->authorise('core.edit.own', 'com_djcatalog2') && $user->id == $this->item->created_by)) ? true : false;

$nullDate = JFactory::getDbo()->getNullDate();

$this->item_cursor = $this->item;

$showItemTitle = (bool)(!$this->params->get( 'show_page_heading', 1) || $this->item->name != $this->params->get('page_heading'));

?>
<div itemscope itemtype="http://schema.org/Product">
	<meta itemprop="url" content="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->item->slug, $this->item->catslug), true, (JUri::getInstance()->isSSL() ? 1 : -1)); ?>" />
	<div id="djcatalog" class="djc_clearfix djc_item<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default'); if ($this->item->featured == 1) echo ' featured_item'; ?>">
		<?php if($this->item->event->beforeDJCatalog2DisplayContent) { ?>
		<div class="djc_pre_content">
				<?php echo $this->item->event->beforeDJCatalog2DisplayContent; ?>
		</div>
		<?php } ?>
		<?php if ($this->navigation && (!empty($this->navigation['prev']) || !empty($this->navigation['next'])) && ($this->params->get('show_navigation', '0') == 'top' || $this->params->get('show_navigation', '0') == 'all')) { ?>
			<div class="djc_product_top_nav djc_clearfix">
				<?php if (!empty($this->navigation['prev'])) { ?>
					<a class="djc_prev_btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->navigation['prev']->slug, $this->navigation['prev']->catslug)); ?>"><span class="btn"><?php echo JText::_('COM_DJCATALOG2_PREVIOUS'); ?></span></a>
				<?php } ?>
				<?php if (!empty($this->navigation['next'])) { ?>
					<a class="djc_next_btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->navigation['next']->slug, $this->navigation['next']->catslug)); ?>"><span class="btn"><?php echo JText::_('COM_DJCATALOG2_NEXT'); ?></span></a>
				<?php } ?>
			</div>
		<?php } ?>
		<?php if ( in_array('item', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'top' && $this->params->get('social_code', '') != '') { ?>
			<div class="djc_clearfix djc_social_t">
				<?php echo $this->params->get('social_code'); ?>
			</div>
		<?php } ?>
		<?php 
		$this->item->images = DJCatalog2ImageHelper::getImages('item',$this->item->id);
		if ($this->item->images && (int)$this->params->get('show_image_item', 1) > 0) {
			echo $this->loadTemplate('images'); 
		} ?>
		
		<?php if ($this->params->get( 'show_page_heading', 1)) { ?>
		<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?> djc_page_heading" <?php if (!$showItemTitle) { echo 'itemprop="name"'; } ?>>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
		<?php } ?>
		
		<?php if ($showItemTitle) {?>
		<h2 class="djc_title" itemprop="name">
			<?php if ($this->item->featured == 1) { 
				echo '<img class="djc_featured_image" alt="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" title="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" src="'.DJCatalog2ThemeHelper::getThemeImage('featured.png').'" />';
			}?>
			<?php if ((int)$this->params->get('fed_edit_button', 0) == 1 && $edit_auth) { ?>
				<a class="btn btn-primary btn-mini djc_edit_button" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=itemform.edit&id='.$this->item->id); ?>"><?php echo JText::_('COM_DJCATALOG2_EDIT')?></a>
			<?php } ?>
			
			<?php echo $this->escape($this->item->name); ?>
		</h2>
		<?php } else { ?>
			<?php /*if ($this->item->featured == 1) { 
				echo '<img class="djc_featured_image" alt="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" title="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" src="'.DJCatalog2ThemeHelper::getThemeImage('featured.png').'" />';
			}*/?>
			<?php if ((int)$this->params->get('fed_edit_button', 0) == 1 && $edit_auth) { ?>
				<a class="btn btn-primary btn-mini djc_edit_button" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=itemform.edit&id='.$this->item->id); ?>"><?php echo JText::_('COM_DJCATALOG2_EDIT')?></a>
			<?php } ?>
		<?php } ?>
		
		<?php if($this->item->event->afterDJCatalog2DisplayTitle) { ?>
			<div class="djc_post_title">
				<?php echo $this->item->event->afterDJCatalog2DisplayTitle; ?>
			</div>
		<?php } ?>
		
		<?php if ( in_array('item', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'aft_title' && $this->params->get('social_code', '') != '') { ?>
			<div class="djc_clearfix djc_social_at">
				<?php echo $this->params->get('social_code'); ?>
			</div>
		<?php } ?>
		
		<?php if ($this->params->get('show_print_button_item', false) == '1' 
	 		|| $this->params->get('show_pdf_button_item', false) == '1' 
	 		|| $this->params->get('show_contact_form', '1')
			//|| ((int)$this->item->available == 1 && $this->params->get('items_show_cart_button_item', '1') == 1 && ($this->params->get('cart_enabled', false) || $this->params->get('cart_query_enabled', 1) ))
		) {?>
		<div class="djc_toolbar">
			<?php if ($this->params->get('show_contact_form', '1')) { ?>
				<button id="djc_contact_form_button" class="btn btn-primary btn-mini"><?php echo JText::_('COM_DJCATALOG2_CONTACT_FORM_OPEN')?></button>
			<?php } ?>
			<?php /*if ((int)$this->item->available == 1 && $this->params->get('items_show_cart_button_item', '1') == 1) { 
				echo $this->loadTemplate('addtocart'); 
			}*/?>
			<?php if ($this->params->get('show_print_button_item', false) == '1') {?>
				<a rel="nofollow" class="djc_printable_version btn btn-mini" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->item->slug, $this->item->catslug).'&tmpl=component&print=1&layout=print'); ?>"><?php echo JText::_('COM_DJCATALOG2_PRINTABLE_BUTTON'); ?></a>
			<?php } ?>
			<?php if ($this->params->get('show_pdf_button_item', false) == '1') { ?>
				<a rel="nofollow" class="djc_print_pdf_button btn btn-mini" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->item->slug, $this->item->catslug).'&tmpl=component&print=1&layout=print&pdf=1'); ?>"><?php echo JText::_('COM_DJCATALOG2_PRINT_PDF_BUTTON'); ?></a>
			<?php } ?>
		</div>
		<?php } ?>
		
		<?php if ($this->params->get('show_labels_item', 0)) {
			echo $this->loadTemplate('labels'); 
		}?>
		
		<div class="djc_description" itemprop="description">
			<div class="djc_item_info">
				<?php if ($this->params->get('show_category_name_item') && $this->item->publish_category == '1') { ?>
					<div class="djc_category_info">
					<small>
					 <?php 
						if ($this->params->get('show_category_name_item') == 2) {
							echo JText::_('COM_DJCATALOG2_CATEGORY').': '?><span><?php echo $this->item->category; ?></span> 
						<?php }
						else {
							echo JText::_('COM_DJCATALOG2_CATEGORY').': ';?><a href="<?php echo DJCatalogHelperRoute::getCategoryRoute($this->item->catslug);?>"><span><?php echo $this->item->category; ?></span></a> 
						<?php } ?>
					</small>
					</div>
				<?php } ?>
				<?php if ($this->params->get('show_producer_name_item') > 0 && $this->item->publish_producer == '1' && $this->item->producer) { ?>
					<div class="djc_producer_info">
						<small>	
						<?php 
							if ($this->params->get('show_producer_name_item') == 2) {
								echo JText::_('COM_DJCATALOG2_PRODUCER').': '; ?><span><?php echo $this->item->producer;?></span>
							<?php /*} else if(($this->params->get('show_producer_name_item') == 3)) { ?>
								<?php echo JText::_('COM_DJCATALOG2_PRODUCER').': ';?><a class="modal" rel="{handler: 'iframe', size: {x: 800, y: 450}}" href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($this->item->prodslug).'&tmpl=component'); ?>"><span><?php echo $this->item->producer; ?></span></a>
							<?php } else { */?>
							<?php } else if(($this->params->get('show_producer_name_item') == 3)) { ?>
								<?php echo JText::_('COM_DJCATALOG2_PRODUCER').': ';?><a data-toggle="modal" data-target="#djc_producer_modal" href="#djc_producer_modal"><span><?php echo $this->item->producer; ?></span></a>
								<?php echo JHtmlBootstrap::renderModal('djc_producer_modal', array('height' => '600px', 'url' => JRoute::_(DJCatalogHelperRoute::getProducerRoute($this->item->prodslug).'&tmpl=component'), 'title'=> $this->item->producer)); ?>
							<?php } else { ?>
								<?php echo JText::_('COM_DJCATALOG2_PRODUCER').': ';?><a href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($this->item->prodslug)); ?>"><span><?php echo $this->item->producer; ?></span></a>
							<?php } ?>
							<?php if ($this->params->get('show_producers_items_item', 1)) { ?>
								<a class="djc_producer_items_link btn btn-mini" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&cm=0&pid='.$this->item->producer_id); ?>"><span><?php echo JText::_('COM_DJCATALOG2_SHOW_PRODUCERS_ITEMS'); ?></span></a>
							<?php } ?>
						</small>
					</div>
					<?php } ?>
					
					<?php if ((int)$this->params->get('show_sku_item', 1) == 1 && $this->item->sku != '') { ?>
						<div class="djc_sku">
							<small>
							<?php echo JText::_('COM_DJCATALOG2_SKU').': '; ?>
							<span><?php echo trim($this->item->sku); ?></span>
							</small>
						</div>
					<?php } ?>
					
					<?php if ((int)$this->params->get('show_author_item', 0) > 0 && $this->item->author) { ?>
						<div class="djc_author">
							<small>
								<?php echo JText::_('COM_DJCATALOG2_CREATED_BY').': '; ?>
								<?php if ((int)$this->params->get('show_author_item') == 1 && $this->item->created_by) {?>
									<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&aid='.$this->item->created_by.':'.JApplication::stringURLSafe($this->item->author));?>"><span><?php echo $this->item->author; ?></span></a>
								<?php } else {?>
									<span><?php echo $this->item->author; ?></span>
								<?php } ?>
							</small>
						</div>
					<?php } ?>
					
					<?php if ((int)$this->params->get('show_date_item', 0) == 1 && $this->item->created != $nullDate) { ?>
						<div class="djc_date djc_created_date">
							<small>
								<?php echo JText::_('COM_DJCATALOG2_CREATED_ON').': '; ?>
								<span><?php echo JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC3')); ?></span>
							</small>
						</div>
					<?php } ?>
					
					<?php if ((int)$this->params->get('show_publishdate_item', 0) == 1 && $this->item->publish_up != $nullDate) { ?>
						<div class="djc_date djc_publish_date">
							<small>
								<?php echo JText::_('COM_DJCATALOG2_PUBLISHED_ON').': '; ?>
								<span><?php echo JHtml::_('date', $this->item->publish_up, JText::_('DATE_FORMAT_LC3')); ?></span>
							</small>
						</div>
					<?php } ?>
					
					<?php if ((int)$this->params->get('show_hits_item', 0) == 1) { ?>
						<div class="djc_hits">
							<small>
								<?php echo JText::_('COM_DJCATALOG2_HITS').': '; ?>
								<span><?php echo $this->item->hits; ?></span>
							</small>
						</div>
					<?php } ?>
					
					<?php if ($price_auth && (int)$this->params->get('show_price_item') > 0) {?>
						<?php if ( $this->params->get('show_price_item') == 2 || ( $this->params->get('show_price_item') == 1 && $this->item->price > 0.0) ) {?>
							<div class="djc_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer" data-itemid="<?php echo $this->item->id; ?>">
								<?php  echo $this->loadTemplate('price'); ?>
							</div>
						<?php } else {?>
							<div class="djc_price" data-itemid="<?php echo $this->item->id; ?>" style="display: none"></div>
						<?php } ?>
					<?php } ?>
					
					<?php 
					$stockInfo = $this->params->get('show_outstock_info', 0);
					if ((int)$stockInfo > 0) {
						$stockData = array('item' => $this->item, 'type' => $stockInfo, 'params' => $this->params);
						$layout = new JLayoutFile('com_djcatalog2.stockinfo', null, array('component'=> 'com_djcatalog2'));
						?>
						<p class="djc_stock"><?php echo $layout->render($stockData); ?></p>
					<?php } ?>
					
					<?php if ( (int)$this->item->available == 1 && $this->params->get('items_show_cart_button_item', '1') == 1 && ($this->params->get('cart_enabled', false) || $this->params->get('cart_query_enabled', 1)) ) { ?>
						<?php echo $this->loadTemplate('addtocart'); ?>
					<?php } ?>
				</div>
				
				<?php if ((int)$this->params->get('show_intro_desc_item', 0) == 1) { ?>
				<div class="djc_introtext">
					<?php echo JHtml::_('content.prepare', $this->item->intro_desc, $this->params, 'com_djcatalog2.item.intro_desc'); ?>
				</div>
				<?php } ?>
				
				<div class="djc_fulltext">
					<?php echo JHtml::_('content.prepare', $this->item->description, $this->params, 'com_djcatalog2.item.description'); ?>
				</div>
				<?php 
				if (count($this->attributes) > 0) {
					$grouppedAttributes = (bool) $this->params->get('group_attributes_item', '0') == '1';
					$attributes_body = ($grouppedAttributes) ? array() : '';
					foreach ($this->attributes as $attribute) {
						$this->attribute_cursor = $attribute;
						if ($grouppedAttributes) {
							if (!isset($attributes_body[$attribute->group_id])) {
								$label = ($attribute->group_label) ? $attribute->group_label : $attribute->group_name;
								$attributes_body[$attribute->group_id] = array('label' => $label, 'attributes' => array() );
							}
							$attributes_body[$attribute->group_id]['attributes'][] = $this->loadTemplate('attributes');
						} else  {
							$attributes_body .= $this->loadTemplate('attributes');
						}
					}
					?>
					<?php if (!is_array($attributes_body) && $attributes_body != '') { ?>
						<div class="djc_attributes">
							<table class="table table-condensed">
							<?php echo JHtml::_('content.prepare', $attributes_body, $this->params, 'com_djcatalog2.item.extra_fields'); ?>
							</table>
						</div>
					<?php } else if (is_array($attributes_body) && !empty($attributes_body)) { ?>
						<?php 
						foreach ($attributes_body as $attGroup) {
							$attributes_body_tmp = '';
							if (empty($attGroup['attributes'])) {
								continue;
							} 
							foreach($attGroup['attributes'] as $value) {
								$attributes_body_tmp .= $value;
							}
							if ($attributes_body_tmp == '') {
								continue;
							}
							?>
						<div class="djc_attributes djc_attribute_group djc_attribute_group-<?php echo ($attGroup['label']) ? JFilterOutput::stringURLSafe($attGroup['label']) : 0; ?>">
						<?php if ($attGroup['label']) {?>
							<h4><?php echo $this->escape($attGroup['label']); ?></h4>
						<?php } ?>
						<table class="table table-condensed">
						<?php echo JHtml::_('content.prepare', $attributes_body_tmp, $this->params, 'com_djcatalog2.item.extra_fields'); ?>
						</table>
						</div>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				
				<?php if (isset($this->item->tabs)) { ?>
					<div class="djc_clear"></div>
					<div class="djc_tabs">
						<?php echo JHTML::_('content.prepare', $this->item->tabs, $this->params, 'com_djcatalog2.item.tabs'); ?>
					</div>
				<?php } ?>
				
				<?php if (!empty($this->children) && (int)$this->params->get('items_show_variants', 1) == 1) {?>
				<div class="djc_clear"></div>
				<div class="djc_item_variants">
					<?php echo $this->loadTemplate('children'); ?>
				</div>
				<?php } ?>
				
				<?php //if( ((int)$this->params->get('show_location_map_item', 1) > 0 || (int)$this->params->get('show_location_details_item', 1) > 0 ) && (($this->item->latitude != 0.0 && $this->item->longitude != 0.0) || ( !empty($this->item->address) || !empty($this->item->city) ))) {
				if( (int)$this->params->get('show_location_map_item', 1) > 0 || (int)$this->params->get('show_location_details_item', 1) > 0) {
					echo $this->loadTemplate('map');
				} ?>
				
				<?php if ($this->params->get('show_files_item', 1) > 0 && ($this->item->files = DJCatalog2FileHelper::getFiles('item',$this->item->id))) {
					echo $this->loadTemplate('files');
				} ?>
				<?php if ($this->params->get('show_contact_form', '1')) { ?>
				<div class="djc_clear"></div>
				<div class="djc_contact_form_wrapper" id="contactform">
					<?php echo $this->loadTemplate('contact'); ?>
				</div>
				<?php } ?>
	
				<?php if($this->item->event->afterDJCatalog2DisplayContent) { ?>
					<div class="djc_post_content">
						<?php echo $this->item->event->afterDJCatalog2DisplayContent; ?>
					</div>
				<?php } ?>
				
				<?php if ($this->navigation && (!empty($this->navigation['prev']) || !empty($this->navigation['next'])) && ($this->params->get('show_navigation', '0') == 'bottom' || $this->params->get('show_navigation', '0') == 'all')) { ?>
					<div class="djc_product_bottom_nav djc_clearfix">
						<?php if (!empty($this->navigation['prev'])) { ?>
							<a class="djc_prev_btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->navigation['prev']->slug, $this->navigation['prev']->catslug)); ?>"><span class=" btn"><?php echo JText::_('COM_DJCATALOG2_PREVIOUS'); ?></span></a>
						<?php } ?>
						<?php if (!empty($this->navigation['next'])) { ?>
							<a class="djc_next_btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->navigation['next']->slug, $this->navigation['next']->catslug)); ?>"><span class=" btn"><?php echo JText::_('COM_DJCATALOG2_NEXT'); ?></span></a>
						<?php } ?>
					</div>
				<?php } ?>
				
				<?php if ( in_array('item', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'aft_desc' && $this->params->get('social_code', '') != '') { ?>
					<div class="djc_clearfix djc_social_ad">
						<?php echo $this->params->get('social_code'); ?>
					</div>
				<?php } ?>
				
				<?php if((int)$this->params->get('comments', 0) > 0 && (int)$this->params->get('show_comments_item', 1) > 0){
					echo $this->loadTemplate('comments');
				} ?>						
				
				<?php if ($this->relateditems && $this->params->get('related_items_count',2) > 0) {
					echo $this->loadTemplate('relateditems');
				} ?>
			</div>
			
			<?php if ( in_array('item', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'bottom' && $this->params->get('social_code', '') != '') { ?>
				<div class="djc_clearfix djc_social_b">
					<?php echo $this->params->get('social_code'); ?>
				</div>
			<?php } ?>
			
			<?php if (false) {?>
				<?php 
				$categoryUrl = JRoute::_(DJCatalogHelperRoute::getCategoryRoute(JFactory::getApplication()->input->getString('refcid', 0), $this->item->prodslug));
				?>
				<a class="btn" href="<?php echo $categoryUrl;?>"><?php echo JText::_('COM_DJCATALOG2_BACK_BUTTON');?></a>
			<?php } ?>
			
		<?php 
		if ($this->params->get('show_footer')) echo DJCATFOOTER;
		?>
	</div>
</div>
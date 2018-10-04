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
	<div id="djcatalog" class="djc_clearfix djc_item_preview djc_item<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default'); if ($this->item->featured == 1) echo ' featured_item'; ?>">
		<?php if($this->item->event->beforeDJCatalog2DisplayContent) { ?>
		<div class="djc_pre_content">
				<?php echo $this->item->event->beforeDJCatalog2DisplayContent; ?>
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
		
		<div class="djc_description" itemprop="description">
			<div class="djc_item_info">
				<?php if ($this->params->get('show_category_name_item') && $this->item->publish_category == '1') { ?>
					<div class="djc_category_info">
					<small>
					<?php echo JText::_('COM_DJCATALOG2_CATEGORY').': '?><span><?php echo $this->item->category; ?></span>
					</small>
					</div>
				<?php } ?>
				<?php if ($this->params->get('show_producer_name_item') > 0 && $this->item->publish_producer == '1' && $this->item->producer) { ?>
					<div class="djc_producer_info">
						<small>	
						<?php 
						echo JText::_('COM_DJCATALOG2_PRODUCER').': '; ?><span><?php echo $this->item->producer;?></span>
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
								<span><?php echo $this->item->author; ?></span>
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
					
					<?php
						if ($price_auth && ($this->params->get('show_price_item') == 2 || ( $this->params->get('show_price_item') == 1 && $this->item->price > 0.0))) {
							?>
							<div class="djc_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
								<?php echo $this->loadTemplate('price'); ?>
							</div>
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
	
				<?php if($this->item->event->afterDJCatalog2DisplayContent) { ?>
					<div class="djc_post_content">
						<?php echo $this->item->event->afterDJCatalog2DisplayContent; ?>
					</div>
				<?php } ?>
			</div>
		<?php 
		if ($this->params->get('show_footer')) echo DJCATFOOTER;
		?>
		
		<div class="djc_product_page_link_wrap">
			<a class="djc_product_page_link btn btn-primary btn-large" href="<?php echo JRoute::_(DJCatalog2HelperRoute::getItemRoute($this->item->slug, $this->item->catslug), false, (JUri::getInstance()->isSSL() ? 1 : -1))?>"><?php echo JText::_('COM_DJCATALOG2_PREVIEW_FULL_PAGE_BTN'); ?></a>
		</div>
	</div>
</div>

<script>

var isIframe = false;
try {
	isIframe = window.self !== window.top;
} catch (e) {
	isIframe = true;
}
if (!isIframe) {
	window.location.href = "<?php echo JRoute::_(DJCatalog2HelperRoute::getItemRoute($this->item->slug, $this->item->catslug), false, (JUri::getInstance()->isSSL() ? 1 : -1)); ?>";
}

(function($){
	$(document).ready(function(){
		$('a').attr('target', '_blank');

		$('a').click(function(evt){
			if ( $(this).not($('.djc_image_switcher a')) == false) {
				return;
			}
			var href = $(this).attr('href');

			if (href != '' && href.indexOf('#') == -1) {
				evt.preventDefault();
				window.top.location.href = $(this).attr('href');
				return false;
			}
		});

		$('form.djc_form_addtocart').attr('data-noajax', '1');
	});
})(jQuery);
</script>

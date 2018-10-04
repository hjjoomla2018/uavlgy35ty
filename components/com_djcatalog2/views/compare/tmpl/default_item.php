<?php
/**
 * @version $Id $
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined('_JEXEC') or die('Restricted access'); 

$user		= JFactory::getUser();
$price_auth = ($this->params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;

$item = $this->item_cursor;
$nullDate = JFactory::getDbo()->getNullDate();

$itemLink = DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug);
$item->_link = $itemLink;
$item->_images = ($this->params->get('compare_image_hover_item', 0) == 1) ? DJCatalog2ImageHelper::getImages('item', $item->id, true) : array();

?>

<div class="djc_item">

	<form action="<?php echo JRoute::_(DJCatalog2HelperRoute::getComparisonRoute()); ?>" method="post" class="djc_remove_compare_form">
		<input type="hidden" name="item_id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="task" value="item.removeFromCompare" />
		<input type="hidden" name="return" value="<?php echo base64_encode(DJCatalog2HelperRoute::getComparisonRoute()); ?>" />
		<button type="submit" class="btn" title="<?php echo JText::_('COM_DJCATALOG2_COMPARE_REMOVE_BTN'); ?>">&times;</button>
	</form>

	<?php 
	if ($item->featured == 1) { 
		echo '<img class="djc_featured_image" alt="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" title="'.JText::_('COM_DJCATALOG2_FEATURED_ITEM').'" src="'.DJCatalog2ThemeHelper::getThemeImage('featured.png').'" />';
	}?>
	<?php if ($item->item_image && (int)$this->params->get('compare_image_link_item', 0) != -1) { ?>
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
	<?php if ((int)$this->params->get('compare_show_item_name','1') > 0 ) {?>
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
	<?php } ?>
	<div class="djc_description">
		<div class="djc_item_info">
			<?php if ($this->params->get('compare_show_category_name') > 0 && $item->publish_category) { ?>
			<div class="djc_category_info">
				<?php 
				if ($this->params->get('compare_show_category_name') == 2) {
					echo JText::_('COM_DJCATALOG2_CATEGORY').': '?>
					<span><?php echo $item->category; ?></span> 
				<?php }
				else {
					echo JText::_('COM_DJCATALOG2_CATEGORY').': ';?>
					<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute($item->catslug));?>">
						<span><?php echo $item->category; ?></span>
					</a> 
				<?php } ?>
			</div>
			<?php } ?>
			<?php if ($this->params->get('compare_show_producer_name') > 0 && $item->producer && $item->publish_producer) { ?>
			<div class="djc_producer_info">
				<?php if ($this->params->get('compare_show_producer_name') == 2) { ?>
					<?php echo JText::_('COM_DJCATALOG2_PRODUCER').': '; ?>
					<span><?php echo $item->producer;?></span>
				<?php } else { ?>
					<?php echo JText::_('COM_DJCATALOG2_PRODUCER').': ';?>
					<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug)); ?>">
						<span><?php echo $item->producer; ?></span>
					</a> 
				<?php } ?>
				<?php if ($this->params->get('compare_show_producers_items', 1)) { ?>
					<a class="djc_producer_items_link btn btn-mini" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&cm=0&pid='.$item->prodslug); ?>"><span><?php echo JText::_('COM_DJCATALOG2_SHOW_PRODUCERS_ITEMS'); ?></span></a>
				<?php } ?>
			</div>
			<?php } ?>
			
			<?php if ((int)$this->params->get('compare_show_sku', 1) == 1 && $item->sku != '') { ?>
				<div class="djc_sku">
					<?php echo JText::_('COM_DJCATALOG2_SKU').': '; ?>
					<span><?php echo trim($item->sku); ?></span>
				</div>
			<?php } ?>
			
			<?php
				if ($price_auth && ($this->params->get('compare_show_price') == 2 || ( $this->params->get('compare_show_price') == 1 && $item->price > 0.0))) { 
			?>
            <div class="djc_price">
            	<?php echo $this->loadTemplate('item_price'); ?>
            </div>
			<?php } ?>
			
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
			
			<?php if( (int)$this->params->get('compare_show_location_details', true) > 0) { ?>
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
			<?php } ?>
		
		</div>
		
		<?php if ($this->params->get('compare_items_show_intro')) { ?>
		<div class="djc_introtext">
			<?php if ($this->params->get('compare_items_intro_length') > 0  && $this->params->get('compare_items_intro_trunc') == '1') {
					?><p><?php echo DJCatalog2HtmlHelper::trimText($item->intro_desc, $this->params->get('compare_items_intro_length'));?></p><?php
				}
				else {
					echo JHtml::_('content.prepare', $item->intro_desc, $this->params, 'com_djcatalog2.items.intro_desc');
				}
			?>
		</div>
		<?php } ?>
		
		<?php if ($this->params->get('compare_items_show_attributes', '1')) { ?>
			<?php 
			if (count($this->attributes) > 0) {
				$attributes_body = '';
				foreach ($this->attributes as $attribute) {
					$this->attribute_cursor = $attribute;
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
			<?php } ?>
		<?php } ?>
	</div>
	<?php if ((int)$item->available == 1 && $this->params->get('compare_items_show_cart_button', '1') == 1) { 
		echo $this->loadTemplate('item_addtocart'); 
	}?>
	
	<?php if ($this->params->get('compare_showreadmore_item')) { ?>
		<div class="clear"></div>
		<div class="djc_readon">
			<a class="btn readmore" href="<?php echo JRoute::_($item->_link); ?>" class="readmore"><?php echo JText::sprintf('COM_DJCATALOG2_READMORE'); ?></a>
		</div>
	<?php } ?>
</div>
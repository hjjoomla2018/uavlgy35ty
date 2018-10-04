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
$cparams = Djcatalog2Helper::getParams();
$price_auth = ($cparams->get('price_restrict', '0') == '1' && $user->guest) ? false : true;

$columns = (int)$params->get('bootstrap_columns', 1);
$count = count($items);
$counter = 0;
$key = 0;

?>
<div class="djc_items mod_djc_relateditems mod_djc_items djc_clearfix" id="mod_djc_items-<?php echo $module_id; ?>">
<?php $categories = Djc2Categories::getInstance(array('state'=>'1')); ?>
<?php foreach ($items as $item) { ?>
	<?php $product_url = JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)); ?>
	<?php $rowcount = ((int) $key % (int) $columns) + 1; ?>
	
	<?php if ($rowcount == 1) { ?>
		<?php $row = $counter / $columns; ?>
		<div class="items-row cols-<?php echo (int) $columns; ?> <?php echo 'row-' . $row; ?> row-fluid clearfix">
	<?php } ?>
	
	<div class="span<?php echo round((12 / $columns)); ?>">
		<div class="djc_item mod_djc_item column-<?php echo $rowcount; ?>">
			<?php if ($item->item_image && $params->get('showimage', '1') == '1' && ((int)$params->get('imagewidth','120')> 0 || (int)$params->get('imageheight','120') > 0)) { ?>
				<div class="djc_image">
					<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)); ?>">
						<img class="img-polaroid" alt="<?php echo $item->image_caption; ?>" src="<?php echo DJCatalog2ImageHelper::getProcessedImage($item->item_image, (int)$params->get('imagewidth','120'), (int)$params->get('imageheight','120'), !(bool)$params->get('imageprocess',true), $item->image_path); ?>"/>
					</a>
				</div>
			<?php } ?>
			<div class="djc_title">
				<h4><?php
					if ($params->get('linktitle', '1') == '1') { ?>
						<a href="<?php echo $product_url; ?>"><?php echo $item->name; ?></a>	
					<?php } else {
						echo $item->name;
					}
				?></h4>
			</div>
			<div class="djc_description">
				<?php if ($params->get('show_category_name') > 0) { ?>
				<div class="djc_category_info">
					<?php 
					if ($params->get('show_category_name') == 2) {
						echo JText::_('MOD_DJC2RELATEDITEMS_CATEGORY').': '?>
						<span><?php echo $item->category; ?></span> 
					<?php }
					else {
						echo JText::_('MOD_DJC2RELATEDITEMS_CATEGORY').': ';?>
						<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute($item->catslug)); ?>">
							<span><?php echo $item->category; ?></span>
						</a> 
					<?php } ?>
				</div>
				<?php } ?>
				<?php if ($params->get('show_producer_name') > 0 && $item->producer && $item->publish_producer) { ?>
				<div class="djc_producer_info">
					<?php if ($params->get('show_producer_name') >= 2) { ?>
						<?php echo JText::_('MOD_DJC2RELATEDITEMS_PRODUCER').': '; ?>
						<span><?php echo $item->producer;?></span>
					<?php } /*else if(($params->get('show_producer_name') == 3)) {
						<?php echo JText::_('MOD_DJC2RELATEDITEMS_PRODUCER').': ';?>
						<a class="modal" rel="{handler: 'iframe', size: {x: 800, y: 600}}" href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug).'&tmpl=component'); ?>">
							<span><?php echo $item->producer; ?></span>
						</a> 
					<?php }*/ else { ?>
						<?php echo JText::_('MOD_DJC2RELATEDITEMS_PRODUCER').': ';?>
						<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug)); ?>">
							<span><?php echo $item->producer; ?></span>
						</a> 
					<?php } ?>
				</div>
				<?php } ?>
				<?php 
				if ($item->price && $price_auth) { 
				?>
				<div class="djc_price">
					<?php if ($item->special_price) { ?>
						<?php echo JText::_('MOD_DJC2RELATEDITEMS_PRICE').': ';?><span class="djc_price_old"><?php echo $item->price; ?></span>&nbsp;<span class="djc_price_new"><?php echo $item->special_price; ?></span>
					<?php } else {?>
						<?php echo JText::_('MOD_DJC2RELATEDITEMS_PRICE').': ';?><span><?php echo $item->price; ?></span>
					<?php } ?>
				</div>
				<?php } ?>
				<?php if ($params->get('items_show_intro')) {?>
				<div class="djc_introtext">
					<?php if ($params->get('items_intro_length') > 0 ) {
							?><p><?php echo DJCatalog2HtmlHelper::trimText($item->intro_desc, $params->get('items_intro_length'));?></p><?php
						}
						else {
							echo $item->intro_desc; 
						}
					?>
				</div>
				<?php } ?>
				</div>
				<div class="djc_clear"></div>
				<?php if ($params->get('showreadmore_item')) { ?>
					<p class="djc_readon">
						<a class="btn readmore" href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)); ?>"><?php echo JText::sprintf('MOD_DJC2_ITEM_READMORE'); ?></a>
					</p>
				<?php } ?>
			</div>
			<?php $counter++;?>
		</div>
		
		<?php if (($rowcount == $columns) or ($counter == $count)) { ?>
			</div>
		<?php } ?>
		<?php $key++; ?>
	<?php } ?>
</div>

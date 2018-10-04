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
$cparams = Djcatalog2Helper::getParams();

$columns = (int)$params->get('bootstrap_columns', 1);
$count = count($producers);
$counter = 0;
$key = 0;

$module_id = $module->id;

?>
<div class="djc_producers mod_djc_producers djc_items mod_djc_items djc_clearfix" id="mod_djc_producers-<?php echo $module_id; ?>">
<?php foreach ($producers as $producer) { ?>
	<?php 
	
	$item = JArrayHelper::toObject($producer);
	
	$url = '';
	if ($params->get('type', '0') == '0') {
		$url = DJCatalogHelperRoute::getCategoryRoute($cid, $item->id.':'.$item->alias).'&cm=0';
	} else {
		$url = DJCatalogHelperRoute::getProducerRoute($item->id.':'.$item->alias);
	}
	
	$url = JRoute::_($url);
	?>
	<?php $rowcount = ((int) $key % (int) $columns) + 1; ?>
	
	<?php if ($rowcount == 1) { ?>
		<?php $row = $counter / $columns; ?>
		<div class="items-row cols-<?php echo (int) $columns; ?> <?php echo 'row-' . $row; ?> row-fluid clearfix">
	<?php } ?>
	
	<div class="span<?php echo round((12 / $columns)); ?>">
		<div class="djc_item mod_djc_item column-<?php echo $rowcount; ?>">
			<?php if ($item->item_image && $params->get('showimage', '1') == '1' && ((int)$params->get('imagewidth','120')> 0 || (int)$params->get('imageheight','120') > 0)) { ?>
				<div class="djc_image">
					<?php if ($params->get('linkimage', '1') == '1') { ?>
						<a href="<?php echo $url; ?>">
					<?php } ?>
						<img class="img-polaroid" alt="<?php echo $item->image_caption; ?>" src="<?php echo DJCatalog2ImageHelper::getProcessedImage($item->item_image, (int)$params->get('imagewidth','120'), (int)$params->get('imageheight','120'), !(bool)$params->get('imageprocess',true), $item->image_path); ?>"/>
					<?php if ($params->get('linkimage', '1') == '1') { ?>
						</a>
					<?php } ?>
				</div>
			<?php } ?>
			<?php if ($params->get('showtitle', '1') == '1') { ?>
			<div class="djc_title">
				<h4><?php
					if ($params->get('linktitle', '1') == '1') { ?>
						<a href="<?php echo $url; ?>"><?php echo $item->name; ?></a>	
					<?php } else {
						echo $item->name;
					}
				?></h4>
			</div>
			<?php } ?>
			<div class="djc_description">
				<?php if ($params->get('items_show_intro')) {?>
				<div class="djc_introtext">
					<?php if ($params->get('items_intro_length') > 0 ) {
							?><p><?php echo DJCatalog2HtmlHelper::trimText($item->description, $params->get('items_intro_length'));?></p><?php
						}
						else {
							echo $item->description; 
						}
					?>
				</div>
				<?php } ?>
		
			</div>
			<div class="djc_clear"></div>
			<?php if ($params->get('showreadmore_item')) { ?>
				<p class="djc_readon">
					<a class="btn readmore" href="<?php echo $url; ?>"><?php echo JText::sprintf('MOD_DJC2PRODUCERS_READMORE'); ?></a>
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

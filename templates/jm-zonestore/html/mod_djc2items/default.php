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

$user = JFactory::getUser();
$cparams = Djcatalog2Helper::getParams();

$price_auth = ($cparams->get('price_restrict', '0') == '1' && $user->guest) ? false : true;
$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2') || $user->authorise('core.admin', 'com_djcatalog2');

$columns = (int)$params->get('bootstrap_columns', 1);
$count = count($items);
$counter = 0;
$key = 0;

?>
<div class="djc_items mod_djc_items djc_clearfix" id="mod_djc_items-<?php echo $module_id; ?>">
	<?php $categories = Djc2Categories::getInstance(array('state'=>'1')); ?>
	<?php foreach ($items as $item) { ?>
		<?php $item->_link = $product_url = JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)); ?>
		<?php $rowcount = ((int) $key % (int) $columns) + 1; ?>

		<?php if ($rowcount == 1) { ?>
			<?php $row = $counter / $columns; ?>
			<div class="items-row cols-<?php echo (int) $columns; ?> <?php echo 'row-' . $row; ?> row-fluid clearfix">
		<?php } ?>

		<div class="span<?php echo round((12 / $columns)); ?>">
			<div class="djc_item mod_djc_item column-<?php echo $rowcount; ?>">
				<?php if ($params->get('showimage', '1') == '1' && ((int)$params->get('imagewidth','120')> 0 || (int)$params->get('imageheight','120') > 0)) { ?>
					<?php
					$variant = 'img';
					$imgLink = (int)$params->get('linkimage', 1);
					if ($imgLink == 1) {
						$variant = 'link';
					}
					$layout = new JLayoutFile('com_djcatalog2.listimage', null, array('component'=> 'com_djcatalog2'));
					$imageData = array(	'item' => &$item,
					                       'type' => 'item',
					                       'size' => array(
						                       'width' => (int)$params->get('imagewidth','120'),
						                       'height' => (int)$params->get('imageheight','120'),
						                       'keep_ratio' => !(bool)$params->get('imageprocess',true)
					                       ),
					                       'variant' => $variant,
					                       'hover_img' => false,
					                       'context' => 'com_djcatalog2.items.module',
					                       'params' => &$cparams);
					echo $layout->render($imageData);
					?>
				<?php } ?>
				<?php if ($params->get('showtitle', '1') == '1') { ?>
					<div class="djc_title">
						<h4><?php
							if ($params->get('linktitle', '1') == '1') { ?>
								<a href="<?php echo $product_url; ?>"><?php echo $item->name; ?></a>
							<?php } else {
								echo $item->name;
							}
							?></h4>
					</div>
				<?php } ?>

				<?php
				$layout = new JLayoutFile('com_djcatalog2.labels', null, array('component'=> 'com_djcatalog2'));
				echo $layout->render(array('item' => $item));
				?>

				<div class="djc_description">
					<?php if ($params->get('show_category_name') > 0) { ?>
						<div class="djc_category_info">
							<?php
							if ($params->get('show_category_name') == 2) {
								echo '<span class="djc_label">'.JText::_('MOD_DJC2ITEMS_CATEGORY').'</span>';?>
								<span><?php echo $item->category; ?></span>
							<?php }
							else {
								echo '<span class="djc_label">'.JText::_('MOD_DJC2ITEMS_CATEGORY').'</span>';?>
								<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute($item->catslug)); ?>">
									<span><?php echo $item->category; ?></span>
								</a>
							<?php } ?>
						</div>
					<?php } ?>
					<?php if ($params->get('show_producer_name') > 0 && $item->producer && $item->publish_producer) { ?>
						<div class="djc_producer_info">
							<?php if ($params->get('show_producer_name') >= 2) { ?>
								<?php echo '<span class="djc_label">'.JText::_('MOD_DJC2ITEMS_PRODUCER').'</span>';?>
								<span><?php echo $item->producer;?></span>
							<?php } /*else if (($params->get('show_producer_name') == 3)) { ?>
						<?php echo JText::_('MOD_DJC2ITEMS_PRODUCER').': ';?>
						<a class="modal" rel="{handler: 'iframe', size: {x: 800, y: 600}}" href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug).'&tmpl=component'); ?>">
							<span><?php echo $item->producer; ?></span>
						</a>
					<?php }*/ else { ?>
								<?php echo '<span class="djc_label">'.JText::_('MOD_DJC2ITEMS_PRODUCER').'</span>';?>
								<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->prodslug)); ?>">
									<span><?php echo $item->producer; ?></span>
								</a>
							<?php } ?>
						</div>
					<?php } ?>

					<?php if ($item->price && $price_auth && ($params->get('show_price') == 2 || ( $params->get('show_price') == 1 && $item->final_price > 0.0))) { ?>
						<div class="djc_price">
							<?php require JModuleHelper::getLayoutPath('mod_djc2items', 'default_price'); ?>
						</div>
					<?php }?>

					<?php
					$stockInfo = $params->get('show_stock_info', 0);
					if ((int)$stockInfo > 0) {
						$stockData = array('item' => $item, 'type' => $stockInfo, 'params' => $cparams);
						$layout = new JLayoutFile('com_djcatalog2.stockinfo', null, array('component'=> 'com_djcatalog2'));
						?>
						<p class="djc_stock"><?php echo $layout->render($stockData); ?></p>
					<?php } ?>

					<?php //require JModuleHelper::getLayoutPath('mod_djc2items', 'default_addtocart'); ?>
					<?php if ($params->get('items_show_intro')) {?>
						<div class="djc_introtext">
							<?php if ($params->get('items_intro_length') > 0 && $params->get('items_intro_trunc', 0)) {
								?><p><?php echo DJCatalog2HtmlHelper::trimText($item->intro_desc, $params->get('items_intro_length'));?></p><?php
							}
							else {
								echo $item->intro_desc;
							}
							?>
						</div>
					<?php } ?>

					<?php if ($params->get('show_attributes', false) && count($attributes) > 0) {
						$layout = new JLayoutFile('com_djcatalog2.attributestable', null, array('component'=> 'com_djcatalog2'));
						echo $layout->render(array('item' => $item, 'attributes' => $attributes, 'context' => 'mod_djc2items', 'params' => $cparams));
					} ?>

					<?php if( (int)$params->get('show_location_details_item', true) > 0) { ?>
						<?php
						$address = array();

						if (($params->get('location_address_item', 1) == '1') && $item->address) {
							$address[] = $item->address;
						}
						if (($params->get('location_postcode_item', 1) == '1') && $item->postcode) {
							$address[] = $item->postcode;
						}
						if (($params->get('location_city_item', 1) == '1') && $item->city) {
							$address[] = $item->city;
						}
						if (($params->get('location_country_item', 1) == '1') && $item->country_name) {
							$address[] = $item->country_name;
						}

						if (count($address)) { ?>
							<p class="djc_address"><?php echo implode(', ', $address); ?></p>
						<?php }

						$contact = array();

						if (($params->get('location_phone_item', 1) == '1') && $item->phone) {
							$contact[] = '<span class="djc_label">'.JText::_('MOD_DJC2ITEMS_UP_PHONE').'</span><span>'.$item->phone.'</span>';
						}
						if (($params->get('location_mobile_item', 1) == '1') && $item->mobile) {
							$contact[] = '<span class="djc_label">'.JText::_('MOD_DJC2ITEMS_UP_MOBILE').'</span><span>'.$item->mobile.'</span>';
						}
						if (($params->get('location_fax_item', 1) == '1') && $item->fax) {
							$contact[] = '<span class="djc_label">'.JText::_('MOD_DJC2ITEMS_UP_FAX').'</span><span>'.$item->fax.'</span>';
						}
						if (($params->get('location_website_item', 1) == '1') && $item->website) {
							$website = (strpos($item->website, 'http') === 0) ? $item->website : 'http://'.$item->website;
							$website = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', htmlspecialchars($item->website));
							$contact[] = '<span class="djc_label">'.JText::_('MOD_DJC2ITEMS_UP_WEBSITE').'</span><span>'.$website.'</span>';
						}
						if (($params->get('location_email_item', 1) == '1') && $item->email) {
							$email = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', htmlspecialchars($item->email));
							$contact[] = '<span class="djc_label">'.JText::_('MOD_DJC2ITEMS_UP_EMAIL').'</span><span>'.$email.'</span>';
						}

						if (count($contact)) { ?>
							<p class="djc_contact"><?php echo implode('<br />', $contact);?></p>
						<?php } ?>
					<?php } ?>
				</div>
				<?php if ((int)$item->available == 1 && $params->get('show_cart_btn', 0) > 0) {
					$layout = new JLayoutFile('com_djcatalog2.addtocart', null, array('component'=> 'com_djcatalog2'));
					echo $layout->render(array('item' => $item, 'context' => 'com_djcatalog2.mod_djc2items.addtocart', 'params' => $cparams));
				} ?>
				<div class="djc_clear"></div>
				<?php if ($params->get('showreadmore_item')) { ?>
					<p class="djc_readon">
						<a class="btn readmore" href="<?php echo $product_url; ?>"><?php echo JText::sprintf('MOD_DJC2_ITEM_READMORE'); ?></a>
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

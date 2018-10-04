<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$item = $displayData['item'];
$type = $displayData['type'];
$size = $displayData['size'];

if (!$type || !$item || (is_string($size) && is_array($size))) {
	return;
}

if (is_array($size) && (empty($size['width']) && empty($size['height']))) {
	return;
}

$variant = isset($displayData['variant']) ? $displayData['variant'] : 'img';
$params = isset($displayData['params']) ? $displayData['params'] : JComponentHelper::getParams('com_djcatalog2');
$context = isset($displayData['context']) ? $displayData['context'] : 'com_djcatalog2.items.list';
$show2nd = isset($displayData['hover_img']) ? $displayData['hover_img'] : false;

$imgData = array();
if ($variant == 'preview' && !empty($item->_popuplink)) {
	$imgData['link'] = array(
		'href' => JRoute::_($item->_popuplink),
		'class' => 'djc_item_preview_img'
	);
} else if ($variant == 'link' && !empty($item->_link)){
	$imgData['link'] = array(
		'href' => JRoute::_($item->_link)
	);
}

if ($item->item_image) {
	$src = '';
	if (is_string($size)) {
		$src = DJCatalog2ImageHelper::getImageUrl($item->image_fullpath, $size);
	} else {
		$src = DJCatalog2ImageHelper::getProcessedImage($item->item_image, $size['width'], $size['height'], $size['keep_ratio'], $item->image_path);
	}
	$imgData['img'] = array(
		'src' => $src,
		'alt' => htmlspecialchars($item->image_caption, ENT_COMPAT, 'UTF-8'),
		'class' => 'img-polaroid'
	);
	if ($show2nd && !empty($item->_images) && count($item->_images) > 1 && is_string($size)) {
		$imgData['img2'] = array(
			'src' => $item->_images[1]->$size,
			'alt' => htmlspecialchars($item->_images[1]->caption, ENT_COMPAT, 'UTF-8'),
			'class' => 'img-polaroid'
		);
	}
	if ($variant == 'popup') {
		$imgData['link'] = array(
			'href' => DJCatalog2ImageHelper::getImageUrl($item->image_fullpath, ( $params->get('image_popup_src', 'opt') == 'opt' ? 'fullscreen' : 'original') ),
			'class' => 'djimagebox',
			'rel' => 'djimagebox-'.$type,
			'title' => htmlspecialchars($item->image_caption, ENT_COMPAT, 'UTF-8')
		);
	}
} else{
	$src = '';
	if (is_string($size)) {
		$src  = DJCatalog2ImageHelper::getDefaultImage($type, $size);
	} else {
		$src = DJCatalog2ImageHelper::getDefaultImage($type, null, $size);
	}
	
	$imgData['img'] = array(
		'src' => $src,
		'alt' => htmlspecialchars($item->name, ENT_COMPAT, 'UTF-8'),
		'class' => 'img-polaroid'
	);
}

if (isset($imgData['img']['src']) && $imgData['img']['src'] != '') {?>
	<div class="djc_image <?php echo !empty($imgData['img2']) ? 'djc_hover_image' : ''; ?>">
		<?php if (isset($imgData['link'])) { ?>
			<a 	href="<?php echo $imgData['link']['href']?>"
				<?php if (isset($imgData['link']['class'])) { echo 'class="'.$imgData['link']['class'].'"'; } ?>
				<?php if (isset($imgData['link']['rel'])) { echo 'rel="'.$imgData['link']['rel'].'"'; } ?>
				<?php if (isset($imgData['link']['title'])) { echo 'title="'.$imgData['link']['title'].'"'; } ?>
				>
		<?php } ?>
		<img 	alt="<?php echo $imgData['img']['alt']; ?>" 
				src="<?php echo $imgData['img']['src']; ?>" 
				class="<?php echo $imgData['img']['class']; ?>" 
		/>
		<?php if (!empty($imgData['img2'])) {?>
			<img 	alt="<?php echo $imgData['img2']['alt']; ?>" 
					src="<?php echo $imgData['img2']['src']; ?>" 
					class="<?php echo $imgData['img2']['class']; ?>" />
		<?php } ?>
		<?php if (isset($imgData['link'])) { ?>
			</a>
		<?php } ?>
	</div>
<?php } ?>
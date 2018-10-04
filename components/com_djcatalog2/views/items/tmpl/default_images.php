<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$djMediaTools = ($this->params->get('djmediatools_integration', 0) == '1' && $this->params->get('djmediatools_album_category', 0) > 0) ? $this->params->get('djmediatools_album_category', 0) : false;
$djMediaToolsMinimum = (int)$this->params->get('djmediatools_minimum', 1);

$fullscreenVar = ($this->params->get('image_popup_src', 'opt') == 'opt') ? 'fullscreen' : 'original'; 

?>

<?php if ($djMediaTools > 0 && count($this->item->images) >= $djMediaToolsMinimum) {?>
<div class="djc_images-mediatools">
	<?php echo JHtml::_('content.prepare', '{djmedia '.(int)$djMediaTools.'}', $this->params, 'com_djcatalog2.category.djmediatools'); ?>
</div>
<?php } else { ?>
<?php 
$galleryType = $this->params->get('gallery_type', 'switcher');
$imageSwitcher = (count($this->item->images) > 1 && $galleryType == 'switcher');
$showMain = $galleryType == 'small_popups' ? false : true;
?>
<div class="djc_images <?php if ($imageSwitcher) { echo 'djc_image_switcher'; }?> pull-right">
	<?php if ($showMain) {?>
	<div class="djc_mainimage">
		<?php if ($imageSwitcher) {?>
			<a data-target="main-image-link" data-thumb="0" title="<?php echo $this->item->images[0]->caption; ?>" href="<?php echo $this->item->images[0]->$fullscreenVar; ?>">
				<img class="img-polaroid" alt="<?php echo $this->item->images[0]->caption; ?>" src="<?php echo $this->item->images[0]->large; ?>" />
			</a>
		<?php } else { ?>
			<a class="djimagebox" title="<?php echo $this->item->images[0]->caption; ?>" href="<?php echo $this->item->images[0]->$fullscreenVar; ?>">
				<img id="djc_mainimage" class="img-polaroid" alt="<?php echo $this->item->images[0]->caption; ?>" src="<?php echo $this->item->images[0]->large; ?>" />
			</a>
		<?php } ?>
	</div>
	<?php } ?>
	<?php if (count($this->item->images) > (($imageSwitcher || !$showMain) ? 0 : 1)) { ?>
		<div class="djc_thumbnails" id="djc_thumbnails" data-toggle="image-thumbs">
		<?php for($i = (($imageSwitcher || !$showMain) ? 0 : 1); $i < count($this->item->images); $i++) { ?>
			<div class="djc_thumbnail">
				<a class="djimagebox" title="<?php echo $this->item->images[$i]->caption; ?>" href="<?php echo $this->item->images[$i]->$fullscreenVar; ?>" data-thumb="<?php echo $i; ?>" data-large="<?php echo $this->item->images[$i]->large; ?>">
					<img class="img-polaroid" alt="<?php echo $this->item->images[$i]->caption; ?>" src="<?php echo $this->item->images[$i]->small; ?>" />
				</a>
			</div>
			<?php } ?>
		</div>
	<?php } ?>
</div>
<?php } ?>

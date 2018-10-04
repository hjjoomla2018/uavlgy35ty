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

$app = JFactory::getApplication();

?>

<div id="djcatalog" class="djc_clearfix djc_item_contact">
	<div class="row-fluid">
		<div class="span8 offset2">
			<?php if ($this->params->get('show_contact_form', '1')) { ?>
			<div class="djc_contact_form_wrapper" id="contactform">
				<?php echo $this->loadTemplate('contact'); ?>
			</div>
			<?php } ?>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function(){
	var isIframe = false;
	try {
		isIframe = window.self !== window.top;
	} catch (e) {
		isIframe = true;
	}

	if (!isIframe) {
		window.location.href="<?php echo JRoute::_(DJCatalogHelperRoute::getItemRoute($this->item->slug, $this->item->catslug), false); ?>";
	}

	<?php if ($app->input->getInt('success')) {?>
	setTimeout(function(){
		if (isIframe) {
			jQuery(window.parent.document).find("button.mfp-close").trigger("click");
		}
	}, 2000);
	<?php } ?>
});
</script>
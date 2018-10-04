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

?>

<?php if (count($this->items) > 0) {?>
	<?php if ($this->params->get('show_layout_switch', '0') == '1' || $this->params->get('item_compare', 1)) { ?>
		<div class="djc_items_toolbar djc_clearfix">
			<?php if ($this->params->get('item_compare', 1)) {?>
				<a href="<?php echo JRoute::_(DJCatalog2HelperRoute::getComparisonRoute());?>" class="btn btn-large djc_compare_btn pull-left" disabled="disabled"><?php echo JText::_('COM_DJCATALOG2_COMPARE_BTN'); ?><span class="djc_compare_count"></span></a>
			<?php } ?>
			
			<?php if ($this->params->get('show_layout_switch', '0') == '1') { ?>
				<div class="djc_layout_switch djc_clearfix">
					<?php echo $this->loadTemplate('layoutswitch'); ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
<?php } ?>

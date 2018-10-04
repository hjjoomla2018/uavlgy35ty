<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$basket = Djcatalog2HelperCart::getInstance(true);

$items = $basket->getItems();
$total = $basket->getTotal();

$cparams = Djcatalog2Helper::getParams();

?>

<div class="mod_djc2cart">
	<?php if (empty($items)) { ?>
		<p class="mod_djc2cart_is_empty"><?php echo JText::_('MOD_DJC2CART_EMPTY_CART');?></p>
	<?php } ?>
	
	<div class="mod_djc2_cart_contents" style="display: <?php echo (empty($items)) ? 'none' : 'block'; ?>;">
		<p class="mod_djc2cart_info">
			<?php echo JText::sprintf('MOD_DJC2CART_YOU_HAVE_ITEMS', count($items)); ?>
		</p>
		<p class="mod_djc2cart_button">
			<a class="btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute()); ?>"><span><?php echo JText::_('MOD_DJC2CART_SHOW_CART');?></span></a>
		</p>
	</div>
</div>

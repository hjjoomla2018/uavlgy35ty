<?php
/**
 * @version $Id: default.php 276 2014-05-23 09:50:49Z michal $
 * @package DJ-Catalog2
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Michal Olczyk - michal.olczyk@design-joomla.eu
 *
 * DJ-Catalog2 is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Catalog2 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Catalog2. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined ('_JEXEC') or die('Restricted access');

$basket = Djcatalog2HelperCart::getInstance(true);

$items = $basket->getItems();
$total = $basket->getTotal();

$cparams = Djcatalog2Helper::getParams();

?>

<div class="mod_djc2cart">
	<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute()); ?>">
		<?php if (empty($items)) { ?>
			<span class="mod_djc2cart_is_empty"><?php echo JText::_('MOD_DJC2CART_CART');?></span>
		<?php } ?>
		<span class="mod_djc2_cart_contents <?php echo (empty($items)) ? 'hide' : ''; ?>">
			<span><?php echo JText::sprintf('MOD_DJC2CART_CART'); ?></span>
			<strong class="djc_mod_cart_items_count"><?php echo count($items);?></strong>
		</span>
	</a>
</div>

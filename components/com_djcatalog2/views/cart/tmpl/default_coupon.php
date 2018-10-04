<?php
/**
 * @version $Id: default.php 389 2015-03-24 17:43:47Z michal $
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

$return_url = base64_encode(JURI::getInstance()->toString());
?>
<div class="djc_cart_coupon">
	<div class="djc_cart_coupon_in clearfix">
		<div class="djc_coupon_desc">
			<h3><?php echo JText::_('COM_DJCATALOG2_COUPON_CODE'); ?></h3>
			<?php if(empty($this->basket->coupon)) { ?>
				<p><?php echo JText::_('COM_DJCATALOG2_COUPON_CODE_DESC'); ?></p>
			<?php } else { ?>
				<?php echo $this->basket->coupon->description ?>
			<?php } ?>
		</div>
		
		<div class="djc_coupon_form">
			<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute());?>" method="post" class="form-horizontal">
				<?php if(empty($this->basket->coupon)) { ?>
					<input type="text" name="coupon_code" class="inputbox" value="" />
					<input type="submit" class="btn btn-gray btn-invert" value="<?php echo JText::_('COM_DJCATALOG2_COUPON_APPLY'); ?>" />
					<input type="hidden" name="task" value="cart.coupon_apply"/>
				<?php } else { ?>
					<input type="text" name="coupon_code" class="inputbox" value="<?php echo $this->basket->coupon->code ?>" disabled readonly />
					<input type="submit" class="btn btn-gray btn-invert" value="<?php echo JText::_('COM_DJCATALOG2_COUPON_REMOVE'); ?>" />
					<input type="hidden" name="task" value="cart.coupon_remove"/>
				<?php } ?>
				<input type="hidden" name="return" value="<?php echo $return_url; ?>" />
				<?php echo JHtml::_( 'form.token' ); ?>
			</form>
		</div>
	</div>
</div>
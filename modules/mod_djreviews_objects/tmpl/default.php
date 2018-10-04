<?php
/**
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

?>
<div class="djrv_listing row-striped mod_djreviews">
<?php if (count($data)) {?>
	<?php foreach ($data as $item) { ?>
		<div class="djrv_single_review row-fluid djrv_clearfix">
			<div class="span12">
			<?php if (!empty($item->_image)) {?>
				<img src="<?php echo $item->_image; ?>" alt="<?php echo htmlspecialchars($item->name);?>" />
			<?php } ?>
		
			<?php if ($params->get('show_rating', '1') == '1') { ?>
				<div class="djrv_rating djrv_user_rating pull-right xsmall">
					<span class="djrv_stars">
					<?php /*for ($i = 1; $i <= 5; $i++) {?>
					<span class="djrv_star <?php if ($i <= $item->avg_rate || ($item->avg_rate - $i) >= -0.5) echo 'active';?>"></span>
					<?php }*/ ?>
					<span class="djrv_stars_sprite"><span class="djrv_stars_sprite_rating" style="width:<?php echo 100 * ($item->avg_rate / 5); ?>%;"></span></span>
					</span>
					<span class="djrv_avg">
						<?php echo $item->avg_rate; ?>
						<?php echo '('.$item->review_count.')'; ?>
					</span>
				</div>
			<?php } ?>
			
			<div class="djrv_object_title">
				<?php if ($item->link && (int)$params->get('link_title', 1) == 1) {?>
				<strong><a href="<?php echo JRoute::_($item->link); ?>"><?php echo htmlspecialchars($item->name); ?></a></strong>
				<?php } else { ?>
				<strong><?php echo htmlspecialchars($item->name); ?></strong>
				<?php } ?>
			</div>
			
			<?php /*if (!empty($item->_additional_info)) {?>
				<div class="djrv_obj_additional">
					<?php echo $item->_additional_info; ?>
				</div>
			<?php }*/ ?>
			</div>
		</div>
	<?php } ?>
<?php }?>
</div>
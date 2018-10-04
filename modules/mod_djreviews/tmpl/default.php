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
						</span>
					</div>
				<?php } ?>
				<?php if ($item->title && (int)$params->get('show_title', 2) > 0) {?>
				<div class="djrv_review_title">
					<?php if ((int)$params->get('show_title', 2) == 2) {?>
					<strong><a href="<?php echo JRoute::_($item->object_url); ?>"><?php echo htmlspecialchars($item->title); ?></a></strong>
					<?php } else { ?>
					<strong><?php echo htmlspecialchars($item->title); ?></strong>
					<?php } ?>
				</div>
				<?php } ?>
				
				<?php if ($item->message && (int)$params->get('show_message', 1) > 0) {?>
				<div class="djrv_review_message">
					<p><?php echo nl2br($item->message); ?></p>
				</div>
				<?php } ?>
				
				<?php if ($item->object_name && (int)$params->get('show_item_title', 2) > 0) {?>
				<div class="djrv_object_title">
					<?php if ((int)$params->get('show_item_title', 2) == 2) {?>
					<strong><a href="<?php echo JRoute::_($item->object_url); ?>"><?php echo htmlspecialchars($item->object_name); ?></a></strong>
					<?php } else { ?>
					<strong><?php echo htmlspecialchars($item->object_name); ?></strong>
					<?php } ?>
				</div>
				<?php } ?>
				
				<?php if ($item->user_login && (int)$params->get('show_info', 1) == 1) {?>
				<div class="djrv_post_info">
					<div class="djrv_poster small">
						<?php echo JText::sprintf('MOD_DJREVIEWS_POSTED_BY_ON', '<span itemprop="author">'.htmlspecialchars($item->user_login).'</span>', JHtml::_('date', $item->created, $params->get('date_format', 'd-m-Y H:i'))); ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
<?php }?>
</div>
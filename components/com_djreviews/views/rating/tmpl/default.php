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


$item = $this->item;

if (empty($item->rating_fields)) {
	return false;
}

//JHtml::_('behavior.tooltip', '.djrv_tooltip');

?>
<div id="djrv-rating-full-<?php echo $item->id; ?>" class="djrv_rating_full djreviews clearfix" data-wrapper="djrv-avg-<?php echo $item->id; ?>" >
<h3><?php echo JText::_('COM_DJREVIEWS_ITEM_RATING_HEADING'); ?></h3>
<?php if (!empty($item->group_description) && $item->group_description!='<p></p>') {?>
<div class="djrv_rating_desc"><?php echo $item->group_description; ?></div>
<?php } ?>
<?php foreach ($item->rating_fields as $field) { ?>
	<?php if (!$field->published) continue; ?>
	<div class="djrv_item_rating djrv_rating row-fluid">
		<div class="span3">
			<?php if ($field->description) { ?>
				<span class="djrv_field djrv_tooltip" title="<?php echo $field->label; ?>" data-content="<?php echo htmlspecialchars($field->description); ?>"><?php echo $field->label; ?></span>
			<?php } else {?>
				<span class="djrv_field"><?php echo $field->label; ?></span>
			<?php } ?>
		</div>
		<div class="span9">
			<span class="djrv_stars">
			<?php /*for ($i = 1; $i <= 5; $i++) {?>
			<span class="djrv_star <?php if ($i <= $field->rating || ($field->rating - $i) >= -0.5) echo 'active';?>"></span>
			<?php }*/ ?>
			<span class="djrv_stars_sprite"><span class="djrv_stars_sprite_rating" style="width:<?php echo 100 * ($field->rating / 5); ?>%;"></span></span>
			<span class="djrv_avg">
				<?php echo $field->rating; ?>
			</span>
		</div>
	</div>
	<?php } ?>
</div>
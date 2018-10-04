<?php
/**
 * @version $Id: avg.php 43 2015-09-03 12:33:32Z michal $
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

/* Basic AVG rating */
$item = $this->item;

if (empty($item->rating_fields)) {
	return false;
}

$params = DJReviewsHelper::getParams($this->item->rating_group_id);

?>

<div class="djrv_rating_avg djreviews djrv_clearfix" data-wrapper="djrv-avg-<?php echo $item->id; ?>" >
	<div class="djrv_item_rating djrv_rating small">
		<span class="djrv_stars">
		<?php /*for ($i = 1; $i <= 5; $i++) {?>
		<span class="djrv_star <?php if ($i <= $item->avg_rate || ($item->avg_rate - $i) >= -0.5) echo 'active';?>"></span>
		<?php }*/ ?>
		<span class="djrv_stars_sprite"><span class="djrv_stars_sprite_rating" style="width:<?php echo 100 * ($item->avg_rate / 5); ?>%;"></span>
		</span>
	</div>
</div>


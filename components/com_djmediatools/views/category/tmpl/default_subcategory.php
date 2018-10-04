<?php
/**
 * @version 1.0
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */
 
defined ('_JEXEC') or die; ?>

<a class="dj-category" href="<?php echo JRoute::_(DJMediatoolsHelperRoute::getCategoryRoute($this->item->slug, $this->item->parent_id)); ?>">
	<span class="dj-category-in" style="width: <?php echo $this->params->get('cwidth', 200) ?>px; height: <?php echo $this->params->get('cheight', 150) ?>px;
		background-image: url(<?php echo $this->item->thumb ?>); background-position: center center; background-repeat: no-repeat;">		
		<?php if($this->params->get('show_cat_titles')) { ?>
		<span class="dj-ctitle<?php echo $this->params->get('show_cat_titles')==2 ? ' showOnOver':''; ?>">
			<span class="dj-ctitle-bg"></span>
			<span class="dj-ctitle-in">
				<?php echo $this->item->title; ?>
			</span>
		</span>
		<?php } ?>
	</span>
</a>

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


<?php if (isset($this->subcategories) && count($this->subcategories) > 0) { ?>
<div class="dj-subcategories">
	
	<?php if(isset($this->slides)) { ?>
		<h2 class="dj-sub-title"><?php echo JText::_('COM_DJMEDIATOOLS_SUBCATEGORIES'); ?></h2>
	<?php } ?>
	
	<div class="dj-categories">
		<?php foreach($this->subcategories as $item) {
			$this->item = &$item;
			echo $this->loadTemplate('subcategory');
		} ?>
		<div style="clear:both"></div>
	</div>
	<div class="dj-pagination pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
</div>
<?php } ?>	

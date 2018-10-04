<?php
/**
 * @version $Id$
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

<?php if($this->params->get('show_page_heading', 1)) : ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<div id="djmediatools" class="djmediatools<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	
	<?php if($this->params->get('show_cat_title') && $this->category != 'root') { ?>
		<h2 class="dj-cat-title"><?php echo $this->category->title; ?></h2>
	<?php } ?>
	<?php if(isset($this->categories) && count($this->categories) > 0){ ?>
	<div class="dj-categories">
		<?php foreach($this->categories as $item) {
			$this->item = &$item;
			echo $this->loadTemplate('item');
		} ?>
		<div style="clear:both"></div>
	</div>
	<div class="dj-pagination pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php } ?>
	
	
<?php 
	//if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>

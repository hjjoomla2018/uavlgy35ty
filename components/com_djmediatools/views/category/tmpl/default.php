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
 
defined ('_JEXEC') or die; 

$user = JFactory::getUser();
?>

<?php if($this->params->get('show_page_heading', 1)) : ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<div id="djmediatools" class="djmediatools<?php echo $this->params->get( 'pageclass_sfx' ); ?>">

	<?php /* if($this->category->parent_id != 0) { ?>
		<a class="btn btn-primary pull-right" href="<?php echo JRoute::_(DJMediatoolsHelperRoute::getCategoryRoute($this->category->parent_id)); ?>">Back</a>
	<?php } else { ?>
		<a class="btn btn-primary pull-right" href="<?php echo JRoute::_(DJMediatoolsHelperRoute::getCategoriesRoute($this->category->parent_id)); ?>">Back</a>
	<?php } */ ?>
	
	<?php 
	 if($this->params->get('show_subcategories') == 'before') echo $this->loadTemplate('subcategories'); ?>
	
	<?php if($user->authorise('core.edit', 'com_djmediatools')) { ?>
		<a href="<?php echo JRoute::_(DJMediatoolsHelperRoute::getCategoryFormRoute($this->category->id, $this->category->parent_id)); ?>" class="btn btn-small pull-right">
		<span class="icon-edit"></span><?php echo JText::_('COM_DJMEDIATOOLS_EDIT_ALBUM') ?></a>
	<?php } ?>
	
	<?php if($this->params->get('show_cat_title')) echo '<h2 class="dj-cat-title">' . $this->escape($this->category->title) . '</h2>'; ?>
	
	<?php if($this->params->get('show_cat_desc') == 'before') echo '<div class="category-desc">'.JHTML::_('content.prepare', $this->category->description).'</div>'; ?>
	
	<?php echo $this->loadTemplate('gallery'); ?>
	
	<?php if($this->params->get('show_cat_desc') == 'after') echo '<div class="category-desc">'.JHTML::_('content.prepare', $this->category->description).'</div>'; ?>
	
	<?php if($this->params->get('show_subcategories') == 'after') echo $this->loadTemplate('subcategories'); ?>
	
</div>

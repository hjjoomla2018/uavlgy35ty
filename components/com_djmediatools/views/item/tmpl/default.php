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

defined ('_JEXEC') or die; 

$item = $this->slides[$this->current];
$albumLink = JRoute::_(DJMediatoolsHelperRoute::getCategoryRoute($this->album->id . ($this->album->alias ? ':'.$this->album->alias : ''), $this->album->parent_id));

?>

<div id="djmediatools" class="dj-album djmediatools<?php echo $this->params->get( 'pageclass_sfx' ); echo ($this->params->get('show_album_title') ? '':' no-title'); ?>" data-album-url="<?php echo $albumLink; ?>">
	
	<?php if($this->params->get('show_album_title')) { ?>
		<h1 class="dj-album-title"><?php echo $this->escape($this->album->title); ?></h1>		
	<?php } ?>
	
	<div class="dj-album-item">
		<div class="dj-album-item-in">
		
			<?php if(!empty($item->video)) { ?>
			<div class="dj-album-image dj-album-video">
				<iframe width="100%" height="100%" src="<?php echo $item->video; ?>" frameborder="0" allowfullscreen></iframe>
			</div>
			<?php } else { ?>
			<div class="dj-album-image">
				<img id="dj-image" src="<?php echo $item->image ?>" alt="<?php echo $item->alt ?>" />
			</div>
			<?php } ?>
			
			<div class="dj-album-item-desc">
				
				<?php if($this->params->get('show_album_desc') && !empty($this->album->description)) : ?>
				<div class="dj-album-desc">
					<?php echo JHTML::_('content.prepare', $this->album->description); ?>
				</div>
				<?php endif; ?>
				
				<?php if($this->params->get('show_title')) { ?>
					<h2 class="dj-item-title">
						<?php if($item->link && empty($item->video)) { ?><a href="<?php echo $item->link; ?>" target="<?php echo ($item->target=='_self' ? '_parent' : $item->target ) ?>"><?php } ?>
							<?php echo $this->escape($item->title); ?>
						<?php if($item->link && empty($item->video)) { ?></a><?php } ?>
					</h2>
				<?php } ?>
				
				<div class="dj-full-item-desc">
					<?php echo JHTML::_('content.prepare', $item->full_desc); ?>
					
					<?php if($this->params->get('show_readmore') && $item->link && empty($item->video)) { ?>
						<div style="clear: both"></div>
						<div class="dj-readmore-wrapper">
							<a href="<?php echo $item->link; ?>" target="<?php echo ($item->target=='_self' ? '_parent' : $item->target ) ?>" class="dj-readmore"><?php echo ($this->params->get('readmore_text',0) ? $this->params->get('readmore_text') : JText::_('COM_DJMEDIATOOLS_READMORE')); ?></a>
						</div>
					<?php } ?>
				</div>
				
				<?php if((int)$this->params->get('comments', 0) > 0){
					echo $this->loadTemplate('comments');
				} ?>				
				
				<?php if(!empty($this->modules['djmt-item-desc'])) : ?>
				<div class="modules-item-desc">
					<?php echo $this->modules['djmt-item-desc'] ?>
				</div>
				<?php endif; ?>
			</div>
			
		</div>
		
	</div>
	
	<div class="dj-album-navi">
			
		<?php if($this->current > 0) : ?>
			<a class="dj-prev" href="<?php echo JRoute::_(DJMediatoolsHelperRoute::getItemRoute($this->slides[$this->current - 1]->id, $this->album->id.':'.$this->album->alias)) ?>"><?php echo $this->escape($this->slides[$this->current - 1]->title) ?></a>
		<?php endif; ?>
		<?php if($this->current < count($this->slides) - 1) : ?>	
			<a class="dj-next" href="<?php echo JRoute::_(DJMediatoolsHelperRoute::getItemRoute($this->slides[$this->current + 1]->id, $this->album->id.':'.$this->album->alias)) ?>"><?php echo $this->escape($this->slides[$this->current + 1]->title) ?></a>
		<?php endif; ?>
		
		<div class="dj-count"><?php echo JText::sprintf('COM_DJMEDIATOOLS_ITEM_CURRENT_OF_TOTAL', ($this->current+1), count($this->slides)); ?></div>
	</div>
</div>

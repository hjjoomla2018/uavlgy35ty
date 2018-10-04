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


defined('_JEXEC') or die('Restricted access'); ?>

<?php if (version_compare(JVERSION, '3.0', '>=')) { ?>

<div class="row-fluid">
		<div class="cpanel-left span8">
			<div id="cpanel" class="cpanel well">
				<div class="module-title nav-header"><?php echo JText::_('COM_DJMEDIATOOLS_SUBMENU_CPANEL') ?></div>
				<div class="row-striped">
					<div class="row-fluid">
						<div class="icon">
							<a href="index.php?option=com_djmediatools&view=categories">
								<img src="components/com_djmediatools/assets/icon-48-category.png" alt="<?php echo JText::_('COM_DJMEDIATOOLS_SUBMENU_CATEGORIES') ?>" />
								<span><?php echo JText::_('COM_DJMEDIATOOLS_SUBMENU_CATEGORIES'); ?></span>
							</a>
						</div>
					</div>
					
					<div class="row-fluid">
						<div class="icon">
							<a href="index.php?option=com_djmediatools&view=category&layout=edit">
								<img src="components/com_djmediatools/assets/icon-48-category-add.png" alt="<?php echo JText::_('COM_DJMEDIATOOLS_NEW_CATEGORY') ?>" />
								<span><?php echo JText::_('COM_DJMEDIATOOLS_NEW_CATEGORY'); ?></span>
							</a>
						</div>
					</div>
					
					<div class="row-fluid">
						<div class="icon">
							<a href="index.php?option=com_djmediatools&view=items">
								<img src="components/com_djmediatools/assets/icon-48-slides.png" alt="<?php echo JText::_('COM_DJMEDIATOOLS_SUBMENU_SLIDES') ?>" />
								<span><?php echo JText::_('COM_DJMEDIATOOLS_SUBMENU_SLIDES'); ?></span>
							</a>
						</div>
					</div>
					<div class="row-fluid">
						<div class="icon">
							<a href="index.php?option=com_djmediatools&view=item&layout=edit">
								<img src="components/com_djmediatools/assets/icon-48-slide-add.png" alt="<?php echo JText::_('COM_DJMEDIATOOLS_NEW_SLIDE') ?>" />
								<span><?php echo JText::_('COM_DJMEDIATOOLS_NEW_SLIDE'); ?></span>
							</a>
						</div>
					</div>					
					
					<div class="row-fluid">
						<div class="icon">
							<a href="index.php?option=com_plugins&view=plugins&filter_folder=djmediatools">
								<img src="components/com_djmediatools/assets/icon-48-plugin.png" alt="<?php echo JText::_('COM_DJMEDIATOOLS_PLUGINS') ?>" />
								<span style="line-height: 1.1;"><?php echo JText::_('COM_DJMEDIATOOLS_PLUGINS'); ?></span>
							</a>
						</div>
					</div>
					
					<div class="row-fluid">
						<div class="icon">
							<a href="index.php?option=com_config&view=component&component=com_djmediatools&return=<?php echo base64_encode(JFactory::getURI()->toString()); ?>">
								<img src="components/com_djmediatools/assets/icon-48-config.png" alt="<?php echo JText::_('JOPTIONS') ?>" />
								<span><?php echo JText::_('JOPTIONS'); ?></span>
							</a>
						</div>
					</div>
					
					<div class="row-fluid">
						<div class="icon">
							<a href="http://dj-extensions.com/documentation/dj-mediatools/" target="_blank">
								<img src="components/com_djmediatools/assets/icon-48-help.png" alt="<?php echo JText::_('COM_DJMEDIATOOLS_DOCUMENTATION') ?>" />
								<span><?php echo JText::_('COM_DJMEDIATOOLS_DOCUMENTATION'); ?></span>
							</a>
						</div>
					</div>
					
				</div>
			</div>
		</div>
			
		<div class="cpanel-right span4">
			<div class="cpanel well">
				<div class="row-fluid">
					<?php echo DJLicense::getSubscription('MediaTools'); ?>
				</div>
			</div>
		</div>

</div>

<?php } else { ?>

<table class="adminform">
	<tr>
		<td width="55%" valign="top">
			<div class="cpanel-left">
				<div id="cpanel" class="cpanel">
					
					<div style="float:left;">
						<div class="icon">
							<a href="index.php?option=com_djmediatools&view=categories">
								<?php echo JHTML::_('image.administrator', 'icon-48-category.png', '/components/com_djmediatools/assets/', null, null, JText::_('COM_DJMEDIATOOLS_SUBMENU_CATEGORIES') ); ?>
								<span><?php echo JText::_('COM_DJMEDIATOOLS_SUBMENU_CATEGORIES'); ?></span>
							</a>
						</div>
					</div>
					
					<div style="float:left;">
						<div class="icon">
							<a href="index.php?option=com_djmediatools&view=category&layout=edit">
								<?php echo JHTML::_('image.administrator', 'icon-48-category-add.png', '/components/com_djmediatools/assets/', null, null, JText::_('COM_DJMEDIATOOLS_NEW_CATEGORY') ); ?>
								<span><?php echo JText::_('COM_DJMEDIATOOLS_NEW_CATEGORY'); ?></span>
							</a>
						</div>
					</div>
					
					<div style="float:left;">
						<div class="icon">
							<a href="index.php?option=com_djmediatools&view=items">
								<?php echo JHTML::_('image.administrator', 'icon-48-slides.png', '/components/com_djmediatools/assets/', null, null, JText::_('COM_DJMEDIATOOLS_SUBMENU_SLIDES') ); ?>
								<span><?php echo JText::_('COM_DJMEDIATOOLS_SUBMENU_SLIDES'); ?></span>
							</a>
						</div>
					</div>
					<div style="float:left;">
						<div class="icon">
							<a href="index.php?option=com_djmediatools&view=item&layout=edit">
								<?php echo JHTML::_('image.administrator', 'icon-48-slide-add.png', '/components/com_djmediatools/assets/', null, null, JText::_('COM_DJMEDIATOOLS_NEW_SLIDE') ); ?>
								<span><?php echo JText::_('COM_DJMEDIATOOLS_NEW_SLIDE'); ?></span>
							</a>
						</div>
					</div>					
					
					<div style="clear:both"></div>
					
					<div style="float:left;">
						<div class="icon">
							<a href="index.php?option=com_plugins&view=plugins&filter_folder=djmediatools">
								<?php echo JHTML::_('image.administrator', 'icon-48-plugin.png', '/components/com_djmediatools/assets/', null, null, JText::_('COM_DJMEDIATOOLS_PLUGINS') ); ?>
								<span style="line-height: 1.1;"><?php echo JText::_('COM_DJMEDIATOOLS_PLUGINS'); ?></span>
							</a>
						</div>
					</div>
					
					<div style="float:left;">
						<div class="icon">
							<a rel="{handler: 'iframe', size: {x: 900, y: 550}, onClose: function() {}}" href="index.php?option=com_config&amp;view=component&amp;component=com_djmediatools&amp;path=&amp;tmpl=component" class="modal">
								<?php echo JHTML::_('image.administrator', 'icon-48-config.png', '/components/com_djmediatools/assets/', null, null, JText::_('JOPTIONS') ); ?>
								<span><?php echo JText::_('JOPTIONS'); ?></span>
							</a>
						</div>
					</div>
					
					<div style="float:left;">
						<div class="icon">
							<a href="http://dj-extensions.com/documentation/dj-mediatools/" target="_blank">
								<?php echo JHTML::_('image.administrator', 'icon-48-help.png', '/components/com_djmediatools/assets/', null, null, JText::_('COM_DJMEDIATOOLS_DOCUMENTATION') ); ?>
								<span><?php echo JText::_('COM_DJMEDIATOOLS_DOCUMENTATION'); ?></span>
							</a>
						</div>
					</div>
				</div>
			</div>
			
			<div class="cpanel-right">
				<div class="cpanel">
					<div>
						<?php echo DJLicense::getSubscription('MediaTools'); ?>
					</div>
					<div style="clear: both;" ></div>
				</div>
			</div>
		
		</td>
	</tr>
</table>

<?php } ?>

<?php echo DJMEDIATOOLSFOOTER; ?>
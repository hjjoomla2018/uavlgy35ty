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

defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm">
<div class="djc_control_panel">
	<div class="cpanel-left">
		<div class="cpanel">
			<div style="float:left;">
				<div class="icon">
					<a href="index.php?option=com_djreviews&amp;view=reviews">
						<img alt="<?php echo JText::_('COM_DJREVIEWS_REVIEWS'); ?>" src="<?php echo JURI::base(); ?>components/com_djreviews/assets/images/icon-48-reviews.png" />
						<span><?php echo JText::_('COM_DJREVIEWS_REVIEWS'); ?></span>
					</a>
				</div>
			</div>
			<div style="float:left;">
				<div class="icon">
					<a href="index.php?option=com_djreviews&amp;view=groups">
						<img alt="<?php echo JText::_('COM_DJREVIEWS_RATING_GROUPS'); ?>" src="<?php echo JURI::base(); ?>components/com_djreviews/assets/images/icon-48-groups.png" />
						<span><?php echo JText::_('COM_DJREVIEWS_RATING_GROUPS'); ?></span>
					</a>
				</div>
			</div>
			<div style="float:left;">
				<div class="icon">
					<a href="index.php?option=com_djreviews&amp;view=fields">
						<img alt="<?php echo JText::_('COM_DJREVIEWS_RATING_FIELDS'); ?>" src="<?php echo JURI::base(); ?>components/com_djreviews/assets/images/icon-48-fields.png" />
						<span><?php echo JText::_('COM_DJREVIEWS_RATING_FIELDS'); ?></span>
					</a>
				</div>
			</div>
			
			<div style="clear: both" class="clr"></div>
			
			<div style="float:left;">
				<div class="icon">
					<a rel="{handler: 'iframe', size: {x: 800, y: 450}, onClose: function() {}}" href="index.php?option=com_config&amp;view=component&amp;component=com_djreviews&amp;path=&amp;tmpl=component" class="modal">
						<img alt="<?php echo JText::_('JOPTIONS'); ?>" src="<?php echo JURI::base(); ?>components/com_djreviews/assets/images/icon-48-config.png" />
						<span><?php echo JText::_('JOPTIONS'); ?></span>
					</a>
				</div>
			</div>
			
			<div style="float:left;">
				<div class="icon">
					<a href="http://dj-extensions.com/extensions/dj-reviews" target="_blank">
						<img alt="<?php echo JText::_('COM_DJREVIEWS_DOCUMENTATION'); ?>" src="<?php echo JURI::base(); ?>components/com_djreviews/assets/images/icon-48-documentation.png" />
						<span><?php echo JText::_('COM_DJREVIEWS_DOCUMENTATION'); ?></span>
					</a>
				</div>
			</div>
			<div style="clear: both" class="clr"></div>
		</div>
	</div>
	<div class="cpanel-right">
		<div class="djlic_cpanel cpanel">
			<div style="float:right;">
				<?php 
				$user = JFactory::getUser();
				if ($user->authorise('core.admin', 'com_djreviews')){
					echo DJLicense::getSubscription('Reviews');
				}
				?>
			</div>
		</div>
	</div>
	<div style="clear: both" class="clr"></div>
</div>

<input type="hidden" name="option" value="com_djreviews" />
<input type="hidden" name="c" value="cpanel" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="view" value="cpanel" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php echo DJREVIEWSFOOTER; ?>
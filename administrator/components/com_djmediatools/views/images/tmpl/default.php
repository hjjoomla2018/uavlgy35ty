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

defined('_JEXEC') or die('Restricted access');
?>
<div>
	<div id="j-main-container" class="span7 form-horizontal">

		<fieldset>
			<?php
			$count = count($this->images);
			?>


			<div class="alert alert-info">
				<?php echo JText::_('COM_DJMEDIATOOLS_IMAGES_DELETE_LABEL_DESC'); ?>
			</div>
			<div class="control-label">
				<label><?php echo JText::_('COM_DJMEDIATOOLS_IMAGES_DELETE_LABEL'); ?>
				</label>
			</div>

			<div class="control-group">
				<div class="controls">
					<?php if ($count > 0) { ?>
					<button disabled="disabled" class="button btn btn-danger"
						id="djmt_delete_images">
						<?php echo JText::sprintf('COM_DJMEDIATOOLS_IMAGES_DELETE_BUTTON', $count); ?>
					</button>
					<?php } else { ?>
					<button disabled="disabled" class="button btn">
						<?php echo JText::_('COM_DJMEDIATOOLS_NOTHING_TO_DELETE'); ?>
					</button>
					<?php } ?>
				</div>
			</div>

			<div style="clear: both">
				<br /> <br />
			</div>

			<?php 
			$resmushed = $this->resmushed;
			$cronUrl = JUri::root().'index.php?option=com_djmediatools&task=optimize';
			?>

			<div class="alert alert-info">
				<?php echo JText::_('COM_DJMEDIATOOLS_IMAGES_RESMUSHIT_LABEL_DESC'); ?>
			</div>
			<div class="alert alert-info">
				<?php echo JText::sprintf('COM_DJMEDIATOOLS_IMAGES_ALREADY_OPTIMIZED', $resmushed); ?>
			</div>

			<div class="control-group">
				<div class="control-label">
					<label><?php echo JText::_('COM_DJMEDIATOOLS_IMAGES_RESMUSHIT_CRON_URL'); ?>
					</label>
				</div>
				<div class="controls">
					<input type="text" class="input-xxlarge" readonly="readonly"
						onclick="this.select();" style="cursor: pointer;"
						value="<?php echo htmlspecialchars($cronUrl, ENT_COMPAT, 'UTF-8') ?>" />
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<label><?php echo JText::_('COM_DJMEDIATOOLS_IMAGES_RESMUSHIT_LABEL'); ?>
					</label>
				</div>
				<div class="controls">
					<?php if ($count > $resmushed) { ?>
					<button disabled="disabled" class="button btn btn-primary"
						id="djmt_resmushit_images">
						<?php echo JText::sprintf('COM_DJMEDIATOOLS_IMAGES_RESMUSHIT_BUTTON', ($count - $resmushed)); ?>
					</button>
					<?php } else { ?>
					<button disabled="disabled" class="button btn">
						<?php echo JText::_('COM_DJMEDIATOOLS_NOTHING_TO_OPTIMIZE'); ?>
					</button>
					<?php } ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">&nbsp;</div>
				<div class="controls djmt_resmushit_log_wrapper">
					<textarea rows="10" cols="50" id="djmt_resmushit_log"
						disabled="disabled" class="input-xxlarge input"></textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">&nbsp;</div>
				<div class="controls djmt_resmushit">
					<div style="clear: both" class="clr"></div>
					<div id="djmt_progress_bar_outer" class="progress">
						<div id="djmt_progress_bar" class="bar"></div>
					</div>
					<div id="djmt_progress_percent" class="center">0%</div>
				</div>
			</div>
			<div style="clear: both">
				<br /> <br />
			</div>

			<?php
			$count = count($this->stylesheets);
			?>

			<div class="alert alert-info">
				<?php echo JText::_('COM_DJMEDIATOOLS_STYLESHEETS_DELETE_LABEL_DESC'); ?>
			</div>

			<div class="control-group">
				<div class="control-label">
					<label><?php echo JText::_('COM_DJMEDIATOOLS_STYLESHEETS_DELETE_LABEL'); ?>
					</label>
				</div>
				<div class="controls">
					<?php if ($count > 0) { ?>
					<button disabled="disabled" class="button btn btn-danger"
						id="djmt_delete_stylesheets">
						<?php echo JText::sprintf('COM_DJMEDIATOOLS_STYLESHEETS_DELETE_BUTTON', $count); ?>
					</button>
					<?php } else { ?>
					<button disabled="disabled" class="button btn">
						<?php echo JText::_('COM_DJMEDIATOOLS_NOTHING_TO_DELETE'); ?>
					</button>
					<?php } ?>
				</div>
			</div>


		</fieldset>
	</div>
</div>

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access'); 

JHtml::_('jquery.framework');
JHtml::_('behavior.tooltip');

$document = JFactory::getDocument();
$document->addScript(JURI::root() . "administrator/components/com_djcatalog2/views/thumbs/thumbs.js");

?>
<div>
<div id="j-sidebar-container" class="span2">
<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10 form-horizontal">

		<fieldset>
		<div class="control-group">
			<div class="control-label">
				<label class="hasTip" title="<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_LABEL_DESC'); ?>"><?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_LABEL'); ?></label>
			</div>
			<div class="controls">
				<div class="djc_thumbrecreator">
					<button disabled="disabled" class="button btn recreator_button" id="djc_start_recreation">
						<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON'); ?>
					</button>
					<button disabled="disabled" class="button btn recreator_button" id="djc_start_recreation_item">
						<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON_I'); ?>
					</button>
					<button disabled="disabled" class="button btn recreator_button" id="djc_start_recreation_category">
						<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON_C'); ?>
					</button>
					<button disabled="disabled" class="button btn recreator_button" id="djc_start_recreation_producer">
						<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON_P'); ?>
					</button>
				</div>
			</div>
		</div>
		
		<div  class="control-group">
			<div class="control-label">
				<label for="djc_thumbrecreator_start"><?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_START_FROM'); ?></label>
			</div>
			<div class="controls">
				<input type="text" class="inputbox input-mini" id="djc_thumbrecreator_start" value="0" />
				<button class="button btn btn-warning" id="djc_thumbrecreator_stop">
					<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON_STOP'); ?>
				</button>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">&nbsp;</div>
			<div class="controls djc_thumbrecreator_log_wrapper">
				<textarea rows="10" cols="50" id="djc_thumbrecreator_log" disabled="disabled" class="input-xxlarge input"></textarea>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">&nbsp;</div>
			<div class="controls djc_thumbrecreator">
				<div style="clear: both" class="clr"></div>
				<div id="djc_progress_bar_outer" class="progress">
					<div id="djc_progress_bar" class="bar"></div>
				</div>
				<div id="djc_progress_percent">0%</div>
			</div>
		</div>
		
		<?php 
		$db = JFactory::getDbo();
		$db->setQuery('select count(*) as path_count, path from #__djc2_images group by path');
		
		$paths = $db->loadObjectList();
		
		$root_dir = new stdClass();
        $root_dir->path_count = 1;
        $root_dir->path = '';
		$paths[] = $root_dir;
        
		$file_count = 0;
		foreach ($paths as $path) {
			if ($path->path_count == '0') {
				continue;
			}
			
			$dir = (empty($path)) ? DJCATIMGFOLDER : DJCATIMGFOLDER.DS.str_replace('/', DS, $path->path);
			if (!JFolder::exists($dir.DS.'custom')){
				continue;
			}
			$files = JFolder::files($dir.DS.'custom', '.', false, false, array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX'));

			if (is_array($files) && count($files) > 0) {
				$file_count += count($files);
			}	
		}
		?>
		
		<div class="control-group">
			<div class="control-label">
			<label for="djc_start_deleting" class="hasTip" title="<?php echo JText::_('COM_DJCATALOG2_IMAGES_DELETE_LABEL_DESC'); ?>"><?php echo JText::_('COM_DJCATALOG2_IMAGES_DELETE_LABEL'); ?></label>
			</div>
			<div class="controls">
			<?php if ($file_count > 0) { ?>
			<button disabled="disabled" class="button btn" id="djc_start_deleting">
				<?php echo JText::sprintf('COM_DJCATALOG2_IMAGES_DELETE_BUTTON', $file_count); ?>
			</button>
			<?php } else { ?>
			<button disabled="disabled" class="button btn"><?php echo JText::_('COM_DJCATALOG2_NOTHING_TO_DELETE'); ?></button>
			<?php } ?>
			</div>
		</div>
		
		<div style="clear: both">
				<br /> <br />
			</div>

			<?php 
			$resmushed = $this->resmushed;
			$count = count($this->images);
			$cronUrl = JUri::root().'index.php?com_djcatalog2&task=thumbs.optimize';
			?>

			<div class="alert alert-info">
				<?php echo JText::_('COM_DJCATALOG2_IMAGES_RESMUSHIT_LABEL_DESC'); ?>
			</div>
			<div class="alert alert-info">
				<?php echo JText::sprintf('COM_DJCATALOG2_IMAGES_ALREADY_OPTIMIZED', $resmushed); ?>
			</div>

			<?php /*
			<div class="control-group">
				<div class="control-label">
					<label><?php echo JText::_('COM_DJCATALOG2_IMAGES_RESMUSHIT_CRON_URL'); ?>
					</label>
				</div>
				<div class="controls">
					<input type="text" class="input-xxlarge" readonly="readonly"
						onclick="this.select();" style="cursor: pointer;"
						value="<?php echo htmlspecialchars($cronUrl, ENT_COMPAT, 'UTF-8') ?>" />
				</div>
			</div>*/ ?>

			<div class="control-group">
				<div class="control-label">
					<label><?php echo JText::_('COM_DJCATALOG2_IMAGES_RESMUSHIT_LABEL'); ?>
					</label>
				</div>
				<div class="controls">
					<?php if ($count > $resmushed) { ?>
					<button disabled="disabled" class="button btn btn-primary"
						id="djc_rmit_resmushit_images">
						<?php echo JText::sprintf('COM_DJCATALOG2_IMAGES_RESMUSHIT_BUTTON', ($count - $resmushed)); ?>
					</button>
					<?php } else { ?>
					<button disabled="disabled" class="button btn">
						<?php echo JText::_('COM_DJCATALOG2_NOTHING_TO_OPTIMIZE'); ?>
					</button>
					<?php } ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">&nbsp;</div>
				<div class="controls djc_rmit_resmushit_log_wrapper">
					<textarea rows="10" cols="50" id="djc_rmit_resmushit_log"
						disabled="disabled" class="input-xxlarge input"></textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">&nbsp;</div>
				<div class="controls djc_rmit_resmushit">
					<div style="clear: both" class="clr"></div>
					<div id="djc_rmit_progress_bar_outer" class="progress">
						<div id="djc_rmit_progress_bar" class="bar"></div>
					</div>
					<div id="djc_rmit_progress_percent" class="center">0%</div>
				</div>
			</div>
			<div style="clear: both">
				<br /> <br />
			</div>
		</fieldset>
</div>
</div>
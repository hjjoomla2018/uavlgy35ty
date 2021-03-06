<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access'); 
JHtml::_('behavior.tooltip');
?>


<div class="width-100">
<fieldset class="adminform">
	
	<ul class="adminformlist">
	<li>
		<label class="hasTip" title="<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_LABEL_DESC'); ?>"><?php echo JText::_('COM_DJCATALOG2_RECREATE_THUMBNAILS'); ?></label>
		<button disabled="disabled" class="button recreator_button" id="djc_start_recreation">
			<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON'); ?>
		</button>
		<button disabled="disabled" class="button recreator_button" id="djc_start_recreation_item">
			<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON_I'); ?>
		</button>
		<button disabled="disabled" class="button recreator_button" id="djc_start_recreation_category">
			<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON_C'); ?>
		</button>
		<button disabled="disabled" class="button recreator_button" id="djc_start_recreation_producer">
			<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON_P'); ?>
		</button>
	</li>
	<li>
		<label for="djc_thumbrecreator_start"><?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_START_FROM'); ?></label>
		<input type="text" class="inputbox" id="djc_thumbrecreator_start" value="0" />
		<button class="button" id="djc_thumbrecreator_stop">
			<?php echo JText::_('COM_DJCATALOG2_THUMBNAILS_RECREATOR_BUTTON_STOP'); ?>
		</button>
	</li>
	<li>
		<span class="faux-label">&nbsp;</span>
		<div class="djc_thumbrecreator_log_wrapper">
			<textarea rows="10" cols="30" id="djc_thumbrecreator_log" disabled="disabled"></textarea>
		</div>
	</li>
	<li>
		<span class="faux-label">&nbsp;</span>
		<div class="djc_thumbrecreator">
			<div style="clear: both" class="clr"></div>
			<div id="djc_progress_bar_outer">
				<div id="djc_progress_bar"></div>
				<div style="clear: both" class="clr"></div>
				<div id="djc_progress_percent">
					0%
				</div>
			</div>
		</div>
	</li>
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
		
		<li>
			<label for="djc_start_deleting" class="hasTip" title="<?php echo JText::_('COM_DJCATALOG2_IMAGES_DELETE_LABEL_DESC'); ?>"><?php echo JText::_('COM_DJCATALOG2_IMAGES_DELETE_LABEL'); ?></label>
			<?php if ($file_count > 0) { ?>
			<button disabled="disabled" class="button btn" id="djc_start_deleting">
				<?php echo JText::sprintf('COM_DJCATALOG2_IMAGES_DELETE_BUTTON', $file_count); ?>
			</button>
			<?php } else { ?>
			<button disabled="disabled" class="button btn"><?php echo JText::_('COM_DJCATALOG2_NOTHING_TO_DELETE'); ?></button>
			<?php } ?>
		</li>
	</ul>
</fieldset>
<div style="clear: both" class="clr"></div>
</div>

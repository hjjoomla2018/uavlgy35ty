<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
// no direct access
defined('_JEXEC') or die();

JHtml::_('jquery.framework');

$app = JFactory::getApplication();

$id = $app->input->get('id');

if (!$id) {
	JError::raiseError(404);
}

$plugin = $app->input->get('extension');

$lang = JFactory::getLanguage();
if ($plugin) {
	$lang = JFactory::getLanguage();
	$plugin_parts = explode('_', $plugin, 3);
	if (count($plugin_parts) == 3) {
		$lang->load($plugin, JPATH_ROOT.DS.'plugins'.DS.$plugin_parts[1].DS.$plugin_parts[2], null, true, false);
		$lang->load($plugin, JPATH_ROOT.DS.'plugins'.DS.$plugin_parts[1].DS.$plugin_parts[2], 'en-GB', false, false);
	}
	$lang->load($plugin, JPATH_ADMINISTRATOR, null, true, false);
	$lang->load($plugin, JPATH_ADMINISTRATOR, 'en-GB', false, false);
}

$db = JFactory::getDbo();
$db->setQuery('select id, caption as text from #__djc2_files where type='.$db->quote('item').' and item_id='.(int)$id.' order by ordering asc');
$files = $db->loadObjectlist('id');

if (empty($files)) {
	echo JText::_('PLG_EDITORS-XTD_DJCATALOG2FILE_ERROR_FILES_MISSING');
} else {
	require_once (str_replace('/',DIRECTORY_SEPARATOR, JPATH_ROOT.'/components/com_djcatalog2/helpers/route.php'));
	
	$file_selector = JHTML::_('select.genericlist', $files, 'file_id', 'class="inputbox input input-medium"', 'id', 'text', null, 'djcatalog2file_selector');
	
	?>
	<div>
		<fieldset class="adminform">
			<legend><?php echo JText::_('PLG_EDITORS-XTD_DJCATALOG2FILE_LEGEND') ?></legend>
			<div class="control-group">
				<div class="control-label">
					<label>
					<?php echo JText::_('PLG_EDITORS-XTD_DJCATALOG2FILE_LABEL') ?>: 
					</label>
				</div>
				<div class="controls">
					<?php echo $file_selector ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label>
					<?php echo JText::_('PLG_EDITORS-XTD_DJCATALOG2FILELABEL_LABEL') ?>: 
					</label>
				</div>
				<div class="controls">
					<input type="text" value="" id="djcatalog2file_label" class="inputbox input-medium input" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label>
					<?php echo JText::_('PLG_EDITORS-XTD_DJCATALOG2FILECLASSNAME_LABEL') ?>: 
					</label>
				</div>
				<div class="controls">
					<input type="text" value="button btn" id="djcatalog2file_classname" class="inputbox input-medium input" />
				</div>
			</div>
			<div class="control-group">
				<span class="faux-label"></span>
				<div class="controls">
					<button class="button btn" id="djcatalog2file_attach_button" onclick="DJCatalog2FileAttach();"><?php echo JText::_('PLG_EDITORS-XTD_DJCATALOG2FILE_ATTACHBUTTON') ?></button>
				</div>
			</div>
		</fieldset>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				this.djcatalog2file_selector = jQuery('#djcatalog2file_selector');
				this.djcatalog2file_label = jQuery('#djcatalog2file_label');
				this.djcatalog2file_classname = jQuery('#djcatalog2file_classname');
				
				djcatalog2file_label.val(djcatalog2file_selector.find('option:selected').first().text());
				
				djcatalog2file_selector.on('change', function(evt){
					if (djcatalog2file_selector.val() != '') {
						djcatalog2file_label.val(djcatalog2file_selector.find('option:selected').first().text());
					}
				});
				this.djcatalog2file_link = 'index.php?option=com_djcatalog2&amp;format=raw&amp;task=download&amp;fid=';
				
				this.DJCatalog2FileAttach= function() {
					if (window.parent) {
						var id = djcatalog2file_selector.val(); 
						if (!id) return;

						var label = djcatalog2file_label.val(); 

						if (label == '') {
							label = 'Download';
						}
						var link = '<a href="'+djcatalog2file_link+id+'" target="_blank" class="'+djcatalog2file_classname.value+'"><span>'+label+'</span></a>';
						window.parent.jInsertDJCatalog2Attachment(link);
					}
				}
			});
		</script>
	</div>
	<?php
}


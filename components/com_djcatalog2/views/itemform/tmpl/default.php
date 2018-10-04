<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die();
JHtmlBehavior::core();
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
//JHtml::_('formbehavior.chosen', '#djcatalog select');

DJCatalog2HtmlHelper::initCalendarScripts();

$user = JFactory::getUser();

?>

<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>

<div id="djcatalog" class="djc_itemform">
	<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'itemform.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			<?php 
			if ($this->params->get('fed_description', '0') != '0') {
				echo $this->form->getField('description')->save(); 
			}
			if ($this->params->get('fed_intro_description', '0') != '0') {
				echo $this->form->getField('intro_desc')->save();
			}
			?>

			if (jQuery('#itemAttributes')) {
				var textareas = jQuery('#itemAttributes').find('textarea.nicEdit');
				if (textareas) {
					textareas.each(function(){
						var textarea = jQuery(this);
						if (textarea.nicEditor != null && textarea.nicEditor) {
							var editor = textarea.nicEditor.instanceById(textarea.id);
							if (editor) {
								if (editor.getContent() == "<br />") {
									editor.setContent("");
								}
								editor.saveContent();
							}
						}
					});
				}
			}
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
	</script>

	<div class="formelm-buttons djc_form_toolbar btn-toolbar">
		<?php if ($user->authorise('core.edit', 'com_djcatalog2') || $user->authorise('core.edit.own', 'com_djcatalog2')) { ?>
		<button type="button" onclick="Joomla.submitbutton('itemform.apply')" class="button btn">
			<?php echo JText::_('COM_DJCATALOG2_APPLY') ?>
		</button>
		<?php } ?>
		<button type="button" onclick="Joomla.submitbutton('itemform.save')" class="button btn">
			<?php echo JText::_('COM_DJCATALOG2_SAVE_AND_CLOSE') ?>
		</button>
		<button type="button" onclick="Joomla.submitbutton('itemform.cancel')" class="button btn">
			<?php echo JFactory::getApplication()->input->get('id') > 0 ? JText::_('COM_DJCATALOG2_CANCEL') : JText::_('COM_DJCATALOG2_CLOSE'); ?>
		</button>
	</div>

	<form
		action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=itemform&id='.(int) $this->item->id); ?>"
		method="post" name="adminForm" id="item-form" class="form-validate"
		enctype="multipart/form-data">
		<div class="djc_itemform">
			<?php //echo JHtml::_('tabs.start','catalog-sliders', array('useCookie'=>0)); ?>
			<?php echo JHtml::_('bootstrap.startTabSet','catalog-sliders', array('active' => 'product-data')); ?>
			<?php //echo JHtml::_('tabs.panel',JText::_('COM_DJCATALOG2_BASIC_DETAILS'), 'product-data'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'catalog-sliders', 'product-data', JText::_('COM_DJCATALOG2_BASIC_DETAILS')); ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('name'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('name'); ?>
				</div>
			</div>
			
			<?php if ($user->authorise('core.edit.state', 'com_djcatalog2') || ($user->authorise('core.edit.state.own', 'com_djcatalog2') && (empty($this->item->id) || $this->item->created_by === $user->id) )) { ?>                    
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('published'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('published'); ?>
				</div>
			</div>
			
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('access'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('access'); ?>
				</div>
			</div>
			<?php } ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('cat_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('cat_id'); ?>
				</div>
			</div>
			
			<?php if ($this->params->get('fed_multiple_categories', '0') != '0' && (int)$this->params->get('fed_multiple_categories_limit', 3) > 0) { ?>
			<div class="control-group formelm">
				<?php /* ?>
				<div class="control-label">
					<?php echo $this->form->getLabel('categories'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('categories'); ?>
				</div><? */ ?>
				<?php echo $this->form->getInput('categories'); ?>
			</div>
			<?php } ?>
			
			<?php if ((int)$this->params->get('fed_producer', '0') > 0) { ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('producer_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('producer_id'); ?>
				</div>
			</div>
			<?php } ?>
			
			<?php if ((int)$this->params->get('fed_sku', '0') > 0) { ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('sku'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('sku'); ?>
				</div>
			</div>
			<?php } ?>
			
			<?php if ((int)$this->params->get('fed_labels', '1') > 0) { ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('labels'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('labels'); ?>
				</div>
			</div>
			<?php } ?>
			
			<?php if ((int)$this->params->get('fed_price', '0') > 0) { ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('price'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('price'); ?>
				</div>
			</div>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('special_price'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('special_price'); ?>
				</div>
			</div>
			<?php } ?>
			
			<?php if ((int)$this->params->get('fed_featured', '0') > 0) { ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('featured'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('featured'); ?>
				</div>
			</div>
			<?php } ?>
			
			<?php if ((int)$this->params->get('fed_available', '0') > 0) { ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('available'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('available'); ?>
				</div>
			</div>
			<?php } ?>
			
			<?php if ((int)$this->params->get('fed_group', '0') > 0) { ?>
			<?php //echo JHtml::_('tabs.panel',JText::_('COM_DJCATALOG2_FORM_ATTRIBUTES'), 'item-data'); ?>
			
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('group_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('group_id'); ?>
				</div>
			</div>
			<?php } ?>
			
			<div id="itemAttributes"></div>
			
			<?php if ($this->params->get('fed_intro_description', '0') != '0') { ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('intro_desc'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('intro_desc'); ?>
				</div>
			</div>
			<div style="clear:both"></div>
			<?php } ?>
			
			<?php if ($this->params->get('fed_description', '0') != '0') { ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('description'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('description'); ?>
				</div>
			</div>
			<div style="clear:both"></div>
			<?php } ?>
			
			<?php if ($this->params->get('fed_location_details', '1') == '1') {?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php //echo JHtml::_('tabs.panel',JText::_('COM_DJCATALOG2_FORM_LOCATION'), 'product-location'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'catalog-sliders', 'product-location', JText::_('COM_DJCATALOG2_FORM_LOCATION')); ?>
			<?php foreach ($this->form->getGroup('location') as $field) : ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php endforeach; ?>
			<?php } ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			
			<?php if ((int)$this->params->get('fed_max_images', 6) > 0) { ?>
			<?php //echo JHtml::_('tabs.panel',JText::_('COM_DJCATALOG2_FORM_IMAGES'), 'product-images'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'catalog-sliders', 'product-images', JText::_('COM_DJCATALOG2_FORM_IMAGES')); ?>
			<p class="djc_fileupload_tip">
				<?php 
				$img_count_limit = (int)$this->params->get('fed_max_images', 6);
				$img_size_limit = (int)$this->params->get('fed_max_image_size', 2048);
				?>
				<?php 
				echo JText::sprintf('COM_DJCATALOG2_IMAGE_MAX_COUNT', $img_count_limit); 
				if ($img_size_limit > 0) {
					echo ' | '.JText::sprintf('COM_DJCATALOG2_IMAGE_MAX_SIZE', DJCatalog2FileHelper::formatBytes($img_size_limit*1024));
				}
				?>
			</p>
			<?php echo DJCatalog2ImageHelper::renderInput('item', JFactory::getApplication()->input->getInt('id', null), (bool)$this->params->get('fed_multiple_image_upload', true)); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php } ?>
			
			<?php if ((int)$this->params->get('fed_max_files', 6) > 0) { ?>
			<?php //echo JHtml::_('tabs.panel',JText::_('COM_DJCATALOG2_FORM_FILES'), 'product-files'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'catalog-sliders', 'product-files', JText::_('COM_DJCATALOG2_FORM_FILES')); ?>
			<p class="djc_fileupload_tip">
				<?php 
				$file_count_limit = (int)$this->params->get('fed_max_files', 6);
				$file_size_limit = (int)$this->params->get('fed_max_file_size', 2048);
				?>
				<?php 
				echo JText::sprintf('COM_DJCATALOG2_FILE_MAX_COUNT', $file_count_limit); 
				if ($img_size_limit > 0) {
					echo ' | '.JText::sprintf('COM_DJCATALOG2_FILE_MAX_SIZE', DJCatalog2FileHelper::formatBytes($file_size_limit*1024));
				}
				?>
			</p>
			<?php echo DJCatalog2FileHelper::renderInput('item', JFactory::getApplication()->input->getInt('id', null), (bool)$this->params->get('fed_multiple_file_upload', true)); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php } ?>
			
			<?php if ((int)$this->params->get('fed_meta', '0') > 0) { ?>
			<?php //echo JHtml::_('tabs.panel',JText::_('COM_DJCATALOG2_META_DETAILS'), 'product-meta'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'catalog-sliders', 'product-meta', JText::_('COM_DJCATALOG2_META_DETAILS')); ?>
			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('metatitle'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('metatitle'); ?>
				</div>
			</div>

			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('metadesc'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('metadesc'); ?>
				</div>
			</div>

			<div class="control-group formelm">
				<div class="control-label">
					<?php echo $this->form->getLabel('metakey'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('metakey'); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php } ?>
			<?php //echo JHtml::_('tabs.end'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>
		<input id="jform_id" type="hidden" name="id" value="<?php echo JFactory::getApplication()->input->getInt('id', null); ?>" />
		<input type="hidden" name="task" value="" />
		<?php if ((int)$this->params->get('fed_group', '0') == 0) { ?>
			<input type="hidden" id="jform_group_id" name="jform[group_id]" value="0" />
		<?php } ?>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

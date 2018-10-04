<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$params = JComponentHelper::getParams('com_djcatalog2');

$net_prices = (bool)((int)$params->get('price_including_tax', 1) == 0);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'item.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			
			<?php echo $this->form->getField('intro_desc')->save(); ?>

			var textareas = document.getElementById('itemAttributes').getElements('textarea.nicEdit');
			if (textareas) {
				textareas.each(function(textarea){
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
			
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=item&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></legend>
			<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('name'); ?>
			<?php echo $this->form->getInput('name'); ?></li>

			<li><?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?></li>
			
			<li><?php echo $this->form->getLabel('sku'); ?>
			<?php echo $this->form->getInput('sku'); ?></li>
			
			<li><?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?></li>
			
			<li><?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?></li>
			
			<li><?php echo $this->form->getLabel('featured'); ?>
			<?php echo $this->form->getInput('featured'); ?></li>
			
			<li><?php echo $this->form->getLabel('parent_id'); ?>
			<?php echo $this->form->getInput('parent_id'); ?>
			</li>
			
			<li><?php echo $this->form->getLabel('group_id'); ?>
			<?php echo $this->form->getInput('group_id'); ?>
			</li>
			
			<li><?php echo $this->form->getLabel('cat_id'); ?>
			<?php echo $this->form->getInput('cat_id'); ?></li>
			
			<li><?php echo $this->form->getLabel('categories'); ?>
			<?php echo $this->form->getInput('categories'); ?></li>
			
			<li><?php echo $this->form->getLabel('producer_id'); ?>
			<?php echo $this->form->getInput('producer_id'); ?></li>

			<li><?php echo $this->form->getLabel('available'); ?>
			<?php echo $this->form->getInput('available'); ?></li>
			
			<li><?php echo $this->form->getLabel('tangible'); ?>
			<?php echo $this->form->getInput('tangible'); ?></li>
			
			<li><?php echo $this->form->getLabel('onstock'); ?>
			<?php echo $this->form->getInput('onstock'); ?></li>
			
			<li><?php echo $this->form->getLabel('stock'); ?>
			<?php echo $this->form->getInput('stock'); ?></li>
			
			<li>
			<?php echo $this->form->getLabel('price'); ?>
			<?php echo $this->form->getInput('price'); ?>
			
			<label>&nbsp;&nbsp;&raquo;&nbsp;<?php echo JText::_($net_prices ? 'COM_DJCATALOG2_PRICE_INCL_TAX' : 'COM_DJCATALOG2_PRICE_EXCL_TAX')?></label>
			<input type="text" class="djc_price_tax readonly inputbox input input-mini" readonly="readonly" id="jform_price_tax" data-type="<?php echo $net_prices ? 'net' : 'gross'?>" value="" />
			</li>
			
			<li>
			<?php echo $this->form->getLabel('special_price'); ?>
			<?php echo $this->form->getInput('special_price'); ?>
			
			<label>&nbsp;&nbsp;&raquo;&nbsp;<?php echo JText::_($net_prices ? 'COM_DJCATALOG2_PRICE_INCL_TAX' : 'COM_DJCATALOG2_PRICE_EXCL_TAX')?></label>
			<input type="text" class="djc_price_tax readonly inputbox input input-mini" readonly="readonly" id="jform_special_price_tax" data-type="<?php echo $net_prices ? 'net' : 'gross'?>" value="" />
			</li>
			
			<li><?php echo $this->form->getLabel('tax_rate_id'); ?>
			<?php echo $this->form->getInput('tax_rate_id'); ?></li>
			
			<li>
			<label><?php echo JText::_('COM_DJCATALOG2_RELATED_ITEMS'); ?></label>
				<div class="fltlft">
				<?php if (empty($this->item->id) || ($this->item->id == 0)) { ?>
					<div class="button2-left">
						<div class="blank">
							<a href="#" onclick="javascript:Joomla.submitbutton('item.apply')">
								<?php echo JText::_('COM_DJCATALOG2_RELATED_ITEMS_SAVE_TO_ASSIGN'); ?>
							</a>
						</div>
					</div>
				<?php } else { ?>
					<div class="button2-left">
						<div class="blank">
							<a class="modal" rel="{handler: 'iframe', size: {x: 875, y: 550}, onClose: function() {}}" href="index.php?option=com_djcatalog2&amp;view=relateditems&amp;item_id=<?php echo $this->item->id; ?>&amp;tmpl=component">
								<?php echo JText::_('COM_DJCATALOG2_RELATED_ITEMS_ASSIGN'); ?>
							</a>
						</div>
					</div>
				<?php }?>
				</div>
			</li>
			
			</ul>
			
			<?php echo $this->form->getLabel('intro_desc'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('intro_desc'); ?>
			<div class="clr"></div>

			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
			
		</fieldset>
		
	</div>
	<div class="width-40 fltrt">
	<?php echo JHtml::_('sliders.start','catalog-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_DJCATALOG2_ATTRIBUTES'), 'item-attributes'); ?>
		<fieldset class="adminform" id="itemAttributes">
			
		</fieldset>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_DJCATALOG2_IMAGES'), 'item-images'); ?>
		<fieldset class="adminform">
			<?php echo DJCatalog2ImageHelper::renderInput('item', JFactory::getApplication()->input->getInt('id', null), (bool)JComponentHelper::getParams('com_djcatalog2')->get('multiple_image_upload', true)); ?>
		</fieldset>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_DJCATALOG2_FILES'), 'item-attachments'); ?>
		<fieldset class="adminform">
			<?php echo DJCatalog2FileHelper::renderInput('item', JFactory::getApplication()->input->getInt('id', null), (bool)JComponentHelper::getParams('com_djcatalog2')->get('multiple_file_upload', true)); ?>
		</fieldset>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_DJCATALOG2_FIELDSET_LOCATION'), 'item-location'); ?>
		<fieldset class="adminform">
			<ul class="adminformlist">
			<?php foreach ($this->form->getGroup('location') as $field) : ?>
				<li class="control-group">
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
		<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_PUBLISHING'), 'item-publishing'); ?>
		<fieldset class="adminform">
			<ul class="adminformlist">
			
				<li>
				<?php echo $this->form->getLabel('heading'); ?>
				<?php echo $this->form->getInput('heading'); ?>
				</li>
			
				<li>
				<?php echo $this->form->getLabel('metatitle'); ?>
				<?php echo $this->form->getInput('metatitle'); ?>
				</li>
				<li>
				<?php echo $this->form->getLabel('metadesc'); ?>
				<?php echo $this->form->getInput('metadesc'); ?>
				</li>
				<li>
				<?php echo $this->form->getLabel('metakey'); ?>
				<?php echo $this->form->getInput('metakey'); ?>
				</li>
				
				<li>
				<?php echo $this->form->getLabel('created'); ?>
				<?php echo $this->form->getInput('created'); ?>
				</li>
				
				<li>
				<?php echo $this->form->getLabel('created_by'); ?>
				<?php echo $this->form->getInput('created_by'); ?>
				</li>

				<li>
				<?php echo $this->form->getLabel('publish_up'); ?>
				<?php echo $this->form->getInput('publish_up'); ?>
				</li>

				<li>
				<?php echo $this->form->getLabel('publish_down'); ?>
				<?php echo $this->form->getInput('publish_down'); ?>
				</li>

				<li>
				<?php echo $this->form->getLabel('hits'); ?>
				<?php echo $this->form->getInput('hits'); ?>
				</li>

				<li>
				<?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?>
				</li>
			</ul>
		</fieldset>
		<fieldset class="adminform">
			<?php echo $this->loadTemplate('params_legacy'); ?>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
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
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'producer.cancel' || document.formvalidator.isValid(document.getElementById('producer-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('producer-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=producer&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="producer-form" class="form-validate" enctype="multipart/form-data">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></legend>
			<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('name'); ?>
			<?php echo $this->form->getInput('name'); ?></li>

			<li><?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?></li>

			<li><?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?></li>

			<li><?php echo $this->form->getLabel('id'); ?>
			<?php echo $this->form->getInput('id'); ?></li>
			
			<li><?php echo $this->form->getLabel('created'); ?>
			<?php echo $this->form->getInput('created'); ?></li>
			
			<li><?php echo $this->form->getLabel('created_by'); ?>
			<?php echo $this->form->getInput('created_by'); ?></li>
			
			</ul>

			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
			
		</fieldset>
		
	</div>
	<div class="width-40 fltrt">
	<?php echo JHtml::_('sliders.start','catalog-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_DJCATALOG2_IMAGES'), 'producer-images'); ?>
		<fieldset class="adminform">
			<?php echo DJCatalog2ImageHelper::renderInput('producer',JFactory::getApplication()->input->getInt('id', null), (bool)JComponentHelper::getParams('com_djcatalog2')->get('multiple_image_upload', true)); ?>
		</fieldset>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_DJCATALOG2_META_DATA'), 'producer-metadata'); ?>
		<fieldset class="adminform">
			<?php echo $this->form->getLabel('heading'); ?>
			<?php echo $this->form->getInput('heading'); ?>
		
			<?php echo $this->form->getLabel('metatitle'); ?>
			<?php echo $this->form->getInput('metatitle'); ?>

			<?php echo $this->form->getLabel('metadesc'); ?>
			<?php echo $this->form->getInput('metadesc'); ?>
			
			<?php echo $this->form->getLabel('metakey'); ?>
			<?php echo $this->form->getInput('metakey'); ?>
		</fieldset>
	<?php echo JHtml::_('sliders.end'); ?>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
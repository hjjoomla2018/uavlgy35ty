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
		if (task == 'field.cancel' || document.formvalidator.isValid(document.getElementById('field-form'))) {
			Joomla.submitform(task, document.getElementById('field-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=field&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="field-form" class="form-validate" enctype="multipart/form-data">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></legend>
			<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('name'); ?>
			<?php echo $this->form->getInput('name'); ?></li>
			
			<li><?php echo $this->form->getLabel('imagelabel'); ?>
			<?php echo $this->form->getInput('imagelabel'); ?></li>

			<li><?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?></li>
			
			<li><?php echo $this->form->getLabel('id'); ?>
			<?php echo $this->form->getInput('id'); ?></li>
			
			<li><?php echo $this->form->getLabel('group_id'); ?>
			<?php echo $this->form->getInput('group_id'); ?>
			</li>
			
			<li>
				<?php echo $this->form->getLabel('type'); ?>
				<?php echo $this->form->getInput('type'); ?>
			</li>
			
			<li>
				<?php echo $this->form->getLabel('required'); ?>
				<?php echo $this->form->getInput('required'); ?>
			</li>
			
			<li>
				<?php echo $this->form->getLabel('visibility'); ?>
				<?php echo $this->form->getInput('visibility'); ?>
			</li>
			
			<li>
				<?php echo $this->form->getLabel('separate_column'); ?>
				<?php echo $this->form->getInput('separate_column'); ?>
			</li>
			
			<li>
				<?php echo $this->form->getLabel('filterable'); ?>
				<?php echo $this->form->getInput('filterable'); ?>
			</li>
			
			<li>
				<?php echo $this->form->getLabel('filter_type'); ?>
				<?php echo $this->form->getInput('filter_type'); ?>
			</li>
			
			<li>
				<?php echo $this->form->getLabel('searchable'); ?>
				<?php echo $this->form->getInput('searchable'); ?>
			</li>
			
			<li>
				<?php echo $this->form->getLabel('sortable'); ?>
				<?php echo $this->form->getInput('sortable'); ?>
			</li>

			<li><?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?></li>

			</ul>
			<div class="clr"></div>
			<div id="fieldtypeSettings">
			
			</div>
		</fieldset>
		
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
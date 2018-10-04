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

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'importconfig.cancel' || document.formvalidator.isValid(document.getElementById('importconfig-form'))) {
			Joomla.submitform(task, document.getElementById('importconfig-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=importconfig&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="importconfig-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="span12 form-horizontal">
			<fieldset>
			<legend><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></legend>
			
			<?php echo $this->form->renderField('name'); ?>
			<?php echo $this->form->renderField('published'); ?>
			<?php echo $this->form->renderField('csv_name'); ?>
			<?php echo $this->form->renderField('target_name'); ?>
			<?php echo $this->form->renderField('is_db'); ?>
			<?php echo $this->form->renderField('db_name'); ?>
			<?php echo $this->form->renderField('db_lookup_column'); ?>
			<?php echo $this->form->renderField('db_value_column'); ?>
			<?php echo $this->form->renderField('db_operator'); ?>
			<?php echo $this->form->renderField('db_where_clause'); ?>
			<?php echo $this->form->renderField('merging'); ?>
			<?php echo $this->form->renderField('html_wrapper'); ?>
			<?php echo $this->form->renderField('id'); ?>
			
			
		</fieldset>
		
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
	</div>
</form>
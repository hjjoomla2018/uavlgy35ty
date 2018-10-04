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
		if (task == 'unit.cancel' || document.formvalidator.isValid(document.getElementById('unit-form'))) {
			Joomla.submitform(task, document.getElementById('unit-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=unit&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="unit-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="span12 form-horizontal">
			<fieldset>
			<legend><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></legend>
			
			<?php echo $this->form->getControlGroup('name'); ?>
			<?php echo $this->form->getControlGroup('unit'); ?>
			<?php echo $this->form->getControlGroup('is_default'); ?>
			<?php echo $this->form->getControlGroup('show_box'); ?>
			<?php echo $this->form->getControlGroup('show_unit'); ?>
			<?php echo $this->form->getControlGroup('show_buttons'); ?>
			<?php echo $this->form->getControlGroup('is_int'); ?>
			<?php echo $this->form->getControlGroup('min_quantity'); ?>
			<?php echo $this->form->getControlGroup('max_quantity'); ?>
			<?php echo $this->form->getControlGroup('step'); ?>
			<?php echo $this->form->getControlGroup('id'); ?>
			
		</fieldset>
		
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
	</div>
</form>
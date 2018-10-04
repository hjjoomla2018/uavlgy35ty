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
		if (task == 'label.cancel' || document.formvalidator.isValid(document.getElementById('label-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('label-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=label&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="label-form" class="form-validate" enctype="multipart/form-data">
	<div class="form-horizontal">
			<fieldset>
			<legend><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></legend>
			
			<div class="row-fluid">
				<div class="span9">
					<?php echo $this->form->getControlGroup('name'); ?>
					<?php echo $this->form->getControlGroup('label'); ?>
					<?php echo $this->form->getControlGroup('image'); ?>
					<?php echo $this->form->getControlGroup('id'); ?>
					<?php echo $this->form->getInput('type'); ?>
				</div>
				<div class="span3">
					<div class="form-vertical">
						<?php foreach ($this->form->getGroup('params') as $field) { ?>
							<?php echo $field->renderField(); ?>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<?php echo $this->form->getControlGroup('description'); ?>
				</div>
			</div>
		</fieldset>
		
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
	</div>
</form>
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
		if (task == 'customergroup.cancel' || document.formvalidator.isValid(document.getElementById('customergroup-form'))) {
			Joomla.submitform(task, document.getElementById('customergroup-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=customergroup&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="customergroup-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="span12 form-horizontal">
			<fieldset>
			<legend><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></legend>
			
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('is_default'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('is_default'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
			</div>
		</fieldset>
		
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
	</div>
</form>
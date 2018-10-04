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
		if (task == 'cartfield.cancel' || document.formvalidator.isValid(document.getElementById('cartfield-form'))) {
			Joomla.submitform(task, document.getElementById('cartfield-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=cartfield&layout=edit&id='.(int) $this->item->id); ?>"
	method="post" name="adminForm" id="cartfield-form" class="form-validate"
	enctype="multipart/form-data">
	<fieldset>
		<legend>
		<?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?>
		</legend>
		<div class="row-fluid">
			<div class="span6 form-horizontal">
				<?php echo $this->form->getControlGroup('name'); ?>
				<?php echo $this->form->getControlGroup('alias'); ?>
				<?php echo $this->form->getControlGroup('id'); ?>
				<?php echo $this->form->getControlGroup('type'); ?>
				<?php echo $this->form->getControlGroup('visibility'); ?>
				<?php //echo $this->form->getControlGroup('required'); ?>
				<?php echo $this->form->getControlGroup('published'); ?>
			</div>
			<div class="span6 form-horizontal">
				<div id="fieldtypeSettings"></div>
			</div>
		</div>
	</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

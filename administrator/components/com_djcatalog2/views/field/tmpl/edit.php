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

JHtml::_('jquery.framework');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

//JHtml::_('script', 'jui/jquery.minicolors.min.js', array('version' => 'auto', 'relative' => true));
//JHtml::_('stylesheet', 'jui/jquery.minicolors.css', array('version' => 'auto', 'relative' => true));

JHtml::_('script', 'jui/jquery.minicolors.min.js', false, true);
JHtml::_('stylesheet', 'jui/jquery.minicolors.css', false, true);

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

<form
	action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=field&layout=edit&id='.(int) $this->item->id); ?>"
	method="post" name="adminForm" id="field-form" class="form-validate"
	enctype="multipart/form-data">
	<fieldset>
		<legend>
		<?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?>
		</legend>
		<div class="row-fluid">
			<div class="span4 form-horizontal">
				<?php echo $this->form->getControlGroup('name'); ?>
				<?php echo $this->form->getControlGroup('imagelabel'); ?>
				<?php echo $this->form->getControlGroup('alias'); ?>
				<?php echo $this->form->getControlGroup('id'); ?>
				<?php echo $this->form->getControlGroup('group_id'); ?>
				<?php echo $this->form->getControlGroup('type'); ?>
				<?php echo $this->form->getControlGroup('required'); ?>

				<?php echo $this->form->getControlGroup('visibility'); ?>
				<?php echo $this->form->getControlGroup('cart_variant'); ?>
				<?php echo $this->form->getControlGroup('separate_column'); ?>
				<?php echo $this->form->getControlGroup('filterable'); ?>
				<?php echo $this->form->getControlGroup('filter_type'); ?>
				<?php echo $this->form->getControlGroup('searchable'); ?>
				<?php echo $this->form->getControlGroup('sortable'); ?>
				<?php echo $this->form->getControlGroup('comparable'); ?>
				<?php echo $this->form->getControlGroup('published'); ?>
			</div>
			<div class="span8 form-horizontal">
				<div id="fieldtypeSettings"></div>
			</div>
		</div>
	</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

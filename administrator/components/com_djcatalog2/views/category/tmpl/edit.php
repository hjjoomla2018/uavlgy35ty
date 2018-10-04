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

try {
	JHtml::_('bootstrap.tooltip');
} catch(Exception $e) {
	JHtml::_('bootstrap.tooltip');
}

try {
	JHtml::_('behavior.formvalidator');
} catch(Exception $e) {
	JHtml::_('behavior.formvalidator');
}

JHtml::_('formbehavior.chosen', 'select');
$app = JFactory::getApplication();

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'category.cancel' || document.formvalidator.isValid(document.getElementById('category-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('category-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=category&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="category-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="span12 form-horizontal">
			<fieldset>
				<ul class="nav nav-tabs">
					<li class="active"><a href="#details" data-toggle="tab"><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></a></li>
					<li>
						<a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING');?></a>
					</li>
					<li>
						<a href="#images" data-toggle="tab"><?php echo JText::_('COM_DJCATALOG2_IMAGES'); ?></a>
					</li>
					<?php $fieldSets = $this->form->getFieldsets('params'); ?>
					<?php foreach ($fieldSets as $name => $fieldSet) { ?>
						<li>
							<a href="#params-<?php echo $name; ?>" data-toggle="tab"><?php echo JText::_($fieldSet->label); ?></a>
						</li>
					<?php } ?>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="details">
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
						</div>
						
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
						</div>
						
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('parent_id'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('parent_id'); ?></div>
						</div>
						
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
						</div>
						
						<div class="control-group">
							<div class="control-label">
							<?php echo $this->form->getLabel('access'); ?>
							</div>
							<div class="controls">
							<?php echo $this->form->getInput('access'); ?>
							</div>
						</div>
						
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
						</div>
						
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('created'); ?></div>
						</div>
						
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
						</div>
						
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
						</div>
						
					</div>
					
					<div class="tab-pane" id="publishing">
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('heading'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('heading'); ?></div>
						</div>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('metatitle'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('metatitle'); ?></div>
						</div>
						
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('metadesc'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('metadesc'); ?></div>
						</div>
						
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('metakey'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('metakey'); ?></div>
						</div>
					</div>
					
					<div class="tab-pane" id="images">
						<?php echo DJCatalog2ImageHelper::renderInput('category',$app->input->get('id', null, 'int'), (bool)JComponentHelper::getParams('com_djcatalog2')->get('multiple_image_upload', true)); ?>
					</div>
					<?php echo $this->loadTemplate('params'); ?>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
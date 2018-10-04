<?php
/**
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'review.cancel' || document.formvalidator.isValid(document.id('edit-form'))) {
			Joomla.submitform(task, document.getElementById('edit-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djreviews&view=review&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="edit-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="span6 form-horizontal">
			<fieldset>
			<legend><?php echo JText::_('COM_DJREVIEWS_REVIEW'); ?></legend>
			
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('message'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('message'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('user_name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('user_name'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('user_login'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('user_login'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('email'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('email'); ?></div>
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
				<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
			</div>
		</fieldset>
		</div>
		<div class="span6 form-horizontal">
			<fieldset>
				<legend><?php echo JText::_('COM_DJREVIEWS_RATING'); ?></legend>
				
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('rating_group_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('rating_group_id'); ?></div>
				</div>
				
				<?php if (count($this->item->rating_fields)) { ?>
				<table class="table table-condensed">
					<thead>
						<tr>
							<th>
								<?php echo JText::_('COM_DJREVIEWS_RATING_FIELD'); ?>
							</th>
							<th>
								<?php echo JText::_('COM_DJREVIEWS_RATING'); ?>
							</th>
							<th>
								<?php echo JText::_('COM_DJREVIEWS_WEIGHT'); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
					</tfoot>
					<tbody>
					<?php foreach($this->item->rating_fields as $field_id => $field) {?>
						<tr>
							<td>
								<?php echo $field->name; ?>
							</td>
							<td>
							<?php if ($field->type == 'rating') {?>
							<input type="text" class="inputbox input input-mini" name="jform[rating][<?php echo $field_id; ?>]" value="<?php echo $field->rating; ?>"/>
							<?php } else if ($field->type == 'list') {?>
								<?php 
									$options = array();
									$options_set = explode(PHP_EOL, $field->list_options); 
									foreach($options_set as $option_element) {
										$pair = explode('=', $option_element, 2);
										if (count($pair) == 2) {
											$opt = new stdClass();
											$opt->value = htmlspecialchars(trim($pair[0]));
											$opt->text = $pair[1];
											$options[] = $opt;
										}
									}
									?>
									<select class="input input-medium <?php echo $field->required ? 'required' : ''; ?>" name="jform[rating][<?php echo $field_id; ?>]">
										<?php foreach ($options as $option) {?>
											<?php 
												$selected = $option->value == $field->rating ? 'selected="selected"' : '';
											?>
											<option value="<?php echo $option->value; ?>" <?php echo $selected; ?>><?php echo $option->text; ?></option>
										<?php } ?>
									</select>
							<?php } else {?>
								<textarea name="jform[rating][<?php echo $field_id; ?>]"><?php echo htmlspecialchars($field->rating); ?></textarea>
							<?php } ?>
							</td>
							<td><?php echo $field->type == 'rating' ? $field->weight : '-'; ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('avg_rate'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('avg_rate'); ?></div>
				</div>
				
				<?php } ?>
			</fieldset>
		</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
	</div>
</form>
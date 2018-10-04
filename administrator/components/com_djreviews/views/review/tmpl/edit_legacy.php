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

<form action="<?php echo JRoute::_('index.php?option=com_djreviews&view=group&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="edit-form" class="form-validate" enctype="multipart/form-data">
	<div class="width-50 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_DJREVIEWS_REVIEW'); ?></legend>
			<ul class="adminformlist">
			
				<li>
					<?php echo $this->form->getLabel('title'); ?>
					<?php echo $this->form->getInput('title'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('message'); ?>
					<?php echo $this->form->getInput('message'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('published'); ?>
					<?php echo $this->form->getInput('published'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('user_name'); ?>
					<?php echo $this->form->getInput('user_name'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('user_login'); ?>
					<?php echo $this->form->getInput('user_login'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('email'); ?>
					<?php echo $this->form->getInput('email'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('created'); ?>
					<?php echo $this->form->getInput('created'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('created_by'); ?>
					<?php echo $this->form->getInput('created_by'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('id'); ?>
					<?php echo $this->form->getInput('id'); ?>
				</li>
			
			</ul>

		</fieldset>
	</div>
	
	<div class="width-50 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_DJREVIEWS_RATING'); ?></legend>
			<ul class="adminformlist">
			
				<li>
					<?php echo $this->form->getLabel('rating_group_id'); ?>
					<?php echo $this->form->getInput('rating_group_id'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('avg_rate'); ?>
					<?php echo $this->form->getInput('avg_rate'); ?>
				</li>
			</ul>
			
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
							<td><input type="text" class="inputbox input input-mini" name="jform[rating][<?php echo $field_id; ?>]" value="<?php echo $field->rating; ?>"/></td>
							<td><?php echo $field->weight; ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				<?php } ?>
		</fieldset>
	</div>
	
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
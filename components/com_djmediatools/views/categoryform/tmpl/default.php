<?php
/**
 * @version $Id: edit.php 46 2014-12-11 15:14:48Z szymon $
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$user = JFactory::getUser();
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'categoryform.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('COM_DJMEDIATOOLS_VALIDATION_FORM_FAILED'));?>');
		}
	}
	
</script>

<form action="<?php echo JURI::base().'index.php'; //?option=com_djmediatools&view=categoryform&id='.(int) $this->item->id; ?>" method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
	<div class="row-fluid">
		
		<div class="width-80 fltlft span10">
		<fieldset class="adminform">
		<?php if(isset($this->button)) echo $this->button; ?>
		
		<div class="well">
			<div class="adminformlist tab-pane active" id="details">
				
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('parent_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('parent_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('image'); ?></div>
				</div>
				
				<div id="plgParams_component" class="plgParams">
					
					<?php echo $this->form->getInput('manage_info'); ?>
					
					<div id="albumItemsWrap">
					
					<!-- hidden item template for JS -->
					<div class="albumItem hide">
						<img />
						<span class="video-icon"></span>
						<input type="hidden" name="item_id[]" value="0">
						<input type="hidden" name="item_image[]" value="">
						<input type="text" class="editTitle" name="item_title[]" placeholder="<?php echo JText::_('COM_DJMEDIATOOLS_SLIDE_TITLE_HINT') ?>" value="">
						<textarea class="editDesc" name="item_desc[]" placeholder="<?php echo JText::_('COM_DJMEDIATOOLS_SLIDE_DESCRIPTION_HINT') ?>"></textarea>
						<a href="#" class="delBtn"></a>
					</div>
					
					<div id="albumItems">
						<?php if(isset($this->items)) foreach($this->items as $item) { ?>
							<div class="albumItem">
								<img src="<?php echo $item->thumb; ?>" alt="<?php echo $this->escape($item->title); ?>" />
								<?php if($item->video) { ?><span class="video-icon"></span><?php } ?>
								<input type="hidden" name="item_id[]" value="<?php echo $this->escape($item->id); ?>">
								<input type="hidden" name="item_image[]" value="<?php echo $this->escape($item->image); ?>">
								<input type="text" class="editTitle" name="item_title[]" placeholder="<?php echo JText::_('COM_DJMEDIATOOLS_SLIDE_TITLE_HINT') ?>" value="<?php echo $this->escape($item->title); ?>">
								<textarea class="editDesc" name="item_desc[]" placeholder="<?php echo JText::_('COM_DJMEDIATOOLS_SLIDE_DESCRIPTION_HINT') ?>"><?php echo $this->escape($item->description); ?></textarea>
								<a href="#" class="delBtn"></a>
							</div>
						<?php } ?>
					</div>
					
					<div class="clearfix"></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('video'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('video'); ?></div>
					</div>
					<?php echo $this->uploader ?>
				</div>
				
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
				</div>
			</div>
			
			<?php //echo $this->loadTemplate('params'); ?>
			
		</div>
		
		<div class="btn-toolbar djev_form_toolbar">
		<?php if(isset($this->button)) { ?>
			<?php echo $this->button; ?>
		<?php } else { ?>
			
				<button type="button" onclick="Joomla.submitbutton('categoryform.save')" class="button btn btn-success">
					<?php echo JText::_('COM_DJMEDIATOOLS_SAVE') ?>
				</button>
				<?php if ($user->authorise('categoryform.edit', 'com_djmediatools') || $user->authorise('categoryform.edit.own', 'com_djmediatools')) { ?>
				<button type="button" onclick="Joomla.submitbutton('categoryform.apply')" class="button btn">
					<?php echo JText::_('COM_DJMEDIATOOLS_APPLY') ?>
				</button>
				<?php } ?>
				<button type="button" onclick="Joomla.submitbutton('categoryform.cancel')" class="button btn">
					<?php echo JFactory::getApplication()->input->get('id') > 0 ? JText::_('COM_DJMEDIATOOLS_CANCEL') : JText::_('COM_DJMEDIATOOLS_CLOSE'); ?>
				</button>
			
		<?php } ?>
		</div>
		
		</fieldset>
		</div>
		
		<input type="hidden" name="option" value="com_djmediatools" />
		<input type="hidden" name="view" value="categoryform" />
		<input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div class="clearfix"></div>

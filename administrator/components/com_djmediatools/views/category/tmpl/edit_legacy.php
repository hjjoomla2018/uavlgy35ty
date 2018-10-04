<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
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

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'category.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('COM_DJMEDIATOOLS_VALIDATION_FORM_FAILED'));?>');
		}
	}
	
	function showPlgParams(source) {
		
		if(typeOf(source)=='element') source = source.value;
		$$('.plgParams').fade('hide');
		$$('.plgParams').setStyle('display','none');
		var plgParams = document.id('plgParams_' + source);
		if(plgParams) {
			plgParams.setStyle('display','block');
			plgParams.fade('in');
		}
		
	}
	
	window.addEvent('domready', function(){
		document.id('more-options').set('slide',{duration: 'short'});
		document.id('more-options').slide('hide');
		document.id('toggle-more-options').addEvent('click', function(e){
			e.preventDefault();
			document.id('more-options').slide('toggle');
		});
		
		var params = document.id('album-params');
		var lForm = document.id('leftForm');
		params.set('slide',{duration: 'short'});
		params.slide('hide');
		document.id('toggle-album-params').addEvent('click', function(e){
			e.preventDefault();
			lForm.toggleClass('width-100');
			lForm.toggleClass('width-60');
			params.slide('toggle');
		});
		
		showPlgParams('<?php echo $this->form->getValue('source') ?>');

		//document.id('jform_title').fireEvent('focus');
	});
		
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djmediatools&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div id="leftForm" class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_DJMEDIATOOLS_NEW') : JText::sprintf('COM_DJMEDIATOOLS_EDIT', $this->item->id); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>
				
				<li><?php echo $this->form->getLabel('parent_id'); ?>
				<?php echo $this->form->getInput('parent_id'); ?></li>
				
				<li><label> </label>
					<button id="toggle-more-options"><?php echo JText::_('COM_DJMEDIATOOLS_SHOW_ALL_OPTIONS'); ?></button>
					<button id="toggle-album-params"><?php echo JText::_('COM_DJMEDIATOOLS_OVERRIDE_SETTINGS'); ?></button>
				</li>
			</ul>
			<div class="clr"></div>
			<div id="more-options">
			<ul class="adminformlist">
				
				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>
				
				<li><?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?></li>

				<li><?php echo $this->form->getLabel('visible'); ?>
				<?php echo $this->form->getInput('visible'); ?></li>
				
				<li><?php echo $this->form->getLabel('image'); ?>
				<?php echo $this->form->getInput('image'); ?></li>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
			</ul>
			
			</div>
			<p> </p>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('source'); ?>
				<?php echo $this->form->getInput('source'); ?></li>
			</ul>
			
			<div class="clr"></div>
			
			<div id="plgParams_component" class="plgParams">
				<ul class="adminformlist">
					<li><?php echo $this->form->getLabel('manage_info'); ?>
					<?php echo $this->form->getInput('manage_info'); ?></li>
				</ul>
				<div id="albumItemsWrap">
				<div id="albumItems">
					<?php if(isset($this->items)) foreach($this->items as $item) { ?>
						<div class="albumItem">
							<img src="<?php echo $item->thumb; ?>" alt="<?php echo $this->escape($item->title); ?>" />
							<?php if($item->video) { ?><span class="video-icon"></span><?php } ?>
							<div class="itemMask">
								<input type="hidden" name="item_id[]" value="<?php echo $this->escape($item->id); ?>">
								<input type="hidden" name="item_image[]" value="<?php echo $this->escape($item->image); ?>">
								<input type="text" class="itemInput editTitle" name="item_title[]" value="<?php echo $this->escape($item->title); ?>">
								
								<span class="delBtn"></span>
							</div>
						</div>
					<?php } ?>
				</div>
				<div class="clr"></div>
				</div>
				<ul class="adminformlist">
					<li><?php echo $this->form->getLabel('video'); ?>
					<?php echo $this->form->getInput('video'); ?></li>
					<li><?php echo $this->form->getLabel('folder'); ?>
					<?php echo $this->form->getInput('folder'); ?></li>
				</ul>
				<div class="clr"></div>
				<?php echo $this->uploader ?>
			</div>
				
			<?php echo $this->loadTemplate('legacy_plgparams'); ?>
			
			<div class="clr"></div>
			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
			<div class="clr"></div>
			
			<?php if(isset($this->button)) echo $this->button; ?>
		</fieldset>
	</div>
	
	<div class="width-40 fltrt">
		
		<div id="album-params">
			<?php echo  JHtml::_('sliders.start', 'item-slider'); ?>
				<?php echo $this->loadTemplate('legacy_params'); ?>
			<?php echo JHtml::_('sliders.end'); ?>
		</div>
		
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div class="clr"></div>

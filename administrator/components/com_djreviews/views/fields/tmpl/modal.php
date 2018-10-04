<?php
/**
 * @version $Id: default.php 8 2014-10-14 10:13:30Z michal $
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

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder == 'a.ordering';
?>
<div class="container-popup">
	<form action="<?php echo JRoute::_('index.php?option=com_djreviews&view=fields&tmpl=component&layout=modal&group_id='.$this->state->get('filter.assign_group'));?>" method="post" name="adminForm" id="adminForm">
		<div id="j-main-container2" class="span12">
			
			<div class="btn-toolbar">
				<button class="btn button" onclick="javascript:Joomla.submitbutton('fields.assign')"><?php echo JText::_('JAPPLY'); ?></button>
				<button class="btn button" onclick="javascript:Joomla.submitbutton('fields.assignclose')"><?php echo JText::_('JSAVE'); ?></button>
				<button class="btn button" onclick="window.parent.jQuery('#fields_modal').modal('hide');return false;"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></button>
			</div>
			
			
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<label class="element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
					<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
				</div>
				<div class="btn-group pull-left">
					<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
					<button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			</div>
			<div class="clearfix"> </div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th>
							<?php echo JHtml::_('grid.sort', 'COM_DJREVIEWS_RATING_FIELD', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JText::_('JGRID_HEADING_ORDERING'); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="3">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$item->max_ordering = 0; //??
					$canCheckin	= $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out==0;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php // echo JHtml::_('grid.id', $i, $item->id); ?>
							<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $item->id; ?>" onclick="Joomla.isChecked(this.checked);" title="<?php echo JText::sprintf('JGRID_CHECKBOX_ROW_N', ($i + 1));?>" <?php if ($item->field_assigned) echo 'checked="yes"'?> />
					    	<?php echo '<input type="hidden" name="listed_cid[]" value="'.$item->id.'" />'; ?>
						</td>
						<td>
							<?php echo $this->escape($item->name); ?>
						</td>
						<td>
							<?php 
							$active = $item->field_assigned;
							$disabled = $active ? '' : 'disabled="disabled"';
							$value = $active ? $item->field_order : 0;
							?>
							<input id="group_ord-<?php echo $item->id; ?>" type="text" name="ordering[]" value="<?php echo $value; ?>" <?php echo $disabled; ?>/>
						</td>
						<td class="center">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		
			<div>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="group_id" value="<?php echo $this->state->get('filter.assign_group'); ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>

<script>
 jQuery(document).ready(function(){
	var cbboxes = jQuery('#adminForm').find('input[type="checkbox"]');
	cbboxes.click(function(){
		var value = jQuery(this).val();
		var ordering = jQuery('#group_ord-' + value);
		if (ordering.length > 0) {
			if (jQuery(this).is(':checked')) {
				ordering.removeAttr('disabled', '');
				ordering.focus();
			} else {
				ordering.attr('disabled', 'disabled');
			}
		}
	});
 });
</script>
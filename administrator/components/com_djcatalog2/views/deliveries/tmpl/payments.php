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

JHtml::_('behavior.tooltip');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= false;
$parent_id = JFactory::getApplication()->input->getInt('item_id',null);
?>
<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=deliveries&layout=payments&tmpl=component');?>" method="post" name="adminForm" id="adminForm">
	<div class="btn-toolbar">
		<button class="btn button" onclick="javascript:Joomla.submitbutton('payments.assign')"><?php echo JText::_('JAPPLY'); ?></button>
		<button class="btn button" onclick="javascript:Joomla.submitbutton('payments.assignclose')"><?php echo JText::_('JSAVE'); ?></button>
		<button class="btn button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></button>
	</div>
	<div class="clearfix"> </div>
	
	<div class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label class="element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"  />
		</div>
		<div class="btn-group pull-left">
			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" class="btn" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
	</div>
	
	<div class="clearfix"> </div>
	
	<div class="btn-toolbar">
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', array(JHtml::_('select.option', '1', 'JPUBLISHED'),JHtml::_('select.option', '0', 'JUNPUBLISHED')), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</div>
	
	<div class="clearfix"> </div>
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_NAME', 'a.name', $listDirn, $listOrder); ?>
				</th>
				<th width="15%"  class="title">
					<?php echo JHTML::_('grid.sort',  'COM_DJCATALOG2_PLUGIN', 'a.plugin', $listDirn, $listOrder ); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="4">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $item->id?>" onclick="isChecked(this.checked);" title="<?php echo JText::sprintf('JGRID_CHECKBOX_ROW_N', ($i + 1));?>" <?php if ($item->related_count > 0) echo 'checked="yes"'?> />
				    <?php echo '<input type="hidden" name="listed_cid[]" value="'.$item->id.'" />'; ?>
				</td>
				<td>
					<?php echo $this->escape($item->name); ?>
				</td>
				<td>
					<?php echo $this->escape($item->plugin); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div class="clr"></div>
	<div class="filter-select fltrt">
		<button class="btn button" onclick="javascript: Joomla.submitbutton('payments.assign')"><?php echo JText::_('JAPPLY'); ?></button>
		<button class="btn button" onclick="javascript:Joomla.submitbutton('payments.assignclose')"><?php echo JText::_('JSAVE'); ?></button>
		<button class="btn button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></button>
	</div>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="item_id" value="<?php echo $parent_id; ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

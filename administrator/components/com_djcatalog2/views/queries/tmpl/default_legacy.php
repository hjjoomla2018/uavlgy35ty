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

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');

?>
<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=queries');?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"  />

			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
				</th>
				<th colspan="4" width="50%">
					<?php echo JText::_('COM_DJCATALOG2_BILLING_DETAILS'); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		
		foreach ($this->items as $i => $item) :
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=query.edit&id='.$item->id);?>">
						<?php echo JHTML::_('date', $item->created_date, 'd/m/Y'); ?>
					</a>
				</td>
				
				<td class="center">
					<a href="mailto:<?php echo $this->escape($item->email); ?>"><?php echo $this->escape($item->email); ?></a>
				</td>
				
				<td class="center">
					<?php if ($item->company) {
						echo $item->company.'<br />';
					}?>
					<?php echo $item->firstname.' '.$item->lastname; ?>
				</td>
				<td class="center">
					<?php echo $item->city ? $item->postcode.' '.$item->city: ''; ?><br />
					<?php echo $item->country; ?>
				</td>
				<td class="center">
					<?php echo $item->address;?>
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
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

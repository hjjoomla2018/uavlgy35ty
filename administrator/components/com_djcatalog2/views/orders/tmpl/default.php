<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');


$statuses = array('N', 'A', 'P', 'C', 'R', 'W', 'F');
$invoiceStatuses = array('C', 'P', 'F');

$status_options = array();
foreach($statuses as $status) {
	$status_options[] = JHtml::_('select.option', $status, JText::_('COM_DJCATALOG2_ORDER_STATUS_'.$status));
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=orders');?>" method="post" name="adminForm" id="adminForm">
	<?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label class="element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
			
		</div>
		<div class="btn-group pull-left">
			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" class="btn" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		
		<div class="filter-search btn-group pull-left">
			<label for="filter_date_from" class="element-invisible"><?php echo JText::_('COM_DJCATALOG2_FILTER_DATE_FROM')?></label>
	        <?php echo JHtml::_('calendar', $this->state->get('filter.date_from', ''), 'filter_date_from', 'filter_date_from', '%Y-%m-%d', 'class="input input-small" placeholder="'.JText::_('COM_DJCATALOG2_FILTER_DATE_FROM').'"'); ?>
		</div>
		<div class="filter-search btn-group pull-left">
		    <label for="filter_date_to" class="element-invisible"><?php echo JText::_('COM_DJCATALOG2_FILTER_DATE_TO')?></label>
		    <?php echo JHtml::_('calendar', $this->state->get('filter.date_to', ''), 'filter_date_to', 'filter_date_to', '%Y-%m-%d', 'class="input input-small" placeholder="'.JText::_('COM_DJCATALOG2_FILTER_DATE_TO').'"'); ?>
		</div>
		<div class="btn-group pull-left">
			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" class="btn" onclick="document.getElementById('filter_date_from').value='';document.id('filter_date_to').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
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
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="5%" colspan="2">
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_ORDER_NUMBER', 'a.order_number', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_ORDER_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_ORDER_STATUS', 'a.status', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="center">
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_ORDER_TOTAL', 'a.grand_total', $listDirn, $listOrder); ?>
				</th>
				<th colspan="4" width="50%" class="center">
					<?php echo JText::_('COM_DJCATALOG2_BILLING_DETAILS'); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'COM_DJCATALOG2_EDIT_TOOLTIP' );?>::<?php echo $this->escape($item->order_number); ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=order.edit&id='.$item->id);?>">
							<?php echo $this->escape($item->order_number); ?></a>
						</span>
				</td>
				<td>
					<?php 
					$invoice = false;
                    $proforma = false;
					if (!empty($item->invoice_number) && in_array($item->status, $invoiceStatuses)) {
						$invoice = true;
					} else if ($item->status == 'N' || $item->status == 'A') {
					    $proforma = true;
					}
					if ($invoice) {?>
					<button class="btn button btn-primary" onclick="return listItemTask('cb<?php echo $i?>','orders.invoices_selected');"><?php echo JText::_('COM_DJCATALOG2_GET_INVOICE'); ?></a>
					<?php } else if ($proforma) {?>
					<button class="btn button" onclick="return listItemTask('cb<?php echo $i?>','orders.proforma_selected');"><?php echo JText::_('COM_DJCATALOG2_GET_PROFORMA'); ?></a>
					<?php } ?>
				</td>
				
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=order.edit&id='.$item->id);?>">
						<?php echo JHTML::_('date', $item->created_date, 'd/m/Y'); ?>
					</a>
				</td>
				
				<td class="center">
					<?php //echo ($item->status) ? JText::_('COM_DJCATALOG2_ORDER_STATUS_'.$item->status) : ''; ?>
					<select name="status_change[<?php echo $item->id?>]" onchange="return listItemTask('cb<?php echo $i?>','orders.change_status')" class="input-medium">
					<?php 
						echo JHtml::_('select.options', $status_options, 'value', 'text', $item->status);
					?>
					</select>
					<label for="status_notify_<?php echo $item->id; ?>"><input type="checkbox" name="status_notify[<?php echo $item->id?>]" value="1" id="status_notify_<?php echo $item->id; ?>" /> <?php echo JText::_('COM_DJCATALOG2_STATUS_NOTIFY');?></label>
				</td>
				
				<td class="center">
					<?php echo number_format($item->grand_total, 2, '.', ' '); ?>
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
	</div>
</form>

<?php echo JHtmlBootstrap::renderModal('invcounters-modal', array('height'=> '300px', 'title' => JText::_('COM_DJCATALOG2_INVOICE_COUNTERS'), 'url' => JRoute::_('index.php?option=com_djcatalog2&view=orders&layout=invcounters&tmpl=component'))); ?>


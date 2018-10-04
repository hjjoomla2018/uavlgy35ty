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

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder == 'a.ordering';

$featured_states = array(
		0	=> array(
				'task'				=> 'featured',
				'text'				=> '',
				'active_title'		=> 'COM_DJCATALOG2_FEATURED',
				'inactive_title'	=> 'COM_DJCATALOG2_FEATURED',
				'tip'				=> true,
				'active_class'		=> 'unfeatured',
				'inactive_class'	=> 'featured'
		),
		1	=> array(
				'task'				=> 'unfeatured',
				'text'				=> '',
				'active_title'		=> 'COM_DJCATALOG2_FEATURED',
				'inactive_title'	=> 'COM_DJCATALOG2_FEATURED',
				'tip'				=> true,
				'active_class'		=> 'featured',
				'inactive_class'	=> 'unfeatured'
		)
);

$available_states = array(
		0	=> array(
				'task'				=> 'available',
				'text'				=> '',
				'active_title'		=> 'COM_DJCATALOG2_AVAILABLE',
				'inactive_title'	=> 'COM_DJCATALOG2_AVAILABLE',
				'tip'				=> true,
				'active_class'		=> 'unpublish',
				'inactive_class'	=> 'publish'
		),
		1	=> array(
				'task'				=> 'inavailable',
				'text'				=> '',
				'active_title'		=> 'COM_DJCATALOG2_AVAILABLE',
				'inactive_title'	=> 'COM_DJCATALOG2_AVAILABLE',
				'tip'				=> true,
				'active_class'		=> 'publish',
				'inactive_class'	=> 'unpublish'
		)
);

?>
<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=items');?>" method="post" name="adminForm" id="adminForm">
	<?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else: ?>
		<div id="j-main-container">
	<?php endif;?>
	
	<?php if ($this->state->get('filter.parent') > 0 ) {?>
		<?php if (!empty($this->parent_item->id)) {?>
			<div class="alert alert-info">
				<?php echo JText::sprintf('COM_DJCATALOG2_NOTICE_CHILD_ITEMS_LIST', $this->escape($this->parent_item->name)); ?>
				<a class="btn btn-primary btn-mini" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=items&filter_parent=0'); ?>"><?php echo JText::_('COM_DJCATALOG2_CHILD_ITEMS_GO_BACK'); ?></a>
			</div>
		<?php } else {?>
			<div class="alert alert-error">
				<?php echo JText::_('COM_DJCATALOG2_NOTICE_CHILD_ITEMS_LIST_MISSING_PARENT'); ?>
				<a class="btn btn-primary btn-mini" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=items&filter_parent=0'); ?>"><?php echo JText::_('COM_DJCATALOG2_CHILD_ITEMS_GO_BACK'); ?></a>
			</div>
		<?php } ?>
		
	<?php } ?>
	
	<div class="js-stools clearfix">
		<div class="clearfix">
			<div class="js-stools-container-bar">
				<div class="filter-search btn-group pull-left">
					<label class="element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
					<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
				</div>
				<div class="btn-group pull-left">
					<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
					<button type="button" class="btn" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				</div>
				<div class="btn-group pull-right">
					<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			</div>
		</div>
		<!-- Filters div -->
		<div class="js-stools-container-filters clearfix">
			<div class="btn-toolbar">
				<div class="btn-group ">
					<select name="filter_published" class="inputbox" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
						<?php echo JHtml::_('select.options', array(JHtml::_('select.option', '1', 'JPUBLISHED'),JHtml::_('select.option', '0', 'JUNPUBLISHED'), JHtml::_('select.option', '2', 'JARCHIVED'), JHtml::_('select.option', '-2', 'JTRASHED')), 'value', 'text', $this->state->get('filter.published'), true);?>
					</select>
				</div>
				<div class="btn-group ">
					<select name="filter_additional" class="inputbox" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_DJCATALOG2_OPT_SELECT_STATE');?></option>
						<?php 
						echo JHtml::_('select.options', array(
								JHtml::_('select.option', 'FTR', 'COM_DJCATALOG2_OPT_FEATURED'),
								JHtml::_('select.option', 'NFTR', 'COM_DJCATALOG2_OPT_NOTFEATURED'), 
								JHtml::_('select.option', 'AVL', 'COM_DJCATALOG2_OPT_AVAILABLE'), 
								JHtml::_('select.option', 'NAVL', 'COM_DJCATALOG2_OPT_NOTAVAILABLE'),
								JHtml::_('select.option', 'HCHILD', 'COM_DJCATALOG2_OPT_HASCHILDREN'),
								JHtml::_('select.option', 'NHCHILD', 'COM_DJCATALOG2_OPT_NOTHASCHILDREN')
						), 'value', 'text', $this->state->get('filter.additional'), true);?>
					</select>
				</div>
				<div class="btn-group ">	
				<?php echo JHTML::_('select.genericlist', $this->categories, 'filter_category', 'class="inputbox" onchange="this.form.submit()"', 'value', 'text', $this->state->get('filter.category')); ?>
				</div>
				<div class="btn-group ">
				<?php 
						$producers_first_option = new stdClass();
						$producers_first_option->id = '';
						$producers_first_option->name = '- '.JText::_('COM_DJCATALOG2_SELECT_PRODUCER').' -';
						$producers_first_option->published = null;
						$producers = count($this->producers) ? array_merge(array($producers_first_option),$this->producers) : array($producers_first_option);
						echo JHTML::_('select.genericlist', $producers, 'filter_producer', 'class="inputbox" onchange="this.form.submit()"', 'id', 'name', $this->state->get('filter.producer'));
					?>
				</div>
			</div>
		</div>
	</div>
	
	<div class="clearfix"> 

	<table class="table table-striped">
		<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="75" align="center">
					<?php echo JText::_('COM_DJCATALOG2_IMAGE'); ?>
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_NAME', 'a.name', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap hidden-phone">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
				</th>
				<th width="15%"  class="title">
					<?php echo JHTML::_('grid.sort',  'COM_DJCATALOG2_CATEGORY', 'category_name', $listDirn, $listOrder ); ?>
				</th>
				<th width="15%"  class="title">
					<?php echo JHTML::_('grid.sort',  'COM_DJCATALOG2_PRODUCER', 'producer_name', $listDirn, $listOrder ); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_PRICE', 'a.price', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_SPECIAL_PRICE', 'a.special_price', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<?php /*?>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_FEATURED', 'a.featured', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_AVAILABLE', 'a.available', $listDirn, $listOrder); ?>
				</th>
				<?php */ ?>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
					<?php if ($saveOrder) :?>
						<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'items.saveorder'); ?>
					<?php endif; ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="12">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php 
		$ordering	= ($listOrder == 'a.ordering');
		foreach ($this->items as $i => $item) :
			$item->max_ordering = 0; //??
			$canCheckin	= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
			$canChange  = $user->authorise('core.edit.state', 'com_djcatalog2') && $canCheckin;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td align="center">
					<?php 
					if ($item->item_image) { ?><img alt="<?php echo $item->image_caption; ?>" src="<?php echo DJCatalog2ImageHelper::getImageUrl($item->image_fullpath,'thumb', $item->image_path); ?>"/><?php }
					else { ?><img src="<?php echo str_replace('/administrator', '', JURI::base()).'components/com_djcatalog2/assets/images/noimage.jpg'; ?>" alt="" /><?php }?>
				</td>
				<td>
					<?php if ($item->checked_out) { ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'items.', $canCheckin); ?>
					<?php } ?>
					<?php if (!$canCheckin) :?>
						<?php echo $this->escape($item->name); ?>
					<?php else: ?>
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'COM_DJCATALOG2_EDIT_ITEM_TOOLTIP' );?>::<?php echo $this->escape($item->name); ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=item.edit&id='.$item->id);?>">
							<?php echo $this->escape($item->name); ?></a>
						</span>
					<?php endif; ?>
						
					<div class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?></div>
				</td>
				<td class="small hidden-phone">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'COM_DJCATALOG2_EDIT_CATEGORY_TOOLTIP' );?>::<?php echo $this->escape($item->category_name); ?>">
					<a href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=category.edit&id='.$item->cat_id);?>" >
					<?php echo $this->escape($item->category_name); ?></a></span>
				</td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'COM_DJCATALOG2_EDIT_PRODUCER_TOOLTIP' );?>::<?php echo $this->escape($item->producer_name); ?>">
					<a href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=producer.edit&id='.$item->producer_id);?>" >
					<?php echo $this->escape($item->producer_name); ?></a></span>
				</td>
				<td class="center">
					<?php echo number_format($item->price, 2); ?>
				</td>
				<td class="center">
					<?php echo number_format($item->special_price, 2); ?>
				</td>
				<td class="center">
					<?php // echo JHtml::_('jgrid.published', $item->published, $i, 'items.', true, 'cb'	); ?>
					<div class="btn-group">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'items.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
						<?php echo JHtml::_('jgrid.state', $featured_states, (bool)$item->featured, $i, 'items.', $canChange); ?>
						<?php echo JHtml::_('jgrid.state', $available_states, (bool)$item->available, $i, 'items.', $canChange); ?>
						<?php
						JHtml::_('actionsdropdown.' . 'archive', 'cb' . $i, 'items');
						JHtml::_('actionsdropdown.' . 'unarchive', 'cb' . $i, 'items');
						JHtml::_('actionsdropdown.' . 'trash', 'cb' . $i, 'items');
						JHtml::_('actionsdropdown.' . 'untrash', 'cb' . $i, 'items');
						echo JHtml::_('actionsdropdown.render', $this->escape($item->name));
						?>
					</div>
				</td>
				
				<?php /*?>
				<td class="center">
					<?php 
					//echo JHtmlGrid::boolean($i, $item->featured, 'items.featured', 'items.unfeatured'	); 
					echo JHtml::_('jgrid.state', $featured_states, (bool)$item->featured, $i, 'items.', true);
					?>
				</td>
				<td class="center">
					<?php 
					//echo JHtmlGrid::boolean($i, $item->featured, 'items.featured', 'items.unfeatured'	); 
					echo JHtml::_('jgrid.state', $available_states, (bool)$item->available, $i, 'items.', true);
					?>
				</td>
				<?php */ ?>
				
				<td class="order">
					<div class="input-prepend">
						<?php $disabled = ''; ?>
						<?php if ($saveOrder && $listDirn == 'asc') :?>
								<span class="add-on"><?php echo $this->pagination->orderUpIcon($i, ($item->cat_id == @$this->items[$i-1]->cat_id), 'items.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span><span class="add-on"><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->cat_id == @$this->items[$i+1]->cat_id), 'items.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
						<?php else: $disabled = 'disabled="disabled"'; echo "<span class=\"add-on tip\" title=\"".JText::_('JDISABLED')."\"><i class=\"icon-ban-circle\"></i></span>"; ?>
						<?php endif; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order width-20" />
					</div>
				</td>
				<td class="center">
					<?php echo (int) $item->hits; ?>
				</td>
				<td class="center">
					<?php echo $item->id; ?>
					<?php if ($item->parent_id == 0) {?>
						<a class="btn btn-mini" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=items&filter_parent='.(int)$item->id); ?>&filter_search="><?php echo JText::_('COM_DJCATALOG2_SHOW_CHILDREN'); ?></a>
					<?php } ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	</div>
</div>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>

	<?php echo JHtmlBootstrap::renderModal('items-modal', array('height'=> '300px', 'title' => JText::_('COM_DJCATALOG2_BATCH_TITLE'), 'footer' => $this->loadTemplate('batch_footer'),), $this->loadTemplate('batch') ); ?>

</form>



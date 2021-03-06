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

require_once JPATH_ROOT . '/components/com_djcatalog2/helpers/route.php';

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

$function	= JFactory::getApplication()->input->getCmd('function', 'jSelectDJCatalog2Item');

?>
<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=items&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm">
	<div class="span12">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label class="element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input class="input input-medium" type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" class="btn" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="clearfix"> </div>
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
			<div class="btn-group pull-right hidden-phone">	
			<?php echo JHTML::_('select.genericlist', $this->categories, 'filter_category', 'class="inputbox" onchange="this.form.submit()"', 'value', 'text', $this->state->get('filter.category')); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
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
		<div class="clearfix"> </div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="75" align="center">
						<?php echo JText::_('COM_DJCATALOG2_IMAGE'); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_DJCATALOG2_NAME', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th width="15%"  class="title">
						<?php echo JHTML::_('grid.sort',  'COM_DJCATALOG2_CATEGORY', 'category_name', $listDirn, $listOrder ); ?>
					</th>
					<th width="15%"  class="title">
						<?php echo JHTML::_('grid.sort',  'COM_DJCATALOG2_PRODUCER', 'producer_name', $listDirn, $listOrder ); ?>
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
					<td colspan="6">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php 
			$ordering	= ($listOrder == 'a.ordering');
			foreach ($this->items as $i => $item) :
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td align="center">
						<?php 
						if ($item->item_image) { ?><img alt="<?php echo $item->image_caption; ?>" src="<?php echo DJCatalog2ImageHelper::getImageUrl($item->image_fullpath,'thumb', $item->image_path); ?>"/><?php }
						else { ?><img src="<?php echo str_replace('/administrator', '', JURI::base()).'components/com_djcatalog2/assets/images/noimage.jpg'; ?>" alt="" /><?php }?>
					</td>
					<td>
						<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->name)); ?>', '<?php echo $this->escape($item->cat_id); ?>', null, '<?php echo $this->escape(DJCatalogHelperRoute::getItemRoute($item->id, $item->cat_id)); ?>', null);">
							<?php echo $this->escape($item->name); ?>
						</a>
						<p class="smallsub"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?></p>
					</td>
					<td>
						<?php echo $this->escape($item->category_name); ?>
					</td>
					<td>
						<?php echo $this->escape($item->producer_name); ?>
					</td>
					<td class="center">
						<?php echo (int) $item->hits; ?>
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

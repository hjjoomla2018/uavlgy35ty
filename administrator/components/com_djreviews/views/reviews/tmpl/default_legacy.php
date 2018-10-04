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

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>
<form action="<?php echo JRoute::_('index.php?option=com_djreviews&view=reviews');?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"  />

			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		
		<div class="filter-select fltrt">
			<select name="filter_rating_object_type" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_DJREVIEWS_SELECT_OPTION_OBJECT_TYPE');?></option>
				<?php 
					echo JHtml::_('select.options', $this->rating_objects, 'value', 'text', $this->state->get('filter.rating_object_type'), true);
				?>
			</select>
		</div>
		
		<div class="filter-select fltrt">
			<select name="filter_rating_group" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_DJREVIEWS_SELECT_OPTION_RATING_GROUP');?></option>
				<?php 
					echo JHtml::_('select.options', $this->rating_groups, 'value', 'text', $this->state->get('filter.rating_group'), true);
				?>
			</select>
		</div>
		
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_DJREVIEWS_SELECT_OPTION_PUBLISHED');?></option>
				<?php 
					echo JHtml::_('select.options',array(JHtml::_('select.option', '1', 'JPUBLISHED'),JHtml::_('select.option', '0', 'JUNPUBLISHED'),JHtml::_('select.option', '-1', 'COM_DJREVIEWS_REPORTED')), 'value', 'text', $this->state->get('filter.published'), true);
				?>
			</select>
		</div>
		
	</fieldset>
	<div class="clr"> </div>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
				</th>
				<th width="25%">
						<?php echo JHtml::_('grid.sort', 'COM_DJREVIEWS_REVIEW', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="center">
						<?php echo JHtml::_('grid.sort', 'COM_DJREVIEWS_REVIEW_OBJECT_TYPE', 'a.object_type', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="center">
						<?php echo JHtml::_('grid.sort', 'COM_DJREVIEWS_RATING_GROUP', 'g.name', $listDirn, $listOrder); ?>
					</th>
					<th width="25%">
						<?php echo JHtml::_('grid.sort', 'COM_DJREVIEWS_REVIEW_OBJECT', 'o.name', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JHtml::_('grid.sort', 'COM_DJREVIEWS_AUTHOR', 'a.user_name', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="center">
						<?php echo JHtml::_('grid.sort', 'COM_DJREVIEWS_AUTHOR_LOGIN', 'a.user_login', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'COM_DJREVIEWS_DATE', 'a.created', $listDirn, $listOrder); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$canCheckin	= $user->authorise('core.manage', 'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php 
						$comment_title = $item->title;
						
						if (!$comment_title) {
							$comment_title  = strip_tags($item->message);
						}
						$comment_title = JString::substr($comment_title, 0, 30);
						
						if (!$comment_title) {
							$comment_title = $item->user_login .'@'. JHtml::_('date', $item->created, 'd/m/Y H:i');
						}
						
						?>
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'reviews.', $canCheckin); ?>
						<?php endif; ?>
						<?php if (!$canCheckin): ?>
							<?php echo $this->escape($comment_title); ?>
						<?php else: ?>
							<span class="editlinktip hasTip" title="<?php echo JText::_( 'COM_DJREVIEWS_EDIT_TOOLTIP' );?>::<?php echo $this->escape($comment_title); ?>">
							<a href="<?php echo JRoute::_('index.php?option=com_djreviews&task=review.edit&id='.$item->id);?>">
								<?php echo $this->escape($comment_title); ?></a>
							</span>
						<?php endif; ?>
					</td>
					<td class="center">
						<?php if (isset($this->rating_objects[$item->object_type])) {
							echo $this->rating_objects[$item->object_type]->text;
						} else {
							echo $item->object_type;
						}
						?>
					</td>
					<td class="center">
						<?php echo $item->group_name; ?>
					</td>
					<td>
						<?php if ($item->object_url) {?>
							<a href="<?php echo JUri::root().$item->object_url; ?>" target="_blank"><?php echo $item->object_name; ?></a>
						<?php } else {
							echo $item->object_name;
						} ?>
					</td>
					<td class="center">
						<?php echo $item->user_name; ?>
					</td>
					<td class="center">
						<?php echo $item->user_login; ?>
						<?php if (!$item->created_by) {
							echo ' ('.JText::_('COM_DJREVIEWS_GUEST').')';
						} else if (!$item->user_id) {
							echo ' ('.JText::_('COM_DJREVIEWS_USER_DELETED').')';
						}
						?>
					</td>
					<td class="nowrap">
						<?php echo JHtml::_('date', $item->created, 'd/m/Y H:i'); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'reviews.', true, 'cb'	); ?>
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

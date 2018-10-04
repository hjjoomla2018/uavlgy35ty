<?php 
/**
 * @version $Id: modal.php 40 2014-09-08 14:28:34Z szymon $
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
 
defined('_JEXEC') or die('Restricted access'); ?>

<?php 

JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
if(version_compare(JVERSION, '3.0', '>=')) {
	JHtml::_('formbehavior.chosen', 'select');
	$btnclass = 'btn btn-primary btn-large';
} else {
	$btnclass = 'button';
}

$function = JRequest::getVar('f_name');
$editor_insert = strstr($function, 'jInsertDJMedia_') ? true : false;

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= true; //$user->authorise('core.edit.state', 'com_contact.category');
$saveOrder	= $listOrder == 'a.ordering';
$search = $this->state->get('filter.search');
$saveOrder	= (empty($search) == false ? false : $saveOrder);
$saveOrder	= (is_numeric($this->state->get('filter.published')) ? false : $saveOrder);
?>
<div class="modalAlbum">
	<a href="<?php echo JRoute::_('index.php?option=com_djmediatools&view=categoryform&tmpl=component&f_name='.$function); ?>" class="<?php echo $btnclass; ?>"><?php echo JText::_('COM_DJMEDIATOOLS_MODAL_CREATE_NEW_ALBUM') ?></a>
	<span><?php echo JText::_('COM_DJMEDIATOOLS_MODAL_CREATE_NEW_ALBUM_DESC') ?></span>
</div>
<form action="<?php echo JRoute::_('index.php?option=com_djmediatools&view=categories&layout=modal&tmpl=component&'.JSession::getFormToken().'=1'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar" class="btn-toolbar">
		
		<div class="filter-select fltrt btn-group pull-right">
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		
	</fieldset>
	<div class="clr"> </div>
	
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="8%" class="center">
					<?php echo JText::_('COM_DJMEDIATOOLS_CATEGORY_IMAGE'); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('COM_DJMEDIATOOLS_SOURCE_TYPE'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('COM_DJMEDIATOOLS_ACTIONS'); ?>
				</th>
				<th>
					<?php echo JText::_('JGLOBAL_TITLE'); ?>
				</th>
				<th width="1%">
					<?php echo JText::_('JGRID_HEADING_ID'); ?>
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
		<?php 
		$n = count($this->categories);
		foreach ($this->categories as $i => $item) :
			$ordering	= ($listOrder == 'a.ordering');
			$canCreate	= $user->authorise('core.create', 'com_djmediatools');
			$canEdit	= $user->authorise('core.edit',	'com_djmediatools');
			$canCheckin	= $user->authorise('core.manage',		'com_djmediatools') || $item->checked_out == $userId || $item->checked_out == 0;
			$canChange	= $user->authorise('core.edit.state',	'com_djmediatools') && $canCheckin;

			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td align="center" class="center">
					<?php if ($item->image) : ?>
						<a class="modal" href="<?php echo $item->image; ?>"><img src="<?php echo $item->thumb; ?>" alt="<?php echo $this->escape($item->title); ?>" style="border: 1px solid #ccc; padding: 1px;" /></a>
					<?php else : ?>
						<img src="<?php echo $item->thumb; ?>" alt="<?php echo $this->escape($item->title); ?>" />
					<?php endif; ?>
				</td>
				<td align="center" valign="middle" class="center">
					<?php if($item->source=='component') { ?>
						<img src="administrator/components/com_djmediatools/assets/icon-48-slides.png" alt="<?php echo JText::_('COM_DJMEDIATOOLS_CUSTOM_ITEMS'); ?>" class="hasTip" title="<?php echo JText::_('COM_DJMEDIATOOLS_CUSTOM_ITEMS'); ?>" width="40" />
					<?php } else { ?>
						<img src="plugins/djmediatools/<?php echo $item->source ?>/icon.png" alt="<?php echo $item->source ?>" class="hasTip" title="<?php echo $item->source ?>" width="32" />
					<?php } ?>
				</td>
				<td align="center" nowrap="nowrap">
					<a href="#" class="pointer button btn" onclick="if (window.parent) window.parent.<?php echo $function ?>('<?php echo $item->id; ?>','<?php echo $item->image ? $item->image : 'administrator/components/com_djmediatools/assets/icon-album.png' ?>','<?php echo addslashes($this->escape($item->title)); ?>');">
					<i class="icon-pictures"></i>
						<?php if($editor_insert) : ?>
						<?php echo JText::_('COM_DJMEDIATOOLS_INSERT_ALBUM'); ?>
						<?php else : ?>
						<?php echo JText::_('JSELECT'); ?>
						<?php endif; ?>
					</a>
					<?php if($editor_insert) : ?>
					<a href="#" class="pointer button btn hasTip" title="<?php echo JText::_('COM_DJMEDIATOOLS_INSERT_LINKED_COVER_DESC'); ?>" onclick="if (window.parent) window.parent.<?php echo $function ?>('<?php echo $item->id; ?>','<?php echo $item->image ? $item->image : 'administrator/components/com_djmediatools/assets/icon-album.png' ?>','<?php echo addslashes($this->escape($item->title)); ?>',true);">
						<i class="icon-picture"></i>
						<?php echo JText::_('COM_DJMEDIATOOLS_INSERT_LINKED_COVER'); ?>
					</a>
					<?php endif; ?>
				</td>
				<td>
					<?php if(isset($item->level)) for($lvl = 0; $lvl < $item->level; $lvl++) : ?>
					<span class="gi">|&mdash;</span>
					<?php endfor; ?>
					<?php echo $this->escape($item->title); ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
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
		<input type="hidden" name="f_name" value="<?php echo $function; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

<?php
/**
* @version $Id: default_groupped.php 699 2017-04-15 07:00:30Z michal $
* @package DJ-Catalog2
* @copyright Copyright (C) 2010 Blue Constant Media LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer $Author: michal $ Michal Olczyk - michal.olczyk@design-joomla.eu
*
*
*** the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ-Catalog2 is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ-Catalog2. If not, see <http://www.gnu.org/licenses/>.
*
*/

defined ('_JEXEC') or die('Restricted access'); 

?>

<?php foreach ($data as $group_id => $group) { ?>
	<?php if ($group->isempty == false) { ?>
		<div class="mod_djc2filters_group djc_clearfix">
		<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=search'); ?>" method="post">
		<?php 
		$display_buttons = false;
		if ($params->get('group_title', '1') == '1' || $params->get('group_accordion', '0') == '1') { ?>
			<h4 class="<?php if ($params->get('group_accordion', '0') == '1') echo 'djc_tab_toggler'; ?>">
			<?php echo ($params->get('group_title', '1') == '1') ? JText::sprintf('MOD_DJC2FILTERS_GROUP_NAME', $group->group_name) : JText::_('MOD_DJC2FILTERS_GROUP_LEGEND'); ?>
			</h4>
		<?php } ?>
		<div class="mod_djc2filters_group_wrapper <?php if ($params->get('group_accordion', '0') == '1') echo 'djc_tab_content'; ?>">
		<?php foreach($group->attributes as $item) { ?>
			<?php if (!empty($item->selectedOptions) || $item->availableOptions > 0 || $item->selected) { ?>
			<div class="control-group mod_djc2filters_attribute <?php echo 'djc2f-'.$item->alias; ?> <?php if ($module_float) echo 'djc2_fixcol'; ?>">
				<div class="control-label">
				<?php require(JModuleHelper::getLayoutPath('mod_djc2filters', 'default_filter_label')); ?>
		            </div>
		            <div id="djc_filter_<?php echo JFilterOutput::stringURLSafe($item->name); ?>" class="controls <?php echo $item->filter_type; ?>">
					<?php if ($item->filter_type == 'checkbox' || $item->filter_type == 'checkbox_or') {
						$display_buttons = true;
						require(JModuleHelper::getLayoutPath('mod_djc2filters', 'default_filter_checkbox'));
					} else if ($item->filter_type == 'radio') {
						$display_buttons = true;
						require(JModuleHelper::getLayoutPath('mod_djc2filters', 'default_filter_radio'));
					} else if ($item->filter_type == 'select') {
						$display_buttons = true;
						require(JModuleHelper::getLayoutPath('mod_djc2filters', 'default_filter_select'));
					} else if ($item->filter_type == 'minmax' || $item->filter_type == 'minmax_text' || $item->type == 'text') {
						$display_buttons = true;
						require(JModuleHelper::getLayoutPath('mod_djc2filters', 'default_filter_minmax'));
					} else  {
						require(JModuleHelper::getLayoutPath('mod_djc2filters', 'default_filter_list')); ?>
					<?php } ?>
		            </div>
				</div>
		        <?php } ?>
		<?php } ?>
		<?php if ($display_buttons || ($autosubmit && !empty($query['djcf']))) {?>
		<div class="mod_djc2filters_buttons">
			<input type="hidden" name="cid" value="<?php echo htmlspecialchars($app->input->get('cid', null, 'string')); ?>" />
			<input type="hidden" name="pid" value="<?php echo htmlspecialchars($app->input->get('pid', null, 'string')); ?>" />
			<?php if ($app->input->getInt('aid') > 0) { ?>
		        <input type="hidden" name="aid" value="<?php echo htmlspecialchars($app->input->get('aid', null, 'string')); ?>" />
		    <?php } ?>
			<input type="hidden" name="option" value="com_djcatalog2" />
			<input type="hidden" name="view" value="items" />
			<input type="hidden" name="task" value="search" />
			<input type="hidden" name="Itemid" value="<?php echo $app->input->getInt('Itemid'); ?>" />
			
			<input type="submit" class="btn submit_button" value="<?php echo JText::_('MOD_DJC2FILTERS_SUBMITBUTTON'); ?>" />
			<?php if (!empty($query['djcf'])) {
				$filter_query = $query;
				unset($filter_query['djcf']);
				$filter_query['task'] = 'search_reset';
				if (array_key_exists('cm', $filter_query)) {
					unset($filter_query['cm']);
				}
				$uri->setQuery($filter_query);
			?>
			<a class="btn reset_button" href="<?php echo htmlspecialchars($uri->toString()); ?>"><?php echo JText::_('MOD_DJC2FILTERS_RESETBUTTON')?></a>
			<?php } ?>
		</div>
		<?php } ?>
		</div>
		</form>
		</div>
	<?php } ?>
<?php } ?>


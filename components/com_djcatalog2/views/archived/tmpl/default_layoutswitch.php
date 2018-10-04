<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');
?>
<?php

JURI::reset();

$app = JFactory::getApplication();
$menu = $app->getMenu();
$juri = JURI::getInstance();
$uri = JURI::getInstance($juri->toString());
$query = $uri->getQuery(true);
$active = $app->input->get('l',$this->params->get('list_layout','items'));
unset($query['l']);

$query['option'] = 'com_djcatalog2';
$query['view'] = 'archived';
$query['Itemid'] = $menu->getActive() ? $menu->getActive()->id : null;
$cid = $app->input->get('cid', false, 'string');
$pid = $app->input->get('pid', false, 'string');

if ($cid) {
	$query['cid'] = $cid;
}
if ($pid) {
	$query['pid'] = $pid;
}

$uri->setQuery($query);

$layoutUrl = 'index.php?'.$uri->getQuery(false);

JURI::reset();

?>
<div class="djc_layout_switch_in">
    <ul class="djc_layout_buttons djc_clearfix btn-group">
		<li><a class="btn<?php if ($active == 'items') echo ' active'; ?>" href="<?php echo JRoute::_( $layoutUrl.'&l=items'); ?>" title="<?php echo JText::_('COM_DJCATALOG2_GRID_LAYOUT'); ?>"><img src="<?php echo DJCatalog2ThemeHelper::getThemeImage('grid.png');?>" alt="<?php echo JText::_('COM_DJCATALOG2_GRID_LAYOUT'); ?>" /></a></li>
		<li><a class="btn<?php if ($active == 'table') echo ' active'; ?>" href="<?php echo JRoute::_( $layoutUrl.'&l=table'); ?>" title="<?php echo JText::_('COM_DJCATALOG2_TABLE_LAYOUT'); ?>"><img src="<?php echo DJCatalog2ThemeHelper::getThemeImage('table.png');?>" alt="<?php echo JText::_('COM_DJCATALOG2_TABLE_LAYOUT'); ?>" /></a></li>
	</ul>
</div>

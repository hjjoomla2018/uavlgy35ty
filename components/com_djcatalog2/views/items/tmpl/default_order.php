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

//

$app = JFactory::getApplication();
$menu = $app->getMenu();

$active = $app->input->get('ind','', 'string');

$juri = JURI::getInstance();
$uri = JURI::getInstance($juri->toString());
$query = $uri->getQuery(true);

$query['option'] = 'com_djcatalog2';
$query['view'] = 'items';
$query['Itemid'] = $menu->getActive() ? $menu->getActive()->id : null;
$cid = $app->input->get('cid', false, 'string');
$pid = $app->input->get('pid', false, 'string');

if ($cid) {
	$query['cid'] = $cid;
}
if ($pid) {
	$query['pid'] = $pid;
}

unset($query['order']);
unset($query['dir']);

$uri->setQuery($query);
$orderUrl = 'index.php?'.$uri->getQuery(false);

//



JURI::reset();

$user		= JFactory::getUser();
$price_auth = ($this->params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;

?>
<div class="djc_order_in thumbnail">
    <ul class="djc_order_buttons djc_clearfix">
        <li class="span2"><span><?php echo JText::_('COM_DJCATALOG2_ORDERBY'); ?></span></li>
        <?php if ($this->params->get('show_name_orderby') > 0) { ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'&order=i.name&dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_NAME'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'i.name', $this->lists['order_Dir']); ?></li>
        <?php } ?>
        <?php if ($this->params->get('show_category_orderby') > 0) { ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'&order=category&dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_CATEGORY'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'category', $this->lists['order_Dir']); ?></li>
        <?php } ?>
        <?php if ($this->params->get('show_producer_orderby') > 0) { ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'&order=producer&dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_PRODUCER'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'producer', $this->lists['order_Dir']); ?></li>
        <?php } ?>
        <?php if ($price_auth && $this->params->get('show_price_orderby') > 0) { ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'&order=i.price&dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_PRICE'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'i.price', $this->lists['order_Dir']); ?></li>
        <?php } ?>
        <?php if ($this->params->get('show_date_orderby') > 0) { ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'&order=i.created&dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_DATE'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'i.created', $this->lists['order_Dir']); ?></li>
        <?php } ?>
        <?php if ($this->params->get('show_modify_date_orderby') > 0) { ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'&order=i.modified&dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_MODIFY_DATE'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'i.created', $this->lists['order_Dir']); ?></li>
        <?php } ?>
        <?php if (count($this->sortables) > 0) { ?>
	        <?php foreach ($this->sortables as $sortable) { ?>
	            <li><a href="<?php echo JRoute::_( $orderUrl.'&order=f_'.$sortable->alias.'&dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo $sortable->name; ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'f_'.$sortable->alias, $this->lists['order_Dir']); ?></li>
	        <?php } ?>
        <?php } ?>
    </ul>
</div>
<?php

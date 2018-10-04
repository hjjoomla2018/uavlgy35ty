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
$input = JFactory::getApplication()->input;
$juri = JURI::getInstance();
$uri = JURI::getInstance($juri->toString());
$query = $uri->getQuery(true);
$app = JFactory::getApplication();

unset($query['order']);
unset($query['dir']);
$uri->setQuery($query);

$orderUrl = htmlspecialchars($uri->toString());

if (strpos($orderUrl,'?') === false ) {
    $orderUrl .= '?';
} else {
    $orderUrl .= '&amp;';
}
JURI::reset();

$ordering = $app->getUserStateFromRequest('com_djcatalog2.myitems.ordering', 'order', 'i.ordering');
$order_dir = $app->getUserStateFromRequest('com_djcatalog2.myitems.order_dir', 'dir', 'asc');

$this->lists = array('order_Dir' => $order_dir == 'desc' ? 'asc' : 'desc', 'order' => $ordering);
?>
<div class="djc_order_in thumbnail">
    <ul class="djc_order_buttons djc_clearfix">
        <li><span><?php echo JText::_('COM_DJCATALOG2_ORDERBY'); ?></span></li>
            <li><a href="<?php echo JRoute::_( $orderUrl.'order=i.name&amp;dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_NAME'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'i.name', $this->lists['order_Dir']); ?></li>
            <?php if ($this->params->get('fed_show_category_name') > 0) { ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'order=category&amp;dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_CATEGORY'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'category', $this->lists['order_Dir']); ?></li>
            <?php } ?>
            <?php if ($this->params->get('fed_show_producer_name') > 0) { ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'order=producer&amp;dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_PRODUCER'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'producer', $this->lists['order_Dir']); ?></li>
            <?php } ?>
            <?php if ($this->params->get('fed_show_price') > 0) { ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'order=i.price&amp;dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_PRICE'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'i.price', $this->lists['order_Dir']); ?></li>
            <?php } ?>
            <li><a href="<?php echo JRoute::_( $orderUrl.'order=i.created&amp;dir='.$this->lists['order_Dir'].'#tlb'); ?>"><?php echo JText::_('COM_DJCATALOG2_DATE'); ?></a><?php echo DJCatalog2HtmlHelper::orderDirImage($this->lists['order'], 'i.created', $this->lists['order_Dir']); ?></li>
    </ul>
</div>
<?php

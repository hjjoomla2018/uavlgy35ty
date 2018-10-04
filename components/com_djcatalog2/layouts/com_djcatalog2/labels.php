<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');


$item = $displayData['item'];
if (empty($item->_labels)) {
	return;
}

$app = JFactory::getApplication();

JHtml::_('bootstrap.tooltip');
JHtmlBootstrap::popover('.djcHasPopover', array('trigger' => 'hover focus click'));

?>

<ul class="djc_items_labels djc_labels list-inline inline list-unstyled unstyled">
	<?php foreach($item->_labels as $label) {?>
		<?php 
		$hasText = (bool)(trim($label->label) != '');
		$hasDesc = (bool)((trim(strip_tags($label->description))));
		$hasImg = (bool)($label->image != '');
		
		$popOverContent = $hasDesc ? 'data-content="'.htmlspecialchars($label->description).'"' : '';
		$popOverTitle = $hasText ? 'title="'.htmlspecialchars($label->label).'"' : 'title=""';
		$popOverAttrs = ($hasDesc || ($hasImg && ($hasDesc || $hasText))) ? $popOverTitle.' '.$popOverContent : '';
		
		$labelLink = null;
		if ($label->type == 'link') {
			switch ($label->params->get('link_type')) {
				case 'ext': {
					$labelLink = JRoute::_(trim($label->params->get('link_url')));
					break;
				}
				case 'menu' : {
					$Itemid = $label->params->get('link_menu');
					if ($Itemid) {
						$menu = $app->getMenu();
						if ($menuitem = $menu->getItem($Itemid)) {
							if ((strpos($menuitem->link, 'index.php?') === 0) && (strpos($menuitem->link, 'Itemid=') === false))
							{
								// If this is an internal Joomla link, ensure the Itemid is set.
								$labelLink = $menuitem->link . '&Itemid=' . $menuitem->id;
							} else {
								$labelLink = $menuitem->link;
							}
							
							if (strcasecmp(substr($labelLink, 0, 4), 'http') && (strpos($labelLink, 'index.php?') !== false))
							{
								$labelLink = JRoute::_($labelLink, true, $menuitem->params->get('secure'));
							}
							else
							{
								$labelLink = JRoute::_($labelLink);
							}
						}
					}
					break;
				}
				case 'article' : {
					$contentId = $label->params->get('link_article');
					if ($contentId) {
						jimport('joomla.application.component.model');
						require_once(JPATH_BASE.'/components/com_content/helpers/route.php');
						JModelLegacy::addIncludePath(JPATH_BASE.'/components/com_content/models');
						$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request'=>true));
						$model->setState('params', $app->getParams());
						$model->setState('filter.article_id', $contentId);
						$model->setState('filter.article_id.include', true); // Include
						$items = $model->getItems();
						if($items && $art = $items[0]) {
							$art->slug = $art->alias ? ($art->id . ':' . $art->alias) : $art->id;
							$labelLink = JRoute::_(ContentHelperRoute::getArticleRoute($art->slug, $art->catid));
						}
					}
					break;
				}
				default: break;
			}
		} else if ($label->type == 'tag') {
			$labelLink = JRoute::_(DJCatalog2HelperRoute::getCategoryRoute(0).'&tag=' . $label->id);
		}
		
		?>
		<li class="badge djc_label_item <?php echo ($popOverAttrs) ? 'djcHasPopover': ''; ?>" <?php echo $popOverAttrs; ?>>
		<?php if ($labelLink) {?>
			<a href="<?php echo $labelLink; ?>">
		<?php } ?>
		<?php if ($hasImg) {?>
			<img alt="<?php echo $this->escape($label->label); ?>" src="<?php echo htmlspecialchars($label->image, ENT_COMPAT, 'UTF-8'); ?>"/>
		<?php } ?>
		<?php if ($hasText) {?>
			<span><?php echo $this->escape($label->label); ?></span>
		<?php } ?>
		<?php if ($labelLink) {?>
			</a>
		<?php } ?>
		</li>
	<?php } ?>
</ul>

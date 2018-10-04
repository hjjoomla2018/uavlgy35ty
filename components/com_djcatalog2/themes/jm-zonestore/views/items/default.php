<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$filter_modules = JModuleHelper::getModules('djc2_filters');

$this->filter_modules = '';

if (!empty($filter_modules)) {
	foreach ($filter_modules as $module){
		$this->filter_modules .= JModuleHelper::renderModule($module);
	}
}

$author_id = $app->input->getInt('aid', 0);
$author_user = false;
if ($author_id > 0) {
	$author_user = Djcatalog2Helper::getUser($author_id);
}

$showItemTitle = (bool)($this->params->get('showcatname', 1) && ($this->item->name != $this->params->get('page_heading') || !$this->params->get( 'show_page_heading', 1)));

?>
<div id="djcatalog" class="djc_list<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

<?php /* WARNING - do not remove nor duplicate .djc_heading_set container */ ?>
<div class="djc_heading_set">
<?php if (!empty($this->feedlink) && $this->params->get('rss_feed_icon', 0) == '1' && $this->params->get('rss_enabled', '1') == '1' && !($this->params->get('showcatdesc') && $this->item && $this->item->id > 0)) { ?>
	<a class="djc_rss_link" href="<?php echo $this->feedlink; ?>"><img alt="RSS" src="<?php echo DJCatalog2ThemeHelper::getThemeImage('rss_icon.png')?>" /></a>
<?php } ?>

<?php if ($author_user) { ?>
	<h1 class="componentheading"><?php echo JText::sprintf('COM_DJCATALOG2_USER_ITEMS_DISPLAYED', $author_user->username); ?>
		<?php
		JUri::reset();
		$uri = JUri::getInstance();
		$query = $uri->getQuery(true);
		if (isset($query['aid'])) {
			unset($query['aid']);
		}
		$uri->setQuery($query);
		$clearUserLink = $uri->toString();
		JUri::reset();
		?>
		&nbsp;<a class="btn btn-mini" href="<?php echo $clearUserLink ?>"><?php echo JText::_('COM_DJCATALOG2_CLEAR_FILTER'); ?></a>	 
	</h1>
<?php } ?>  

<?php if (!$author_user) { ?>

<?php if ($this->params->get( 'show_page_heading', 1) && (empty($this->item) || empty($this->item->id) || (!$this->params->get('showcatdesc') && !empty($this->item)) )) : ?>
	<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?> djc_page_heading">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<?php if ($this->params->get('showcatdesc') && $this->item && $this->item->id > 0) { ?>
	
	<?php if ( in_array('category', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'top' && $this->params->get('social_code', '') != '') { ?>
	<div class="djc_clearfix djc_social_t">
		<?php echo $this->params->get('social_code'); ?>
	</div>
	<?php } ?>
	
	<?php if ($this->params->get( 'show_page_heading', 1)) : ?>
	<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?> djc_page_heading">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>	
	
	<div class="djc_category djc_clearfix">
		<?php 
			$this->item->images = DJCatalog2ImageHelper::getImages('category',$this->item->id);
			if ((int)$this->params->get('showcatimg', 1) > 0 && $this->item->images) {
				echo $this->loadTemplate('images'); 
			} 
		?>
		
		<?php if ($showItemTitle) { ?>
		<h2 class="djc_title">
			<?php echo $this->escape($this->item->name); ?>
			<?php if (!empty($this->feedlink) && $this->params->get('rss_feed_icon', 0) == '1' && $this->params->get('rss_enabled', '1') == '1') { ?>
				<a class="djc_rss_link" href="<?php echo $this->feedlink; ?>"><img alt="RSS" src="<?php echo DJCatalog2ThemeHelper::getThemeImage('rss_icon.png')?>" /></a>
			<?php } ?>
		</h2>
		<?php } else if (!empty($this->feedlink) && $this->params->get('rss_feed_icon', 0) == '1' && $this->params->get('rss_enabled', '1') == '1') { ?>
			<a class="djc_rss_link" href="<?php echo $this->feedlink; ?>"><img alt="RSS" src="<?php echo DJCatalog2ThemeHelper::getThemeImage('rss_icon.png')?>" /></a>
		<?php } ?>
		
		<?php if ( in_array('category', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'aft_title' && $this->params->get('social_code', '') != '') { ?>
			<div class="djc_clearfix djc_social_at">
				<?php echo $this->params->get('social_code'); ?>
			</div>
		<?php } ?>
		
		<?php if (JString::strlen(trim($this->item->description)) > 0) {?>
		<div class="djc_description">
			<div class="djc_fulltext">
				<?php echo JHTML::_('content.prepare', $this->item->description, $this->params, 'com_djcatalog2.category.description'); ?>
			</div>
			<?php if (isset($this->item->tabs)) { ?>
				<div class="djc_clear"></div>
				<div class="djc_tabs">
					<?php echo $this->item->tabs; ?>
				</div>
			<?php } ?>
		</div>
		<?php } ?>
		
		<?php if ( in_array('category', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'aft_desc' && $this->params->get('social_code', '') != '') { ?>
			<div class="djc_clearfix djc_social_ad">
				<?php echo $this->params->get('social_code'); ?>
			</div>
		<?php } ?>
	</div>
<?php } ?>
</div>

<?php /* WARNING - do not remove nor duplicate .djc_subcategories_set container */ ?>
<div class="djc_subcategories_set">
<?php if ($this->params->get('showsubcategories') && $this->subcategories && JFactory::getApplication()->input->get('filtering', false) === false) { ?>
<div class="djc_subcategories">
	<?php if ($this->params->get('showsubcategories_label')) { ?>
		<h2 class="djc_title"><?php echo JText::_('COM_DJCATALOG2_SUBCATEGORIES'); ?></h2>
	<?php } ?>
	<div class="djc_subcategories_grid djc_clearfix">
		<?php echo $this->loadTemplate('subcategories'); ?>
	</div>
</div>
<?php } ?>
</div>

<?php } ?>
	
		<?php if ( ($this->params->get('product_catalogue') == '0' || count($this->items) > 0)
	           && ($this->params->get('show_category_filter') > 0
	               || $this->params->get('show_producer_filter') > 0
	               || $this->params->get('show_price_filter') > 0
	               || $this->params->get('show_search') > 0
	               || $this->params->get('show_location_search') > 0
	               || $this->params->get('show_pictures_search') > 0 
				   || $this->params->get('show_atoz_filter') > 0)) { ?>
				   	
<div class="djc_filters_wrapper">

	<?php
	$toggleAdvFilters = false;
	?>
	<?php if ( ($this->params->get('product_catalogue') == '0' || count($this->items) > 0)
	           && ($this->params->get('show_category_filter') > 0
	               || $this->params->get('show_producer_filter') > 0
	               || $this->params->get('show_price_filter') > 0
	               || $this->params->get('show_search') > 0
	               || $this->params->get('show_location_search') > 0
	               || $this->params->get('show_pictures_search') > 0 )) { ?>
		<div class="djc_filters djc_clearfix" id="tlb">
			<?php echo $this->loadTemplate('filters'); ?>
			<?php $toggleAdvFilters = true; ?>
		</div>
	<?php } ?>

	<?php
	if (!empty($this->filter_modules)) {?>
		<div id="djc_additional_filters" class="djc_additional_filters djc_clearfix">
			<div class="djc_additional_filters_in thumbnail">
				<?php echo $this->filter_modules; ?>
				<?php if ($toggleAdvFilters) {?>
					<p class="djc_adv_search_wrap">
						<span class="djc_adv_search_toggle"><?php echo JText::_('COM_DJCATALOG2_ADVANCED_SEARCH_CLOSE'); ?> <span class="icon-uparrow"></span></span>
					</p>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

	<?php /* WARNING - do not remove nor duplicate .djc_atoz_set container */ ?>
	<div class="djc_atoz_set">
		<?php if (($this->params->get('product_catalogue') == '0' || count($this->items) > 0) && $this->params->get('show_atoz_filter') > 0) { ?>
			<div class="djc_atoz djc_clearfix">
				<?php echo $this->loadTemplate('atoz'); ?>
			</div>
		<?php } ?>
	</div>

</div>

<?php } ?>

<div class="djc_order_wrapper">
	<?php /* WARNING - do not remove nor duplicate .djc_order_set container */ ?>
	<div class="djc_order_set">
		<?php
		if (count($this->items) > 0 && (
				$this->params->get('show_category_orderby') > 0
				|| $this->params->get('show_producer_orderby') > 0
				|| $this->params->get('show_name_orderby') > 0
				|| $this->params->get('show_price_orderby') > 0
				|| $this->params->get('show_date_orderby') > 0
				|| $this->params->get('show_modify_date_orderby') > 0
				|| count($this->sortables) > 0)) { ?>
			<div class="djc_order djc_clearfix">
				<?php echo $this->loadTemplate('order'); ?>
			</div>
		<?php } ?>
	</div>

	<?php /* WARNING - do not remove nor duplicate .djc_toolbar_set container */ ?>
	<div class="djc_toolbar_set">
		<?php /*if (count($this->items) > 0 && $this->params->get('show_layout_switch', '0') == '1') { ?>
		<div class="djc_layout_switch djc_clearfix">
			<?php echo $this->loadTemplate('layoutswitch'); ?>
		</div>
	<?php }*/ ?>
		<?php echo $this->loadTemplate('listing_toolbar'); ?>
	</div>
</div>

<?php 
$onDJC2BeforeItemsList = JEventDispatcher::getInstance()->trigger('onDJC2BeforeItemsList', array (&$this->items, &$this->params)); 
if ($onDJC2BeforeItemsList) { ?>
<div class="djc_clearfix djc_pre_list">
	<?php echo trim(implode("\n", $onDJC2BeforeItemsList)); ?>
</div>
<?php } ?>

<?php /* WARNING - do not remove nor duplicate .djc_result_set container */ ?>
<div class="djc_result_set">
	<?php if (count($this->items) > 0){ ?>
		<div class="djc_items djc_clearfix djc_listing_<?php echo $this->params->get('list_layout','items'); ?>">
			<?php echo $this->loadTemplate($this->params->get('list_layout','items')); ?>
		</div>
	<?php } ?>
	
	<?php if ($this->total < 1 && $this->params->get('product_catalogue') == '0' && $this->params->get('show_zero_results_info', false)) {?>
	<p class="alert alert-info"><?php echo JText::_('COM_DJCATALOG2_NO_ITEMS_FOUND');?></p>
	<?php } ?>
</div>

<?php
$onDJC2AfterItemsList = JEventDispatcher::getInstance()->trigger('onDJC2AfterItemsList', array (&$this->items, &$this->params)); 
if ($onDJC2AfterItemsList) { ?>
<div class="djc_clearfix djc_post_list">
	<?php echo trim(implode("\n", $onDJC2AfterItemsList)); ?>
</div>
<?php } ?>

<?php /* WARNING - do not remove nor duplicate .djc_pagination_set container */ ?>
<div class="djc_pagination_set">
	<?php if ($this->pagination->total > 0 && $this->pagination->total > $this->pagination->limit) { ?>
	<div class="djc_pagination pagination djc_clearfix">
	<?php
		echo $this->pagination->getPagesLinks();
	?>
	</div>
	<?php } ?>

<?php if (false) { ?>
	<form method="post" action="<?php echo JURI::getInstance()->toString(); ?>">
		<?php 
			$default_limit =  $this->params->get('limit_items_show', 10);
			$selected =  JFactory::getApplication()->input->get( 'limit', $default_limit, 'int' );
			
			$limits = array();
			
			// Make the option list.
			for ($i = $default_limit; $i <= 100; $i*=2)
			{
				$limits[] = JHtml::_('select.option', "$i");
			}
			
			$html = JHtml::_(
				'select.genericlist',
				$limits,
				$this->prefix . 'limit',
				'class="inputbox input-mini" size="1" onchange="this.form.submit()"',
				'value',
				'text',
				$selected
			);

			echo $html;
		?>
	</form>
<?php } ?>
</div>

<?php if ( in_array('category', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'bottom' && $this->params->get('social_code', '') != '') { ?>
	<div class="djc_clearfix djc_social_b">
		<?php echo $this->params->get('social_code'); ?>
	</div>
<?php } ?>

<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>

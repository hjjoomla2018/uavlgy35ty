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
<?php if ($this->params->get( 'show_page_heading', 1) /*&& ($this->params->get( 'page_heading') != @$this->item->name)*/) : ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<div id="djcatalog" class="djc_archived_list djc_list<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

<?php if (!empty($this->feedlink) && $this->params->get('rss_feed_icon', 0) == '1' && $this->params->get('rss_enabled', '1') == '1' && !($this->params->get('showcatdesc') && $this->item && $this->item->id > 0)) { ?>
	<a class="djc_rss_link" href="<?php echo $this->feedlink; ?>"><img alt="RSS" src="<?php echo DJCatalog2ThemeHelper::getThemeImage('rss_icon.png')?>" /></a>
<?php } ?>

<?php 
	if (count($this->items) > 0 && ($this->params->get('show_category_orderby') > 0 || $this->params->get('show_producer_orderby') > 0 || $this->params->get('show_name_orderby') > 0 || $this->params->get('show_price_orderby') > 0 || count($this->sortables) > 0)) { ?>
	<div class="djc_order djc_clearfix">
		<?php echo $this->loadTemplate('order'); ?>
	</div>
<?php } ?>

<?php 
	if (count($this->items) > 0 && $this->params->get('show_layout_switch', '0') == '1') { ?>
	<div class="djc_layout_switch djc_clearfix">
		<?php echo $this->loadTemplate('layoutswitch'); ?>
	</div>
<?php } ?>

<?php if (count($this->items) > 0){ ?>
	<div class="djc_items djc_clearfix">
		<?php echo $this->loadTemplate($this->params->get('list_layout','items')); ?>
	</div>
<?php } ?>
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

<?php if ( in_array('category', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'bottom' && $this->params->get('social_code', '') != '') { ?>
	<div class="djc_clearfix djc_social_b">
		<?php echo $this->params->get('social_code'); ?>
	</div>
<?php } ?>

<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>

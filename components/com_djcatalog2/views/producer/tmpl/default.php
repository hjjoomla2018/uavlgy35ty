<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access'); ?>

<?php if ($this->params->get( 'show_page_heading', 1) /*&& ($this->params->get( 'page_heading') != @$this->item->name)*/) : ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<div id="djcatalog" class="djc_clearfix djc_producer<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">
	
	<?php if ( in_array('producer', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'top' && $this->params->get('social_code', '') != '') { ?>
		<div class="djc_clearfix djc_social_t">
			<?php echo $this->params->get('social_code'); ?>
		</div>
	<?php } ?>

	<?php if ($this->item->images = DJCatalog2ImageHelper::getImages('producer',$this->item->id)) {
		echo $this->loadTemplate('images'); 
	} ?>
	
	<?php if (!$this->params->get( 'show_page_heading', 1) || $this->params->get('page_heading') != $this->item->name) {?>
		<h2 class="djc_title"><?php echo $this->item->name; ?></h2>
	<?php } ?>
	<?php if ( in_array('producer', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'aft_title' && $this->params->get('social_code', '') != '') { ?>
		<div class="djc_clearfix djc_social_at">
			<?php echo $this->params->get('social_code'); ?>
		</div>
	<?php } ?>
	
	<?php if ($this->params->get('show_producers_items_item', 1)) { ?>
		<a <?php if (JFactory::getApplication()->input->get('tmpl') == 'component') echo 'target="_blank"'; ?> class="djc_producer_items_link btn btn-mini" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute(0).'&cm=0&pid='.$this->item->id.':'.$this->item->alias); ?>"><span><?php echo JText::_('COM_DJCATALOG2_SHOW_PRODUCERS_ITEMS'); ?></span></a>
        <?php } ?>
    <div class="djc_description">
    	<?php if (JString::strlen(trim($this->item->description)) > 0) {?>
		<div class="djc_fulltext">
			<?php echo JHTML::_('content.prepare', $this->item->description, $this->params, 'com_djcatalog2.producer.description'); ?>
		</div>
		<?php } ?>
		<?php if (isset($this->item->tabs)) { ?>
            	<div class="djc_clear"></div>
            	<div class="djc_tabs">
            		<?php echo $this->item->tabs; ?>
            	</div>
            <?php } ?>
            
        <?php if ( in_array('producer', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'aft_desc' && $this->params->get('social_code', '') != '') { ?>
			<div class="djc_clearfix djc_social_ad">
				<?php echo $this->params->get('social_code'); ?>
			</div>
		<?php } ?>
    </div>
    
    <?php if ( in_array('producer', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'bottom' && $this->params->get('social_code', '') != '') { ?>
		<div class="djc_clearfix djc_social_b">
			<?php echo $this->params->get('social_code'); ?>
		</div>
	<?php } ?>
    
	<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
	?>
</div>
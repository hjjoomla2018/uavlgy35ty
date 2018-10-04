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
<?php //if ($this->params->get( 'show_page_heading', 1) && $this->params->get('page_heading')) : ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php //endif; ?>

<div id="djcatalog" class="djc_list<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

<?php if (count($this->items) > 0){ ?>
	<div class="djc_orders djc_producers djc_clearfix">
		<?php echo $this->loadTemplate('table'); ?>
	</div>
<?php } else { ?>
<p class="djc_empty_orders"><?php echo JText::_('COM_DJCATALOG2_QUESTIONS_LIST_IS_EMPTY'); ?></p>
<?php } ?>
<?php if ($this->pagination->total > 0) { ?>
<div class="djc_pagination pagination djc_clearfix">
<?php
	echo $this->pagination->getPagesLinks();
?>
</div>
<?php } ?>

<?php if ( in_array('producers', $this->params->get('social_code_views',array())) && $this->params->get('social_code_position','top') == 'bottom' && $this->params->get('social_code', '') != '') { ?>
	<div class="djc_clearfix djc_social_b">
		<?php echo $this->params->get('social_code'); ?>
	</div>
<?php } ?>

<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>

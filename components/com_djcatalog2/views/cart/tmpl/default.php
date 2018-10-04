<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');

$user = JFactory::getUser();
?>

<?php if ($this->params->get('cart_enabled', '1') == '1') { ?>
<div class="djc_checkout_progress djc_clearfix <?php echo (JFactory::getUser()->guest && !($this->params->get('cart_registered', '1') == '0' && $this->params->get('cart_skip_login', '0') == '1')) ? 'steps-3' : 'steps-2'?>">
	<div class="djc_checkout-step djc_checkout-step-1 active">
		<a href="<?php echo JRoute::_(DJCatalog2HelperRoute::getCartRoute()); ?>"><?php echo JText::_('COM_DJCATALOG2_CHECKOUT_STEP_CART');?></a>
	</div>
	<?php if ( !($this->params->get('cart_registered', '1') == '0' && $this->params->get('cart_skip_login', '0') == '1') ) { ?>
	<div class="djc_checkout-step djc_checkout-step-2">
		<span><?php echo JText::_('COM_DJCATALOG2_CHECKOUT_STEP_CART_LOGIN');?></span>
	</div>
	<?php } ?>
	<div class="djc_checkout-step djc_checkout-step-3">
		<span><?php echo JText::_('COM_DJCATALOG2_CHECKOUT_STEP_CART_CHECKOUT');?></span>
	</div>
</div>
<?php } ?>

<?php //if ($this->params->get( 'show_page_heading', 1)) { ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php //} ?>

<div id="djcatalog" class="djc_cart<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

<?php if (count($this->items) > 0){ ?>
	<div class="djc_cart djc_clearfix">
		<?php echo $this->loadTemplate('table2'); ?>
	</div>
<?php } else { ?>
<p class="djc_empty_cart"><?php echo JText::_('COM_DJCATALOG2_CART_IS_EMPTY'); ?></p>
<?php } ?>

<div class="djc_cart_actions">
<?php if (count($this->items) > 0){ ?>
	<?php echo $this->loadTemplate('coupon'); ?>

	<?php if ($this->params->get('cart_query_enabled', '1') == '1') { ?>
		<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getQueryRoute());?>" method="post">
			<input type="submit" value="<?php echo JText::_('COM_DJCATALOG2_PROCEED_TO_CONTACT_FORM');?>" class="btn button btn-success djc_query_btn" />
			<input type="hidden" name="option" value="com_djcatalog2" />
			<input type="hidden" name="task" value="cart.query" />
			<?php echo JHtml::_( 'form.token' ); ?>
		</form>
	<?php } ?>
	
	<?php if ($this->params->get('cart_enabled', '1') == '1') { ?>
	<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getCheckoutRoute());?>" method="post">
		<input type="submit" value="<?php echo JText::_('COM_DJCATALOG2_CONFIRM_CART');?>" class="btn button btn-success djc_checkout_btn" />
		<input type="hidden" name="option" value="com_djcatalog2" />
		<input type="hidden" name="task" value="cart.checkout" />
		<?php echo JHtml::_( 'form.token' ); ?>
	</form>
	<?php } ?>
<?php } ?>
</div>

<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>

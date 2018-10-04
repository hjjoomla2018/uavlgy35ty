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
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.tooltip');

?>

<div class="djc_checkout_progress djc_clearfix <?php echo JFactory::getUser()->guest ? 'steps-3' : 'steps-2'?>">
	<div class="djc_checkout-step djc_checkout-step-1 passed">
		<a href="<?php echo JRoute::_(DJCatalog2HelperRoute::getCartRoute()); ?>"><?php echo JText::_('COM_DJCATALOG2_CHECKOUT_STEP_CART');?></a>
	</div>
	<div class="djc_checkout-step djc_checkout-step-2 active">
		<a href="<?php echo JRoute::_(DJCatalog2HelperRoute::getCartRoute().'&layout=login'); ?>"><?php echo JText::_('COM_DJCATALOG2_CHECKOUT_STEP_CART_LOGIN');?></a>
	</div>
	<div class="djc_checkout-step djc_checkout-step-3">
		<a href="<?php echo JRoute::_(DJCatalog2HelperRoute::getCheckoutRoute()); ?>"><?php echo JText::_('COM_DJCATALOG2_CHECKOUT_STEP_CART_CHECKOUT');?></a>
	</div>
</div>

<?php //if ($this->params->get( 'show_page_heading', 1)) { ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php //} ?>

<div id="djcatalog" class="djc_cart-login<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">
	<div class="row-fluid">
		<div class="span6">
			<div class="djc_cart_login_container">
				<h3><?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_FORM_LOGIN'); ?></h3>
				<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="login-form" class="form-inline">
				<div class="userdata">
					<div id="form-login-username" class="control-group">
						<div class="control-group">
							<div class="control-label hidden">
								<label for="modlgn-username"><?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_VALUE_USERNAME') ?></label>
							</div>
							<div class="controls">
								<input id="modlgn-username" type="text" name="username" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_VALUE_USERNAME') ?>" />
							</div>
						</div>
					</div>
					<div id="form-login-password" class="control-group">
						<div class="controls-group">
							<div class="control-label hidden">
								<label for="modlgn-passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
							</div>
							<div class="controls">
								<input id="modlgn-passwd" type="password" name="password" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" />
							</div>
						</div>
					</div>
					<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
					<div id="form-login-remember" class="control-group checkbox">
						<label for="modlgn-remember" class="control-label"><?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_REMEMBER_ME') ?></label> <input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
					</div>
					<?php endif; ?>
					<div id="form-login-submit" class="control-group">
						<div class="controls">
							<button type="submit" tabindex="0" name="Submit" class="btn btn-primary"><?php echo JText::_('JLOGIN') ?></button>
						</div>
					</div>
					<?php
						$usersConfig = JComponentHelper::getParams('com_users'); ?>
						<ul class="unstyled">
							<li>
								<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind&Itemid=' . UsersHelperRoute::getRemindRoute()); ?>">
								<?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
							</li>
							<li>
								<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset&Itemid=' . UsersHelperRoute::getResetRoute()); ?>">
								<?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
							</li>
						</ul>
					<input type="hidden" name="option" value="com_djcatalog2" />
					<input type="hidden" name="task" value="user_login" />
					<input type="hidden" name="return" value="<?php echo base64_encode(DJCatalog2HelperRoute::getCheckoutRoute()); ?>" />
					<?php echo JHtml::_('form.token'); ?>
				</div>
			</form>
		</div>
		</div>
		<div class="span6">
			<div class="djc_cart_registration_container">
				<h3><?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_FORM_REGISTER'); ?></h3>
				<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="registration-form" class="form">
					<div class="control-group">
						<div class="controls"><?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_FORM_REGISTER_INFO');?></div>
					</div>
					<div id="form-login-submit" class="control-group">
						<div class="controls">
							<button type="submit" tabindex="0" name="Submit" class="btn btn-primary"><?php echo JText::_('COM_DJCATALOG2_CART_REGISTRATION_BTN') ?></button>
						</div>
					</div>
					<input type="hidden" name="option" value="com_djcatalog2" />
					<input type="hidden" name="task" value="user_register" />
					<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_(DJCatalog2HelperRoute::getCheckoutRoute() , false)); ?>" />
					<?php echo JHtml::_('form.token'); ?>
				</form>
			</div>
			
			<?php if ($this->params->get('cart_registered', '1') == '0') {?>
			<div class="djc_cart_guestcheckout_container">
			<h3><?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_CHECKOUT_AS_GUEST'); ?></h3>
			<p>
				<a class="btn btn-primary" href="<?php echo JRoute::_(DJCatalog2HelperRoute::getCheckoutRoute());?>"><?php echo JText::_('COM_DJCATALOG2_CART_LOGIN_CHECKOUT_AS_GUEST_BTN'); ?></a>
			</p>
			</div>
			<?php } ?>
		</div>
	</div>
</div>

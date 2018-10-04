<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');
$user = JFactory::getUser();
$app = JFactory::getApplication();
$return_url = base64_encode(DJCatalogHelperRoute::getCheckoutRoute());

$app->setUserState('com_users.edit.profile.redirect', DJCatalogHelperRoute::getCheckoutRoute());
$editBilling = (bool)$app->input->getInt('billing', false);

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.tooltip');

$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2') || $user->authorise('core.admin', 'com_djcatalog2');

?>

<div class="djc_checkout_progress djc_clearfix <?php echo (JFactory::getUser()->guest && !($this->params->get('cart_registered', '1') == '0' && $this->params->get('cart_skip_login', '0') == '1')) ? 'steps-3' : 'steps-2'; ?>">
	<div class="djc_checkout-step djc_checkout-step-1 passed">
		<a href="<?php echo JRoute::_(DJCatalog2HelperRoute::getCartRoute()); ?>"><?php echo JText::_('COM_DJCATALOG2_CHECKOUT_STEP_CART');?></a>
	</div>
	<?php if ( !($this->params->get('cart_registered', '1') == '0' && $this->params->get('cart_skip_login', '0') == '1') ) { ?>
	<div class="djc_checkout-step djc_checkout-step-2 passed">
		<?php if (JFactory::getUser()->guest) {?>
		<a href="<?php echo JRoute::_(DJCatalog2HelperRoute::getCartRoute().'&layout=login'); ?>"><?php echo JText::_('COM_DJCATALOG2_CHECKOUT_STEP_CART_LOGIN');?></a>
		<?php } else {?>
		<a href="<?php echo JRoute::_('index.php?option=com_users&view=profile&layout=edit&return='.$return_url); ?>"><?php echo JText::_('COM_DJCATALOG2_EDIT_PROFILE'); ?></a>
		<?php } ?>
	</div>
	<?php } ?>
	<div class="djc_checkout-step djc_checkout-step-3 active">
		<span><?php echo JText::_('COM_DJCATALOG2_CHECKOUT_STEP_CART_CHECKOUT');?></span>
	</div>
</div>

<?php //if ($this->params->get( 'show_page_heading', 1)) { ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php //} ?>
<div id="djcatalog" class="djc_checkout<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

<?php if (count($this->items) > 0) { ?>
	<h2><?php echo JText::_('COM_DJCATALOG2_ORDER_ITEMS'); ?></h2>
	<div class="djc_cart djc_cart_checkout djc_clearfix">
		<?php echo $this->loadTemplate('table'); ?>
	</div>
<?php }  ?>

<div class="djc_checkout_form">
<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getCheckoutRoute());?>" method="post" class="form-validate form" id="djc_checkout_form">
	<fieldset class="djc_checkout_notes">
		<?php $deliveryFields = $this->form->getFieldset('delivery'); ?>
		<?php //if (!empty($deliveryFields)) {?>
		<div class="row-fluid">
			<div class="span<?php echo (!empty($deliveryFields)) ? 6: 12; ?>">
				<div class="djc_billing_details" id="djc_billing_wrapper">
					<h2 id="bilform"><?php echo JText::_('COM_DJCATALOG2_USER_BILLING_HEADING'); ?></h2>
					
					<?php if ( false /* no longer used */  /*!$editBilling && (!empty($this->user_profile) && !empty($this->user_profile->id) && $this->user_valid)*/) { ?>
					<div class="djc_checkout_header">
						<div class="pull-right djc_billing_buttons">
							<?php if (!$salesman) {?>
							<a class="btn" href="<?php echo JRoute::_('index.php?option=com_users&view=profile&layout=edit&return='.$return_url); ?>"><?php echo JText::_('COM_DJCATALOG2_EDIT_PROFILE'); ?></a>
							<?php } ?>
							<?php if ((!empty($this->user_profile) && !empty($this->user_profile->id) && $this->user_valid)) { ?>
							<a class="btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCheckoutRoute().'&billing=1#bilform'); ?>"><?php echo JText::_('COM_DJCATALOG2_USE_OTHER'); ?></a>
							<?php } ?>
						</div>
						<?php if ((!empty($this->user_profile) && !empty($this->user_profile->id) && $this->user_valid)) { ?>
							<p>
							<?php if ($this->user_profile->company) { ?>
								<strong><?php echo $this->user_profile->company?></strong><br />
							<?php }?>
							<strong><?php echo $this->user_profile->firstname.' '.$this->user_profile->lastname; ?></strong><br />
							<?php echo $this->user_profile->postcode.', '.$this->user_profile->city; ?><br />
							<?php echo $this->user_profile->address; ?>
							<br />
							<?php if ($this->user_profile->vat_id) {
							   echo JText::_('COM_DJCATALOG2_UP_VATID').': '.$this->user_profile->vat_id; 
							}?>
							</p>
						<?php } ?>
					</div>
					<?php } else if (true /*$editBilling || (empty($this->user_profile) || empty($this->user_profile->id) || $this->user_valid == false)*/) { ?>
						<?php /*if ($user->guest) { ?>
							<p class="djc_login_link">
							<?php 
							
							$login_url = JRoute::_('index.php?option=com_users&view=login&return='.$return_url);
							echo JText::sprintf('COM_DJCATALOG2_CLICK_TO_LOGIN', $login_url);
							?>
							</p>
						<?php }*/ ?>
						<?php 
						$fields = $this->form->getFieldset('basicprofile'); 
						foreach ($fields as $field) { ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
								<div class="controls">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php } ?>
					<?php } ?>
					
				</div>
			</div>
			<?php if (!empty($deliveryFields)) {?>
			<div class="span6">
				<div class="djc_delivery_form" id="djc_delivery_wrapper">
					<h2><?php echo JText::_('COM_DJCATALOG2_DELIVERY_DETAILS_HEADING'); ?></h2>
					<?php $deliveryToggle = $this->form->getField('delivery_to_billing', 'djcatalog2delivery');?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $deliveryToggle->label; ?>
						</div>
						<div class="controls">
							<?php echo $deliveryToggle->input; ?>
						</div>
					</div>
					<div id="djc_delivery_fields">
					<?php foreach ($deliveryFields as $field) { ?>
						<?php if ($field->fieldname == 'delivery_to_billing') continue; ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php } ?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php //} ?>
		
		<?php $deliveryPaymentFields = $this->form->getFieldset('delivery_payment'); ?>
		<?php if (!empty($deliveryPaymentFields)) {?>
		<div class="djc_orderdetails">
			<div class="row-fluid">
				<div class="span6">
					<h2><?php echo JText::_('COM_DJCATALOG2_DELIVERY_OPTIONS_HEADING'); ?></h2>
					<?php echo $this->form->getControlGroup('delivery_method_id', 'djcatalog2orderdetails');?>
				</div>
				<div class="span6">
					<h2><?php echo JText::_('COM_DJCATALOG2_PAYMENT_OPTIONS_HEADING'); ?></h2>
					<?php echo $this->form->getControlGroup('payment_method_id', 'djcatalog2orderdetails');?>
				</div>
			</div>
			
			
			<?php if (!empty($this->delivery_info) || !empty($this->payment_info)) { ?>
			<div class="row-fluid">
				<div class="span6">
					<div class="djc_delivery_extra_data">
					<?php if (!empty($this->delivery_info)) {
						foreach ($this->delivery_info as $deliveryId => $deliveryPlg) {
							if (!empty($deliveryPlg)) { ?>
								<div id="djc_delivery_details-<?php echo $deliveryId; ?>" data-id="<?php echo $deliveryId; ?>" class="djc_delivery_details" style="display: none">
									<?php echo implode('', $deliveryPlg); ?>
								</div>
							<?php }
						}   
					} ?>
					</div>
				</div>
				<div class="span6">
					<div class="djc_payment_extra_data">
					<?php if (!empty($this->payment_info)) {
						foreach ($this->payment_info as $paymentId => $paymentPlg) {
							if (!empty($paymentPlg)) { ?>
								<div id="djc_payment_details-<?php echo $paymentId; ?>" data-id="<?php echo $paymentId; ?>" class="djc_payment_details" style="display: none">
									<?php echo implode('', $paymentPlg); ?>
								</div>
							<?php }
						}   
					} ?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
		
		<div class="row-fluid">
			<div class="span6">
				<div class="djc_order_totals">
					<h2><?php echo JText::_('COM_DJCATALOG2_ORDER_SUMMARY'); ?></h2>
					<div id="djc_ordersummary">
						<table width="100%" cellpadding="0" cellspacing="0">
							<thead></thead>
							<tfoot>
								<tr>
									<td class="text-left djc_label"><strong><?php echo JText::_('COM_DJCATALOG2_ORDER_SUM_TOTAL'); ?></strong></td>
									<td class="text-left value"><strong><span id="djc_summary_total"><?php echo DJCatalog2HtmlHelper::formatPrice($this->basket->total['gross'], $this->params, false); ?></span></strong></td>
								</tr>
							</tfoot>
							<tbody>
								<tr>
									<td class="text-left djc_label"><?php echo JText::_('COM_DJCATALOG2_ORDER_SUM_PRODUCTS'); ?></td>
									<td class="text-left value"><span id="djc_summary_gross"><?php echo DJCatalog2HtmlHelper::formatPrice($this->basket->product_total['gross'], $this->params, false); ?></span></td>
								</tr>
								<tr>
									<td class="text-left djc_label"><?php echo JText::_('COM_DJCATALOG2_ORDER_SUM_DELIVERY'); ?></td>
									<td class="text-left value"><span id="djc_summary_delivery"><?php 
									if ($this->basket->delivery) {
										echo DJCatalog2HtmlHelper::formatPrice($this->basket->delivery->_prices['total']['gross'], $this->params, false);   
									} else {
										echo DJCatalog2HtmlHelper::formatPrice(0, $this->params, false);
									}
									?></span></td>
								</tr>
								<tr>
									<td class="text-left djc_label"><?php echo JText::_('COM_DJCATALOG2_ORDER_SUM_PAYMENT'); ?></td>
									<td class="text-left value"><span id="djc_summary_payment"><?php 
									if ($this->basket->payment) {
										echo DJCatalog2HtmlHelper::formatPrice($this->basket->payment->_prices['total']['gross'], $this->params, false);	
									} else {
										echo DJCatalog2HtmlHelper::formatPrice(0, $this->params, false);
									}
									?></span></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="span6">
				<?php foreach ($this->form->getFieldsets() as $fieldset) { ?>
					<?php if ($fieldset->name != 'basicprofile' && $fieldset->name != 'delivery' && $fieldset->name != 'delivery_payment') { ?>
						<?php $fields = $this->form->getFieldset($fieldset->name);?>
						<?php if (count($fields)>0) { ?>
							<div class="djc_order_additional form-horizontal">
							<?php if ($fieldset->label) { ?>
							<h2><?php echo JText::_($fieldset->label); ?></h2>
							<?php } ?>
							<?php foreach($fields as $field) { ?>
								<?php if ($field->hidden) { ?>
									 <?php echo $field->input;?>
								<?php } /*else if ($field->fieldname == 'customer_note') { ?>
									<div class="control-group">
										<div class="controls">
											<div style="display: none;"><?php echo $field->label; ?></div>
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php }*/ else { ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input;?>
										</div>
									 </div>
								<?php }?>
							<?php } ?>
							</div>
						<?php } ?>
					<?php } ?>
				 <?php } ?>
			</div>
		</div>
		
		<div class="row-fluid">
			<div class="span12">
				<div class="control-group djc_order_submit" id="djc_order_submit">
					<a class="button btn djc_back_to_cart_btn btn-primary" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute());?>"><span><?php echo JText::_('COM_DJCATALOG2_BACK_TO_CART'); ?></span></a>
					<input type="submit" value="<?php echo JText::_('COM_DJCATALOG2_CONFIRM_CHECKOUT');?>" class="btn btn-success button validate" />
				</div>
			</div>
		</div>
	</fieldset>
	
	
	<input type="hidden" name="option" value="com_djcatalog2" />
	<input type="hidden" name="task" value="cart.confirm" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>
<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>
<script type="text/javascript">
	<?php /*?>
	var DJC2Checkout = {
		init : function() {
			this.request = new Request({
				url: '<?php echo JUri::base(false).'index.php?option=com_djcatalog2&task=getUserData&format=raw'; ?>',
				link: 'cancel',
				onRequest: function(){
					},
				onSuccess: function (responseText, responseXML) {
					var jsonObj = JSON.decode(responseText);
					},
				onFailure: function(xhr) {
					}
			});
		},
		getUserData : function(user_id){
			window.location.href = '<?php echo JUri::base(false).'index.php?option=com_djcatalog2&task=cart.selectUser'; ?>' + '&user_id=' + user_id;
			//this.request.send('user_id=' + user_id);
		}
	};

	DJC2Checkout.init();
	<?php */ ?>
</script>

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

$user = JFactory::getUser();

$return_url = base64_encode(DJCatalogHelperRoute::getCheckoutRoute());
?>

<?php //if ($this->params->get( 'show_page_heading', 1)) { ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php //} ?>

<div id="djcatalog" class="djc_checkout<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

<?php if (!empty($this->user_profile) && !empty($this->user_profile->id) && $this->user_valid) { ?>
<div class="djc_checkout_header">
	<h2><?php echo JText::_('COM_DJCATALOG2_USER_PROFILE'); ?></h2>
	<p>
	<?php if ($this->user_profile->company) { ?>
		<strong><?php echo $this->user_profile->company?></strong><br />
	<?php }?>
	<?php if (!empty($this->user_profile->_name)) {?>
	<strong><?php echo $this->user_profile->_name; ?></strong><br />
	<?php } ?>
	<?php 
		$addr = array();
		if (!empty($this->user_profile->address)) {
			$addr['address'] = $this->user_profile->address;
		}
		if (!empty($this->user_profile->postcode)) {
			$addr['postcode'] = $this->user_profile->postcode;
		}
		if (!empty($this->user_profile->city)) {
			$addr['city'] = $this->user_profile->city;
		}
		if (!empty($this->user_profile->country_name)) {
			$addr['country'] = $this->user_profile->country_name;
		}
		if (!empty($this->user_profile->state_name)) {
			$addr['state'] = $this->user_profile->state_name;
		}
		if (count($addr) > 0) {
			echo implode(', ', $addr).'<br />';
		}
	?>
	
	</p>
</div>
<?php } ?>

<?php if (count($this->items) > 0) { ?>
	<h2><?php echo JText::_('COM_DJCATALOG2_CART_HEADING'); ?></h2>
	<div class="djc_cart djc_cart_checkout djc_clearfix">
		<?php echo $this->loadTemplate('table'); ?>
	</div>
<?php }  ?>

<div class="djc_checkout_form">
<form action="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute());?>" method="post" class="form-validate form form-horizontal">
	<fieldset class="djc_checkout_notes">
	
		<?php if (empty($this->user_profile) || empty($this->user_profile->id) || $this->user_valid == false) { ?>
			<h2><?php echo JText::_('COM_DJCATALOG2_USER_PROFILE'); ?></h2>
			<?php if ($user->guest) { ?>
				<p class="djc_login_link">
				<?php 
				$login_url = JRoute::_('index.php?option=com_users&view=login&return='.$return_url);
				echo JText::sprintf('COM_DJCATALOG2_CLICK_TO_LOGIN', $login_url);
				?>
				</p>
			<?php } ?>
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
		
		<?php //Dynamically load any additional fields from plugins. ?>
	    <?php foreach ($this->form->getFieldsets() as $fieldset) { ?>
	          <?php if ($fieldset->name != 'basicprofile') { ?>
	               <?php $fields = $this->form->getFieldset($fieldset->name);?>
	               
	               <?php if ($fieldset->name == 'message') {$fieldset->label = 'COM_DJCATALOG2_QUERY_NOTES';}?>
	               
	               <?php if ($fieldset->label && count($fields)) {?>
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
	          <?php } ?>
	     <?php } ?>
		
		<div class="control-group">
			<div class="controls">
				<a class="btn djc_back_to_cart_btn btn-primary" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCartRoute());?>"><span><?php echo JText::_('COM_DJCATALOG2_BACK_TO_CART'); ?></span></a>
				<input type="submit" value="<?php echo JText::_('COM_DJCATALOG2_CONFIRM_QUERY');?>" class="btn btn-success validate" />
			</div>
		</div>
		
	</fieldset>
	
	
	<input type="hidden" name="option" value="com_djcatalog2" />
	<input type="hidden" name="task" value="cart.query_confirm" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>
<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>

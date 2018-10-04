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

$app = JFactory::getApplication();

if (isset($this->error)) { ?>
	<div class="djc_contact-error">
		<?php echo $this->error; ?>
	</div>
<?php } ?>

<?php if ($this->getLayout() != 'contact' || !$app->input->get('success')) {?>
<div class="djc_contact_form">
	<form id="djc_contact_form" action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-validate <?php if ($this->getLayout() == 'contact') echo 'form-horizontal'; ?>">
		<fieldset>
			<legend>
				<?php echo JText::_('COM_DJCATALOG2_FORM_LABEL'); ?>
				<?php if ($this->getLayout() == 'contact') {?>
					<br /><small><?php echo $this->item->name; ?></small>
				<?php } ?>
			</legend>
				<div class="control-group">
					<div class="control-label"><?php echo $this->contactform->getLabel('contact_name'); ?></div>
					<div class="controls"><?php echo $this->contactform->getInput('contact_name'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->contactform->getLabel('contact_email'); ?></div>
					<div class="controls"><?php echo $this->contactform->getInput('contact_email'); ?></div>
				</div>
				
				<?php if ((int)$this->params->get('contact_company_name_field', '0') > 0) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->contactform->getLabel('contact_company_name'); ?></div>
						<div class="controls"><?php echo $this->contactform->getInput('contact_company_name'); ?></div>
					</div>
				<?php } ?>
				<?php if ((int)$this->params->get('contact_street_field', '0') > 0) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->contactform->getLabel('contact_street'); ?></div>
						<div class="controls"><?php echo $this->contactform->getInput('contact_street'); ?></div>
					</div>
				<?php } ?>
				<?php if ((int)$this->params->get('contact_city_field', '0') > 0) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->contactform->getLabel('contact_city'); ?></div>
						<div class="controls"><?php echo $this->contactform->getInput('contact_city'); ?></div>
					</div>
				<?php } ?>
				<?php if ((int)$this->params->get('contact_country_field', '0') > 0) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->contactform->getLabel('contact_country'); ?></div>
						<div class="controls"><?php echo $this->contactform->getInput('contact_country'); ?></div>
					</div>
				<?php } ?>
				<?php if ((int)$this->params->get('contact_state_field', '0') > 0) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->contactform->getLabel('contact_state'); ?></div>
						<div class="controls"><?php echo $this->contactform->getInput('contact_state'); ?></div>
					</div>
				<?php } ?>
				<?php if ((int)$this->params->get('contact_zip_field', '0') > 0) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->contactform->getLabel('contact_zip'); ?></div>
						<div class="controls"><?php echo $this->contactform->getInput('contact_zip'); ?></div>
					</div>
				<?php } ?>
				<?php if ((int)$this->params->get('contact_phone_field', '0') > 0) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->contactform->getLabel('contact_phone'); ?></div>
						<div class="controls"><?php echo $this->contactform->getInput('contact_phone'); ?></div>
					</div>
				<?php } ?>
				
				<div class="control-group">
					<div class="control-label"><?php echo $this->contactform->getLabel('contact_subject'); ?></div>
					<div class="controls"><?php echo $this->contactform->getInput('contact_subject'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->contactform->getLabel('contact_message'); ?></div>
					<div class="controls"><?php echo $this->contactform->getInput('contact_message'); ?></div>
				</div>
				<?php if ((int)$this->params->get('contact_gdpr_policy_field', '0') > 0) { ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->contactform->getLabel('contact_gdpr_policy'); ?></div>
					<div class="controls"><?php echo $this->contactform->getInput('contact_gdpr_policy'); ?></div>
				</div>
				<?php } ?>
				<?php if ((int)$this->params->get('contact_gdpr_agreement_field', '0') > 0) { ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->contactform->getLabel('contact_gdpr_agreement'); ?></div>
					<div class="controls"><?php echo $this->contactform->getInput('contact_gdpr_agreement'); ?></div>
				</div>
				<?php } ?>
				<?php if ((int)$this->params->get('contact_email_copy_field', '1') > 0) { ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->contactform->getLabel('contact_email_copy'); ?></div>
					<div class="controls"><?php echo $this->contactform->getInput('contact_email_copy'); ?></div>
				</div>
				<?php } ?>
			<?php //Dynamically load any additional fields from plugins. ?>
			     <?php foreach ($this->contactform->getFieldsets() as $fieldset): ?>
			          <?php if ($fieldset->name != 'contact'):?>
			               <?php $fields = $this->contactform->getFieldset($fieldset->name);?>
			               <?php foreach($fields as $field): ?>
			                    <?php if ($field->hidden): ?>
			                         <?php echo $field->input;?>
			                    <?php else:?>
			                    	<div class="control-group">
			                         <div class="control-label">
			                            <?php echo $field->label; ?>
			                            <?php if (!$field->required && $field->type != "Spacer"): ?>
			                               <span class="optional"><?php echo JText::_('COM_DJCATALOG2_OPTIONAL');?></span>
			                            <?php endif; ?>
			                         </div>
			                         <div class="controls"><?php echo $field->input;?></div>
			                         </div>
			                    <?php endif;?>
			               <?php endforeach;?>
			          <?php endif ?>
			     <?php endforeach;?>
				<div class="controls">
					<button class="btn btn-primary validate" type="submit"><?php echo JText::_('COM_DJCATALOG2_CONTACT_SEND'); ?></button>
					<?php if ($this->getLayout() == 'contact') {?>
						<a id="djc_contact_form_button_close" class="btn" href="<?php echo JRoute::_(DJCatalog2HelperRoute::getItemRoute($this->item->slug, $this->item->catslug));?>"><?php echo JText::_('COM_DJCATALOG2_CONTACT_FORM_CLOSE'); ?></a>
						<input type="hidden" name="layout" value="contact" />
					<?php } else {?>
						<button id="djc_contact_form_button_close" class="btn"><?php echo JText::_('COM_DJCATALOG2_CONTACT_FORM_CLOSE')?></button>
					<?php } ?>
					<input type="hidden" name="option" value="com_djcatalog2" />
					<input type="hidden" name="task" value="item.contact" />
					<input type="hidden" name="id" value="<?php echo $this->item->slug; ?>" />
					<?php echo JHtml::_( 'form.token' ); ?>
				</div>
		</fieldset>
	</form>
</div>
<?php } ?>


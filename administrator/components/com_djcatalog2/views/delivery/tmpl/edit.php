<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

$params = JComponentHelper::getParams('com_djcatalog2');

$net_prices = (bool)((int)$params->get('price_including_tax', 1) == 0);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'delivery.cancel' || document.formvalidator.isValid(document.getElementById('edit-form'))) {
			Joomla.submitform(task, document.getElementById('edit-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=delivery&layout=edit&id='.(int) $this->item->id); ?>"
	method="post" name="adminForm" id="edit-form" class="form-validate"
	enctype="multipart/form-data">
	<fieldset>
		<legend>
		<?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?>
		</legend>
		<div class="row-fluid">
			<div class="span12 form-horizontal">
			
				<ul class="nav nav-tabs">
					<li class="active"><a href="#details" data-toggle="tab"><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></a></li>
					<li ><a href="#params" data-toggle="tab"><?php echo JText::_('COM_DJCATALOG2_PARAMS'); ?></a></li>
				</ul>
				
				<div class="tab-content">
					<div class="tab-pane active" id="details">
						<div class="control-group">
						<div class="control-label">
						<?php echo $this->form->getLabel('name'); ?>
						</div>
						<div class="controls">
						<?php echo $this->form->getInput('name'); ?>
						</div>
					</div>
	
					<div class="control-group">
						<div class="control-label">
						<?php echo $this->form->getLabel('id'); ?>
						</div>
						<div class="controls">
						<?php echo $this->form->getInput('id'); ?>
						</div>
					</div>
	
					<div class="control-group">
						<div class="control-label">
						<?php echo $this->form->getLabel('plugin'); ?>
						</div>
						<div class="controls">
						<?php echo $this->form->getInput('plugin'); ?>
						</div>
					</div>
					
					<div class="control-group">
						<div class="control-label">
						<?php echo $this->form->getLabel('price'); ?>
						</div>
						<div class="controls">
						<?php echo $this->form->getInput('price'); ?>
						</div>
					</div>
					
					<div class="control-group">
						<div class="control-label">
							<label>&nbsp;&nbsp;&raquo;&nbsp;<?php echo JText::_($net_prices ? 'COM_DJCATALOG2_PRICE_INCL_TAX' : 'COM_DJCATALOG2_PRICE_EXCL_TAX')?></label>
						</div>
						<div class="controls">
							<input type="text" class="djc_price_tax readonly inputbox input input-mini" readonly="readonly" id="jform_price_tax" data-type="<?php echo $net_prices ? 'net' : 'gross'?>" value="" />
						</div>
					</div>
					
					<div class="control-group">
						<div class="control-label">
						<?php echo $this->form->getLabel('tax_rule_id'); ?>
						</div>
						<div class="controls">
						<?php echo $this->form->getInput('tax_rule_id'); ?>
						</div>
					</div>
					
					<div class="control-group">
						<div class="control-label">
						<?php echo $this->form->getLabel('free_amount'); ?>
						</div>
						<div class="controls">
						<?php echo $this->form->getInput('free_amount'); ?>
						</div>
					</div>
					
					<div class="control-group">
						<div class="control-label">
						<?php echo $this->form->getLabel('shipping_details'); ?>
						</div>
						<div class="controls">
						<?php echo $this->form->getInput('shipping_details'); ?>
						</div>
					</div>
	
					<div class="control-group">
						<div class="control-label">
						<?php echo $this->form->getLabel('published'); ?>
						</div>
						<div class="controls">
						<?php echo $this->form->getInput('published'); ?>
						</div>
					</div>
					
					<?php echo $this->form->getControlGroup('access'); ?>
					
					<?php echo $this->form->getControlGroup('countries'); ?>
					
					<?php echo $this->form->getControlGroup('postcodes'); ?>
					
					<div class="control-group">
						<div class="control-label">
						<?php echo $this->form->getLabel('description'); ?>
						</div>
						<div class="controls">
						<?php echo $this->form->getInput('description'); ?>
						</div>
					</div>
					</div>
				
				<div class="tab-pane" id="params">
				<?php if (empty($this->item->plugin)) {
					echo JText::_('COM_DJCATALOG2_PLUGIN_SAVE_FIRST');
				} else {
					$fieldSets = $this->form->getFieldsets('params');
					if (empty($fieldSets)) {
						echo JText::_('COM_DJCATALOG2_PLUGIN_EMPTY_CONFIG');
					} else {
						foreach ($fieldSets as $name => $fieldSet) {
							if (isset($fieldSet->description) && trim($fieldSet->description)) {
								echo '<p class="alert alert-info">'.$this->escape(JText::_($fieldSet->description)).'</p>';
							}
							?>
							<?php foreach ($this->form->getFieldset($name) as $field) { ?>
								<div class="control-group">
									<div class="control-label"><?php echo $field->label; ?></div>
									<div class="controls"><?php echo $field->input; ?></div>
								</div>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				</div>
				</div>
			</div>
		</div>
	</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
(function($){
	$(document).ready(function() {
		var djItemPriceInput = $('#jform_price');
		djItemPriceInput.on('keyup change click', function(){
			djValidatePrice(djItemPriceInput);
		});
		
		if ($('#jform_tax_rule_id')) {
			$('#jform_tax_rule_id').change(function(evt) {
				$('#jform_tax_rule_id').trigger("liszt:updated");
				djValidatePrice(djItemPriceInput);
			});
			
			$('#jform_tax_rule_id').trigger('change');
		}
	});

	function djValidatePrice(priceInput) {
			//var r = new RegExp("\,", "i");
			//var t = new RegExp("[^0-9\,\.]+", "i");
			//priceInput.setProperty('value', priceInput.getProperty('value').replace(r, "."));
			//priceInput.setProperty('value', priceInput.getProperty('value').replace(t, ""));
		
		
			var price = priceInput.val();
			
			// valid format
			var valid_price = new RegExp(/^(\d+|\d+\.\d+)$/);
			
			// comma instead of dot
			var wrong_decimal = new RegExp(/\,/g);
			
			// non allowed characters
			var restricted = new RegExp(/[^\d+\.]/g);
			
			// replace comma with a dot
			price = price.replace(wrong_decimal, ".");
			
			if (valid_price.test(price) == false) {
				// remove illegal chars
				price = price.replace(restricted, '');
			}
			
			if (valid_price.test(price) == false) {
				// too many dots in here
				parts = price.split('.');
				if (parts.length > 2 ) {
					price = parts[0] + '.' + parts[1];
				}
			}
			
			priceInput.val(price);
			
			taxInput = $('#' + priceInput.attr('id') + '_tax');
			if(!taxInput.length) {
				return;
			}
			
			rateInput = $('#jform_tax_rule_id');
			
			if (!rateInput.length) {
				return;
			}
			
			var inputType = taxInput.attr('data-type');
			var taxRateOption = rateInput.find('option:selected').first().text();
			
			parser = new RegExp(/.*\[(.+)\]$/);
			
			if (parser.test(taxRateOption)) {
				taxRate = parseFloat(parser.exec(taxRateOption)[1]);
				if (inputType == 'gross') {
					djPriceFromGross(taxInput, price, taxRate);
				} else if (inputType == 'net') {
					djPriceFromNet(taxInput, price, taxRate);
				}
			}
		}

	function djPriceFromGross(element, price, taxrate) {
		price = parseFloat(price);
		taxrate = parseFloat(taxrate);
		if (!price || !(taxrate >= 0)) {
			element.val('');
			return;
		}

		var netPrice = (price * 100) / (100 + taxrate);
		element.val(netPrice.toFixed(2));
	}

	function djPriceFromNet(element, price, taxrate) {
		price = parseFloat(price);
		taxrate = parseFloat(taxrate);

		if (!price || !(taxrate >= 0)) {
			element.val('');
			return;
		}

		var grossPrice = price * ((100 + taxrate)/100) ;
		element.val(grossPrice.toFixed(2));
	}
})(jQuery);
</script>

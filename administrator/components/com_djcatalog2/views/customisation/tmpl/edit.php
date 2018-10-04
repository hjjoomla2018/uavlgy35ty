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
		if (task == 'customisation.cancel' || document.formvalidator.isValid(document.getElementById('customisation-form'))) {
			Joomla.submitform(task, document.getElementById('customisation-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=customisation&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="customisation-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="span12 form-horizontal">
			<fieldset>
			<legend><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></legend>
			
			<?php echo $this->form->getControlGroup('name'); ?>
			<?php echo $this->form->getControlGroup('type'); ?>
			<?php echo $this->form->getControlGroup('price'); ?>
			
			<div class="control-group">
				<div class="control-label">
					<label>&nbsp;&nbsp;&raquo;&nbsp;<?php echo JText::_($net_prices ? 'COM_DJCATALOG2_PRICE_INCL_TAX' : 'COM_DJCATALOG2_PRICE_EXCL_TAX')?></label>
				</div>
				<div class="controls">
					<input type="text" class="djc_price_tax readonly inputbox input input-mini" readonly="readonly" id="jform_price_tax" data-type="<?php echo $net_prices ? 'net' : 'gross'?>" value="" />
				</div>
			</div>
			
			<?php echo $this->form->getControlGroup('tax_rule_id'); ?>
			<?php echo $this->form->getControlGroup('price_modifier'); ?>
			
			<?php echo $this->form->getControlGroup('min_quantity'); ?>
			<?php echo $this->form->getControlGroup('max_quantity'); ?>
			<?php echo $this->form->getControlGroup('id'); ?>
			<?php echo $this->form->getControlGroup('input_params'); ?>
			
		</fieldset>
		
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
	</div>
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
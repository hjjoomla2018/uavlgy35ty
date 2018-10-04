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
//JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$tmpl = $app->input->getString('tmpl');

$params = JComponentHelper::getParams('com_djcatalog2');

$net_prices = (bool)((int)$params->get('price_including_tax', 1) == 0);

?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'query.cancel' || document.formvalidator.isValid(document.getElementById('query-form'))) {
			Joomla.submitform(task, document.getElementById('query-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<div id="djcatalog" class="djc_questionform<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

	<div class="formelm-buttons djc_form_toolbar btn-toolbar">
		<button type="button" onclick="Joomla.submitbutton('questionform.apply')" class="button btn">
			<?php echo JText::_('COM_DJCATALOG2_APPLY') ?>
		</button>
		<?php if ($tmpl == '') {?>
		<button type="button" onclick="Joomla.submitbutton('questionform.save')" class="button btn">
			<?php echo JText::_('COM_DJCATALOG2_SAVE_AND_CLOSE') ?>
		</button>
		<button type="button" onclick="Joomla.submitbutton('questionform.cancel')" class="button btn">
			<?php echo JFactory::getApplication()->input->get('id') > 0 ? JText::_('COM_DJCATALOG2_CANCEL') : JText::_('COM_DJCATALOG2_CLOSE'); ?>
		</button>
		<?php } ?>
	</div>
	
<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=questionform&id='.(int) $this->item->id . ($tmpl != '' ? '&tmpl=component' : '') ); ?>" method="post" name="adminForm" id="query-form" class="form-validate">
	<div class="row-fluid">
		<div class="span6 form-horizontal">
			<fieldset class="adminform">
			<legend><?php echo JText::_('COM_DJCATALOG2_ORDER_FIELDSET_HEADER'); ?></legend>

				<?php 
				$fields = $this->form->getFieldset('header');
				foreach ($fields as $field) { ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
				<?php } ?>
			</fieldset>
		</div>
		<div class="span6 form-horizontal">
			<fieldset class="adminform">
			<legend><?php echo JText::_('COM_DJCATALOG2_ORDER_FIELDSET_CUSTOMER'); ?></legend>

				<?php 
				$fields = $this->form->getFieldset('customer');
				foreach ($fields as $field) { ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
				<?php } ?>

			</fieldset>
		</div>
	</div>
	
	<div class="row-fluid">
	
	<div class="clr"></div>
	
	<div class="span10 form-horizontal">
	    <fieldset class="adminform">
	        <legend><?php echo JText::_( 'COM_DJCATALOG2_ORDER_ITEMS_FIELDSET' ); ?></legend>
	
	        <table class="admintable table table-striped ">
	            <thead>
	                <tr>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_ITEM_ID') ?>
	                    </th>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_NAME') ?>
	                    </th>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_SKU') ?>
	                    </th>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_QUANTITY') ?>
	                    </th>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_PRICE') ?>
	                    </th>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_GROSS_PRICE') ?>
	                    </th>
	                    <th></th>
	                </tr>
	            </thead>
	            <tfoot>
	                <tr>
	                    <td colspan="7">
	                        <hr />
	                    </td>
	                    </tr>
	                <tr>
	                    <td colspan="5"><?php echo JText::_('COM_DJCATALOG2_FOOT_TOTAL') ?></td>
	                    <td>
	                        <input type="text" name="grand_total" id="grand_total" value="<?php echo number_format($this->item->grand_total, 2, '.', '') ?>" class="readonly input input-mini" readonly="readonly" size="10"/>
	                    </td>
	                    <td><span id="order_add" class="button btn"><?php echo JText::_('COM_DJCATALOG2_ADD_NEW'); ?></span></td>
	                </tr>
	            </tfoot>
	            <tbody id="quote_items">
	            <?php 
	            foreach ($this->item->items as $row) { ?>
	                <tr>
	                    <td>
	                        <input name="jform[quote_items][item_id][<?php echo $row->id; ?>]" type="text" value="<?php echo $row->item_id ?>" size="5" disabled="disabled" class="input input-mini" />
	                    	<?php if ($row->item_id > 0 && $row->type='item') {?>
	                    		<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=item&layout=edit&id='.$row->item_id); ?>"><?php echo JText::_('COM_DJCATALOG2_LINK'); ?></a>
	                    	<?php } ?>
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][item_name][<?php echo $row->id; ?>]" type="text" value="<?php echo $row->item_name ?>" size="40"  class="input input-medium"/>
	                        <input name="jform[quote_items][id][<?php echo $row->id; ?>]" type="hidden" value="<?php echo $row->id ?>" disabled="disabled"/>
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][sku][<?php echo $row->id; ?>]" type="text" value="<?php echo $row->sku ?>" size="40" class="input input-mini"/>
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][quantity][<?php echo $row->id; ?>]" type="text" value="<?php echo $row->quantity ?>" size="5" class="calc quantity input input-mini" />
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][price][<?php echo $row->id; ?>]" type="text" value="<?php echo number_format($row->price,2,'.','') ?>" size="10" class="calc basecost input input-mini" />
	                        <input name="jform[quote_items][cost][<?php echo $row->id; ?>]" type="hidden" value="<?php echo number_format($row->price * $row->quantity,2,'.','') ?>" class="calc cost" disabled="disabled"/>
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][total][<?php echo $row->id; ?>]" type="text" value="<?php echo number_format($row->total,2,'.','') ?>" size="10" class="calc total input input-mini" />
	                    </td>
	                    <td>
	                        <span class="order_remove button btn"><?php echo JText::_('COM_DJCATALOG2_REMOVE_ITEM'); ?></span>
	                    </td>
	                </tr>
	                
	                <?php if (trim($row->additional_info) != '') {?>
		                <?php $data = json_decode($row->additional_info); ?>
		                <?php if ($data) {?>
		                	<tr>
		                		<td colspan="9">
		                			<?php if ($row->item_type == 'item') {?>
		                				<ul class="inline list-inline">
			                				<?php foreach($data as $label => $value) {?>
		                					<li>
		                						<strong><?php echo $label; ?></strong>: <?php echo ($value ? $value : '---'); ?>
		                					</li>
			                				<?php } ?>
			                			</ul>
		                			<?php } else if ($row->item_type == 'customisation') {?>
		                				<ul class="unstyled list-unstyled">
			                				<?php foreach($data as $customisation_info) {?>
		                					<li>
		                						<strong><?php echo $customisation_info->name; ?></strong><br />
		                						<?php if (empty($customisation_info->value)) {?>
		                							<?php echo '---'; ?>
		                						<?php } else {?>
		                							<?php if ($customisation_info->type == 'text') { ?>
			                							<textarea class="readonly" readonly="readonly"><?php echo nl2br($customisation_info->value); ?></textarea>
			                						<?php } else if ($customisation_info->type == 'file') {?>
			                							<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=download_file&path='.base64_encode($customisation_info->value->fullpath)); ?>"><?php echo JText::_('COM_DJCATALOG2_ACTION_FILE_DOWNLOAD'); ?>: <?php echo $customisation_info->value->caption; ?></a>
			                						<?php } ?>
		                						<?php } ?>
		                					</li>
			                				<?php } ?>
			                			</ul>
		                			<?php } ?>
		                		</td>
		                	</tr>
		                <?php } ?>
	                <?php } ?>
	                
	                <?php if (trim($row->combination_info) != '') { ?>
		                <?php $data = json_decode($row->combination_info); ?>
		                <?php if ($data) {?>
		                	<tr>
		                		<td colspan="7">
		                			<ul class="inline list-inline">
		                				<?php foreach($data as $combination_info) {?>
	                					<li>
	                						<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=extrafield&layout=edit&id='.$combination_info->field_id);?>"><?php echo $combination_info->field_name; ?></a>: 
	                						<?php echo $combination_info->field_value; ?>
	                					</li>
		                				<?php } ?>
		                			</ul>
		                		</td>
		                	</tr>
		                <?php } ?>
	                <?php } ?>
	            <?php } ?>
	            
	                <tr id="order_row_pattern" style="display:none">
	                    <td>
	                        <input name="jform[quote_items][item_id][]" type="text" value="" size="5" class="input input-mini"/>
	                    </td>
	                     <td>
	                        <input name="jform[quote_items][item_name][]" type="text" value="" size="40" class="input input-medium" />
	                        <input name="jform[quote_items][id][]" type="hidden" value="" />
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][sku][]" type="text" value="" size="40" class="input input-mini" />
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][quantity][]" type="text" value="0" size="5" class="calc quantity input input-mini" />
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][base_cost][]" type="text" value="" size="10" class="calc basecost input input-mini"/>
	                        <input name="jform[quote_items][cost][]" type="hidden" value="" class="calc cost"/>
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][total][]" type="text" value="" size="10" class="calc total input input-mini"/>
	                    </td>
	                    <td>
	                        <span class="order_remove button btn"><?php echo JText::_('COM_DJCATALOG2_REMOVE_ITEM'); ?></span>
	                    </td>
	                </tr>
	            
	            </tbody>
	        </table>
	    </fieldset>
	</div>
	
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	
	</div>
	</form>
</div>

<script type="text/javascript">
    function invoiceRecalculate(e) {
        var src = jQuery(e.target);
        var parent = src.parents('tr');
        
        var quantity, basecost, cost, tax, taxrate, total;
        
        parent.find('input.calc').each(function(i, e){
            var el = jQuery(e);
            if (el.hasClass('quantity')) {
                quantity = el;
            } else if (el.hasClass('basecost')) {
                basecost = el;
            } else if (el.hasClass('cost')) {
                cost = el;
            } else if (el.hasClass('taxrate')) {
                taxrate = el;
            } else if (el.hasClass('tax')) {
                tax = el;
            } else if (el.hasClass('total')) {
                total = el;
            } else {
                console.log(el.className);
            }
        });
        
        var r = new RegExp("\,", "i");
        var t = new RegExp("[^0-9\,\.]+", "i");
        src.attr('value', src.attr('value').replace(r, "."));
        src.attr('value', src.attr('value').replace(t, ""));
        
        if (src.hasClass('quantity')) {
            new_cost = (parseFloat(basecost.val()) * parseFloat(quantity.val()));
            if (isNaN(new_cost)) return;
            new_cost = new_cost.toFixed(4);
            cost.val(new_cost);

            new_total = parseFloat(new_cost);
            if (isNaN(new_total)) return;
            new_total = new_total.toFixed(4);
            total.val(new_total);
            
        } else if (src.hasClass('basecost')) {
            new_cost = (parseFloat(basecost.val()) * parseFloat(quantity.val()));
            if (isNaN(new_cost)) return;
            new_cost = new_cost.toFixed(4);
            cost.val(new_cost);
            
            new_total = parseFloat(new_cost);
            if (isNaN(new_total)) return;
            new_total = new_total.toFixed(4);
            total.val(new_total);
            
        } else if (src.hasClass('taxrate')) {
            new_tax = parseFloat(cost.val()) * parseFloat(taxrate.val());
            if (isNaN(new_tax)) return;
            new_tax = new_tax.toFixed(4);
            tax.val(new_tax);
            
            new_total = parseFloat(cost.val()) + parseFloat(new_tax);
            if (isNaN(new_total)) return;
            new_total = new_total.toFixed(4);
            total.val(new_total);
            
        } else if (src.hasClass('total')) {
            new_cost = parseFloat(total.val());
            if (isNaN(new_cost)) return;
            new_cost = new_cost.toFixed(4);
            cost.val(new_cost);
            
            new_basecost = parseFloat(new_cost) / parseFloat(quantity.val());
            if (isNaN(new_basecost)) return;
            new_basecost = new_basecost.toFixed(4);
            basecost.val(new_basecost);
        }
        
        invoiceRecalculateTotal();         
    }
    
    function invoiceRecalculateTotal() {
        var base_total = 0.0;
        var tax_total = 0.0;
        var grand_total = 0.0;

        jQuery('#quote_items').find('input.calc').each(function(i,e){
            var el = jQuery(e);
            var toAdd = parseFloat(el.val());
            if (isNaN(toAdd)) return;
            
            if (el.hasClass('quantity')) {
                
            } else if (el.hasClass('basecost')) {
            } else if (el.hasClass('cost')) {
                base_total += toAdd;
            } else if (el.hasClass('taxrate')) {
            } else if (el.hasClass('tax')) {
                tax_total += toAdd;
            } else if (el.hasClass('total')) {
                grand_total += toAdd;
            } else {
                console.log(el.className);
            }
        });

        jQuery('#baseprice_total').val(base_total.toFixed(4));
        jQuery('#tax_total').val(tax_total.toFixed(4));
        jQuery('#grand_total').val(grand_total.toFixed(4));
        return true;
    }
    
    function invoiceAddRow(e) {
        e.preventDefault();
        
        var copy = jQuery('#order_row_pattern').clone();
        copy.appendTo('#quote_items');
        copy.css('display', '');
        copy.find('span.order_remove').on('click', function(evt) {
            evt.preventDefault();
            var src = jQuery(this);
            var parent = src.parents('tr');
            parent.remove();
            invoiceRecalculateTotal();
        });
        
        var inputs = copy.find('input.calc');
        inputs.on('change', function(e){
            invoiceRecalculate(e); 
        });
        
        inputs.on('keyup', function(e){
            invoiceRecalculate(e); 
        });
        
        return false;
    }
    

jQuery(document).ready(function(){
	var body = jQuery(document.body);
	var table = jQuery('#quote_items');
	var inputs = table.find('input.calc');
	
    table.find('input').each(function(i, input){
    	jQuery(input).removeAttr('disabled');
    });
    
    table.find('span.order_remove').each(function(i,el){
    	jQuery(el).on('click', function(e) {
            e.preventDefault();
            var src = jQuery(e.target);
            var parent = src.parents('tr');
            parent.remove();
            
            invoiceRecalculateTotal();
        });
    });
    
    jQuery('#order_add').on('click', invoiceAddRow);


    inputs.on('change', function(e){
        invoiceRecalculate(e); 
    });

    inputs.on('keyup', function(e){
        invoiceRecalculate(e); 
    });

    invoiceRecalculateTotal();

    /** Delivery & payment prices **/

    jQuery('#jform_delivery_tax_rate, #jform_delivery_price, #jform_payment_tax_rate, #jform_payment_price').on('change click keyup', function(evt){
		var value = jQuery(this).val();
		
        var validNo = new RegExp(/^(\d+|\d+\.\d+)$/);
		var semiValidNo = new RegExp(/^\d+|\.$/);
		var wrongDec = new RegExp(/\,/g);
		var restricted = new RegExp(/[^\d+\.]/g);
		
		value = value.replace(wrongDec, ".");

		if (validNo.test(value) == false) {
			if (evt.type != 'keyup' || semiValidNo.test(value) == false) {
				value = value.replace(restricted, '');
			}
		}

		jQuery(this).val(value);

		if (value == '' || isNaN(value)) {
			value = 0.0000;
		}

		switch( jQuery(evt.target).attr('id') ) {
			case 'jform_delivery_price' : {
				var tax = parseFloat(jQuery('#jform_delivery_tax_rate').val()) * parseFloat(value);
				var total = tax + parseFloat(value);
				jQuery('#jform_delivery_tax').val(tax.toFixed(4));
				jQuery('#jform_delivery_total').val(total.toFixed(4));
				break;
			}
			case 'jform_payment_price' : {
				var tax = parseFloat(jQuery('#jform_payment_tax_rate').val()) * parseFloat(value);
				var total = tax + parseFloat(value);
				jQuery('#jform_payment_tax').val(tax.toFixed(4));
				jQuery('#jform_payment_total').val(total.toFixed(4));
				break;
			}
			case 'jform_delivery_tax_rate' : {
				var tax = parseFloat(jQuery('#jform_delivery_price').val()) * parseFloat(value);
				var total = tax + parseFloat(parseFloat(jQuery('#jform_delivery_price').val()));
				jQuery('#jform_delivery_tax').val(tax.toFixed(4));
				jQuery('#jform_delivery_total').val(total.toFixed(4));
				break;
			}
			case 'jform_payment_tax_rate' : {
				var tax = parseFloat(jQuery('#jform_payment_price').val()) * parseFloat(value);
				var total = tax + parseFloat(jQuery('#jform_payment_price').val());
				jQuery('#jform_payment_tax').val(tax.toFixed(4));
				jQuery('#jform_payment_total').val(total.toFixed(4));
				break;
			}
		}
    });
});
  
</script>


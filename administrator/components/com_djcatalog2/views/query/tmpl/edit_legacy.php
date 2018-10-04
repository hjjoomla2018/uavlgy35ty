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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$params = JComponentHelper::getParams('com_djcatalog2');

$net_prices = (bool)((int)$params->get('price_including_tax', 1) == 0);

?>
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

<form
	action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=query&layout=edit&id='.(int) $this->item->id); ?>"
	method="post" name="adminForm" id="query-form" class="form-validate"
	enctype="multipart/form-data">
	
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_DJCATALOG2_QUERY_FIELDSET_CUSTOMER'); ?></legend>
			<ul class="adminformlist">
				<?php 
				$fields = $this->form->getFieldset('customer');
				foreach ($fields as $field) { ?>
				<?php 
					if ($field->fieldname == 'user_id') { ?>
					<?php 
					$value = '('.$field->value.')';
					$customer = JFactory::getUser($field->value);
					if (!empty($customer) && $customer->id > 0) {
						$value .= ' '.$customer->name.' / '.$customer->username;
					} else if ($field->value == 0 ){
						$value = '-';
					}
					?>
					<li><?php echo $field->title; ?>: <strong><?php echo $value; ?></strong></li>
				<?php } else { ?>
					<li><?php echo $field->title; ?>: <strong><?php echo $field->value; ?></strong></li>
				<?php } ?>
				<?php } ?>

			</ul>

		</fieldset>

	</div>
	
	<div class="width-40 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_DJCATALOG2_QUERY_FIELDSET_HEADER'); ?></legend>

			<ul class="adminformlist">
				<?php 
				$fields = $this->form->getFieldset('header');
				foreach ($fields as $field) { ?>
				<li><?php echo $field->title; ?>: <strong><?php echo $field->value; ?></strong></li>
				<?php } ?>

			</ul>

		</fieldset>
	</div>
	
	<div class="clr"></div>
	
	<div class="width-100">
	    <fieldset class="adminform">
	        <legend><?php echo JText::_( 'COM_DJCATALOG2_QUERY_ITEMS_FIELDSET' ); ?></legend>
	
	        <table class="admintable ordertable">
	            <thead>
	                <tr>
	                    <th width="5%">
	                        <?php echo JText::_('COM_DJCATALOG2_ITEM_ID') ?>
	                    </th>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_NAME') ?>
	                    </th>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_SKU') ?>
	                    </th>
	                    <th width="10%">
	                        <?php echo JText::_('COM_DJCATALOG2_QUANTITY') ?>
	                    </th>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_PRICE') ?>
	                    </th>
	                    <th>
	                        <?php echo JText::_('COM_DJCATALOG2_TOTAL') ?>
	                    </th>
	                </tr>
	            </thead>
	            <tbody id="quote_items">
	            <?php 
	            foreach ($this->item->items as $row) { ?>
	                <tr>
	                    <td>
	                        <input name="jform[quote_items][item_id][<?php echo $row->id; ?>]" type="text" value="<?php echo $row->item_id ?>" size="5" disabled="disabled" readonly="readonly" />
	                    	<a target="_blank" href="<?php echo JUri::root().'index.php?option=com_djcatalog2&view=item&id='.$row->item_id; ?>"><?php echo JText::_('COM_DJCATALOG2_LINK'); ?></a>
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][item_name][<?php echo $row->id; ?>]" type="text" value="<?php echo $row->item_name ?>" size="30" disabled="disabled" readonly="readonly"/>
	                        <input name="jform[quote_items][id][<?php echo $row->id; ?>]" type="hidden" value="<?php echo $row->id ?>" disabled="disabled" readonly="readonly"/>
	                    </td>
	                    <td>
	                    	<input name="jform[quote_items][sku][<?php echo $row->id; ?>]" type="text" value="<?php echo $row->sku ?>" size="40" disabled="disabled" readonly="readonly"/>
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][quantity][<?php echo $row->id; ?>]" type="text" value="<?php echo $row->quantity ?>" size="5" class="calc quantity" disabled="disabled" readonly="readonly"/>
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][price][<?php echo $row->id; ?>]" type="text" value="<?php echo number_format($row->price,2,'.','') ?>" size="10" class="calc basecost input input-mini" disabled="disabled"/>
	                    </td>
	                    <td>
	                        <input name="jform[quote_items][total][<?php echo $row->id; ?>]" type="text" value="<?php echo number_format($row->total,2,'.','') ?>" size="10" class="calc total input input-mini" disabled="disabled"/>
	                    </td>
	                </tr>
	            <?php } ?>
	            </tbody>
	        </table>
	    </fieldset>
	</div>
	
	
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

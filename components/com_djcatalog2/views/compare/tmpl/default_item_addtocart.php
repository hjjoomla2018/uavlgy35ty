<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

// DEPRECATED - check addtocart Layout

defined ('_JEXEC') or die('Restricted access');

$onStock = (bool)( ($this->item_cursor->onstock == 1 && $this->item_cursor->stock > 0) || $this->item_cursor->onstock == 2 );
$canCheckout = (bool)($this->params->get('cart_enabled', false) && $onStock  && $this->item_cursor->final_price > 0.0);
$canQuery = (bool)($this->params->get('cart_query_enabled', 1));

if (($canCheckout || $canQuery) && $this->item_cursor->available) {
	$return_url = base64_encode(JUri::getInstance()->__toString());
	$button_value = $canCheckout ? JText::_('COM_DJCATALOG2_ADD_TO_CART') : JText::_('COM_DJCATALOG2_ADD_TO_QUOTE_CART');
	$button_class = $canCheckout ? 'djc_addtocart_btn' : 'djc_addtoquote_btn';
	
	$results = JFactory::getApplication()->triggerEvent('onDJCatalog2BeforeCart', array($this->item_cursor, $this->params, 'items.'.$this->params->get('list_layout','items')));
	foreach($results as $html){
		echo $html;
	}
	
	?>
	<div class="djc_addtocart">
		<form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="djc_form_addtocart">
			<?php 
				$unit = DJCatalog2HelperQuantity::getUnit($this->item_cursor->unit_id); 
				echo DJCatalog2HelperQuantity::renderInput($unit, $this->item_cursor, array('show_box'=>false, 'cart_button'=>array('type'=>'input', 'value' => $button_value, 'class' => 'btn btn-primary '.$button_class, 'attributes' => '')));
				?>
			<input type="hidden" name="option" value="com_djcatalog2" />
			<input type="hidden" name="task" value="cart.add" />
			<input type="hidden" name="return" value="<?php echo $return_url; ?>" />
			<input type="hidden" name="item_id" value="<?php echo (int)$this->item_cursor->id; ?>" />
			<?php echo JHtml::_( 'form.token' ); ?>
		</form>
	</div>
<?php } ?>
<?php if ($this->params->get('cart_enabled', false) && !$onStock) { ?>
<div class="djc_stock_info djc_addtocart">
	<button disabled="disabled" class="btn btn-mini"><?php echo JText::_('COM_DJCATALOG2_PRODUCT_OUT_OF_STOCK'); ?></button>
</div>
<?php } ?>

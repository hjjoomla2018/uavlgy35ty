<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

require_once(JPATH_ROOT.'/components/com_djcatalog2/helpers/quantity.php');

$item = $displayData['item'];
$params = $displayData['params'];
$context = isset($displayData['context']) ? $displayData['context'] : 'com_djcatalog2.addtocart';
$multiForm = isset($displayData['multi_form']) ? (bool)$displayData['multi_form'] : false;

$onStock = (bool)( ($item->onstock == 1 && $item->stock > 0) || $item->onstock == 2 );
$canCheckout = (bool)($params->get('cart_enabled', false) && $onStock  && $item->final_price > 0.0);
$canQuery = (bool)($params->get('cart_query_enabled', 1));

$button_value = $canCheckout ? JText::_('COM_DJCATALOG2_ADD_TO_CART') : JText::_('COM_DJCATALOG2_ADD_TO_QUOTE_CART');
$button_class = $canCheckout ? 'djc_addtocart_btn' : 'djc_addtoquote_btn';

if (isset($item->combo_count) && $item->combo_count) { ?>
	<div class="djc_has_combinations djc_addtocart">
		<a class="btn" href="<?php echo JRoute::_($item->_link);?>"><?php echo JText::_('COM_DJCATALOG2_ADD_TO_CART_CHOOSE_VARIANT'); ?></a>
	</div>
<?php } else {
	if (($canCheckout || $canQuery) && $item->available) {
		$return_url = base64_encode(JUri::getInstance()->__toString());
		
		$results = JFactory::getApplication()->triggerEvent('onDJCatalog2BeforeCart', array($item, $params, $context));
		foreach($results as $html){
			echo $html;
		}
		
		?>
		<div class="djc_addtocart">
			<form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="djc_form_addtocart <?php echo $multiForm ? 'djc_multi_addtocart' : '';?>">
				<?php 
				$unit = DJCatalog2HelperQuantity::getUnit($item->unit_id); 
				$options = array();
				if ($multiForm) {
					$options = array(
						'allow_empty' => true,
						'value' => 0
					);
				} else {
					$options = array(
						'cart_button'=>
						array('type'=>'input',
							 'value' => $button_value,
							 'class' => 'btn btn-primary '.$button_class,
							 'attributes' => ''
						 )
					);
				}
				echo DJCatalog2HelperQuantity::renderInput($unit, $item, $options);
				?>
				<input type="hidden" name="option" value="com_djcatalog2" />
				<input type="hidden" name="task" value="cart.add" />
				<input type="hidden" name="return" value="<?php echo $return_url; ?>" />
				<input type="hidden" name="item_id" value="<?php echo (int)$item->id; ?>" />
				<?php echo JHtml::_( 'form.token' ); ?>
			</form>
		</div>
	<?php } ?>
	<?php if ($params->get('cart_enabled', false) && !$onStock) { ?>
	<div class="djc_stock_info djc_addtocart">
		<button disabled="disabled" class="btn"><?php echo JText::_('COM_DJCATALOG2_PRODUCT_OUT_OF_STOCK'); ?></button>
	</div>
	<?php } ?>
<?php }

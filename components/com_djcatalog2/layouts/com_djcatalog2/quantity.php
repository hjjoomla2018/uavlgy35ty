<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access');

$unit = $displayData['unit'];
$item = $displayData['item'];
$options = $displayData['options'];

$name = (isset($options['name'])) ? $options['name'] : 'quantity';
$class = (isset($options['class'])) ? $options['class'].' djc_qty_input' : 'djc_qty_input input input-mini';
$readonly = (bool)(!empty($options['readonly']));
$disabled = (bool)(!empty($options['disabled']));
$value = (isset($options['value'])) ? $options['value'] : null;

$allowEmpty = (bool)(isset($options['allow_empty']) ? $options['allow_empty'] : false);
if (!$allowEmpty) {
	$value = ($value && $value >= $unit->min_quantity) ? $value : $unit->min_quantity;
} else {
	$unit->min_quantity = 0;
}

if ($value !== null) {
	$value = ($unit->is_int) ? intval($value) : (floatval($value) + 0);
}

// reducing number of zeroes after dec point
$precision = 0;
if (!$unit->is_int) {
	$min = abs(min($unit->min_quantity, $unit->step));
	if ($min < 1) {
		while ( ($min < 1 || (int)$min != $min) && $precision < 4 ) {
			$min *= 10;
			$precision++;
		}
	}
}

$size = (isset($options['size'])) ? (int)$options['size'] : false;

if (!$size) {
	$maxLen = 3;
	if ($unit->max_quantity >= 100) {
		$base = $unit->max_quantity;
		while($base > 99) {
			$maxLen++;
			$base /= 10;
		}
	} else if ($unit->max_quantity < 10 && $unit->max_quantity > 0) {
		$maxLen = 1;
	}
	if ($precision > 0) {
		$maxLen += ($precision + 1);
	}
	$size = $maxLen;
}

$attrs = ' data-type="'.($unit->is_int ? 'int' : 'flo').'"';
$attrs .= ' data-min="'.($unit->is_int ? intval($unit->min_quantity) : floatval($unit->min_quantity)).'"';
$attrs .= ' data-max="'.($unit->is_int ? intval($unit->max_quantity) : floatval($unit->max_quantity)).'"';
$attrs .= ' data-step="'.($unit->is_int ? $unit->step : ($unit->step + 0)).'"';
$attrs .= ' data-precision="'.$precision.'"';
$attrs .= ' data-unit="'.htmlspecialchars($unit->unit, ENT_COMPAT, 'UTF-8').'"';

if ($size) {
	$attrs .= ' size="'.$size.'" maxLength="'.$size.'"';
}
if ($allowEmpty) {
	$attrs .= ' data-allowempty="1" ';
}
if (!empty($options['attributes'])) {
	$attrs .= ' '.$options['attributes'];
}
if ($readonly) {
	$class .= ' readonly';
	$attrs .= ' readonly="readonly"';
}
if ($disabled) {
	$class .= ' disabled';
	$attrs .= ' disabled="disabled"';
}

// Bootstrap markup
$displayUnit = (isset($options['show_unit'])) ? (bool)$options['show_unit'] : $unit->show_unit;
$displayBtns = (isset($options['show_buttons'])) ? (bool)$options['show_buttons'] : $unit->show_buttons;
if ($disabled) {
	$displayBtns = false;
}
$displayBox = (isset($options['show_box'])) ? $options['show_box'] : $unit->show_box;
$cartBtn = '';

if (!empty($options['cart_button']) && is_array($options['cart_button'])) {
	$btnValue = isset($options['cart_button']['value']) ? $options['cart_button']['value'] : JText::_('COM_DJCATALOG2_ADD_TO_CART');
	$btnType = isset($options['cart_button']['type']) ? $options['cart_button']['type'] : 'input';
	$btnClass = isset($options['cart_button']['class']) ? $options['cart_button']['class'] : 'btn btn-primary';
	$btnAttrs = isset($options['cart_button']['attributes']) ? $options['cart_button']['attributes'] : '';
	
	if ($btnType == 'input') {
		$cartBtn = '<input type="submit" value="'.$btnValue.'" class="'.$btnClass.'" '.trim($btnAttrs).' />';
	} else if ($btnType == 'button') {
		$cartBtn = '<button type="submit" class="'.$btnClass.'" '.trim($btnAttrs).'>'.$btnValue.'</button>';
	}
}

if (!empty($options['remove_button'])) {
	$cartBtn .= $options['remove_button'];
}
?>

<?php if ($displayBox) { ?>
	<div class="btn-group">
		<div class="djc_qty input-append input-prepend">
		
		<?php if ($displayBtns) { ?>
			<span data-toggle="dec" class="btn djc_qty_btn djc_qty_dec">&minus;</span>
		<?php } ?>
		
		<input type="text" name="<?php echo $name; ?>" class="<?php echo $class; ?>" value="<?php echo $value; ?>" <?php echo trim($attrs); ?> />
		
		<?php if ($displayUnit) { ?>
			<span class="add-on"><?php echo $unit->unit; ?></span>
		<?php } ?>
		
		<?php if ($displayBtns) { ?>
			<span data-toggle="inc" class="btn djc_qty_btn djc_qty_inc">&#43;</span>
		<?php } ?>
		
		<?php echo $cartBtn; ?>
		
		</div>
	</div>
	
<?php } else { ?>
	<?php echo $cartBtn; ?>
	<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
<?php }



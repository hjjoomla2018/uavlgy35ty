<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
*/

defined ('_JEXEC') or die('Restricted access');

$field = $this->variant_field_cursor;

$jsonData = json_encode($field->_variantData);
?>

<div class="control-group">
	<div class="control-label"><label><?php echo $this->escape($field->name); ?></label></div>
	<div class="controls">
		<?php if ($field->type == 'color' || $field->type == 'multicolor') {?>
			<fieldset>
				<div class="djc_cartvariant_colors" data-fieldid="<?php echo $field->id; ?>" data-combinations='<?php echo $jsonData; ?>'>
					<?php $idx = 0;?>
					<?php foreach ($field->_variantData->options as $option) { ?>
						<?php if (!in_array($option, $field->_variantData->availableOptions)) { continue; } ?>
						
						<?php 
						$optionCombinations = isset($field->_variantData->optionCombinations[$option]) ? $field->_variantData->optionCombinations[$option] : array();
						$style = '';
						$optionParams = $field->optionParams[$option];
						if ($optionParams->get('hexcode')) {
							$style .= 'background-color: ' . $optionParams->get('hexcode').';';
						}
						if ($optionParams->get('file_name')) {
							$style .= 'background-image: url(\''.JUri::base(true) .'/media/djcatalog2/images/colors/'. $optionParams->get('file_name').'\');';
						}
						if ($style == '') {
							$style = 'background-color: #ccc; background-image: url(\''.JUri::base(true) .'/components/com_djcatalog2/themes/'.$this->params->get('theme', 'default').'/images/icon-no-color.png\')';
						}
						?>
						<label for="<?php echo 'djc_cart_field-'.$field->id.'-'.$idx; ?>" class="radio">
							<input type="radio" value="<?php echo $option; ?>"  name="cart_field[<?php echo $field->id; ?>]" id="djc_cart_field-<?php echo $field->id.'-'.$idx; ?>" data-fieldid="<?php echo $field->id; ?>" data-fieldoption="<?php echo $option; ?>" data-optioncombinations='<?php echo json_encode($optionCombinations); ?>' />
							<span class="djc_cartvariant_color">
								<span class="djc_cartvariant_color-bg" style="<?php echo $style; ?>">
								</span> 
								<span class="djc_cartvariant_color-name"><?php echo $field->optionValues[$option]; ?></span>
							</span>
						</label>
						<?php $idx++; ?>
					<?php } ?>
				</div>
			</fieldset>
		<?php } else { ?>
			<select name="cart_field[<?php echo $field->id; ?>]" id="djc_cart_field-<?php echo $field->id; ?>" data-fieldid="<?php echo $field->id; ?>" data-combinations='<?php echo $jsonData; ?>'>
				<option value="" data-fieldid="<?php echo $field->id; ?>" data-fieldoption="" data-optioncombinations=''><?php echo JText::sprintf('COM_DJCATALOG2_OPTION_SELECT', $field->name); ?></option>
				<?php foreach ($field->_variantData->options as $option) { ?>
					<?php if (!in_array($option, $field->_variantData->availableOptions)) { continue; } ?>
					<?php $optionCombinations = isset($field->_variantData->optionCombinations[$option]) ? $field->_variantData->optionCombinations[$option] : array(); ?>
					<option value="<?php echo $option?>" data-fieldid="<?php echo $field->id; ?>" data-fieldoption="<?php echo $option; ?>" data-optioncombinations='<?php echo json_encode($optionCombinations); ?>'><?php echo $field->optionValues[$option]; ?></option>
				<?php } ?>
			</select>
		<?php } ?>
	</div>
</div>
				
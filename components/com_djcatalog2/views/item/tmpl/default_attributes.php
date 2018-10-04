<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access'); 
?>
<?php

$item = $this->item_cursor;

$attribute = $this->attribute_cursor; 
$attributeName = '_ef_'.$attribute->alias; 
?>
<?php if (isset($this->item_cursor->$attributeName) && (is_array($this->item_cursor->$attributeName) || (is_scalar($this->item_cursor->$attributeName) && trim($this->item_cursor->$attributeName) != ''))) { ?>
<tr class="djc_attribute djc<?php echo $attributeName; ?>">
	<td class="djc_label">
	<?php 
		if ($attribute->imagelabel != '') {
			echo '<img class="djc_attribute-imglabel" alt="'.htmlspecialchars($attribute->name).'" src="'.JURI::base().$attribute->imagelabel.'" />';
		} else {
			echo '<span class="djc_attribute-label">'.htmlspecialchars($attribute->name).'</span>';
		} 
	?>
	</td>
	<td  class="djc_value">
	<?php 
	if (is_array($this->item_cursor->$attributeName) && $attribute->type != 'multicolor' && $attribute->type != 'color'){
			$this->item_cursor->$attributeName = implode(', ', $this->item_cursor->$attributeName);
		}
		if ($attribute->type == 'textarea' || $attribute->type == 'text'){
			$value = nl2br(htmlspecialchars($this->item_cursor->$attributeName));
			// convert URLs
			$value = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', $value);
			// convert emails
			$value = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', $value);
			echo $value;
		}
		else if ($attribute->type == 'html') {
			echo $this->item_cursor->$attributeName;
		}
		else if ($attribute->type == 'calendar') {
			echo JHtml::_('date', $this->item_cursor->$attributeName, JText::_('DATE_FORMAT_LC4'));
		}
		else if ($attribute->type == 'multicolor' || $attribute->type == 'color') {
			$optionParamsName = '_efp_' . $attribute->alias;
			if (!empty($item->$optionParamsName)) { ?>
				<?php foreach ($item->$optionParamsName as $optId => $optionParams) {?>
					<?php 
					$style = '';
					$optionName = '';
					foreach ($item->$attributeName as $optId2 => $optName) {
						if ($optId2 == $optId) {
							$optionName = $optName;
							break;
						}
					}
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
					<span class="djc_cartattr_color">
						<span class="djc_cartattr_color-bg" style="<?php echo $style; ?>">
						</span> 
						<span class="djc_cartattr_color-name"><?php echo $optionName; ?></span>
					</span>
				<?php } ?>
			<?php } else {
				echo htmlspecialchars(implode(', ', $item->$attributeName));
			}
		}
		else {
			echo htmlspecialchars($this->item_cursor->$attributeName);
		}	
	?>
	</td>
</tr>
<?php } ?>
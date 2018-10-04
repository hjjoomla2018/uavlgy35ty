<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access'); 

$item = $displayData['item'];
$attribute = $displayData['attribute'];
$params = $displayData['params'];

$attributeName = '_ef_'.$attribute->alias; 

$output =  '';

if (is_array($item->$attributeName) && $attribute->type != 'multicolor' && $attribute->type != 'color'){
	$item->$attributeName = implode(', ', $item->$attributeName);
}
if ($attribute->type == 'textarea' || $attribute->type == 'text'){
	$value = nl2br(htmlspecialchars($item->$attributeName));
	// convert URLs
	$value = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', $value);
	// convert emails
	$value = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', $value);
	$output .= $value;
}
else if ($attribute->type == 'html') {
	$output .= $item->$attributeName;
}
else if ($attribute->type == 'calendar') {
	$output .= JHtml::_('date', $item->$attributeName, JText::_('DATE_FORMAT_LC4'));
}
else if ($attribute->type == 'multicolor' || $attribute->type == 'color') {
	$optionParamsName = '_efp_' . $attribute->alias;
	if (!empty($item->$optionParamsName)) {
		foreach ($item->$optionParamsName as $optId => $optionParams) {
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
				$style = 'background-color: #ccc; background-image: url(\''.JUri::base(true) .'/components/com_djcatalog2/themes/'.$params->get('theme', 'default').'/images/icon-no-color.png\')';
			}
			$output .= '<span class="djc_cartattr_color">';
			$output .= '<span class="djc_cartattr_color-bg" style="'.$style.'">';
			$output .= '</span>';
			$output .= '<span class="djc_cartattr_color-name">'.$optionName.'</span>';
			$output .= '</span>';
		}
	} else {
		$output .= htmlspecialchars(implode(', ', $item->$attributeName));
	}
}
else {
	$output .= htmlspecialchars($item->$attributeName);
}

echo $output;
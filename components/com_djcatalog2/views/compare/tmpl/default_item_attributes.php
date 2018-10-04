<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access'); 

?>
<?php
$item = $this->item_cursor;

$attribute = $this->attribute_cursor; 
$attributeName = '_ef_'.$attribute->alias; 
?>
<?php if (isset($item->$attributeName) && (is_array($item->$attributeName) || (is_scalar($item->$attributeName) && trim($item->$attributeName) != ''))) { ?>
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
		if (is_array($item->$attributeName)){
			$item->$attributeName = implode(', ', $item->$attributeName);
		}
		if ($attribute->type == 'textarea' || $attribute->type == 'text'){
			$value = nl2br(htmlspecialchars($item->$attributeName));
			// convert URLs
			$value = preg_replace('#([\w]+://)([^\s()<>]+)#iS', '<a target="_blank" href="$1$2">$2</a>', $value);
			// convert emails
			$value = preg_replace('#([\w.-]+(\+[\w.-]+)*@[\w.-]+)#i', '<a target="_blank" href="mailto:$1">$1</a>', $value);
			echo $value;
		}
		else if ($attribute->type == 'html') {
			echo $item->$attributeName;
		} 
		else if ($attribute->type == 'calendar') {
			echo JHtml::_('date', $item->$attributeName, JText::_('DATE_FORMAT_LC4'));
		}
		else {
			echo htmlspecialchars($item->$attributeName);
		}	
	?>
	</td>
</tr>
<?php } ?>
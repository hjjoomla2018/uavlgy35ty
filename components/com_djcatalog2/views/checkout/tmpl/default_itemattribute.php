<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

$attribute = $this->attribute_cursor;
$item = $this->item_cursor;
$attribute_values = $this->attribute_values;
$attribute->field_value = '';

if (is_array($attribute_values) && isset($attribute_values[$attribute->id])) {
	$attribute->field_value = $attribute_values[$attribute->id];
}

if (!empty($attribute->field_value)) {
	echo $attribute->field_value;
}

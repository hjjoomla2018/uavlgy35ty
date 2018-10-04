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
<?php if (isset($item->$attributeName) && (is_array($item->$attributeName) || (is_scalar($item->$attributeName) && trim($item->$attributeName) != ''))) { ?>
	<div class="djc_value">
	<?php 
	$layout = new JLayoutFile('com_djcatalog2.attributevalue', null, array('component'=> 'com_djcatalog2'));
	echo $layout->render(array('item' => $item, 'attribute' => $attribute, 'params' => $this->params));
	?>
	</div>
<?php } ?>
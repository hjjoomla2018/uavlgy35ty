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
$attributes = $displayData['attributes'];
$context = isset($displayData['context']) ? $displayData['context'] : 'com_djcatalog2.items.extra_fields';
$params = isset($displayData['params']) ? $displayData['params'] : JComponentHelper::getParams('com_djcatalog2');

if (count($attributes) > 0) {
	$attributes_body = '';
	
	foreach ($attributes as $attribute) {
		$attributeName = '_ef_'.$attribute->alias; 
		
		if (isset($item->$attributeName) && (is_array($item->$attributeName) || (is_scalar($item->$attributeName) && trim($item->$attributeName) != ''))) {
			$attributes_body .= '<tr class="djc_attribute djc'.$attributeName.'">';
			$attributes_body .= '<td class="djc_label">';
			
			$layout = new JLayoutFile('com_djcatalog2.attributelabel', null, array('component'=> 'com_djcatalog2'));
			$attributes_body .= $layout->render(array('attribute' => $attribute, 'params' => $params));
			
			$attributes_body .= '</td>';
			$attributes_body .= '<td  class="djc_value">';
			
			$layout = new JLayoutFile('com_djcatalog2.attributevalue', null, array('component'=> 'com_djcatalog2'));
			$attributes_body .= $layout->render(array('item' => $item, 'attribute' => $attribute, 'params' => $params));
			
		$attributes_body .= '</td>';
		$attributes_body .= '</tr>';
		}
	}

	if ($attributes_body != '') { ?>
		<div class="djc_attributes">
			<table class="table table-condensed">
			<?php echo JHtml::_('content.prepare', $attributes_body, $params, $context); ?>
			</table>
		</div>
		<?php } ?>
<?php } ?>


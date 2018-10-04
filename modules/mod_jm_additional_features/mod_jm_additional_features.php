<?php
/*
 * Copyright (C) joomla-monster.com
 * Website: http://www.joomla-monster.com
 * Support: info@joomla-monster.com
 *
 * JM Additional Features is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JM Additional Features is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JM Additional Features. If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$version = new JVersion;
$jversion = '3';
if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
		$jversion = '2.5';
}

$doc = JFactory::getDocument();

$moduleId = $module->id;
$id = 'jmm-add-features-' . $moduleId;

$data = trim( $params->get('items') );

$json_data = ( !empty($data) ) ? json_decode($data) : false;

if ($json_data === false) {
	echo JText::_('MOD_JM_ADDITIONAL_FEATURES_NO_ITEMS');
	return false;
}

$field_pattern = '#^jform\[params\]\[([a-zA-Z0-9\_\-]+)\]#i';

$output_data = array();
foreach ($json_data as $item) {
	$item_obj = new stdClass();
	foreach($item as $field) {
		if (preg_match($field_pattern, $field->name, $matches)) {
			$attr = $matches[1];
			if (isset($item_obj->$attr)) {
				if (is_array($item_obj->$attr)) {
					$temp = $item_obj->$attr;
					$temp[] = $field->value;
					$item_obj->$attr = $temp;
				} else {
					$temp = array($item_obj->$attr);
					$temp[] = $field->value;
					$item_obj->$attr = $temp;
				}
			} else {
				$item_obj->$attr = $field->value;
			}
		}
	}
	$output_data[] = $item_obj;
}

$elements = count($output_data);

if( $elements < 1 ) {
	echo JText::_('MOD_JM_ADDITIONAL_FEATURES_NO_ITEMS');
	return false;
}

$load_fa = $params->get('load_fontawesome', 0);

if( $load_fa == 1 ) {
	$doc->addStyleSheet('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
}

$theme = $params->get('theme', 1);
$theme_class = ( $theme == 1 ) ? 'default' : 'override';

if( $theme == 1 ) { //default
	$doc->addStyleSheet(JURI::root(true).'/modules/mod_jm_additional_features/assets/default.css');
}

$style = '';
$i_s = 0;

foreach($output_data as $item) {
	$i_s++;
	if( !empty($item->color) ) {
		$style .= '#' . $id . ' .jmm-items .item-' . $i_s . ' .jmm-icon {'
						. 'color: ' . $item->color . ';'
						. '}';
	}
}

if( !empty($style) ) {
	$doc->addStyleDeclaration($style);
}

$mod_class_suffix = $params->get('moduleclass_sfx', '');

$show_readmore = $params->get('show_readmore', 0);
$readmore_name = $params->get('readmore_name', '');
$readmore_url = $params->get('readmore_url', '');

$icon_link = $params->get('icon_link', 1);
$icon_side = $params->get('icon_side', 'left');
$columns = $params->get('columns', 1);

require JModuleHelper::getLayoutPath('mod_jm_additional_features', $params->get('layout', 'default'));

?>

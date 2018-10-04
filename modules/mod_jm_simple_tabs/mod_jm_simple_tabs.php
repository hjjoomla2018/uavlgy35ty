<?php
/*
 * Copyright (C) joomla-monster.com
 * Website: http://www.joomla-monster.com
 * Support: info@joomla-monster.com
 *
 * JM Simple Tabs is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JM Simple Tabs is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JM Simple Tabs. If not, see <http://www.gnu.org/licenses/>.
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
$id = 'jmm-simple-tabs-' . $moduleId;
$tabs_id = 'jmm-tabs' . $moduleId;

$data = $params->get('items');
$json_data = ( !empty($data) ) ? json_decode($data) : false;

if ($json_data === false) {
	echo JText::_('MOD_JM_SIMPLE_TABS_NO_ITEMS');
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
	echo JText::_('MOD_JM_SIMPLE_TABS_NO_ITEMS');
	return false;
}

$load_fa = $params->get('load_fontawesome', 0);

if( $load_fa == 1 ) {
	$doc->addStyleSheet('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
}

$theme = $params->get('theme', 1);
$theme_class = ( $theme == 1 ) ? 'default' : 'override';

if( $theme == 1 ) { //default
	$doc->addStyleSheet(JURI::root(true).'/modules/mod_jm_simple_tabs/assets/default.css');
}

JHtml::_('bootstrap.framework');

$align = $params->get('align', 1);

$video_responsive = $params->get('video_responsive', 0);

$responsive_view = $params->get('responsive_view', 0);

$responsive_breakpoint = $params->get('responsive_breakpoint', 767);

if( $align == 2 ) {
	$align_class = 'tabs-left';
} elseif( $align == 3 ) {
	$align_class = 'tabs-right';
} elseif( $align == 4 ) {
	$align_class = 'tabs-below';
} else {
	$align_class = 'tabs-above';
}

$script = '
	jQuery(document).on(\'click\', \'#' . $tabs_id . ' a\', function (e) {
		e.preventDefault();
		jQuery(this).tab(\'show\');
		var parent = jQuery(this).parent();
		parent.removeClass(\'prev next\');
		parent.siblings().removeClass(\'prev next\');
		parent.prev().addClass(\'prev\');
		parent.next().addClass(\'next\');
	});';

if($responsive_view == 1) {
	$script .= '
		jQuery(document).ready(function() {
			var module = jQuery(\'#' . $tabs_id . '\');
			var align = "' . $align_class . '";
			if(jQuery(window).width() <= ' . $responsive_breakpoint . ') {
				module.addClass(\'nav-tabs-responsive\');
				module.parent().removeClass(align);
			}
			jQuery(window).resize(function() {
				if(jQuery(window).width() <= ' . $responsive_breakpoint . ') {
					module.addClass(\'nav-tabs-responsive\');
					module.parent().removeClass(align);
				} else {
					module.removeClass(\'nav-tabs-responsive\');
					module.parent().addClass(align);
				}
			});
		});';
}

$doc->addScriptDeclaration($script);

$mod_class_suffix = $params->get('moduleclass_sfx', '');

require JModuleHelper::getLayoutPath('mod_jm_simple_tabs', $params->get('layout', 'default'));

?>

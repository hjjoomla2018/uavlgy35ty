<?php
//--------------------------------------------------------------
// Copyright (C) joomla-monster.com
// License: http://www.joomla-monster.com/license.html Joomla-Monster Proprietary Use License
// Website: http://www.joomla-monster.com
// Support: info@joomla-monster.com
//---------------------------------------------------------------

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$version = new JVersion;
$jversion = '3';
if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
		$jversion = '2.5';
}

$moduleId = $module->id;
$id = 'jmm-offcanvas-button-' . $moduleId;

$image = $params->get('image_file', '');
$icon = $params->get('icon', '');

$mod_class_suffix = $params->get('moduleclass_sfx', '');

$app = JFactory::getApplication();
$tpl = $app->getTemplate(true);


if( $tpl->params->get('offCanvas') != 1 ) {
	return;
}

require JModuleHelper::getLayoutPath('mod_jm_offcanvas_button', $params->get('layout', 'default'));

?>

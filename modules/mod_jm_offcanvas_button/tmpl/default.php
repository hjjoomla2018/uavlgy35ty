<?php
//--------------------------------------------------------------
// Copyright (C) joomla-monster.com
// License: http://www.joomla-monster.com/license.html Joomla-Monster Proprietary Use License
// Website: http://www.joomla-monster.com
// Support: info@joomla-monster.com
//---------------------------------------------------------------

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if( !empty($image) ) {
	$output = '<a class="toggle-nav menu"><img src="' . $image . '" alt="' . JText::_('MOD_JM_OFFCANVAS_BUTTON_FIELD_IMAGE_ALT')  . '"></a>';
} elseif( !empty($icon) ) {
	$output = '<a class="toggle-nav menu"><span class="' . $icon . '"></span></a>';
} else {
	$output = '<a class="toggle-nav menu"><span class="icon-align-justify"></span></a>';
}

?>

<div id="<?php echo $id; ?>" class="jmm-offcanvas-button <?php echo $mod_class_suffix; ?>">
<?php echo $output; ?>
</div>



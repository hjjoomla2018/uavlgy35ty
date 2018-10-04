<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */
defined('_JEXEC') or die; 
//Header ("Content-type: text/css");

// Get slideshow parameters
$mid = isset($options['mid']) ? $options['mid'] : $_GET['mid'];
$image_width = isset($options['w']) ? $options['w'] : $_GET['w'];
$image_height = isset($options['h']) ? $options['h'] : $_GET['h'];
$thumb_width = isset($options['tw']) ? $options['tw'] : $_GET['tw'];
$thumb_height = isset($options['th']) ? $options['th'] : $_GET['th'];
$desc_position = isset($options['dp']) ? $options['dp'] : $_GET['dp'];
$desc_width = isset($options['dw']) ? $options['dw'] : $_GET['dw'];
$arrows_top = isset($options['at']) ? $options['at'] : $_GET['at'];
$arrows_horizontal = isset($options['ah']) ? $options['ah'] : $_GET['ah'];
$loader_position = isset($options['lip']) ? $options['lip'] : $_GET['lip'];
if($desc_position == 'over') {
	$desc_bottom = isset($options['db']) ? $options['db'] : $_GET['db'];
	$desc_left = isset($options['dl']) ? $options['dl'] : $_GET['dl'];
}

/* =Skitter styles
 ----------------------------------------------- */
?>
#box_skitter<?php echo $mid ?> {
	width: <?php echo $image_width; ?>px;
	height: <?php echo $image_height; ?>px;
	border: 1px solid #ccc;
	box-shadow: 0 0 5px #666;
}
#box_skitter<?php echo $mid ?> .box_clone img {
	max-width: none !important;
}
<?php /* Slide description area settings */ ?>
#box_skitter<?php echo $mid; ?> .label_skitter {
	position: absolute;
	z-index:150;
	width: <?php echo $desc_width; ?>% !important;
	max-height: <?php echo $image_height; ?>px;
	<?php if($desc_position=='over') { ?>
		bottom: <?php echo $desc_bottom; ?>%;
		left: <?php echo $desc_left; ?>%;
	<?php } else if($desc_position=='left') { ?>
		left: 0;
		bottom: 0;
		height: 100%;
	<?php } else if($desc_position=='right') { ?>
		right: 0;
		bottom: 0;
		height: 100%;
	<?php } ?>
	display:none;
}
#box_skitter<?php echo $mid; ?> .dj-slide-desc-in {
	position: relative;
	<?php if($desc_position!='over') { ?>
		height: 100%;
	<?php } ?>
}
#box_skitter<?php echo $mid; ?> .dj-slide-desc-bg {
	position:absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: #000;
	<?php //if($desc_position=='over') { ?>
		opacity: 0.5;
		filter: alpha(opacity = 50);
	<?php //} ?>
}
#box_skitter<?php echo $mid; ?> .dj-slide-desc-text {
	position: relative;
	color: #ccc;
	padding: 10px;
	text-align: left;
}
#box_skitter<?php echo $mid; ?> .dj-slide-desc-text p {
	display: block;
	padding: 0;
}
#box_skitter<?php echo $mid; ?> .dj-slide-desc-text a {
	color: #f5f5f5;
}
#box_skitter<?php echo $mid; ?> .dj-slide-title {
	font-size: 1.5em;
	font-weight: bold;
	line-height: 1.1;
	color: #f5f5f5;
	margin-bottom: 5px;
}
#box_skitter<?php echo $mid; ?> .dj-slide-title a {
	background: none;
}
#box_skitter<?php echo $mid; ?> .dj-readmore-wrapper {
	padding: 5px 0 0;
	text-align: right;
}
#box_skitter<?php echo $mid; ?> a.dj-readmore {
	font-size: 1.1em;
}
#box_skitter<?php echo $mid; ?> .dj-extra {
	float: right;
	margin: 0 120px 5px 20px;
}

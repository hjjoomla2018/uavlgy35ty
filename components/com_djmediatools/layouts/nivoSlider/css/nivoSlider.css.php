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
//$thumb_height = isset($options['th']) ? $options['th'] : $_GET['th'];
$desc_position = isset($options['dp']) ? $options['dp'] : $_GET['dp'];
$desc_width = isset($options['dw']) ? $options['dw'] : $_GET['dw'];
$arrows_top = isset($options['at']) ? $options['at'] : $_GET['at'];
$arrows_horizontal = isset($options['ah']) ? $options['ah'] : $_GET['ah'];
$loader_position = isset($options['lip']) ? $options['lip'] : $_GET['lip'];
if($desc_position == 'over') {
	$desc_bottom = isset($options['db']) ? $options['db'] : $_GET['db'];
	$desc_left = isset($options['dl']) ? $options['dl'] : $_GET['dl'];
}

/*
Skin Name: Nivo Slider Default Theme
Skin URI: http://nivo.dev7studios.com
Description: The default skin for the Nivo Slider.
Version: 1.3
Author: Gilbert Pellegrom
Author URI: http://dev7studios.com
Supports Thumbs: true
*/
?>

#nivoSlider-wrapper<?php echo $mid; ?> {
	max-width: <?php echo $image_width ?>px;
}
#nivoSlider<?php echo $mid; ?> {
	position:relative;
	background:#fff url(<?php echo $ipath ?>/images/loading.gif) no-repeat 50% 50%;
    margin-bottom:10px;
    -webkit-box-shadow: 0px 1px 5px 0px #4a4a4a;
    -moz-box-shadow: 0px 1px 5px 0px #4a4a4a;
    box-shadow: 0px 1px 5px 0px #4a4a4a;
}
#nivoSlider<?php echo $mid; ?> img {
	position:absolute;
	top:0px;
	left:0px;
	display:none;
}
#nivoSlider<?php echo $mid; ?> a {
	border:0;
	display:block;
	background-color: transparent !important;
}
#nivoSlider<?php echo $mid; ?> .video-icon {
	display: block;
	position: absolute;
	left: 50%;
	top: 50%;
	width: 100px;
	height: 100px;
	margin: -50px 0 0 -50px;
	background: url(<?php echo $ipath ?>/images/video.png) center center no-repeat;
}


#nivoSlider-wrapper<?php echo $mid; ?> .nivo-controlNav {
	text-align: center;
	padding: 20px 0;
}
#nivoSlider-wrapper<?php echo $mid; ?> .nivo-controlNav a {
	display:inline-block;
	width:22px;
	height:22px;
	background:url(<?php echo $ipath ?>/images/bullets.png) no-repeat !important;
	text-indent:-9999px;
	border:0;
	margin: 0 2px;
	background-color: transparent !important;
}
#nivoSlider-wrapper<?php echo $mid; ?> .nivo-controlNav a.active {
	background-position:0 -22px !important;
}

#nivoSlider-wrapper<?php echo $mid; ?> .nivo-directionNav a {
	display:block;
	width:30px;
	height:30px;
	background:url(<?php echo $ipath ?>/images/arrows.png) no-repeat !important;
	text-indent:-9999px;
	border:0;
	opacity: 0;
	-webkit-transition: all 200ms ease-in-out;
    -moz-transition: all 200ms ease-in-out;
    -o-transition: all 200ms ease-in-out;
    transition: all 200ms ease-in-out;
    background-color: transparent !important;
}
#nivoSlider-wrapper<?php echo $mid; ?>:hover .nivo-directionNav a { opacity: 1; }
#nivoSlider-wrapper<?php echo $mid; ?> a.nivo-nextNav {
	background-position:-30px 0 !important;
	right:15px;
}
#nivoSlider-wrapper<?php echo $mid; ?> a.nivo-prevNav {
	left:15px;
}

<?php /* Slide description area settings */ ?>
#nivoSlider-wrapper<?php echo $mid; ?> .nivo-caption {
	position: absolute;
	width: <?php echo $desc_width; ?>%;
	max-height: <?php echo $image_height; ?>px;
	<?php if($desc_position=='over') { ?>
		bottom: <?php echo $desc_bottom; ?>%;
		left: <?php echo $desc_left; ?>%;
	<?php } else if($desc_position=='left') { ?>
		left: 0;
		bottom: 0;
	<?php } else if($desc_position=='right') { ?>
		right: 0;
		bottom: 0;
	<?php } ?>
}
#nivoSlider-wrapper<?php echo $mid; ?> .dj-slide-desc-in {
	position: relative;
	<?php if($desc_position!='over') { ?>
		height: <?php echo $image_height; ?>px;
	<?php } ?>
}
#nivoSlider-wrapper<?php echo $mid; ?> .dj-slide-desc-bg {
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
#nivoSlider-wrapper<?php echo $mid; ?> .dj-slide-desc-text {
	position: relative;
	color: #ccc;
	padding: 10px;
	text-align: left;
}
#nivoSlider-wrapper<?php echo $mid; ?> .dj-slide-desc-text p {
	display: block;
	padding: 0;
}
#nivoSlider-wrapper<?php echo $mid; ?> .dj-slide-desc-text a {
	color: #f5f5f5;
}
#nivoSlider-wrapper<?php echo $mid; ?> .dj-slide-title {
	font-size: 1.5em;
	font-weight: bold;
	line-height: 1.1;
	color: #f5f5f5;
	margin-bottom: 5px;
}
#nivoSlider-wrapper<?php echo $mid; ?> .dj-slide-title a {
	background: none;
}
#nivoSlider-wrapper<?php echo $mid; ?> .dj-readmore-wrapper {
	padding: 5px 0 0;
	text-align: right;
}
#nivoSlider-wrapper<?php echo $mid; ?> a.dj-readmore {
	font-size: 1.1em;
}
#nivoSlider-wrapper<?php echo $mid; ?> .dj-extra {
	float: right;
	margin: 0 0 5px 20px;
}


#nivoSlider-wrapper<?php echo $mid; ?> .nivo-controlNav.nivo-thumbs-enabled {
	width: 100%;
}
#nivoSlider-wrapper<?php echo $mid; ?> .nivo-controlNav.nivo-thumbs-enabled a {
	width: auto;
	height: auto;
	background: none;
	margin-bottom: 5px;
}
#nivoSlider-wrapper<?php echo $mid; ?> .nivo-controlNav.nivo-thumbs-enabled img {
	display: block;
	width: <?php echo $thumb_width ?>px;
	height: auto;
}


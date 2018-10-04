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
$slide_width = isset($options['w']) ? $options['w'] : $_GET['w'];
$desc_position = isset($options['dp']) ? $options['dp'] : $_GET['dp'];
$desc_width = isset($options['dw']) ? $options['dw'] : $_GET['dw'];
$loader_position = isset($options['lip']) ? $options['lip'] : $_GET['lip'];
if($desc_position == 'over') {
	$desc_bottom = isset($options['db']) ? $options['db'] : $_GET['db'];
	$desc_left = isset($options['dl']) ? $options['dl'] : $_GET['dl'];
}
$spacing = isset($options['s']) ? $options['s'] : $_GET['s'];

$img_w = 100;
if($desc_position == 'left' || $desc_position == 'right') $img_w -= $desc_width;

$image_width = 'width: '.$img_w.'%';

/* DON'T CHANGE ANYTHING UNLESS YOU ARE SURE YOU KNOW WHAT YOU ARE DOING */

/* General grid gallery settings */ ?>
#dj-galleryGrid<?php echo $mid; ?> {
	margin: 10px 0 10px -<?php echo $spacing; ?>px;
	border: 0px;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-galleryGrid-in {
	position: relative;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slides {
	position: relative;
	z-index: 5;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide {
	position: relative;
	float: left;
	width: <?php echo $slide_width; ?>px;
	overflow: hidden;
	margin-bottom: <?php echo $spacing; ?>px;
	margin-left: <?php echo $spacing; ?>px;
	text-align: center;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    background: transparent url(<?php echo $ipath ?>/images/loading.gif) center center no-repeat;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide.dj-first {
	clear: both;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide.active {
	z-index: 1;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-in {
	opacity: 0;
	position: relative;
	height: 100%;
	overflow: hidden;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-in noscript {
	position: absolute;
	top:0;
	left:0;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-in > a {
	background: none;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-image {
	<?php echo $image_width; ?>;
	<?php if($desc_position=='left') { ?>
		float: right;
	<?php } else if($desc_position=='right') { ?>
		float: left;
	<?php } ?>
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide img.dj-image, 
#dj-galleryGrid<?php echo $mid; ?> .dj-slide a:hover img.dj-image {
	max-width: 100%;
	height: auto;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-in .video-icon {
	display: block;
	position: absolute;
	left: 50%;
	top: 50%;
	width: 100px;
	height: 100px;
	margin: -50px 0 0 -50px;
	background: url(<?php echo $ipath ?>/images/video.png) center center no-repeat;
}

<?php /* Slide description area settings */ ?>
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-desc {
	<?php if($desc_position=='over') { ?>
		position: absolute;
		bottom: <?php echo $desc_bottom; ?>%;
		left: <?php echo $desc_left; ?>%;
		width: <?php echo $desc_width; ?>%;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
	<?php } else if($desc_position=='left') { ?>
		margin-right: <?php echo $img_w; ?>%;
	<?php } else if($desc_position=='right') { ?>
		margin-left: <?php echo $img_w; ?>%;
	<?php } ?>
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-desc-in {
	position: relative;
	<?php if($desc_position!='over') { ?>
		height: 100%;
	<?php } ?>
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-desc-bg {
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
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-desc-text {
	position: relative;
	font-size: 10px;
	color: #ccc;
	padding: 10px;
	text-align: left;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-desc-text p {
	display: block;
	padding: 0;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-desc-text a {
	color: #f5f5f5;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-title {
	font-size: 1.3em;
	font-weight: bold;
	line-height: 1.1;
	color: #f5f5f5;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-slide-title a {
	background: none;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-readmore-wrapper {
	padding: 5px 0 0;
	text-align: right;
}
#dj-galleryGrid<?php echo $mid; ?> a.dj-readmore {
	font-size: 1.1em;
}
#dj-galleryGrid<?php echo $mid; ?> .dj-extra {
	float: right;
	margin: 0 0 5px 20px;
}

#dj-galleryGrid<?php echo $mid; ?> .showOnMouseOver {
	opacity: 0;
}

<?php /* Description in tooltip */ ?>
.tip-wrap { 
	z-index: 50;
}
.tip-wrap .tip {
	font-size:10px;
	text-align:left;
	padding:10px;
	max-width:400px;
	color: #ccc;
	background: #222;
	border: 3px solid #eee;
	border-radius: 5px;
	box-shadow: 0 0 10px #000;
	opacity: 0.8;
}
.tip-wrap .tip-title {
	font-size: 1.3em;
	font-weight: bold;
	line-height: 1.1;
	color: #f5f5f5;
	margin-bottom: 5px;
}

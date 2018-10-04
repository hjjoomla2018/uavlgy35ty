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
$thumb_width = isset($options['tw']) ? $options['tw'] : $_GET['tw'];
$thumb_height = isset($options['th']) ? $options['th'] : $_GET['th'];
$tab_width = isset($options['tabw']) ? $options['tabw'] : $_GET['tabw'];
$tab_height = isset($options['tabh']) ? $options['tabh'] : $_GET['tabh'];
$tab_position = isset($options['tabp']) ? $options['tabp'] : $_GET['tabp'];
$desc_position = isset($options['dp']) ? $options['dp'] : $_GET['dp'];
$desc_width = isset($options['dw']) ? $options['dw'] : $_GET['dw'];
$arrows_top = isset($options['at']) ? $options['at'] : $_GET['at'];
$arrows_horizontal = isset($options['ah']) ? $options['ah'] : $_GET['ah'];
$loader_position = isset($options['lip']) ? $options['lip'] : $_GET['lip'];
if($desc_position == 'over') {
	$desc_bottom = isset($options['db']) ? $options['db'] : $_GET['db'];
	$desc_left = isset($options['dl']) ? $options['dl'] : $_GET['dl'];
}

$img_w = 100;
if($desc_position == 'left' || $desc_position == 'right') $img_w -= $desc_width;

$image_width = 'width: '.$img_w.'%';

/* DON'T CHANGE ANYTHING UNLESS YOU ARE SURE YOU KNOW WHAT YOU ARE DOING */

/* General tabber settings */ ?>
#dj-tabber<?php echo $mid; ?> {
	max-width: <?php echo ($slide_width + $tab_width); ?>px;
	margin: 10px auto;
	padding: 9px;
	background: #d7d7d7;
	border: 1px solid #d0d0d0; <?php /* must be declared in pixels */ ?>
	box-shadow: 0 0 5px #ccc;
}
#dj-tabber<?php echo $mid; ?> .dj-tabber-in {
	max-width: <?php echo ($slide_width + $tab_width); ?>px;
	position: relative;
	overflow: hidden;
}
#dj-tabber<?php echo $mid; ?> .dj-slides {
	opacity: 0;
	width: auto;
	<?php if($tab_position=='left') { ?>
		margin-left: <?php echo $tab_width; ?>px;
	<?php } else { ?>
		margin-right: <?php echo $tab_width; ?>px;
	<?php } ?>
	overflow: hidden;
	position: relative;
	z-index: 5;
}
#dj-tabber<?php echo $mid; ?> .dj-slide {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	overflow: hidden;
	text-align: center;
}
#dj-tabber<?php echo $mid; ?> .dj-slide.dj-active {
	position: relative;
}
#dj-tabber<?php echo $mid; ?> .dj-slide-in > a {
	background: none;
}
#dj-tabber<?php echo $mid; ?> .dj-slide-image {
	<?php echo $image_width; ?>;
	<?php if($desc_position=='left') { ?>
		float: right;
	<?php } else if($desc_position=='right') { ?>
		float: left;
	<?php } ?>
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
}
#dj-tabber<?php echo $mid; ?> .dj-slide img.dj-image, 
#dj-tabber<?php echo $mid; ?> .dj-slide a:hover img.dj-image {
	max-width: 100%;
	height: auto;
}
#dj-tabber<?php echo $mid; ?> .dj-slide-in .video-icon {
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
#dj-tabber<?php echo $mid; ?> .dj-slide-desc {
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
#dj-tabber<?php echo $mid; ?> .dj-slide-desc-in {
	position: relative;
	height: 100%;
}
#dj-tabber<?php echo $mid; ?> .dj-slide-desc-bg {
	position:absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: #000;
	opacity: 0.5;
}
#dj-tabber<?php echo $mid; ?> .dj-slide-desc-text {
	position: relative;
	color: #ccc;
	padding: 10px;
	text-align: left;
}
#dj-tabber<?php echo $mid; ?> .dj-slide-desc-text p {
	display: block;
	padding: 0;
}
#dj-tabber<?php echo $mid; ?> .dj-slide-desc-text a {
	color: #f5f5f5;
}
#dj-tabber<?php echo $mid; ?> .dj-slide-title {
	font-size: 1.5em;
	font-weight: bold;
	line-height: 1.1;
	color: #f5f5f5;
	margin-bottom: 5px;
}
#dj-tabber<?php echo $mid; ?> .dj-slide-title a {
	background: none;
}
#dj-tabber<?php echo $mid; ?> .dj-readmore-wrapper {
	padding: 5px 0 0;
	text-align: right;
}
#dj-tabber<?php echo $mid; ?> a.dj-readmore {
	font-size: 1.1em;
}
#dj-tabber<?php echo $mid; ?> .dj-extra {
	float: right;
	margin: 0 0 5px 20px;
}

<?php /* Navigation buttons settings */ ?>
#dj-tabber<?php echo $mid; ?> .dj-navigation {
	position: absolute;
	top: <?php echo $arrows_top; ?>%;
	width: 100%;
	<?php if($tab_position=='left') { ?>
		border-left: <?php echo $tab_width; ?>px solid transparent;
	<?php } else { ?>
		border-right: <?php echo $tab_width; ?>px solid transparent;
	<?php } ?>
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
	z-index: 10;
}
#dj-tabber<?php echo $mid; ?> .dj-navigation-in {
	position: relative;
	margin: 0 <?php echo $arrows_horizontal; ?>px;
}
#dj-tabber<?php echo $mid; ?> .dj-navigation .dj-prev {
	cursor: pointer;
	display: block;
	position: absolute;
	left: 0;
}
#dj-tabber<?php echo $mid; ?> .dj-navigation .dj-next {
	cursor: pointer;
	display: block;
	position: absolute;
	right: 0;
}
#dj-tabber<?php echo $mid; ?> .dj-navigation .dj-play, 
#dj-tabber<?php echo $mid; ?> .dj-navigation .dj-pause {
	cursor: pointer;
	display: block;
	position: absolute;
	left: 50%;
	margin-left: -18px;
}

<?php /* Tabber tabs and indicator styling */ ?>
#dj-tabber<?php echo $mid; ?> .dj-tabs {
	position: absolute;
	top: 0;
	<?php if($tab_position=='left') { ?>
		left: 0;
	<?php } else { ?>
		right: 0;
	<?php } ?>
	width: <?php echo $tab_width; ?>px;
	height: 100%;
	z-index: 5;
}
#dj-tabber<?php echo $mid; ?> .dj-tabs-in {
	position: relative;
	width: <?php echo $tab_width; ?>px;
	margin: 0;
}
#dj-tabber<?php echo $mid; ?> .dj-tab-indicator {
	position: absolute;
	top: 0;
	<?php if($tab_position=='left') { ?>
		left: <?php echo $tab_width; ?>px;
		background: url(<?php echo $ipath ?>/images/dj-tab-indicator-right.png) left center no-repeat;
	<?php } else { ?>
		right: <?php echo $tab_width; ?>px;
		background: url(<?php echo $ipath ?>/images/dj-tab-indicator-left.png) right center no-repeat;
	<?php } ?>
	width: 30px;
	height: <?php echo $tab_height; ?>px;
	z-index: 15;
}
#dj-tabber<?php echo $mid; ?> .dj-tab {
	display: block;
	height: <?php echo $tab_height; ?>px;
	overflow: hidden;
	cursor: pointer;
	background: #fff url(<?php echo $ipath ?>/images/dj-tab.png) 0 100% repeat-x;
	padding: 0 6px;
	text-decoration: none;
	<?php if($tab_position=='left') { ?>
		box-shadow: -1px 1px 2px #999;
		margin: 0 10px 8px 2px;
	<?php } else { ?>
		box-shadow: 1px 1px 2px #999;
		margin: 0 2px 8px 10px;
	<?php } ?>
	
}
#dj-tabber<?php echo $mid; ?> span.dj-tab-in {
	display: table;
	height: <?php echo $tab_height; ?>px;
	font-size: 13px;
	line-height: 15px;
	font-weight: bold;
	color: #505050;
	text-shadow: 1px 1px 1px #ddd;
}
#dj-tabber<?php echo $mid; ?> span.dj-tab-in span {
	display: table-cell;
	vertical-align: middle;
}

#dj-tabber<?php echo $mid; ?> .dj-tab:focus,
#dj-tabber<?php echo $mid; ?> .dj-tab-active {
	background: #2f2f2f url(<?php echo $ipath ?>/images/dj-tab-active.png) 0 100% repeat-x;
	<?php if($tab_position=='left') { ?>
		margin-right: 0;
		padding-right: 16px;
	<?php } else { ?>
		margin-left: 0;
		padding-left: 16px;
	<?php } ?>
}

#dj-tabber<?php echo $mid; ?> .dj-tab:focus span.dj-tab-in,
#dj-tabber<?php echo $mid; ?> .dj-tab:focus span.dj-tab-in span,
#dj-tabber<?php echo $mid; ?> .dj-tab-active span.dj-tab-in,
#dj-tabber<?php echo $mid; ?> .dj-tab-active span.dj-tab-in span {
	color: #fff;
	text-shadow: none;
}
#dj-tabber<?php echo $mid; ?> .dj-tab img {
	border: 1px solid #ccc;
	margin-right: 8px;
	max-width: none;
}
#dj-tabber<?php echo $mid; ?> .dj-tab:focus img,
#dj-tabber<?php echo $mid; ?> .dj-tab-active img {
	border: 1px solid #fff;
}

<?php /* Loader icon styling */ ?>
#dj-tabber<?php echo $mid; ?> .dj-loader {
	position: absolute;
	<?php if($loader_position=='tl') { ?>
		top: 10px;
		left: 10px;
	<?php } else if($loader_position=='tr') { ?>
		top: 10px;
		right: 10px;
	<?php } else if($loader_position=='bl') { ?>
		bottom: 10px;
		left: 10px;
	<?php } else if($loader_position=='br') { ?>
		bottom: 10px;
		right: 10px;
	<?php } ?>
	z-index: 20;
	width: 24px;
	height: 24px;
	display: block;
	background: url(<?php echo $ipath ?>/images/ajax-loader.gif) left top no-repeat;
	opacity: 0.8;
}

<?php /* Fading elements */ ?>
#dj-tabber<?php echo $mid; ?> .showOnMouseOver {
	opacity: 0;
	-webkit-transition: opacity 200ms ease 50ms;
	transition: opacity 200ms ease 50ms;
}
#dj-tabber<?php echo $mid; ?>:hover .showOnMouseOver,
#dj-tabber<?php echo $mid; ?>.focused .showOnMouseOver {
	opacity: 0.5;
}
#dj-tabber<?php echo $mid; ?>:hover .showOnMouseOver:hover,
#dj-tabber<?php echo $mid; ?>.focused .showOnMouseOver:focus {
	opacity: 1;
}

<?php /* responsiveness */?>
@media (max-width: <?php echo 2*$tab_width ?>px) {
	
	#dj-tabber<?php echo $mid; ?> {
		padding: 2px 2px 0;
	}
	#dj-tabber<?php echo $mid; ?> .dj-slides {
		<?php if($tab_position=='left') { ?>
			margin-left: 0;
		<?php } else { ?>
			margin-right: 0;
		<?php } ?>
	}
	
	#dj-tabber<?php echo $mid; ?> .dj-navigation {
		<?php if($tab_position=='left') { ?>
			border-left: 0;
		<?php } else { ?>
			border-right: 0;
		<?php } ?>
	}
	
	#dj-tabber<?php echo $mid; ?> .dj-tabs {
		position: relative;
		overflow: hidden;
		width: 100%;
		height: <?php echo 3*($tab_height + 8); ?>px;
		margin-top: 8px;
	}
	#dj-tabber<?php echo $mid; ?> .dj-tabs-in {
		width: 100%;
	}
	#dj-tabber<?php echo $mid; ?> .dj-tab-indicator {
		display: none;
	}
	#dj-tabber<?php echo $mid; ?> .dj-tab {
		margin: 0 1px 8px;
	}
}
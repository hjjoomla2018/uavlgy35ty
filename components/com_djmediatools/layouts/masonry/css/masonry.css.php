<?php 
/**
 * @version $Id: galleryGrid.css.php 99 2017-08-04 10:55:30Z szymon $
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
#dj-masonry<?php echo $mid; ?> {
	max-width: 100%;
	overflow: hidden;
}
#dj-masonry<?php echo $mid; ?> .dj-slide {
	float: left;
	max-width: 100%;
	width: <?php echo $slide_width ?>px;
	margin-bottom: <?php echo $spacing; ?>px;
	text-align: center;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-in {
	position: relative;
	overflow: hidden;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-image {
	<?php echo $image_width; ?>;
	<?php if($desc_position=='left') { ?>
		float: right;
	<?php } else if($desc_position=='right') { ?>
		float: left;
	<?php } ?>
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
	overflow: hidden;
}
#dj-masonry<?php echo $mid; ?> .dj-slide img.dj-image, 
#dj-masonry<?php echo $mid; ?> .dj-slide a:hover img.dj-image {
	max-width: 100%;
	height: auto;
	-webkit-transition: -webkit-transform 0.3s ease;
	transition: transform 0.3s ease;
}
#dj-masonry<?php echo $mid; ?> .dj-slide:hover img.dj-image,
#dj-masonry<?php echo $mid; ?> .dj-slide a:focus img.dj-image {
	-webkit-transform: scale(1.1);
	transform: scale(1.1);
}

#dj-masonry<?php echo $mid; ?> .dj-slide-in .video-icon {
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
#dj-masonry<?php echo $mid; ?> .dj-slide-desc {
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
		max-height: 100%;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-desc-in {
	position: relative;
	<?php if($desc_position!='over') { ?>
		height: 100%;
	<?php } else { ?>
		opacity: 0.5;
	<?php } ?>
	-webkit-transition: all 0.3s ease;
	transition: all 0.3s ease;
	background: #303030;
	color: #c3c3c3;
}
#dj-masonry<?php echo $mid; ?> .dj-slide:hover .dj-slide-desc-in,
#dj-masonry<?php echo $mid; ?> .dj-slide:focus .dj-slide-desc-in {
	background: #1681d1;
	color: #a4c4f5;
	opacity: 1;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-desc-bg {
	display: none;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-desc-text {
	position: relative;
	padding: 15px;
	text-align: left;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-desc-text p {
	display: block;
	padding: 0;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-desc-text a {
	color: #f5f5f5;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-title {
	font-size: 1.3em;
	font-weight: bold;
	line-height: 1.2;
	color: #f5f5f5;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-title + .dj-slide-description {
	margin-top: 1em;
}
#dj-masonry<?php echo $mid; ?> .dj-slide-title + .dj-readmore-wrapper,
#dj-masonry<?php echo $mid; ?> .dj-slide-description + .dj-readmore-wrapper {
	margin-top: 1em;
}
#dj-masonry<?php echo $mid; ?> .dj-readmore-wrapper {
	text-align: right;
	font-size: 1.1em;
}
#dj-masonry<?php echo $mid; ?> a.dj-readmore {}
#dj-masonry<?php echo $mid; ?> .dj-extra {
	float: right;
	margin: 0 0 5px 20px;
}

#dj-masonry<?php echo $mid; ?> .showOnMouseOver {
	opacity: 0;
	-webkit-transition: opacity 0.3s ease;
	transition: opacity 0.3s ease;
}
#dj-masonry<?php echo $mid; ?> .dj-slide:hover .showOnMouseOver,
#dj-masonry<?php echo $mid; ?> .dj-slide:focus .showOnMouseOver {
	opacity: 1;
}

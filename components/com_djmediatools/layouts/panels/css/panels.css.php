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

// Get slideshow parameters
$mid = isset($options['mid']) ? $options['mid'] : $_GET['mid'];
$width = isset($options['w']) ? $options['w'] : $_GET['w'];
$height = isset($options['h']) ? $options['h'] : $_GET['h'];
$duration = isset($options['d']) ? $options['d'] : $_GET['d'];


/* DON'T CHANGE ANYTHING UNLESS YOU ARE SURE YOU KNOW WHAT YOU ARE DOING */
?>

#djkwicks<?php echo $mid; ?> {
	padding: 0 !important;
	margin: 0 !important;
	min-height: <?php echo $height ?>px;
}

#djkwicks<?php echo $mid; ?> > li {
}

#djkwicks<?php echo $mid; ?> .dj-image,
#djkwicks<?php echo $mid; ?> .dj-image-color {
	display: block;
	height: <?php echo $height ?>px;
	background-position: top center;
	background-repeat: no-repeat;
}

#djkwicks<?php echo $mid; ?> .dj-image-color {
	-webkit-transition: opacity <?php echo $duration ?>ms ease-in-out;
	-moz-transition: opacity <?php echo $duration ?>ms ease-in-out;
	-ms-transition: opacity <?php echo $duration ?>ms ease-in-out;
	-o-transition: opacity <?php echo $duration ?>ms ease-in-out;
	transition: opacity <?php echo $duration ?>ms ease-in-out;
	opacity: 0;
}
#djkwicks<?php echo $mid; ?> .kwicks-expanded .dj-image-color,
#djkwicks<?php echo $mid; ?> .kwicks-selected:not(.kwicks-collapsed) .dj-image-color {
	opacity: 1;
}

#djkwicks<?php echo $mid; ?> .dj-slide-desc {
	position: absolute;
	bottom: 0;
	width: <?php echo $width ?>px;
	overflow: hidden;
	background: rgba(0,0,0,0.5);
}

#djkwicks<?php echo $mid; ?> .dj-slide-desc-text {
	position: relative;
	padding: 10px;
	box-sizing: border-box;
	color: #a5a5a5;
	max-height: 40px;
	-webkit-transition: max-height <?php echo $duration ?>ms ease-in-out;
	-moz-transition: max-height <?php echo $duration ?>ms ease-in-out;
	-ms-transition: max-height <?php echo $duration ?>ms ease-in-out;
	-o-transition: max-height <?php echo $duration ?>ms ease-in-out;
	transition: max-height <?php echo $duration ?>ms ease-in-out;
}

#djkwicks<?php echo $mid; ?> .dj-slide-title {
	font-size: 17px;
	line-height: 20px;
	font-weight: bold;
	margin-bottom: 10px;
	white-space: nowrap;
	text-overflow: ellipsis;
	overflow: hidden;
}
#djkwicks<?php echo $mid; ?> a {
	color: #f5f5f5;
}

#djkwicks<?php echo $mid; ?> .dj-slide-description {
	-webkit-transition: opacity <?php echo $duration ?>ms ease-in-out 250ms;
	-moz-transition: opacity <?php echo $duration ?>ms ease-in-out 250ms;
	-ms-transition: opacity <?php echo $duration ?>ms ease-in-out 250ms;
	-o-transition: opacity <?php echo $duration ?>ms ease-in-out 250ms;
	transition: opacity <?php echo $duration ?>ms ease-in-out 250ms;
	opacity: 0;
}
#djkwicks<?php echo $mid; ?> .dj-slide-description p {
	margin: 0;
}

#djkwicks<?php echo $mid; ?> .dj-readmore-wrapper {
	margin-top: 10px;
	text-align: right;
	-webkit-transition: opacity <?php echo $duration ?>ms ease-in-out 500ms;
	-moz-transition: opacity <?php echo $duration ?>ms ease-in-out 500ms;
	-ms-transition: opacity <?php echo $duration ?>ms ease-in-out 500ms;
	-o-transition: opacity <?php echo $duration ?>ms ease-in-out 500ms;
	transition: opacity <?php echo $duration ?>ms ease-in-out 500ms;
	opacity: 0;
}

#djkwicks<?php echo $mid; ?> .kwicks-expanded .dj-slide-desc-text {
	max-height: <?php echo $height ?>px;
}
#djkwicks<?php echo $mid; ?> .kwicks-expanded .dj-slide-description {
	opacity: 1;
}
#djkwicks<?php echo $mid; ?> .kwicks-expanded .dj-readmore-wrapper {
	opacity: 1;
}

#djkwicks<?php echo $mid; ?> .video-icon {
	display: block;
	position: absolute;
	left: 50%;
	top: 50%;
	width: 100px;
	height: 100px;
	margin: -50px 0 0 -50px;
	background: url(<?php echo $ipath ?>/images/video.png) center center no-repeat;
}

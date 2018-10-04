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

// no direct access
defined('_JEXEC') or die ('Restricted access'); 

$imagelink = $params->get('link_image',1);
?>

<div class="dj-slide-image"><?php

if ((($slide->link || !empty($slide->video)) && $imagelink==1) || $imagelink > 1) {

$caption = '';
if($params->get('show_title')) {
	$caption = htmlspecialchars($slide->title);
}
if($params->get('show_desc') && !empty($slide->description)) {
	$caption.= (!empty($caption) ? ' - ':'').htmlspecialchars(strip_tags($slide->description,"<a><b><strong><em><i><u>"));
}
 
switch($imagelink) {
	case 2:
		$type = $params->get('lightbox','magnific');
		if($type == 'magnific') {
			if(!empty($slide->video)) {
				echo '<a class="dj-slide-link mfp-iframe" href="'.$slide->video.'" target="'.$slide->target.'">'.$image.'<span class="video-icon showOnMouseOver"></span></a>';				
			} else {
				echo '<a class="dj-slide-link" data-title="'.$caption.'" href="'.$slide->image.'" target="'.$slide->target.'">'.$image.'</a>';
			}
		} else {
			echo '<a rel="lightbox-grid'.$mid.'" title="'.$caption.'" '
				.'href="'.$slide->image.'" target="'.$slide->target.'">'.$image.'</a>';
		}		
		break;
	case 3:
		echo '<a class="dj-slide-popup" href="'.$slide->item_link.'" target="'.$slide->target.'">'.$image.(isset($slide->video) && !empty($slide->video) ? '<span class="video-icon showOnMouseOver"></span>':'').'</a>';
		break;
	default:
		if(!empty($slide->video)) {
			echo '<a class="dj-slide-link mfp-iframe" href="'.$slide->video.'" target="'.$slide->target.'">'.$image.'<span class="video-icon showOnMouseOver"></span></a>';
		} else {
			$attr = 'target="'.$slide->target.'"' .(!empty($slide->rel) ? ' rel="'.$slide->rel.'"':'');
			echo '<a href="'.$slide->link.'" '.$attr.'>'.$image.'</a>';
		}
		break;
}

} else {
	
	echo $image;
	
} ?>
</div>

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

class PanelsDJMediatoolsLayoutHelper extends DJMediatoolsLayoutHelper {
	
	public function getSlides(&$params) {
		
		$slides = parent::getSlides($params);
		$juri_root = JURI::root(true);
		
		if(is_array($slides) && count($slides)>0) foreach($slides as $key => $slide) {
			
			$resized = !empty($juri_root) ? str_replace($juri_root.'/', '', $slide->resized_image) : $slide->resized_image;
			if(!$slide->grayscale_image = DJImageResizer::grayscaleImage($resized, 'media/djmediatools/cache')) {
				$slide->grayscale_image = $resized;
			}
			// fix path for SEF links but not for external image urls
			if(strcasecmp(substr($slide->grayscale_image, 0, 4), 'http') != 0 && !empty($slide->grayscale_image)) {
				$slide->grayscale_image = $juri_root.'/'.$slide->grayscale_image;
			}
		}
		
		return $slides;
	}
	
	public function addScripts(&$params) {
		
		$mid = $params->get('gallery_id');
		
		$document = JFactory::getDocument();		
		
		if($params->get('link_image',1)==2) $this->addLightbox($params->get('lightbox','picbox'));
		
		$document->addStyleSheet('components/com_djmediatools/layouts/panels/css/jquery.kwicks.css');
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js');
		} else {
			JHtml::_('jquery.framework');
		}
		
		$canDefer = preg_match('/(?i)msie [6-9]/',$_SERVER['HTTP_USER_AGENT']) ? false : true;
		
		$document->addScript('media/djextensions/jquery-easing/jquery.easing.min.js', 'text/javascript', $canDefer);
		$document->addScript('components/com_djmediatools/layouts/panels/js/jquery.kwicks.min.js', 'text/javascript', $canDefer);
		
		$animationOptions = "{".implode(',', $this->getAnimationOptions($params))."}";
		
		$js = "
			jQuery(document).ready(function() {
                jQuery('#djkwicks$mid').kwicks($animationOptions);
            });";
		$document->addScriptDeclaration($js);
	}
	

	public function getAnimationOptions(&$params) {
	
		$effect = $params->get('effect');
		$effect_type = $params->get('effect_type');
		$duration = $params->get('duration');
		$delay = $params->get('delay');
	
		switch($effect){
			case 'Linear':
				$transition = 'swing';
				if(!$duration) $duration = 500;
				break;
			case 'Circ':
			case 'Expo':
			case 'Back':
				if(!$effect_type) $transition = 'easeOut'.$effect;
				else $transition = $effect_type.$effect;
				if(!$duration) $duration = 750;
				break;
			case 'Bounce':
			case 'Elastic':
				if(!$effect_type) $transition = 'easeOut'.$effect;
				else $transition = $effect_type.$effect;
				if(!$duration) $duration = 1000;
				break;
			case 'Cubic':
			default:
				if(!$effect_type) $transition = 'easeInOut'.$effect;
				else $transition = $effect_type.$effect;
				if(!$duration) $duration = 500;
		}
			
		$width = $params->get('image_width');
		//if($params->get('desc_position')!='over') $width += $params->get('desc_width');
	
		$options[] = "maxSize: $width";
		$options[] = "spacing: ".$params->get('space_between_images');
		//$options[] = "isVertical: true";
		$options[] = "duration: $duration";
		$options[] = "easing: '$transition'";
		
		if($params->get('autoplay')) {
			$options[] = "behavior: 'slideshow'";
			$options[] = "interval: $delay";
		} else {
			$options[] = "behavior: 'menu'";
		}
	
		return $options;
	}
	
public function getStyleSheetParams(&$params) {
		
		$mid = $params->get('gallery_id');
		$slide_width = $params->get('image_width');
		$slide_height = $params->get('image_height');
		$duration = $params->get('duration');
		$effect = $params->get('effect');
		
		if(!$duration) switch($effect){
			case 'Linear': 
				$duration = 500;	
				break;
			case 'Circ':
			case 'Expo':
			case 'Back':
				$duration = 750;
				break;
			case 'Bounce':
			case 'Elastic':
				$duration = 1000;
				break;
			case 'Cubic':
			default:
				$duration = 500;
		}
		
		$options['mid'] = $mid;
		$options['w'] = $slide_width;
		$options['h'] = $slide_height;
		$options['d'] = $duration;
		
		return $options;
	}	
}


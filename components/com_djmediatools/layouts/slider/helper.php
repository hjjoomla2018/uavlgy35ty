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

class SliderDJMediatoolsLayoutHelper extends DJMediatoolsLayoutHelper
{
	private $count = 1;
	
	public function getSlides(&$params) {
		
		$slides = parent::getSlides($params);
		if(!$slides) return $slides;
		
		if($params->get('slider_type')=='down') {
			$slides = array_reverse($slides);
		}
		
		$this->count = count($slides);
		
		$this->setVisibleImages($params);
		
		return $slides;
	}
	
	public function addScripts(&$params) {
		
		$mid = $params->get('gallery_id');
		
		$jquery = version_compare(JVERSION, '3.0.0', 'ge');
		
		if ($jquery) {
			JHTML::_('jquery.framework');
		} else {
			JHTML::_('behavior.framework', true);
		}
		
		$document = JFactory::getDocument();		
		
		if($params->get('link_image',1)==2) $this->addLightbox($params->get('lightbox','magnific'));
		
		$canDefer = preg_match('/(?i)msie [6-9]/',$_SERVER['HTTP_USER_AGENT']) ? false : true;
		
		$document->addScript(JURI::root(true).'/media/djextensions/picturefill/picturefill.min.js', 'text/javascript', $canDefer);
		if($jquery) $document->addScript(JURI::root(true).'/media/djextensions/jquery-easing/jquery.easing.min.js', 'text/javascript', $canDefer);
		else $document->addScript(JURI::root(true).'/components/com_djmediatools/assets/js/powertools-1.2.0.js', 'text/javascript', $canDefer);
		$document->addScript(JURI::root(true).'/components/com_djmediatools/layouts/slider/js/'.(!$jquery ? 'moo.':'').'slider.js?v='.self::$_version, 'text/javascript', $canDefer);
		
		$width = $params->get('image_width');
		$height = $params->get('image_height');
		$spacing = $params->get('space_between_images');
		
		$count = $params->get('visible_images');
		$slider_type = $params->get('slider_type');
		switch($slider_type){
			case 'fade':
				$slide_size = $width;
				break;
			case 'down':
			case 'up':
				$slide_size = $height + $spacing;
				break;
			case 'left':
			case 'right':
			default:
				$slide_size = $width + $spacing;
				break;
		}
		
		$animationOptions = $this->getAnimationOptions($params);
		$showB = $params->get('show_buttons',2);
		$showA = $params->get('show_arrows',2);
		$showI = $params->get('show_custom_nav',1);
		$preload = $params->get('preload');
		$direction = $params->get('direction') == 'rtl' ? 'right':'left';
		$moduleSettings = "{id: '$mid', slider_type: '$slider_type', slide_size: $slide_size, visible_slides: $count, show_buttons: $showB, show_arrows: $showA, show_indicators: $showI, preload: $preload, direction: '$direction'}";
		
		if($jquery) {
			$js = "jQuery(document).ready(function(){ if(!this.DJSlider$mid) this.DJSlider$mid = new DJImageSlider($moduleSettings,$animationOptions) });";
		} else {
			$js = "window.addEvent('domready',function(){ if(!this.DJSlider$mid) this.DJSlider$mid = new DJImageSlider($moduleSettings,$animationOptions) });";
		}
		
		$document->addScriptDeclaration($js);
	}
	
	private function setVisibleImages(&$params){
		
		$count = $params->get('visible_images');
		$max = $params->get('max_images');
		if($count > $this->count) $count = $this->count;
		if($count < 1) $count = 1;
		if($count > $max) $count = $max;
		if($params->get('slider_type')=='fade') $count = 1;
		
		$params->set('visible_images',$count);
	}

	public function getAnimationOptions(&$params) {
		
		$transition = $params->get('effect');
		$easing = $params->get('effect_type');
		$duration = $params->get('duration');
		$delay = $params->get('delay');
		$autoplay = $params->get('autoplay');
		$pause = $params->get('pause_autoplay');
		if(($params->get('slider_type')=='fade'||$params->get('slider_type')=='ifade') && !$duration) {
			$transition = 'Sine';
			$easing = 'easeInOut';
			$duration = 400;
		} else switch($transition){
			case 'Linear':
				$easing = '';
				$transition = 'linear';
				if(!$duration) $duration = 400;
				break;
			case 'Back':
				if(!$easing) $easing = 'easeIn';
				if(!$duration) $duration = 400;
				break;
			case 'Bounce':
				if(!$easing) $easing = 'easeOut';
				if(!$duration) $duration = 800;
				break;
			case 'Elastic':
				if(!$easing) $easing = 'easeOut';
				if(!$duration) $duration = 1000;
				break;
			default: 
				if(!$easing) $easing = 'easeInOut';
				if(!$duration) $duration = 400;
		}
		$delay = $delay + $duration;
		
		$css3transition = self::getCSS3Transition($transition, $easing);
		
		if (version_compare(JVERSION, '3.0.0', '<')) { // Joomla!2.5 - Mootools
			if($transition=='ease') $transition = 'Sine';
			$transition = $transition.(!empty($easing) ? '.'.$easing : '');
			$transition = self::getMooTransition($transition);
		} else { // Joomla!3 - jQuery
			if($transition=='ease') {
				$transition = 'swing';
				$easing = '';
			}
			$transition = $easing.$transition;
		}
		
		$options = "{auto: $autoplay, pause_autoplay: $pause, transition: '$transition', css3transition: '$css3transition', duration: $duration, delay: $delay}";
		return $options;
	}
	
	public function getStyleSheetParams(&$params) {
		
		$mid = $params->get('gallery_id');		
		$slide_width = $params->get('image_width');
		$slide_height = $params->get('image_height');
		$spacing = $params->get('space_between_images');
		$count = $params->get('visible_images');
		if(($desc_width = $params->get('desc_width')) > $slide_width) $desc_width = $slide_width;
		$desc_bottom = $params->get('desc_bottom');
		$desc_left = $params->get('desc_horizontal');
		$arrows_top = $params->get('arrows_top');
		$arrows_horizontal = $params->get('arrows_horizontal');
		$slider_type = $params->get('slider_type');
		$resizing = $params->get('resizing');
		
		switch($slider_type){
			case 'fade':
			case 'ifade':
				$slider_width = $slide_width;
				$slider_height = $slide_height;
				break;
			case 'down':
			case 'up':
				$slider_width = $slide_width;
				$slider_height = $slide_height * $count + $spacing * ($count - 1);
				break;
			case 'left':
			case 'right':
			default:
				$slider_width = $slide_width * $count + $spacing * ($count - 1);
				$slider_height = $slide_height;
				break;
		}
		
		$desc_width = (($desc_width / $slide_width) * 100);
		$desc_left = (($desc_left / $slide_width) * 100);
		$desc_bottom = (($desc_bottom / $slide_height) * 100);
		$arrows_top = (($arrows_top / $slider_height) * 100);	
		
		
		$options['mid'] = $mid;
		$options['st'] = $slider_type;
		$options['w'] = $slide_width;
		$options['h'] = $slide_height;
		$options['sw'] = $slider_width;
		$options['sh'] = $slider_height;
		$options['s'] = $spacing;
		$options['dw'] = $desc_width;
		$options['db'] = $desc_bottom;
		$options['dl'] = $desc_left;
		$options['at'] = $arrows_top;
		$options['ah'] = $arrows_horizontal;
		$options['sb'] = $params->get('show_buttons');
		$options['sa'] = $params->get('show_arrows');
		$options['sc'] = $params->get('show_custom_nav');
		$options['cnp'] = $params->get('custom_nav_pos');
		$options['cna'] = $params->get('custom_nav_align');
		$options['r'] = $resizing;
		
		return $options;
	}

}

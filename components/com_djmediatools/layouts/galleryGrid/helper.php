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

class GalleryGridDJMediatoolsLayoutHelper extends DJMediatoolsLayoutHelper
{
	
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
		$document->addScript(JURI::root(true).'/components/com_djmediatools/layouts/galleryGrid/js/'.(!$jquery ? 'moo.':'').'galleryGrid.js?v='.self::$_version, 'text/javascript', $canDefer);
		
		$animationOptions = "{".implode(',', $this->getAnimationOptions($params))."}";
		
		$className = ucfirst($this->_prefix);
		
		if($jquery) {
			$js = "jQuery(document).ready(function(){ if(!this.DJGalleryGrid$mid) this.DJGalleryGrid$mid = new DJImage$className('dj-$this->_prefix$mid',$animationOptions) });";
		} else {
			$js = "window.addEvent('domready',function(){ if(!this.DJGalleryGrid$mid) this.DJGalleryGrid$mid = new DJImage$className('dj-$this->_prefix$mid',$animationOptions) });";
		}
		$document->addScriptDeclaration($js);
	}
	
	public function getAnimationOptions(&$params) {
		
		$transition = $params->get('effect');
		$easing = $params->get('effect_type');
		if(!is_numeric($duration = $params->get('duration'))) $duration = 0;
		if(!is_numeric($delay = $params->get('delay'))) $delay = 50;
		
		if(($params->get('slider_type')=='fade' || $params->get('slider_type')=='ifade') 
				&& !$duration && !$easing) {
			$transition = 'Sine';
			$easing = 'easeInOut';
			$duration = 200;
		} else switch($transition){
			case 'Linear':
				$easing = '';
				$transition = 'linear';
				if(!$duration) $duration = 200;
				break;
			case 'Back':
				if(!$easing) $easing = 'easeIn';
				if(!$duration) $duration = 200;
				break;
			case 'Bounce':
				if(!$easing) $easing = 'easeOut';
				if(!$duration) $duration = 400;
				break;
			case 'Elastic':
				if(!$easing) $easing = 'easeOut';
				if(!$duration) $duration = 500;
				break;
			default:
				if(!$easing) $easing = 'easeInOut';
				if(!$duration) $duration = 200;
		}
		
		if($delay > $duration) $delay = 50;
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
		
		$fx = $params->get('slider_type');
		$dfx = $params->get('desc_effect');
		
		$width = $params->get('image_width');
		if(in_array($params->get('desc_position'), array('left','right'))) $width += $params->get('desc_width');
		
		$options[] = "transition: '$transition'";
		$options[] = "css3transition: '$css3transition'";
		$options[] = "duration: $duration";
		$options[] = "delay: $delay";
		$options[] = "effect: '$fx'";
		if($dfx!='none') $options[] = "desc_effect: '".$dfx."'";
		$options[] = "width: $width";
		$options[] = "height: ".$params->get('image_height');
		$options[] = "spacing: ".$params->get('space_between_images');
		$options[] = "preload: ".$params->get('preload');
		
		return $options;
	}

	public function getStyleSheetParams(&$params) {
		
		$options = parent::getStyleSheetParams($params);
		
		$options['s'] = $params->get('space_between_images');
		
		return $options;
	}
	
}

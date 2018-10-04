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

class NivoSliderDJMediatoolsLayoutHelper extends DJMediatoolsLayoutHelper {
	
public function getParams(&$params) {
		
		$params = parent::getParams($params);
		
		if(!is_numeric($params->get('thumb_width'))) $params->set('thumb_width', 100);
		if(!is_numeric($params->get('thumb_height'))) $params->set('thumb_height', 100);
		
		return $params;
	}
		
	public function addScripts(&$params) {
		
		$mid = $params->get('gallery_id');
		
		$document = JFactory::getDocument();		
		
		if($params->get('link_image',1)==2) $this->addLightbox($params->get('lightbox','picbox'));
		
		$document->addStyleSheet('components/com_djmediatools/layouts/nivoSlider/css/nivo-slider.css');
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js');
		} else {
			JHtml::_('jquery.framework');
		}
		
		$canDefer = preg_match('/(?i)msie [6-9]/',$_SERVER['HTTP_USER_AGENT']) ? false : true;
		
		$document->addScript('components/com_djmediatools/layouts/nivoSlider/js/jquery.nivo.slider.pack.js', 'text/javascript', $canDefer);
		//$animationOptions = "{".implode(',', $this->getAnimationOptions($params))."}";
		
		//$js = "jQuery(window).load(function() { jQuery('#nivoSlider$mid').nivoSlider({effect: 'random'}); }); ";
		//$document->addScriptDeclaration($js);
	}
	
	public function getStyleSheetParams(&$params) {
		
		$options = parent::getStyleSheetParams($params);
		
		$slide_width = $params->get('image_width');
		$desc_width = $params->get('desc_width');
		$desc_position = $params->get('desc_position');
		if($desc_position == 'over') {
			$desc_left = $params->get('desc_horizontal');
		}
		$desc_width = (($desc_width / $slide_width) * 100);
		$desc_left = (($desc_left / $slide_width) * 100);
		
		$thumb_width = $params->get('thumb_width');
		//$thumb_height = $params->get('thumb_height');
		
		$options['w'] = $slide_width;
		$options['dw'] = $desc_width;
		
		if($desc_position == 'over') {
			$options['dl'] = $desc_left;
		}
		$options['tw'] = $thumb_width;
		//$options['th'] = $thumb_height;
		
		return $options;
	}
	
}


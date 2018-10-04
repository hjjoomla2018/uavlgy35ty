<?php
/**
 * @version $Id: helper.php 99 2017-08-04 10:55:30Z szymon $
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

class MasonryDJMediatoolsLayoutHelper extends DJMediatoolsLayoutHelper
{
	
	public function addScripts(&$params) {
		
		$mid = $params->get('gallery_id');
		
		JHTML::_('jquery.framework');
		
		$document = JFactory::getDocument();
		
		if($params->get('link_image',1)==2) $this->addLightbox($params->get('lightbox','magnific'));
		
		$canDefer = preg_match('/(?i)msie [6-9]/',@$_SERVER['HTTP_USER_AGENT']) ? false : true;
		
		$document->addScript(JURI::root(true).'/media/djextensions/picturefill/picturefill.min.js', 'text/javascript', $canDefer);
		$document->addScript(JURI::root(true).'/components/com_djmediatools/layouts/masonry/js/masonry.pkgd.min.js?v='.self::$_version, 'text/javascript', $canDefer);
		
		$animationOptions = "{".implode(', ', $this->getAnimationOptions($params))."}";
		
		$js = " jQuery(document).ready(function(){ 
			window.masonry$mid = jQuery('#dj-masonry$mid').masonry($animationOptions);
			jQuery(window).load(function(){
				window.masonry$mid.masonry('layout');
			});
		});";
		
		$document->addScriptDeclaration($js);
	}
	
	public function getAnimationOptions(&$params) {
		
		if(!is_numeric($duration = $params->get('duration')) || !$duration) $duration = 200;
		if(!is_numeric($stagger = $params->get('lag'))) $stagger = 50;
		
		if($stagger > $duration) $stagger = $duration;
		
		$width = $params->get('image_width');
		if(in_array($params->get('desc_position'), array('left','right'))) $width += $params->get('desc_width');
		
		$options[] = "itemSelector: '.dj-slide'";
		$options[] = "columnWidth: $width";
		$options[] = "gutter: ".$params->get('space_between_images');
		$options[] = "transitionDuration: $duration";
		$options[] = "stagger: $stagger";
		$options[] = "fitWidth: true";
		
		return $options;
	}

	public function getStyleSheetParams(&$params) {
		
		$options = parent::getStyleSheetParams($params);
		
		$options['s'] = $params->get('space_between_images');
		
		return $options;
	}
	
}

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

class MsliderDJMediatoolsLayoutHelper extends DJMediatoolsLayoutHelper {
	
	public function getSlides(&$params) {
	
		$slides = parent::getSlides($params);
		if(!$slides) return $slides;
		
		// set visible slides
		if(!is_numeric($params->get('visible_images'))) $params->set('visible_images', 3);
		$count = $params->get('visible_images');
		$max = $params->get('max_images');
		if($count > count($slides)) $count = count($slides);
		if($count < 1) $count = 1;
		if($count > $max) $count = $max;
		$params->set('visible_images',$count);
		
		return $slides;
	}	
	
	public function getAnimationOptions(&$params) {
	
		$options = parent::getAnimationOptions($params);
	
		$options[] = "visible: ".$params->get('visible_images');
		$options[] = "dwidth: ".$params->get('desc_width');
		$options[] = "lag: ".$params->get('lag',100);
	
		return $options;
	}
	
	public function getStyleSheetParams(&$params) {
		
		$options = parent::getStyleSheetParams($params);
		
		if($params->get('desc_position') == 'over') {
			//$options['dlpx'] = $params->get('desc_horizontal');
		}
		
		$options['v'] = $params->get('visible_images');
		$options['s'] = $params->get('space_between_images');
		
		return $options;
	}
	
}


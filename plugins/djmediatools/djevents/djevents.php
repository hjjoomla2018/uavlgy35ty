<?php
/**
 * @version $Id:$
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
defined('_JEXEC') or die;

class plgDJMediatoolsDJEvents extends JPlugin
{
	/**
	 * Plugin that returns the object list for DJ-Mediatools album
	 * 
	 * Each object must contain following properties (mandatory): title, description, image
	 * Optional properties: link, target (_blank or _self), alt (alt attribute for image)
	 * 
	 * @param	object	The album params
	 */
	public function onAlbumPrepare(&$source, &$params)
	{
		// Lets check the requirements
		$check = $this->onCheckRequirements($source);
		if (is_null($check) || is_string($check)) {
			return null;
		}
						
		$app = JFactory::getApplication();
		
		$max = $params->get('max_images');
		$default_image = $params->get('plg_djevents_image');
		
		require_once JPath::clean(JPATH_ROOT.'/components/com_djevents/helpers/route.php');
		JModelLegacy::addIncludePath(JPATH_ROOT.'/administrator/components/com_djevents/models', 'DJEventsModel');
		$model = JModelLegacy::getInstance('Events', 'DJEventsModel', array('ignore_request'=>true));
		
		$cparams = JComponentHelper::getParams('com_djevents');
		$model->setState('params', $cparams);
				
		$model->setState('filter.category', $params->get('plg_djevents_category',''));
		$model->setState('filter.city', $params->get('plg_djevents_city',''));
		
		$model->setState('list.start', 0);
		$model->setState('list.limit', $max);
		
		if((int)$params->get('plg_djevents_featured_only',0) == 1) {
			$model->setState('filter.featured', 1);
		}
		$model->setState('filter.published', 1);
		
		$items = $model->getItems();
		
		//$app->enqueueMessage("<pre>".print_r($items, true)."</pre>");
		
		foreach($items as $item){
			$slide = (object) array();
			
			$slide->image = $item->image;
			// if no image was added to the event then try to find image in description
			if(!$slide->image) $slide->image = DJMediatoolsLayoutHelper::getImageFromText($item->description);
			// if no image found in event description then take default image
			if(!$slide->image) $slide->image = $default_image;
			// if no default image set then don't display this event
			if(!$slide->image) continue;
			
			$slide->object = $item;
			
			// we got image now take extra information
			$slide->extra = '';
			if($params->get('plg_djevents_show_date')==1){
				$start = JFactory::getDate($item->start);
				$slide->extra.= '<div class="date">';
				$slide->extra.= $start->format('d F Y'.($item->start_time ? ' H:i':''));
				$slide->extra.= '</div>';
			}
			if($params->get('plg_djevents_show_cat')==1){
				/* todo: category with definded color and icon
				$cat = $categories[$item->cat_id];
				$slide->extra.= '<div class="category" style="background: '.$cat->icon_bg.'; color: '.$cat->icon_color.'">';
				if($item->icon_type == 'fa') {
					$slide->extra.=	'<i class="'.$cat->fa_icon.'" aria-hidden="true"></i>';
				} else if($item->icon_type == 'image') {
					$slide->extra.=	'<img style="max-height: 1em" src="'.JURI::base(true).$cat->image_icon.'" />';
				}
				if($params->get('plg_djevents_cat_link')==1){
					$slide->extra.= '<a class="title_cat" href="'.JRoute::_(DJEventsHelperRoute::getEventsListRoute().'&cid='.$item->cat_id).'">'.$item->category_name.'</a>';
				}else{
					$slide->extra.= $item->category_name;
				}
				$slide->extra.= '</div>';
				 */
				$slide->extra.= '<div class="category">';
				if($params->get('plg_djevents_cat_link')==1){
					$slide->extra.= '<a class="title_cat" href="'.JRoute::_(DJEventsHelperRoute::getEventsListRoute().'&cid='.$item->cat_id).'">'.$item->category_name.'</a>';
				}else{
					$slide->extra.= $item->category_name;
				}
				$slide->extra.= '</div>';
			}
			if($params->get('plg_djevents_show_city')==1){
				$slide->extra.= '<div class="city">';
				$slide->extra.= $item->city_name;
				$slide->extra.= '</div>';
			}
			// extra info end
			
			$slide->title = $item->title;
			$slide->description = $item->intro;

			$slide->id = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
			$slide->canonical = $slide->link = JRoute::_(DJEventsHelperRoute::getEventRoute($item->id.':'.$item->alias, $item->start));
				
			if($comments = $params->get('commnets',0)) {
				$host = str_replace(JURI::root(true), '', JURI::root());
				$host = preg_replace('/\/$/', '', $host);
				switch($comments) {
					case 1: // jcomments
						$slide->comments = array('id' => $item->id, 'group' => 'com_djevents');
						break;
					case 2: // disqus
						$disqus_shortname = $params->get('disqus_shortname','');
						if(!empty($disqus_shortname)) {
							$slide->comments = array();
							$slide->comments['url'] =  $host . $slide->link;
							$slide->comments['identifier'] = substr(md5($disqus_shortname), 0, 10)."_id".$item->id;
						}
						break;
					case 3: // facebook
						$slide->comments = $host . $slide->link;
						break;
					case 4: //komento
						// not implemented
						break;
				}
			}
			
			$slides[] = $slide;
		}
		
		return $slides;		
	}
	
	/*
	 * Define any requirements here (such as specific extensions installed etc.)
	 * 
	 * Returns true if requirements are met or text message about not met requirement
	 */
	public function onCheckRequirements(&$source) {
		
		// Don't run this plugin when the source is different
		if ($source != $this->_name) {
			return null;
		}
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_djevents/djevents.php')) return JText::_('PLG_DJMEDIATOOLS_DJEVENTS_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_djevents', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_DJEVENTS_COMPONENT_DISABLED');
		
		return true;		
	}
}

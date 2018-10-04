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
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_djmediatools'.DS.'lib'.DS.'image.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_djmediatools'.DS.'helpers'.DS.'route.php');

jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class DJMediatoolsLayoutHelper extends JObject
{
	protected $_prefix = null;
	protected static $_modal = false;
	protected static $_version = null;
	
	public static function getInstance($prefix = 'slideshow') {
		
		$layout = explode(':', $prefix);
		if(count($layout)>1) $prefix = $layout[1];
		
		// Get the helper file path.
		$path = dirname(__FILE__).DS.'..'.DS.'layouts'.DS.$prefix.DS.'helper.php';
		
		// Get the helper class name.
		$class = ucfirst($prefix).'DJMediatoolsLayoutHelper';

		// Include the class if not present.
		if (!class_exists($class)) {
			// If the helper file path exists, include it.
			if (file_exists($path)) {
				require_once $path;
			}
			else {
				$class = 'DJMediatoolsLayoutHelper';
			}
		}

		// Instantiate the class.
		if (class_exists($class)) {
			$instance = new $class($prefix);
		}
		else {
			JError::raiseError(500, JText::sprintf( 'COM_DJMEDIATOOLS_ERROR_INVALID_HELPER_CLASS' , $class) );
		}
		
		return $instance;
	}
	
	public function __construct($prefix = null){
		$this->_prefix = $prefix;
		
		if(!isset(self::$_version)) {
			$db = JFactory::getDBO();
			$db->setQuery("SELECT manifest_cache FROM #__extensions WHERE element='com_djmediatools' LIMIT 1");
			$version = json_decode($db->loadResult());
			self::$_version = $version->version;
		}
	}
	
	public function getParams(&$params) {
		
		// set default values and check if numeric params are numeric
		if(!is_numeric($params->get('max_images'))) $params->set('max_images', 50);
		if(!is_numeric($params->get('visible_images'))) $params->set('visible_images', 3);
		if(!in_array($this->_prefix , array('slider', 'mslider'))) $params->set('visible_images', 1);
		if(!is_numeric($params->get('autoplay'))) $params->set('autoplay', 1);
		if(!is_numeric($params->get('pause_autoplay'))) $params->set('pause_autoplay', 1);
		if(!is_numeric($params->get('image_width'))) $params->set('image_width', 700);
		if(!is_numeric($params->get('image_height'))) $params->set('image_height', 450);
		if(!is_numeric($params->get('space_between_images'))) $params->set('space_between_images', 20);
		$params->set('desc_position', $params->get('desc_position','over'));
		if(!$params->get('show_title') && !$params->get('show_desc')) $params->set('desc_position','over');
		if($params->get('desc_position') != 'over') { if(!is_numeric($params->get('desc_width'))) $params->set('desc_width', $params->get('image_width')); }
		else { if(!is_numeric($params->get('desc_width'))) $params->set('desc_width', $params->get('image_width')); }
		if(!is_numeric($params->get('desc_bottom'))) $params->set('desc_bottom', 0);
		if(!is_numeric($params->get('desc_horizontal'))) $params->set('desc_horizontal', 0);
		if(!is_numeric($params->get('arrows_top'))) $params->set('arrows_top', 50);
		if(!is_numeric($params->get('arrows_horizontal'))) $params->set('arrows_horizontal', 10);
		if(!is_numeric($params->get('duration'))) $params->set('duration', 0);
		if(!is_numeric($params->get('delay'))) $params->set('delay', 6000);
		if(!is_numeric($params->get('preload'))) $params->set('preload', 0);
		
		$params->set('slider_type', $params->get('slider_type','left'));
		if($params->get('desc_position') != 'over') {
			$params->set('desc_effect', '');
			$params->set('show_desc', $params->get('show_desc') > 0 ? 1 : 0);
		} else {
			$params->set('desc_effect', ($params->get('desc_effect','none')=='none' ? '': $params->get('desc_effect')));
		}
		
		if(!$params->get('show_custom_nav')) {
			$params->set('custom_nav_pos','none');
		}
		
		$params->set('link_image', (int)$params->get('link_image', 1));
		$params->def('window_size', 'fluid');
		
		$params->set('wcag', (int)$params->get('wcag', 1));
		$params->set('kenburns', (int)$params->get('kenburns', 0));		
		
		$app = JFactory::getApplication();
		$direction = JFactory::getDocument()->direction;
		// direction integration with joomla monster templates
		if ($app->input->get('direction') == 'rtl'){
			$direction = 'rtl';
		} else if ($app->input->get('direction') == 'ltr') {
			$direction = 'ltr';
		} else {
			if (isset($_COOKIE['jmfdirection'])) {
				$direction = $_COOKIE['jmfdirection'];
			} else {
				$direction = $app->input->get('jmfdirection', $direction);
			}
		}
		$params->set('direction', $direction);
		
		return $params;
	}
	
	public function getSlides(&$params) {
			
		// taking the slides from the source
		$app = JFactory::getApplication();
		
		$slides = null;
		$source = $params->get('source');
		
		$skip = $params->get('skip', 0);
		if($skip > 0) {
			$params->set('max_images', $params->get('max_images') + $skip);
		}
		
		switch($source) {
			case 'component':
				$slides = $this->getImagesFromDJMediatools($params);
				break;
			default:
				$dispatcher	= JDispatcher::getInstance();
				JPluginHelper::importPlugin('djmediatools', $source);
				$results = $dispatcher->trigger('onAlbumPrepare', array (&$source, &$params));
				if(isset($results[0])) $slides = $results[0];
				break;
		}
		
		if(is_array($slides) && $skip > 0) {
			if(count($slides) <= $skip) return null;
			for($i = 0; $i < $skip; $i++) array_shift($slides);
		}
		//djdebug($params);
		$root = JURI::root(true);
		$host = str_replace($root, '', JURI::root());
		$host = preg_replace('/\/$/', '', $host);
		$item_link = $params->get('link_image',1)==3 ? true : false;
		$comments = (int)$params->get('comments',0);
		
		// we need to calculate slider width for calculation of 'sizes' image attribute 
		$slider_width = $params->get('image_width');
		if($params->get('show_desc') && in_array($params->get('desc_position'), array('left','right'))) $slider_width += $params->get('desc_width');
		if($this->_prefix != 'slider' || ($params->get('slider_type') != 'up' && $params->get('slider_type') != 'down')) {
			$slider_width += $params->get('space_between_images');
			$slider_width *= $params->get('visible_images');
			$slider_width -= $params->get('space_between_images');
		}
		if($this->_prefix == 'tabber') $slider_width += $params->get('tab_width');  
		$video = false;
		
		if(is_array($slides) && count($slides)>0) foreach($slides as $key => $slide) {
			
			$resized = true;
			
			if(!$slide->resized_image = DJImageResizer::createThumbnail($slide->image, 'media/djmediatools/cache', $params->get('image_width'), $params->get('image_height'), $params->get('resizing','crop'), $params->get('quality',90), true, $params->get('watermark', 0))) {
				$slide->resized_image = $slide->image;
				$resized = false;
			}
			
			$path = JPath::clean(JPATH_ROOT . DS . str_replace('/', DS, $slide->resized_image));
			$size = @getimagesize($path);
			$slide->size = (object) array('w' => $size[0], 'h' => $size[1]);
			
			$srcset = array();
			
			$j38 = version_compare(JVERSION, '3.8.0', 'ge');
			
			if($resized) {
			
				$srcset[$slide->size->w] = ($j38 ? '' : $root.'/') . $slide->resized_image .' '.$slide->size->w.'w';
				
				$filename = JFile::getName($slide->resized_image);
				$folder = str_replace($filename, '', $slide->resized_image);
				
				foreach(DJImageResizer::$widths as $w) {
					if($slide->size->w <= $w) continue;
					// create path to image of $w width and add 'w' descriptor
					$wpath = $folder . '_'.$w.'w/' . $filename;
					if(JFile::exists(JPATH_ROOT . '/' . $wpath)) $srcset[$w] = ($j38 ? '' : $root .'/') . $wpath . ' '.$w.'w';
				}
				
				$slide->srcset = implode(', ', $srcset);
				$slide->data_srcset = $slide->srcset;
				if($j38) {
					$data_srcset = array();
					foreach($srcset as $src) {
						$data_srcset[] = $root .'/'.$src;
					}
					$slide->data_srcset = implode(', ', $data_srcset);
				}
				$slide->sizes = floor(100 * $slide->size->w / $slider_width) .'vw';
			}
			
			if(!$slide->thumb_image = DJImageResizer::createThumbnail($slide->resized_image, 'media/djmediatools/cache', $params->get('thumb_width'), $params->get('thumb_height'), 'crop', 80, false, false)) {
				$slide->thumb_image = $slide->resized_image;
			}
			
			// fix path for SEF links but not for external image urls
			if(strcasecmp(substr($slide->image, 0, 4), 'http') != 0 && !empty($slide->image)) {
				// if watermarks are enabled and image is internal then add watermark
				if($params->get('watermark', 0)) {
					$path = JPath::clean(JPATH_ROOT . DS . str_replace('/', DS, $slide->image));
					$size = @getimagesize($path);
					$slide->image = DJImageResizer::createThumbnail($slide->image, 'media/djmediatools/cache', $size[0], 0, 'toWidth', $params->get('quality',90), false, true);
				}
				$slide->image = $root.'/'.$slide->image;
			}
			if(strcasecmp(substr($slide->resized_image, 0, 4), 'http') != 0 && !empty($slide->resized_image)) {
				$slide->resized_image = $root.'/'.$slide->resized_image;
			}
			if(strcasecmp(substr($slide->thumb_image, 0, 4), 'http') != 0 && !empty($slide->thumb_image)) {
				$slide->thumb_image = $root.'/'.$slide->thumb_image;
			}
			
			if(!isset($slide->full_desc)) $slide->full_desc = $slide->description;
			$slide->description = $this->truncateDescription($slide->description, $params->get('limit_desc'));
			if(!isset($slide->target)) $slide->target = $this->getSlideTarget($slide->link);
			if(!isset($slide->alt)) $slide->alt = $slide->title;
			
			// id has to be defined in source plugin otherwise the array index and title will be used
			if(!isset($slide->id)) $slide->id = $key . ':' . JFilterOutput::stringURLSafe($slide->title);
			
			if($item_link || $comments) {
				$slide->item_link = JRoute::_(DJMediatoolsHelperRoute::getItemRoute($slide->id, $params->get('category')));
			}
			
			if(empty($slide->comments)) { 
				switch($comments) { // comments should be declared in source plugin to display the same comments which are assigned to the original item
					case 1: // jcomments
						$slide->comments = array('id' => (int) $slide->id, 'group' => 'djmediatool-'.$source.'-a'.(int)$params->get('category'));
						break;
					case 2: // disqus
						$disqus_shortname = $params->get('disqus_shortname','');
						if(!empty($disqus_shortname)) {
							$slide->comments = array();
							$slide->comments['url'] =  $host . $slide->item_link;
							$slide->comments['identifier'] = $disqus_shortname.'-djmt-'.$source.'-a'.(int)$params->get('category').'-id'.(int)$slide->id;
						}
						break;
					case 3: // facebook
						$slide->comments = $host . $slide->item_link;
						break;
					case 4: //komento
						// not implemented
						break;
				}
			}
			
			if(!empty($slide->video)) {
				$video = true;
				$slide->video .= (strstr($slide->video, '?') === false ? '?autoplay=1&amp;rel=0' : '&amp;autoplay=1&amp;rel=0');
			}
						
		} else {
			return null;
		}
		
		if($video || $params->get('link_image',1)==3) $this->addLightbox('magnific', true);
		
		return $slides;
	}
	
    private function getImagesFromDJMediatools(&$params) {
		
    	$app = JFactory::getApplication();
		$max = $params->get('max_images');
        $catid = (int) $params->get('category',0);
		
		// build query to get slides
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__djmt_items AS a, #__djmt_albums AS c');
		
		if (is_numeric($catid)) {
			$query->where('a.catid = ' . (int) $catid);
		}
		$query->where('a.catid = c.id');
		$query->where('c.published = 1');
		$query->where('a.published = 1');
		
		// Filter by start and end dates.
		$nullDate	= $db->Quote($db->getNullDate());
		$nowDate	= $db->Quote(JFactory::getDate()->toSql());
		
		$query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')');
		$query->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');
		
		if($params->get('sort_by',1)) {
			$query->order('a.ordering ASC');
		} else {
			$query->order('RAND()');
		}

		$db->setQuery($query, 0 , $max);
		$slides = $db->loadObjectList();
		
		foreach($slides as $slide){
			$slide->params = new JRegistry($slide->params);
			$slide->link = $this->getSlideLink($slide, $params);
			$slide->description = $this->getSlideDescription($slide);
			$slide->alt = $slide->params->get('alt_attr', $slide->title);
			$slide->img_title = $slide->params->get('title_attr');
			$slide->target = $slide->params->get('link_target','');
			if(empty($slide->target)) $slide->target = $this->getSlideTarget($slide->link);
			$slide->rel = $slide->params->get('link_rel','');
			$slide->id .= ':'.$slide->alias;
		}
		
		return $slides;
    }
	
	public static function getImageFromText(&$text, $remove = true)
	{
		$src = '';
		if(preg_match("/<img [^>]*src=\"([^\"]*)\"[^>]*>/", $text, $matches)){
			if($remove) $text = preg_replace("/<img[^>]*>/", '', $text);
			$src = $matches[1];
		}
		
		return $src;
	}
	
	private function getSlideLink(&$slide, &$params) {
		
		$link = '';
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		
		$link_type = explode(';',$slide->params->get('link_type', ''));
		switch($link_type[0]) {
			case 'menu':
				if ($menuid = $slide->params->get('link_menu',0)) {
					
					$menu = $app->getMenu();
					
					if($menuitem = $menu->getItem($menuid)) {
						//djdebug($menuitem);
						if ((strpos($menuitem->link, 'index.php?') === 0) && (strpos($menuitem->link, 'Itemid=') === false))
						{
							// If this is an internal Joomla link, ensure the Itemid is set.
							$link = $menuitem->link . '&Itemid=' . $menuitem->id;
						} else {
							$link = $menuitem->link;
						}
						
						if (strcasecmp(substr($link, 0, 4), 'http') && (strpos($link, 'index.php?') !== false))
						{
							$link = JRoute::_($link, true, $menuitem->params->get('secure'));
						}
						else
						{
							$link = JRoute::_($link);
						}
					}
				}
				break;
			case 'url':
				if($itemurl = $slide->params->get('link_url',0)) {
					$link = JRoute::_($itemurl);
				}
				break;
			case 'article':
				if ($artid = $slide->params->get('id',$slide->params->get('link_article',0))) {
					jimport('joomla.application.component.model');
					require_once(JPATH_BASE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
					JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_content'.DS.'models');
					$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request'=>true));
					$model->setState('params', $app->getParams());
					$model->setState('filter.article_id', $artid);
					$model->setState('filter.article_id.include', true); // Include
					$items = $model->getItems();
					if($items && $item = $items[0]) {
						$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
						$link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid));
						$slide->introtext = $item->introtext;
					}
				}
				break;
		}
		
		return $link;
	}
	
	private function getSlideTarget($link) {
		
		if(preg_match("/^http/",$link) && !preg_match("/^".str_replace(array('/','.','-'), array('\/','\.','\-'),JURI::base())."/",$link)) {
			$target = '_blank';
		} else {
			$target = '_self';
		}
		
		return $target;
	}
	
	private function getSlideDescription($slide) {
		
		$link_type = explode(';',$slide->params->get('link_type', ''));
		if($link_type=='article' && empty($slide->description)){ // if article and no description then get introtext as description
			if(isset($slide->introtext)) $slide->description = $slide->introtext;
		}
		
		return $slide->description;
	}
	
	private function truncateDescription($text, $limit) {
		
		$text = preg_replace('/{djmedia\s*(\d*)}/i', '', $text);
		
		$desc = strip_tags($text);
		
		if($limit && $limit - strlen($desc) < 0) {
			$desc = substr($desc, 0, $limit);
			// don't cut in the middle of the word unless it's longer than 20 chars
			if($pos = strrpos($desc, ' ')) {
				$limit = ($limit - $pos > 20) ? $limit : $pos;
				$desc = substr($desc, 0, $limit);
			}
			// cut text and add dots
			if(preg_match('/[a-zA-Z0-9]$/', $desc)) $desc.='&hellip;';
			$desc = '<p>'.nl2br($desc).'</p>';
		} else { // no limit or limit greater than description
			$desc = $text;
		}

		return $desc;
	}
	
	public function getNavigation(&$params) {
		
		$mid = $params->get('gallery_id');
		
		$prev = $params->get('left_arrow');
		$next = $params->get('right_arrow');
		$play = $params->get('play_button');
		$pause = $params->get('pause_button');
		
		if($params->get('slider_type')=='up' || $params->get('slider_type')=='down') {			
			if(empty($prev) || !file_exists(JPATH_ROOT.DS.$prev)) $prev = 'components/com_djmediatools/layouts/'.$this->_prefix.'/images/up.png';			
			if(empty($next) || !file_exists(JPATH_ROOT.DS.$next)) $next = 'components/com_djmediatools/layouts/'.$this->_prefix.'/images/down.png';
		} else {			
			if(empty($prev) || !file_exists(JPATH_ROOT.DS.$prev)) $prev = 'components/com_djmediatools/layouts/'.$this->_prefix.'/images/prev.png';			
			if(empty($next) || !file_exists(JPATH_ROOT.DS.$next)) $next = 'components/com_djmediatools/layouts/'.$this->_prefix.'/images/next.png';
		}
		if(empty($play) || !file_exists(JPATH_ROOT.DS.$play)) $play = 'components/com_djmediatools/layouts/'.$this->_prefix.'/images/play.png';
		if(empty($pause) || !file_exists(JPATH_ROOT.DS.$pause)) $pause = 'components/com_djmediatools/layouts/'.$this->_prefix.'/images/pause.png';
		
		$prev = JURI::root(true).'/'.$prev;
		$next = JURI::root(true).'/'.$next;
		$play = JURI::root(true).'/'.$play;
		$pause = JURI::root(true).'/'.$pause;
		
		$navi = (object) array('prev'=>$prev,'next'=>$next,'play'=>$play,'pause'=>$pause);
		
		return $navi;
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
		$document->addScript(JURI::root(true).'/components/com_djmediatools/layouts/slideshow/js/'.(!$jquery ? 'moo.':'').'slideshow.js?v='.self::$_version, 'text/javascript', $canDefer);
		$script = 'components/com_djmediatools/layouts/'.$this->_prefix.'/js/'.(!$jquery ? 'moo.':'').$this->_prefix.'.js';
		if(JFile::exists(JPath::clean(JPATH_ROOT.DS.$script))) {
			$document->addScript(JURI::root(true).'/'.$script.'?v='.self::$_version, 'text/javascript', $canDefer);
		}
		
		$animationOptions = "{".implode(',', $this->getAnimationOptions($params))."}";
		
		$className = ucfirst($this->_prefix);
		
		if($jquery) {
			$js = "jQuery(document).ready(function(){ if(!this.DJSlideshow$mid) this.DJSlideshow$mid = new DJImage$className('dj-$this->_prefix$mid',$animationOptions) });";
		} else {
			$js = "window.addEvent('domready',function(){ if(!this.DJSlideshow$mid) this.DJSlideshow$mid = new DJImage$className('dj-$this->_prefix$mid',$animationOptions) });";
		}
		
		
		//$js = "(function($){ ".$js." })(document.id);";
		$document->addScriptDeclaration($js);
	}

	public function addLightbox($type, $popup = false) {
		
		$j3 = version_compare(JVERSION, '3.0.0', 'ge');
		$document = JFactory::getDocument();
		$canDefer = preg_match('/(?i)msie [6-9]/',$_SERVER['HTTP_USER_AGENT']) ? false : true;
		
		switch($type) {
			case 'magnific':
				if($j3) JHTML::_('jquery.framework');
				else $document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js');
				$document->addStyleSheet(JURI::root(true).'/media/djextensions/magnific/magnific.css');
				$document->addScript(JURI::root(true).'/media/djextensions/magnific/magnific.js', 'text/javascript', $canDefer);
				$document->addScript(JURI::root(true).'/components/com_djmediatools/assets/js/magnific-init.js', 'text/javascript', $canDefer);
				if($popup) {
					// item details in popup
					$document->addStyleSheet(JURI::root(true).'/components/com_djmediatools/assets/css/slide-popup.css');
					$document->addScript(JURI::root(true).'/components/com_djmediatools/assets/js/album.js?v='.self::$_version, 'text/javascript', $canDefer);
				}
				break;
			case 'picbox':
				JHTML::_('behavior.framework');
				$document->addStyleSheet(JURI::root(true).'/components/com_djmediatools/assets/picbox/css/picbox.css');
				$document->addScript(JURI::root(true).'/components/com_djmediatools/assets/picbox/js/picbox.js', 'text/javascript', $canDefer);
				break;
			case 'slimbox':
				JHTML::_('behavior.framework');
				$document->addStyleSheet(JURI::root(true).'/components/com_djmediatools/assets/slimbox-1.8/css/slimbox.css');
				$document->addScript(JURI::root(true).'/components/com_djmediatools/assets/slimbox-1.8/js/slimbox.js', 'text/javascript', $canDefer);
				break;
			default:
				return;
		}
	}

	public function addModal(&$params) { // this function is depreciated, use addLigthbox('magnific', true) instead
		
		if(self::$_modal) return;
		
		$document = JFactory::getDocument();
		$canDefer = preg_match('/(?i)msie [6-9]/',$_SERVER['HTTP_USER_AGENT']) ? false : true;
		
		$close = 'function(){ var s = window.getScroll(); window.location.hash = ""; window.scrollTo(s.x, s.y); setTimeout(function(){window.fireEvent(\'resize\');}, 500);';
		
		if($params->get('window_size') == 'fluid') $modal_options = '{handler: \'iframe\', size: {x: \'100%\', y: \'100%\'}, classWindow: \'djmtmodal-window\', classOverlay: \'djmtmodal-overlay\', onOpen: function() { window.addEvent(\'resize\', function(){ var space = (window.getSize().x < 768 ? 30 : 70); this.resize({x: window.getSize().x - space, y: window.getSize().y - space}, true); }.bind(this) ); window.fireEvent(\'resize\'); }, onClose: '.$close.' }}';
		else $modal_options = '{handler: \'iframe\', size: {x: '.$params->get('window_width','850').', y: '.$params->get('window_height','510').'}, classWindow: \'djmtmodal-window\', classOverlay: \'djmtmodal-overlay\', onClose: '.$close.' }}';
		
		JHTML::_('behavior.framework',true);
		JHTML::_('behavior.modal','a.modal');
		$document->addStyleSheet(JURI::root(true).'/components/com_djmediatools/assets/css/modal.css');
		$document->addScript(JURI::root(true).'/components/com_djmediatools/assets/js/album.js?v='.self::$_version, 'text/javascript', $canDefer);
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			if(Browser.ie && Browser.version < 9) {
				$$('a.djmodal').each(function(link){
					link.setProperty('target','_blank');
				});
			} else {
				SqueezeBox.assign($$('a.djmodal'), $modal_options );
			}
		});
		");
		//djdebug(JURI::getInstance()->current());
		
		self::$_modal = true;
	}
	
	public function getAnimationOptions(&$params) {
		
		$transition = $params->get('effect');
		$easing = $params->get('effect_type');
		if(!is_numeric($duration = $params->get('duration'))) $duration = 0;
		if(!is_numeric($delay = $params->get('delay'))) $delay = 3000;
		
		if($params->get('slider_type')=='fade' && !$duration && !$easing) {
			$transition = 'Sine';
			$easing = 'easeInOut';
			$duration = 400;
		} else if($params->get('slider_type')=='ifade' && !$duration && !$easing) {
			$transition = 'Expo';
			$easing = 'easeOut';
			$duration = 800;
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
		// add transition duration to delay
		$delay = $delay + $duration;
		
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
		
		$width = $params->get('image_width');
		if($params->get('desc_position')=='left'||$params->get('desc_position')=='right') $width += $params->get('desc_width');
		
		$options[] = "autoplay: ".$params->get('autoplay');
		$options[] = "pause_autoplay: ".$params->get('pause_autoplay');
		$options[] = "transition: '".$transition."'";
		$options[] = "duration: $duration";
		$options[] = "delay: $delay";
		$options[] = "slider_type: '".$params->get('slider_type')."'";
		$options[] = "desc_effect: '".$params->get('desc_effect')."'";
		$options[] = "width: $width";
		$options[] = "height: ".$params->get('image_height');
		$options[] = "spacing: ".$params->get('space_between_images');
		$options[] = "navi_margin: ".$params->get('arrows_horizontal');
		$options[] = "preload: ".$params->get('preload');
		$options[] = "wcag: ".$params->get('wcag');
		$options[] = "kenburns: ".$params->get('kenburns');
		
		return $options;
	}
	
	static public function getMooTransition($transition) {
	
		$parts = explode('.', $transition);
	
		$easing = '';
		if(isset($parts[1])) {
			switch($parts[1]) {
				case 'easeIn': $easing = ':in'; break;
				case 'easeOut': $easing = ':out'; break;
				default: $easing = ':in:out'; break;
			}
		}
	
		return strtolower($parts[0]).$easing;
	
	}
	
	static public function getCSS3Transition($transition, $easing) {
	
		switch($easing) {
				
			case '': return 'linear';
			case 'easeInOut':
				switch($transition) {
					case 'Quad': 	return 'cubic-bezier(0.455, 0.030, 0.515, 0.955)';
					case 'Cubic': 	return 'cubic-bezier(0.645, 0.045, 0.355, 1.000)';
					case 'Quart':	return 'cubic-bezier(0.645, 0.045, 0.355, 1.000)';
					case 'Quint': 	return 'cubic-bezier(0.860, 0.000, 0.070, 1.000)';
					case 'Sine': 	return 'cubic-bezier(0.445, 0.050, 0.550, 0.950)';
					case 'Expo': 	return 'cubic-bezier(1.000, 0.000, 0.000, 1.000)';
					case 'Circ': 	return 'cubic-bezier(0.785, 0.135, 0.150, 0.860)';
					case 'Back': 	return 'cubic-bezier(0.680, -0.550, 0.265, 1.550)';
					default: 		return 'ease-in-out';
				}
			case 'easeOut':
				switch($transition) {
					case 'Quad': 	return 'cubic-bezier(0.250, 0.460, 0.450, 0.940)';
					case 'Cubic': 	return 'cubic-bezier(0.215, 0.610, 0.355, 1.000)';
					case 'Quart':	return 'cubic-bezier(0.165, 0.840, 0.440, 1.000)';
					case 'Quint': 	return 'cubic-bezier(0.230, 1.000, 0.320, 1.000)';
					case 'Sine': 	return 'cubic-bezier(0.390, 0.575, 0.565, 1.000)';
					case 'Expo': 	return 'cubic-bezier(0.190, 1.000, 0.220, 1.000)';
					case 'Circ': 	return 'cubic-bezier(0.075, 0.820, 0.165, 1.000)';
					case 'Back': 	return 'cubic-bezier(0.175, 0.885, 0.320, 1.275)';
					default: 		return 'ease-out';
				}
			case 'easeIn':
				switch($transition) {
					case 'Quad': 	return 'cubic-bezier(0.550, 0.085, 0.680, 0.530)';
					case 'Cubic': 	return 'cubic-bezier(0.550, 0.055, 0.675, 0.190)';
					case 'Quart':	return 'cubic-bezier(0.895, 0.030, 0.685, 0.220)';
					case 'Quint': 	return 'cubic-bezier(0.755, 0.050, 0.855, 0.060)';
					case 'Sine': 	return 'cubic-bezier(0.470, 0.000, 0.745, 0.715)';
					case 'Expo': 	return 'cubic-bezier(0.950, 0.050, 0.795, 0.035)';
					case 'Circ': 	return 'cubic-bezier(0.600, 0.040, 0.980, 0.335)';
					case 'Back': 	return 'cubic-bezier(0.600, -0.280, 0.735, 0.045)';
					default: 		return 'ease-in';
				}
			default: return 'ease';
		}
	}
	
	public function addStyles(&$params){
		
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		
		if($params->get('kenburns')) {
			$document->addStyleSheet(JURI::root(true).'/components/com_djmediatools/assets/css/default.css');
		}
		
		$options = $this->getStyleSheetParams($params);
		$file = 'media/djmediatools/css/' . $this->_prefix . '_' . md5(implode('&',$options)) . '.css';
		
		// Get the css file path and 
		$path = JPATH_ROOT.DS.'components'.DS.'com_djmediatools'.DS.'layouts'.DS.'slideshow'.DS.'css'.DS.'slideshow.css.php';
		$ipath = JURI::root(true).'/components/com_djmediatools/layouts/slideshow';
		$tp_path = JPATH_ROOT.DS.'templates'.DS.$app->getTemplate().DS.'css'.DS.$this->_prefix.'.css.php';
		$com_path = JPATH_ROOT.DS.'components'.DS.'com_djmediatools'.DS.'layouts'.DS.$this->_prefix.DS.'css'.DS.$this->_prefix.'.css.php';
		if(JFile::exists($tp_path)) {
			$path = $tp_path;
			$ipath = JURI::root(true).'/templates/'.$app->getTemplate();
		} else if(JFile::exists($com_path)) {
			$path = $com_path;
			$ipath = JURI::root(true).'/components/com_djmediatools/layouts/'.$this->_prefix;
		}
		
		if(!JFile::exists(JPATH_ROOT . DS . $file) || filemtime(JPATH_ROOT . DS . $file) < filemtime($path)) {
			
			ob_start();
			
			require($path);
			
			$buffer = ob_get_clean();
			
			if(!JFile::write(JPATH_ROOT . DS . $file, $buffer)) {
				// if write to file faild we have to add the styles anyway
				// add parameters to the css file path
				$urloptions = '';
				foreach($options as $key => $val) {
					$urloptions.='&'.$key.'='.$val;
				}
				$file = 'index.php?option=com_djmediatools&amp;task=getcss&amp;format=raw&amp;layout='.$this->_prefix.'&amp;params='.base64_encode($urloptions);
			}
		}
		
		$document->addStyleSheet(JURI::root(true).'/'.$file);
	}
	
	public function getStyleSheetParams(&$params) {
		
		$mid = $params->get('gallery_id');
		$slide_width = $params->get('image_width');
		$slide_height = $params->get('image_height');
		$desc_width = $params->get('desc_width');
		$desc_position = $params->get('desc_position');
		if($desc_position == 'over') {
			if($desc_width > $slide_width) $desc_width = $slide_width;
			$desc_bottom = $params->get('desc_bottom');
			$desc_left = $params->get('desc_horizontal');
		} else if($desc_position == 'left' || $desc_position == 'right'){
			$slide_width += $desc_width;
		}
		$arrows_top = $params->get('arrows_top');
		$arrows_horizontal = $params->get('arrows_horizontal');
		$arrows_top = (($arrows_top / $slide_height) * 100);
		
		$desc_width = (($desc_width / $slide_width) * 100);
		if($desc_position == 'over') {
			$desc_left = (($desc_left / $slide_width) * 100);
			$desc_bottom = (($desc_bottom / $slide_height) * 100);
		}			
		
		$options['mid'] = $mid;
		$options['w'] = $slide_width;
		$options['h'] = $slide_height;
		$options['dp'] = $desc_position;
		$options['dw'] = $desc_width;
		$options['at'] = $arrows_top;
		$options['ah'] = $arrows_horizontal;
		$options['cnp'] = $params->get('custom_nav_pos');
		$options['cna'] = $params->get('custom_nav_align');
		
		$options['lip'] = $params->get('loader_position');
		if($desc_position == 'over') {
			$options['db'] = $desc_bottom;
			$options['dl'] = $desc_left;
		}
		
		$options['r'] = $params->get('resizing');
		
		return $options;
	}
	
	public function debug($msg,$type='message'){
	
		$app=JFactory::getApplication();
		$app->enqueueMessage('<pre>'.print_r($msg,true).'</pre>',$type);
	
	}
}

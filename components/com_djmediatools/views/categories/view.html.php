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
 
defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pagination');

class DJMediatoolsViewCategories extends JViewLegacy {
	
	protected $params = null;
	protected $category = null;
	protected $categories = null;
	protected $pagination = null;
	protected $state = null;
	
	function display($tpl = null) {
		
		// Initialise variables
		$category	= $this->get('Item');
		$categories	= $this->get('Items');		
		$pagination = $this->get('Pagination');
		$params		= $this->get('Params');
		$state 		= $this->get('State');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
		
		if ($category === false)
		{
			JError::raiseError(404, JText::_('COM_DJMEDIATOOLS_ERROR_CATEGORY_NOT_FOUND'));
			return false;
		}
		
		if ($this->getLayout() == 'modal') {
			foreach($categories as $item) {
				if(!$item->thumb = DJImageResizer::createThumbnail($item->image, 'media/djmediatools/cache', 60, 40, 'crop', 80)) {
					$item->thumb = 'administrator/components/com_djmediatools/assets/icon-48-category.png';
				}
				if(strcasecmp(substr($item->thumb, 0, 4), 'http') != 0 && !empty($item->thumb)) {
					$item->thumb = JURI::root(true).'/'.$item->thumb;
				}
			}
		} else {
			foreach($categories as $item) {
				if(!$item->thumb = DJImageResizer::createThumbnail($item->image, 'media/djmediatools/cache', $params->get('cwidth', 200), $params->get('cheight', 150), $params->get('cresizing', 'crop'), $params->get('cquality', 80))) {
					$item->thumb = 'administrator/components/com_djmediatools/assets/icon-album.png';
				}
				if(strcasecmp(substr($item->thumb, 0, 4), 'http') != 0 && !empty($item->thumb)) {
					$item->thumb = JURI::root(true).'/'.$item->thumb;
				}
			}
		}
		
		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));
		
		$this->assignRef('params', $params);
		$this->assignRef('category', $category);
		$this->assignRef('categories', $categories);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('state', $state);
		
		$this->_prepareDocument();
		
        parent::display($tpl);
	}
	
	protected function _prepareDocument() {
			
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$pathway= $app->getPathway();
		$title	= null;
		
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_DJMEDIATOOLS'));
		}
		$title = $this->params->get('page_title', '');
		
		if ($menu && ($menu->query['option'] != 'com_djmediatools' || $menu->query['view'] != 'categories'))
		{
			$pathway->addItem(JText::_('COM_DJMEDIATOOLS_CATEGORIES'), '');
		}
		
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		
		$this->document->addCustomTag('<meta property="og:title" content="'.trim($title).'" />');
		if (!empty($this->category->description)) {
			$metadesc = JHtml::_('string.truncate', $this->category->description, 300, true, false);
			$this->document->addCustomTag('<meta property="og:description" content="'.trim($metadesc).'" />');
		}
		if($this->category != 'root') {
			$link = JRoute::_(DJMediatoolsHelperRoute::getCategoryRoute($this->category->id.':'.$this->category->alias, $this->category->parent_id), true, -1);
		} else {
			$link = JURI::current();
		}
		$this->document->addCustomTag('<meta property="og:url" content="'.$link.'" />');
		if (!empty($this->category->image)) {
			if(!$image = DJImageResizer::createThumbnail($this->category->image, 'media/djmediatools/cache', 1200, 630, 'toWidth', 75)) {
				$image = $this->category->image;
			}
			
			$path = JPath::clean(JPATH_ROOT . '/' .$image);
			$size = @getimagesize($path);
			
			if(strcasecmp(substr($image, 0, 4), 'http') != 0) {
				$image = JURI::root().$image;
			}
			$this->document->addCustomTag('<meta property="og:image" content="'.$image.'" />');
			if(isset($size[0])) $this->document->addCustomTag('<meta property="og:image:width" content="'.$size[0].'" />');
			if(isset($size[1])) $this->document->addCustomTag('<meta property="og:image:height" content="'.$size[1].'" />');
		}
	}

}





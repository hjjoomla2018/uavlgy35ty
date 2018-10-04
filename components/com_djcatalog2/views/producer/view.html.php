<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.html.pagination');

class DJCatalog2ViewProducer extends JViewLegacy {
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/producer');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/producer');
		}
	}
	function display($tpl = null) {
		$app = JFactory::getApplication();
		
		$document= JFactory::getDocument();
		
		$model = $this->getModel();
		$params = Djcatalog2Helper::getParams();
	   	$menus = $app->getMenu('site');
		$menu  = $menus->getActive();
		$dispatcher	= JEventDispatcher::getInstance();
		$categories = Djc2Categories::getInstance(array('state'=>'1'));
		
		$item = $model->getData();
		
		/* If Item not published set 404 */
		if ($item->id == 0 || !$item->published)
		{
			throw new Exception(JText::_('COM_DJCATALOG2_PRODUCT_NOT_FOUND'), 404);
		}
		

		/* plugins */
		JPluginHelper::importPlugin('djcatalog2');
		$results = $dispatcher->trigger('onPrepareItemDescription', array (& $item, & $params, 0));
		
		$this->assignref('categories', $categories);
		$this->assignref('item', $item);
		$this->assignref('images', $images);
		$this->assignref('params', $params);
		$this->_prepareDocument();
		parent::display($tpl);
	}
	protected function _prepareDocument() {
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;
		$heading		= null;

		$menu = $menus->getActive();
		
		$cid = (int) @$menu->query['cid'];
		$pid = (int) @$menu->query['pid'];
		
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		$title = $this->params->get('page_title', '');
		
		if (!empty($this->item->metatitle)) {
			$title = $this->item->metatitle;
		}
		
		$metakeys = null;
		$metadesc = null;

		if ($menu && ($menu->query['option'] != 'com_djcatalog2' || $menu->query['view'] != 'producer' || $pid != $this->item->id)) {
			
			if ($this->item->metatitle) {
				$title = $this->item->metatitle;
			}
			else if ($this->item->name) {
				$title = $this->item->name;
			}
			$path = array(array('title' => $this->item->name, 'link' => ''));

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
			
			$this->params->set('page_heading', $this->item->name);
		} else if (!empty($menu)) {
			if ($this->params->get('menu-meta_description')) {
				$metadesc = $this->params->get('menu-meta_description');
			}
			if ($this->params->get('menu-meta_keywords')) {
				$metakeys = $this->params->get('menu-meta_keywords');
			}
		}
		
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0)) {
			if ($app->getCfg('sitename_pagetitles', 0) == '2') {
				$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
			} else {
				$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
			}
		}
		$this->document->setTitle($title);
		
		foreach($this->document->_links as $key => $headlink) {
			if ($headlink['relation'] == 'canonical' ) {
				unset($this->document->_links[$key]);
			}
		}
		
		$canonical = JRoute::_(DJCatalogHelperRoute::getProducerRoute($this->item->prodslug), true, (JUri::getInstance()->isSSL() ? 1 : -1));
		//$canonical = JUri::base(false).substr(JRoute::_($canonical), strlen(JUri::base(true)) + 1);

		$this->document->addHeadLink($canonical, 'canonical');

		if (!empty($this->item->metadesc))
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif (!empty($metadesc)) 
		{
			$this->document->setDescription($metadesc);
		}

		if (!empty($this->item->metakey))
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif (!empty($metakeys)) 
		{
			$this->document->setMetadata('keywords', $metakeys);
		}
		
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		
		// Preparing data for Social Networks
		
		$description = null;
		if ($this->item->metadesc) {
			$description = $this->item->metadesc;
		} else if ($metadesc) {
			$description = $metadesc;
		} else {
			$description = $this->item->description;
		}
		
		$description = JHtml::_('string.truncate', $description, 300, true, false);
		$item_images = DJCatalog2ImageHelper::getImages('producer',$this->item->id);
		$url = JRoute::_(DJCatalogHelperRoute::getProducerRoute($this->item->id.':'.$this->item->alias), true, (JUri::getInstance()->isSSL() ? 1 : -1));
		//$url = JUri::base(false).substr($url, strlen(JUri::base(true)) + 1);
		$image_size = null;
		
		if (isset($item_images[0])) {
			$image_path = DJCatalog2ImageHelper::getImagePath($item_images[0]->fullpath, 'fullscreen');
			$image_size = @getimagesize($image_path);
		}
		
		// Facebook OG
		$this->document->addCustomTag('<meta property="og:title" content="'.trim($title).'" />');
		$this->document->addCustomTag('<meta property="og:description" content="'.$description.'" />');
		$this->document->addCustomTag('<meta property="og:url" content="'.$url.'" />');
		
		if (isset($item_images[0])) {
			$this->document->addCustomTag('<meta property="og:image" content="'.$item_images[0]->fullscreen.'" />');
		
			if (is_array($image_size) && count($image_size) > 1) {
				$this->document->addCustomTag('<meta property="og:image:width" content="'.$image_size[0].'" />');
				$this->document->addCustomTag('<meta property="og:image:height" content="'.$image_size[1].'" />');
			}
		}
		
		// Twitter Cards
		$this->document->addCustomTag('<meta property="twitter:card" content="summary" />');
		$this->document->addCustomTag('<meta property="twitter:title" content="'.trim($title).'" />');
		$this->document->addCustomTag('<meta property="twitter:description" content="'.$description.'" />');
		
		if (isset($item_images[0])) {
			$this->document->addCustomTag('<meta property="twitter:image:src" content="'.$item_images[0]->fullscreen.'" />');
		
			if (is_array($image_size) && count($image_size) > 1) {
				$this->document->addCustomTag('<meta property="twitter:image:width" content="'.$image_size[0].'" />');
				$this->document->addCustomTag('<meta property="twitter:image:height" content="'.$image_size[1].'" />');
			}
		}
	}
}

?>
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

class DJCatalog2ViewItem extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/item');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/item');
		}
	}
	
	function display($tpl = null) {
		$app = JFactory::getApplication();
		$document= JFactory::getDocument();
		$model = $this->getModel();
		
		$menus		= $app->getMenu('site');
		$menu  = $menus->getActive();
		$dispatcher	= JEventDispatcher::getInstance();
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		
		$categories = Djc2Categories::getInstance(array('state'=>'1', 'access'=> $groups));
		
		$limitstart	= $app->input->get('limitstart', 0, 'int');
		
		$state = $this->get('State');
		$item = $this->get('Item');
		$this->contactform	= $this->get('Form');
		$this->showcontactform = ($app->getUserState('com_djcatalog2.contact.data')) ? 'false' : 'true';

		if (empty($item) || $item->published <= 0) {
			throw new Exception(JText::_('COM_DJCATALOG2_PRODUCT_NOT_FOUND'), 404);
		}

		$catid = (int)$app->input->getInt('cid');
		$category = $categories->get($item->cat_id);
		$current_category = ($catid == $item->cat_id) ? $category : $categories->get($catid);
		
		$errorAction = false;
		
		if (empty($category)) {
			$allCategories =  Djc2Categories::getInstance();
			$category2nd = $allCategories->get($item->cat_id);
			
			if (empty($category2nd)) {
				// product category really doesn't exist
				$errorAction = 404;
			} else {
				// user doesn't have access to this category
				$errorAction = 303;
			}
		}
		if (empty($current_category) && $catid != $item->cat_id) {
			$allCategories =  Djc2Categories::getInstance();
			$category2nd = $allCategories->get($catid);
			
			if (empty($category2nd)) {
				// product category really doesn't exist
				$errorAction = 404;
			} else {
				// user doesn't have access to this category
				$errorAction = 303;
			}
		} else if (!empty($current_category) && !in_array($current_category->access, $groups)) {
			$errorAction = 303;
		}
		
		if ($errorAction) {
			if (!$user->guest && ($errorAction == 301 || $errorAction == 303) ) {
				$errorAction = 403;
			}
			
			switch($errorAction) {
				case 404 : {
					throw new Exception(JText::_('COM_DJCATALOG2_PRODUCT_NOT_FOUND'), 404);
					return false;
					break;
				}
				case 403: {
					throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
					return false;
					break;
				}
				case 301 :
				case 303 : {
					$return_url = base64_encode((string)JUri::getInstance());
					$app->enqueueMessage(JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
					$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), $errorAction);
					return true;
					
				}
				default : {
					throw new Exception(JText::_('COM_DJCATALOG2_PRODUCT_NOT_FOUND'), 404);
					return false;
					break;
				}
			}
		}
		
		/*if ($item->parent_id > 0) {
			$app->redirect(JRoute::_(DJCatalogHelperRoute::getItemRoute($item->parent_id.':'.$item->alias, $item->cat_id.':'.$item->category_alias)));
		}*/
		
		// if category id in the URL differs from product's category id
		// we add canonical link to document's header
		/*if (JString::strcmp(DJCatalogHelperRoute::getItemRoute($item->slug, (int)$item->cat_id), DJCatalogHelperRoute::getItemRoute($item->slug, (int)$catid)) != 0) {
			$document->addHeadLink(JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)), 'canonical');
			//$document->addCustomTag('<link rel="canonical" href="'.JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug)).'"/>');
		}*/
		
		foreach($this->document->_links as $key => $headlink) {
			if ($headlink['relation'] == 'canonical' ) {
				unset($this->document->_links[$key]);
			}
		}
		
		$canonical = JRoute::_(DJCatalogHelperRoute::getItemRoute($item->slug, $item->catslug), true, (JUri::getInstance()->isSSL() ? 1 : -1));
		$this->document->addHeadLink($canonical, 'canonical');
		
		$app->input->set('refcid', $app->input->getString('cid'));
		
		// if category id is not present in the URL or it equals 0
		// we set it to product's cat id
		if ($catid == 0) {
			$app->input->set('cid', $item->cat_id);
		}
		
		// params in this view should be generated only after we make sure
		// that product's cat id is in the request.
		$params = Djcatalog2Helper::getParams(true);
		if (!empty($item)) {
			if (!empty($item->params)) {
				$item_params = new JRegistry($item->params);
				$params->merge($item_params);
				$item->params = $item_params;
			} else {
				$item->params = new JRegistry($item->params);
			}
		}
		
		if (!in_array($item->access, $groups))
		{
			if ($params->get('items_show_restricted') && $user->guest) {
				$uri = JURI::getInstance();
				$return_url = base64_encode((string)$uri);
				$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
				return true;
			} else {
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}
		}
		
		/* plugins */
		JPluginHelper::importPlugin('djcatalog2');
		JPluginHelper::importPlugin('content');
		
		$results = $dispatcher->trigger('onPrepareItemDescription', array (& $item, & $params, $limitstart));
		
		$item->event = new stdClass();
		$item->event->afterDJCatalog2DisplayTitle = false;
		$item->event->beforeDJCatalog2DisplayContent = false;
		$item->event->afterDJCatalog2DisplayContent = false;
		
		if ($this->getLayout() != 'print') {
			$resultsAfterTitle = $dispatcher->trigger('onAfterDJCatalog2DisplayTitle', array (&$item, &$params, $limitstart));
			$item->event->afterDJCatalog2DisplayTitle = trim(implode("\n", $resultsAfterTitle));
			
			$resultsBeforeContent = $dispatcher->trigger('onBeforeDJCatalog2DisplayContent', array (&$item, &$params, $limitstart));
			$item->event->beforeDJCatalog2DisplayContent = trim(implode("\n", $resultsBeforeContent));
			
			$resultsAfterContent = $dispatcher->trigger('onAfterDJCatalog2DisplayContent', array (&$item, &$params, $limitstart));
			$item->event->afterDJCatalog2DisplayContent = trim(implode("\n", $resultsAfterContent));
		}

		$this->assignref('categories', $categories);
		$this->assignref('category', $category);
		$this->assignref('item', $item);
		$this->assignref('images', $images);
		$this->assignref('files', $files);
		
		$this->assignref('params', $params);
		$this->relateditems = $model->getRelatedItems();
		$this->attributes = $model->getAttributes();
		//$this->cart_attributes = $model->getCartAttributes();
		$this->cart_variant_fields = $model->getCartVariants();
		$this->item->_combinations = $model->getCombinations($this->item->id);
		$this->item->_customisations = $model->getCustomisations($this->item->id);
		$this->cart_customisations = $model->getCustomisations(0);
		
		$this->all_customisations = array_merge($this->item->_customisations, $this->cart_customisations);
		
		$this->customisations_form = $model->getCustomisationsForm($this->all_customisations, $this->item, $this->params, $app->getUserState('com_djcatalog2.recent_customisation', array()));
		
		$this->navigation = $model->getNavigation($this->item->id, $this->item->cat_id, $params);
		
		$this->children = $model->getChildren($this->item->id);
		if (!empty($this->children)) {
			$childrenModel = $model->getChildrenModel();
			$this->childrenAttributes = $childrenModel->getAttributes();
			$this->childrenColumns = $childrenModel->getFieldGroups($childrenModel);
		}
		
		if ($app->input->get('pdf') == '1' && $app->input->get('tmpl') == 'component' && $this->getLayout() == 'print') {

			if (JFile::exists(JPath::clean(JPATH_ROOT.'/libraries/dompdf/dompdf_config.inc.php')) == false) {
				throw new Exception('DOMPDF Libary is missing!');
			}
			
			$this->_preparePDF();
			
			$app->close();
			return true;
		}
		
		$this->_prepareDocument();
		
		$model->hit();
		
		parent::display($tpl);
	}
	protected function _prepareDocument() {
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;
		$heading		= null;
		$document= JFactory::getDocument();
		$menu = $menus->getActive();
		
		$id = (int) @$menu->query['id'];
		$cid = (int) @$menu->query['cid'];
		
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		
		$title = $this->params->get('page_title', '');
		
		if (!empty($this->item->metatitle)) {
			$title = $this->item->metatitle;
		}
		
		$headingOverride = $this->params->get('item_heading_override', 0);
		
		if ($this->item->id > 0) {
			$heading = $this->item->heading ? trim($this->item->heading): trim($this->item->name);
			if ($headingOverride == '1') {
				$this->params->set('page_heading', $heading);
			}
		}
		
		if ($headingOverride == '1') {
			$this->params->set('show_page_heading', 1);
		}
		else if ($headingOverride == '-1') {
			$this->params->set('show_page_heading', 0);
		}
		
		$metakeys = null;
		$metadesc = null;

		if ($menu && ($menu->query['option'] != 'com_djcatalog2' || $menu->query['view'] == 'items' || $id != $this->item->id )) {
			
			if ($this->item->metatitle) {
				$title = $this->item->metatitle;
			}
			else if ($this->item->name) {
				$title = $this->item->name;
			}
			$category = $this->categories->get($this->item->cat_id);
			$path = array(array('title' => $this->item->name, 'link' => ''));
			while (($menu->query['option'] != 'com_djcatalog2' || ($menu->query['view'] == 'items' && $cid != $category->id)) && $category->id > 0)
			{
				$path[] = array('title' => $category->name, 'link' => DJCatalogHelperRoute::getCategoryRoute($category->catslug));
				$category = $this->categories->get($category->parent_id);
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
			
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

		if (!empty($this->item->metadesc))
		{
			$this->document->setDescription($this->item->metadesc);
			$metadesc = $this->item->metadesc;
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
		} else if (strip_tags($this->item->intro_desc) != '') {
			$description = $this->item->intro_desc;
		} else {
			$description = $this->item->description;
		}
		
		$description = JHtml::_('string.truncate', $description, 300, true, false);
		$item_images = DJCatalog2ImageHelper::getImages('item',$this->item->id);
		$url = JRoute::_(DJCatalogHelperRoute::getItemRoute($this->item->slug, $this->item->catslug), true, (JUri::getInstance()->isSSL() ? 1 : -1));
		
		$image_size = null;
		
		if (isset($item_images[0])) {
			$image_path = DJCatalog2ImageHelper::getImagePath($item_images[0]->fullpath, 'fullscreen');
			$image_size = @getimagesize($image_path);
		}
		
		
		// Facebook OG
		$this->document->addCustomTag('<meta property="og:title" content="'.trim($title).'" />');
		if ($description) {
			$this->document->addCustomTag('<meta property="og:description" content="'.$description.'" />');
		}
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
		if ($description) {
			$this->document->addCustomTag('<meta property="twitter:description" content="'.$description.'" />');
		}
		if (isset($item_images[0])) {
			$this->document->addCustomTag('<meta property="twitter:image:src" content="'.$item_images[0]->fullscreen.'" />');
			
			if (is_array($image_size) && count($image_size) > 1) {
				$this->document->addCustomTag('<meta property="twitter:image:width" content="'.$image_size[0].'" />');
				$this->document->addCustomTag('<meta property="twitter:image:height" content="'.$image_size[1].'" />');
			}
		}
		
	}
	protected function _preparePDF() {
		if (!defined('DOMPDF_ENABLE_REMOTE'))
		{
			define('DOMPDF_ENABLE_REMOTE', true);
		}
			
		$config = JFactory::getConfig();
		$document = JFactory::getDocument();
		
		$document->setMimeEncoding('application/pdf');
		
		if (!defined('DOMPDF_FONT_CACHE'))
		{
			define('DOMPDF_FONT_CACHE', $config->get('tmp_path'));
		}
		
		if (!defined('DOMPDF_DEFAULT_FONT'))
		{
			define('DOMPDF_DEFAULT_FONT', 'DejaVuSans');
		}

		require_once JPath::clean(JPATH_ROOT.'/libraries/dompdf/dompdf_config.inc.php');
		
		if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}
		
		$pdf =new DOMPDF();
		
		ob_start();
		parent::display(null);
		$body = ob_get_contents();
		ob_end_clean();

		$document->_scripts = array();
		$document->_script = array();
		
		$head = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'; //$document->getBuffer('head');
		
		$data = '<html><head>'.$head.'</head><body style="font-family: firefly, DejaVu Sans, sans-serif !important;">'.$body.'</body></html>';
		
		DJCatalog2HtmlHelper::setFullPaths($data);

		$pdf->load_html($data);
		$pdf->render();
		$pdf->stream(JFile::makeSafe($this->item->name) . '.pdf');
	}
}

?>
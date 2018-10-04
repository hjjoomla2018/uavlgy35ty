<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class Djcatalog2ViewItemform extends JViewLegacy {
	protected $state;
	protected $item;
	protected $form;
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/itemform');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/itemform');
		}
	}
	
	public function display($tpl = null)
	{
		
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->params = Djcatalog2Helper::getParams();
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$authorised = false;
		if (empty($this->item->id)) {
			$authorised = $user->authorise('core.create', 'com_djcatalog2');
		}
		else {
			if ($user->authorise('core.edit', 'com_djcatalog2')) {
				$authorised = true;
			} else {
				$ownerId	= (int) $this->item->created_by;
				if (!$user->guest && $ownerId == $user->id && $user->authorise('core.edit.own', 'com_djcatalog2')) {
					$authorised = true;
				}
			}
		}
		
		if ($authorised !== true) {
			if ((bool)$user->guest && empty($this->item->id)) {
				$return_url = base64_encode(DJCatalogHelperRoute::getMyItemsRoute());
				$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
				return true;
			} else {
				JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
				return false;
			}
		}
		
		$document = JFactory::getDocument();
		//JHTML::_('behavior.framework');
		JHTML::_('behavior.calendar');
		$document->addScriptDeclaration('var djc_joomla_base_url = \''.JUri::base().'\';');
        $document->addScript(JURI::base() . "components/com_djcatalog2/views/itemform/itemform.js");
		$document->addScript(JURI::base() . "components/com_djcatalog2/assets/nicEdit/nicEdit.js");
		
		$this->_prepareDocument();
		
		parent::display($tpl);
	}
	
	protected function _prepareDocument() {
		$app		= JFactory::getApplication();

		$title = ($this->item->id > 0) ? JText::sprintf('COM_DJCATALOG2_ITEM_EDIT_HEADING', $this->item->name) : JText::_('COM_DJCATALOG2_ITEM_SUBMISSION_HEADING');
		
		$this->params->set('page_heading', $title);
		
		
		if ($app->getCfg('sitename_pagetitles', 0)) {
			if ($app->getCfg('sitename_pagetitles', 0) == '2') {
				$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
			} else {
				$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
			}
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
	}
}
?>
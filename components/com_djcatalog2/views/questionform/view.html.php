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

class Djcatalog2ViewQuestionform extends JViewLegacy {
	protected $state;
	protected $item;
	protected $form;
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/questionform');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/questionform');
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
		
		if (!$this->item->id) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}
		
		$authorised = $user->authorise('djcatalog2.salesman', 'com_djcatalog2') || $user->authorise('core.admin', 'com_djcatalog2');

		if ($authorised !== true) {
			if ((bool)$user->guest) {
				$return_url = base64_encode((string)JUri::getInstance());
				$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
				return true;
			} else {
				JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
				return false;
			}
		}
		
		$this->_prepareDocument();
		
		parent::display($tpl);
	}
	
	protected function _prepareDocument() {
		$app		= JFactory::getApplication();

		$userDetails = array();
		if ($this->item->lastname) {
			$userDetails[] = $this->item->lastname;
		}
		if ($this->item->firstname) {
			$userDetails[] = $this->item->firstname;
		}
		if ($this->item->company) {
			$userDetails[] = $this->item->company;
		}
		
		$title = JText::sprintf('COM_DJCATALOG2_QUERY_EDIT_HEADING', implode(', ', $userDetails));
		
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
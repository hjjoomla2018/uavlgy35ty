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

class DJCatalog2ViewQuestion extends JViewLegacy {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->_addPath('template', JPATH_COMPONENT.  '/themes/default/views/question');
		$theme = DJCatalog2ThemeHelper::getThemeName();
		if ($theme && $theme != 'default') {
			$this->_addPath('template', JPATH_COMPONENT.  '/themes/'.$theme.'/views/question');
		}
	}
	
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		$this->params = Djcatalog2Helper::getParams();
		
		$model = JModelLegacy::getInstance('Query', 'Djcatalog2Model', array());
		$this->setModel($model, true);
		$this->model = $this->getModel();
		
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		
		$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2');
		
		if (empty($this->item)) {
			return JError::raiseError(404, JText::_('COM_DJCATALOG2_QUESTION_NOT_FOUND'));
			return;
		}
		
		if ($user->guest) {
			$return_url = base64_encode(DJCatalogHelperRoute::getQuestionRoute($this->item->id));
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJCATALOG2_PLEASE_LOGIN'));
			return true;
		}
		
		if ($user->id != $this->item->user_id && !$salesman) {
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		$this->items = $this->item->items;
		
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
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
		$menu_query = (!empty($menu->query)) ? $menu->query : array();
		$option = (!empty($menu_query['option'])) ? $menu_query['option'] : null;
		$view = (!empty($menu_query['view'])) ? $menu_query['view'] : null;
		
		$this->params->set('page_heading', JText::_('COM_DJCATALOG2_QUESTION_HEADING'));
		
		$title = JText::_('COM_DJCATALOG2_QUESTION_HEADING');

		if ($app->getCfg('sitename_pagetitles', 0)) {
			if ($app->getCfg('sitename_pagetitles', 0) == '2') {
				$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
			} else {
				$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
			}
		}

		$this->document->setTitle($title);
	}

}





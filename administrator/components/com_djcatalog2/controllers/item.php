<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class Djcatalog2ControllerItem extends JControllerForm {
	function __construct($config = array())
	{
		$this->view_list = 'items';
		$this->view_item = 'item';
		parent::__construct($config);
		
	}
	
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$model = $this->getModel('Item', '', array());
		
		$this->setRedirect(JRoute::_('index.php?option=com_djcatalog2&view=items' . $this->getRedirectToListAppend(), false));
		return parent::batch($model);
	}
}
?>

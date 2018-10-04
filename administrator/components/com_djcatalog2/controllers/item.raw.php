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
	function download() {
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$authorised = $user->authorise('djcatalog2.filedownload', 'com_djcatalog2');
		
		if ($authorised !== true) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		if ($out = DJCatalog2FileHelper::getFile($app->input->get('fid','','int'))) {
			$app->input->set('format','raw');
			echo $out;
		}
		else {
			JError::raiseError(404);
			return false;
		}
	}
}
?>

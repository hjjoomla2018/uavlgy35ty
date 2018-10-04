<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

class Djcatalog2ControllerOrderform extends JControllerForm {
	function __construct($config = array())
	{
		$this->view_list = 'orders';
		$this->view_item = 'orderform';
		
		parent::__construct($config);
		
		$this->unregisterTask('save2copy');
		
	}
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$app = JFactory::getApplication();
		$tmpl   = $app->input->get('tmpl');
		
		// got rid of edit layout
		$layout = $app->input->get('layout');
		$append = '';
	
		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}
	
		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}
	
		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}
	
		return $append;
	}
	
	protected function getRedirectToListAppend()
	{
		$app = JFactory::getApplication();
		$tmpl = JFactory::getApplication()->input->get('tmpl');
		
		$append = '';
	
		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}
		
		$needles = array(
				'orders' => array(0),
				'items' => array(0)
		);
		
		if ($item = DJCatalogHelperRoute::_findItem($needles)) {
			$append .= '&Itemid='.$item;
		}
		
		return $append;
	}
	
	protected function allowEdit($data = array(), $key = 'id') {
		$user = JFactory::getUser();
		return (bool)($user->authorise('djcatalog2.salesman', 'com_djcatalog2') || $user->authorise('core.admin', 'com_djcatalog2'));
	}
	
	protected function allowAdd($data = array())
	{
		return false;
	}
	
}
?>

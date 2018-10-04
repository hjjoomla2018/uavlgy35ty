<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );

class DJCatalog2ControllerCpanel extends JControllerLegacy
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}

	function display($cachable = true, $urlparams = null) {
		$app = JFactory::getApplication();
		
		$app->input->set( 'layout', 'default'  );
		$app->input->set( 'view'  , 'cpanel');
		$app->input->set( 'edit', false );
		
		parent::display($cachable, $urlparams);
	}
}
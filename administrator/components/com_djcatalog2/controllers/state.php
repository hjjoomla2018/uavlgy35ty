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

class Djcatalog2ControllerState extends JControllerForm {
	public function save($key = null, $urlVar = null) {
		return parent::save($key, $urlVar);
	}
	
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		
		$country_id = JFactory::getApplication()->input->get('country_id');
		if ($country_id) {
			$append .= '&country_id='.$country_id;
		}
		
	
		return $append;
	}
}
?>

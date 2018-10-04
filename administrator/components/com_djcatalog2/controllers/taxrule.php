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

class Djcatalog2ControllerTaxrule extends JControllerForm {
	public function save($key = null, $urlVar = null) {
		return parent::save($key, $urlVar);
	}
	
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		
		$tax_rate_id = JFactory::getApplication()->input->get('tax_rate_id');
		if ($tax_rate_id) {
			$append .= '&tax_rate_id='.$tax_rate_id;
		}
		
	
		return $append;
	}
}
?>

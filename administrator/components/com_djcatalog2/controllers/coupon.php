<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class Djcatalog2ControllerCoupon extends JControllerForm {
	public function save($key = null, $urlVar = null) {
		return parent::save($key, $urlVar);
	}
}
?>

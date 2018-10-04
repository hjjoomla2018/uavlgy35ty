<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

$version = new JVersion;
if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
	abstract class DJCJControllerForm extends JControllerForm
	{
		protected function postSaveHook(JModel &$model, $validData = array())
		{
			if (method_exists($this, '_postSaveHook')) {
				return $this->_postSaveHook($model, $validData);
			}
		}

	}
} else {
	abstract class DJCJControllerForm extends JControllerForm
	{
		protected function postSaveHook(JModelLegacy $model, $validData = array())
		{
			if (method_exists($this, '_postSaveHook')) {
				return $this->_postSaveHook($model, $validData);
			}
		}

	}
}


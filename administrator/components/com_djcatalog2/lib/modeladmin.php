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

jimport('joomla.application.component.modeladmin');

$version = new JVersion;
if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
	abstract class DJCJModelAdmin extends JModelAdmin
	{
		protected function prepareTable(&$table)
		{
			if (method_exists($this, '_prepareTable')) {
				return $this->_prepareTable($table);
			}
		}
		
	}	
} else {
	abstract class DJCJModelAdmin extends JModelAdmin
	{
		protected function prepareTable($table)
		{
			if (method_exists($this, '_prepareTable')) {
				return $this->_prepareTable($table);
			}
		}
	
	}
}


<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class Djcatalog2ViewExtensions extends JViewLegacy
{

	public function display($tpl = null)
	{

		if (empty($this->_layout)) {
			JError::raiseError(404);
			return false;
		}

		parent::display($tpl);
	}
}

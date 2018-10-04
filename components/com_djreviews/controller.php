<?php
/**
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class DJReviewsController extends JControllerLegacy
{

	function __construct($config = array())
	{
		parent::__construct($config);
	}

	function display($cachable = true, $urlparams = null)
	{
		$app = JFactory::getApplication();
		
		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = $app->input->get('view', 'reviews');
		$viewLayout = $app->input->get('layout', 'default', 'string');
		
		$view = $this->getView($viewName, $viewType, 'DJReviewsView', array('base_path' => $this->basePath, 'layout' => $viewLayout));

		$noncachable = array('reviewslist', 'reviewform');

		if (in_array($viewName, $noncachable)) {
			$cachable = false;
		}
		
		$urlparams = array(
				'id' => 'INT',
				'return' => 'BASE64',
				'limitstart' => 'UINT',
				'limit' => 'UINT'
		);
		
		return parent::display($cachable, $urlparams);
	}
}
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

require_once(JPath::clean(JPATH_ROOT.'/components/com_djreviews/lib/controller.review.php'));

class DJReviewsControllerReview extends DJReviewsBaseControllerReview {
	public $controller_type = 'HTML';
	
	public function save($key = null, $urlVar = null)
	{
		$app = JFactory::getApplication();
		$return = base64_decode($app->input->getBase64('return'));
		
		$success = parent::save($key, $urlVar);
		
		if ($success) {
			$this->setRedirect($return, $this->message);
		} else {
			$errors = $this->getErrors();
			for($i = 0; $i < count($errors); $i++) {
				$app->enqueueMessage($this->getError($i, true), 'error');
			}
			$this->setRedirect($return);
		}
		
		return true;
	}
	
	public function delete() {
		$app = JFactory::getApplication();
		$return = base64_decode($app->input->getBase64('return'));
		
		$success = parent::delete();
		
		if ($success) {
			$this->setRedirect($return, $this->message);
		} else {
			$errors = $this->getErrors();
			for($i = 0; $i < count($errors); $i++) {
				$app->enqueueMessage($this->getError($i, true), 'error');
			}
			$this->setRedirect($return);
		}
		
		return true;
	}
}
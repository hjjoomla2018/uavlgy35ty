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
	public $controller_type = 'JSON';
	
	public function add() {
		$app   = JFactory::getApplication();
		
		$response = array(
				'status' => 1,
				'error' => false,
				'error_message' => '',
				'message' => '',
				'object' => array()
		);
		
		try {
			parent::add();
		} catch (Exception $e) {
			$response['status'] = 0;
			$response['error'] = true;
			$response['error_message'] = $e->getMessage();
		}
		
		echo json_encode($response);
		$app->close();
	}

	public function edit($key = null, $urlVar = null)
	{
		$app   = JFactory::getApplication();
		
		$recordId   = $app->input->getInt('id', 0);
		
		$response = array(
				'status' => 1,
				'error' => false,
				'error_message' => '',
				'message' => '',
				'object' => array()
		);
		
		try {
			parent::edit($key, $urlVar);
		} catch (Exception $e) {
			$response['status'] = 0;
			$response['error'] = true;
			$response['error_message'] = $e->getMessage();
			
			echo json_encode($response);
			$app->close();
		}
		
		$model = $this->getModel();
		$item = $model->getItem($recordId);

		if (empty($item) || empty($item->id)) {
			$response['status'] = 0;
			$response['error'] = true;
			$response['error_message'] = JText::_('COM_DJREVIEWS_ERROR_REVIEW_NOT_EXIST');
		} else {
			$response['object'] = JArrayHelper::fromObject($item, true);
		}
		
		echo json_encode($response);
		$app->close();
	}
	
	public function save($key = null, $urlVar = null)
	{
		$app = JFactory::getApplication();
		
		$response = array(
				'status' => 1,
				'error' => false,
				'error_message' => '',
				'message' => JText::_('COM_DJREVIEWS_REVIEW_SUCCESSFULLY_ADDED'),
				'object' => array()
		);
	
		$success = parent::save($key, $urlVar);
	
		if (!$success) {
			
			$errors = $this->getErrors();
			for($i = 0; $i < count($errors); $i++) {
				$response['error_message'] .= $this->getError($i, true).'. ';
			}
			
			$response['status'] = 0;
			$response['error'] = true;
		}
		
		echo json_encode($response);
		$app->close();
	
		return true;
	}
}
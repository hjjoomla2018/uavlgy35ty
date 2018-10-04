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

class Djcatalog2ControllerCustomer extends JControllerForm {
	public function save($key = null, $urlVar = null) {
		return parent::save($key, $urlVar);
	}
	public function edit($key = null, $urlVar = null)
	{
		$app = JFactory::getApplication();
		$cid   = $app->input->get('cid', array(), 'array');
		$recordId = count($cid) ? $cid[0] : 0;
		
		$parts  = explode(',', $recordId);
		if (count($parts) == 2) {
			$cid = $parts[1];
			$user_id = $parts[0];
			$app->input->set('user_id', $user_id);
			$app->input->set('cid', array($cid));
			$app->input->post->set('cid', array($cid));
			$app->input->set('cid', array($cid));
			@JRequest::setVar('cid', array($cid), 'post');
			@JRequest::setVar('user_id', $user_id, 'post');
			
			if (!$cid) {
				return $this->add();
			}
		}
		
		return parent::edit($key, $urlVar);
	}
	public function edituser($key = null, $urlVar = null)
	{
		$app = JFactory::getApplication();
		$cid   = $app->input->post->get('cid', array(), 'array');
		$recordId = count($cid) ? $cid[0] : 0;
		
		$parts  = explode(':', $recordId);
		if (count($parts) == 2) {
			$recordId = $parts[0];
		}
		
		$this->setRedirect('index.php?option=com_users&task=user.edit&id='.$recordId);
		return true;
		
	}
	
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		
		$app = JFactory::getApplication();
		if ($user_id = $app->input->get('user_id')) {
			$append .= '&user_id='.(int)$user_id;
		}
		
		return $append;
	}
}
?>

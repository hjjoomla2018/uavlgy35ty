<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.application.component.controlleradmin');


class Djcatalog2ControllerCustomers extends JControllerAdmin
{
	public function getModel($name = 'Customer', $prefix = 'Djcatalog2Model', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	public function delete()
	{
		$app = JFactory::getApplication();
		$cid   = $app->input->get('cid', array(), 'array');
		
		$new_cids = array();
		
		if (count($cid) > 0) {
			foreach($cid as $k=>$v) {
				$parts  = explode(',', $v);
				if (count($parts) == 2) {
					$new_cid = $parts[1];
					$new_cids[] = $new_cid;
				}
			}
		}
		
		$app->input->post->set('cid', $new_cids);
		$app->input->set('cid', $new_cids);
		@JRequest::setVar('cid', $new_cids, 'post');
		
		return parent::delete();
	}
	
}
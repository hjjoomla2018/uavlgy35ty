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


class Djcatalog2ControllerRelateditems extends JControllerAdmin
{
public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('assignclose',		'assign');
	}
	public function getModel($name = 'Relateditems', $prefix = 'Djcatalog2Model', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	public function assign()
	{
		JHTML::_('behavior.framework');
		$app = JFactory::getApplication();
		
		$task		= $this->getTask();
		$item_id = $app->input->get('item_id',null, 'int');
		$document	= JFactory::getDocument();
		
		$user = JFactory::getUser();
		if (!$user->authorise('core.edit', 'com_djcatalog2')){
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&item_id='.$item_id.'&tmpl=component'.$extensionURL, false));
			return false;
		}
		
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$session	= JFactory::getSession();
		$registry	= $session->get('registry');

		// Get items to publish from the request.
		$cid	= $app->input->get('cid', array(), 'array');
		$listed_cid    = $app->input->get('listed_cid', array(), 'array');
		$task 	= $this->getTask();
		if (empty($item_id)) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_PARENT_ITEM_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);
			JArrayHelper::toInteger($listed_cid);

			// Publish the items.
			if (!$model->assign($cid, $listed_cid, $item_id)) {
				JError::raiseWarning(500, $model->getError());
			}
			else {
				if (count($cid) > 0) {
					$this->setMessage(JText::_($this->text_prefix.'_N_RELATED_ITEMS_ASSIGNED'));
				} else {
					$this->setMessage(JText::_($this->text_prefix.'_RELATED_ITEMS_REMOVED'));
				}
			}
		}
		if ($task == 'assignclose') {
		$document->addScriptDeclaration('jQuery(document).ready(function(){
				window.parent.jQuery("#djc_related_modal").modal("hide");
		});');
		} else {
			$extension = $app->input->get('extension', null, 'cmd');
			$extensionURL = ($extension) ? '&extension=' . $app->input->get('extension', null, 'cmd') : '';
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&item_id='.$item_id.'&tmpl=component'.$extensionURL, false));
		}
		
	}
	
}
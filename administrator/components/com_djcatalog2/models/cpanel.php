<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.helper');
jimport( 'joomla.application.component.model');


class DJCatalog2ModelCPanel extends JModelLegacy {

	function __construct()
	{
		parent::__construct();
	}
	
	function performChecks() {
		$app = JFactory::getApplication();
		$menus = $app->getMenu('site');
		$component	= JComponentHelper::getComponent('com_djcatalog2');
		$menu_items		= $menus->getItems('component_id', $component->id);
		
		$checks = array();
		
		$checks['images'] = DJCATIMGFOLDER;
		$checks['custom_images'] = DJCATIMGFOLDER.DS.'custom';
		$checks['attachments'] = DJCATATTFOLDER;
		$checks['licence'] = JPATH_COMPONENT;

		foreach ($checks as $type => $folder) {
			if (!is_writable($folder)) {
				$app->enqueueMessage(JText::_('COM_DJCATALOG2_FOLDER_CHECK_'.strtoupper($type)), 'warning');
			}
		}
		
		if (!extension_loaded('gd')){
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_GD_CHECK_FAIL'), 'warning');
		}
		
		$root_menu_found = false;
		foreach ($menu_items as $item) {
			if (isset($item->query)) {
				if (array_key_exists('view', $item->query)) {
					$cid = 0;
					if (isset($item->query['cid'])) {
						$cid = (int)$item->query['cid'];
					}
					if ($item->query['view'] == 'items' && $cid == 0) {
						$root_menu_found = true;
						break;
					}
				}
			}
		}
		if ($root_menu_found === false) {
			$app->enqueueMessage(JText::_('COM_DJCATALOG2_MENU_CHECK_FAIL'), 'message');
		}
		
	}
}
?>

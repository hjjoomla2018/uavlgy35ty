<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

class DJCatalog2ControllerLabels extends JControllerLegacy
{
	/**
	 * Method to search tags with AJAX
	 *
	 * @return  void
	 */
	public function searchAjax()
	{
		// Required objects
		$app = JFactory::getApplication();
			
		$like = trim($app->input->get('like', null, 'string'));
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id AS value, name AS text');
		$query->from('#__djc2_labels');
		
		$query->where(
			'(' . $db->quoteName('name') . ' LIKE ' . $db->quote('%' . $db->escape($like) . '%')
			. ' OR ' . $db->quoteName('label') . ' LIKE ' . $db->quote('%' . $db->escape($like) . '%') . ')'
			);
		
		$query->order('label ASC, name ASC');
		
		$db->setQuery($query);
		$results = $db->loadObjectList();
		
		echo json_encode($results);

		$app->close();
	}
}

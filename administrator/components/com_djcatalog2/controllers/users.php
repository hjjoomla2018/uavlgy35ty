<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

class DJCatalog2ControllerUsers extends JControllerLegacy
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
		$context = trim($app->input->get('context', null, 'string'));
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('u.id AS value, CONCAT(u.name, " [", u.username ,"]") AS text');
		$query->from('#__users AS u');
		$query->join('left', '#__djc2_users as du ON du.user_id = u.id');
		
		if ($like != '**') {
			$query->where(
				'(' . $db->quoteName('u.name') . ' LIKE ' . $db->quote('%' . $db->escape($like) . '%')
				. ' OR ' . $db->quoteName('u.username') . ' LIKE ' . $db->quote('%' . $db->escape($like) . '%')
				. ' OR ' . $db->quoteName('du.company') . ' LIKE ' . $db->quote('%' . $db->escape($like) . '%')
				. ' OR ' . $db->quoteName('du.firstname') . ' LIKE ' . $db->quote('%' . $db->escape($like) . '%')
				. ' OR ' . $db->quoteName('du.lastname') . ' LIKE ' . $db->quote('%' . $db->escape($like) . '%')
				. ')'
				);
		}
		
		$query->order('name ASC');
		
		$db->setQuery($query);
		$results = $db->loadObjectList();
		
		echo json_encode($results);

		$app->close();
	}
}

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class Djcatalog2ModelUnits extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'a.unit', 'a.name', 'a.ordering', 'a.is_default'
			);
		}

		parent::__construct($config);
	}
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.name', 'asc');
		
		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		// Load the parameters.
		$params = JComponentHelper::getParams('com_djcatalog2');
		$this->setState('params', $params);
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('#__djc2_units AS a');
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
	
		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
	}
	
}
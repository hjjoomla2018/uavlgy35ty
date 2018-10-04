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

class Djcatalog2ModelOrders extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
					'a.id', 'a.order_number', 'a.invoice_number', 'a.created_date', 'a.status', 'a.grand_total'
			);
		}

		parent::__construct($config);
	}
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.order_number', 'desc');
		
		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$date_from = $this->getUserStateFromRequest($this->context.'.filter.date_from', 'filter_date_from');
		$this->setState('filter.date_from', $date_from);
		
		$date_to = $this->getUserStateFromRequest($this->context.'.filter.date_to', 'filter_date_to');
		$this->setState('filter.date_to', $date_to);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_djcatalog2');
		$this->setState('params', $params);
	}
	
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.ids');
		$id	.= ':'.$this->getState('filter.invoice');
		$id	.= ':'.$this->getState('filter.date_from');
		$id	.= ':'.$this->getState('filter.date_to');

		return parent::getStoreId($id);
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
		$query->from('#__djc2_orders AS a');
		
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else {
				$search = $db->quote('%'.$db->escape($search, true).'%');
				$query->where('(a.order_number LIKE '.$search.' OR a.email LIKE '.$search.' OR a.firstname LIKE '.$search.' OR a.lastname LIKE '.$search.' OR a.company LIKE '.$search.')');
			}
		}
		
		$ids = $this->getState('filter.ids', false);
		if (!empty($ids)) {
			$query->where('a.id IN ('.$ids.')');
		}
		
		$invoice = $this->getState('filter.invoice', false);
		if ($invoice) {
			$query->where('(a.invoice_number != "" AND a.invoice_number IS NOT NULL)');
		}
		
		$date_from = $this->getState('filter.date_from', false);
		$date_to = $this->getState('filter.date_to', false);
		
		if ($date_from) {
			$query->where('a.created_date >= '.$db->quote($date_from));
		}
		
		if ($date_to) {
			$query->where('a.created_date <= '.$db->quote($date_to));
		}
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.order_number');
		$orderDirn	= $this->state->get('list.direction', 'desc');
	
		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
	}
	
	public function getCounters() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*')->from('#__djc2_inv_counters AS a')->order('a.year DESC');
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
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

class Djcatalog2ModelQuestions extends JModelList
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
		parent::populateState('a.created_date', 'desc');
		
		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$user = JFactory::getUser();
		$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2');
		$salesUser = $app->getUserState($app->getUserState('com_djcatalog2.checkout.user_id', null));
		
		if (!$salesman && !$salesUser) {
			$this->setState('filter.user', $user->id);
		} else {
			if ((int)$salesUser > 0) {
				$this->setState('filter.user', (int)$salesUser);
			} else {
				$this->setState('filter.user', -1);
			}
		}
		
		$limit		= 10;
		$this->setState('list.limit', $limit);
		
		$limitstart	= $app->input->get( 'limitstart', 0, 'int' );
		$this->setState('list.start', $limitstart);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_djcatalog2');
		$this->setState('params', $params);
	}
	
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');

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
		$query->from('#__djc2_quotes AS a');
		
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else {
				$search = $db->quote('%'.$db->escape($search, true).'%');
				$query->where('(a.email LIKE '.$search.' OR a.firstname LIKE '.$search.' OR a.lastname LIKE '.$search.' OR a.company LIKE '.$search.')');
			}
		}
		
		$user = (int)$this->getState('filter.user', -1); 
		if ($user > 0){ 
			$query->where('a.user_id='.$user);
		}
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
	
		$query->order($db->escape($orderCol.' '.$orderDirn));
		//echo $query;
		return $query;
	}
	
}
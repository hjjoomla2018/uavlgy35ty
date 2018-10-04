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

class Djcatalog2ModelTaxrules extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name', 'a.tax_rate_id', 'c.country_name', 'c.country_id'
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
		
		$tax_rate = $this->getUserStateFromRequest($this->context.'.filter.tax_rate_id', 'tax_rate_id', false);
		$this->setState('filter.tax_rate_id', $tax_rate);

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
		$query->from('#__djc2_tax_rules AS a');
		
		if ($rate_id = $this->getState('filter.tax_rate_id')) {
			$query->where('tax_rate_id='.(int)$rate_id);
		}
		
		$query->select('c.country_name, c.country_2_code as code');
		$query->join('left', '#__djc2_countries as c on c.id = a.country_id');
		
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
	
		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
	}
	
	public function getParent() {
		$tax_rate_id = JFactory::getApplication()->input->get('tax_rate_id', $this->state->get('filter.tax_rate_id'));
		$tax_rate = null;
		if ($tax_rate_id > 0) {
			$this->_db->setQuery('select * from #__djc2_tax_rates where id ='.(int)$tax_rate_id);
			$tax_rate = $this->_db->loadObject();
		}
		return $tax_rate;
		
	}
	
}
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

class Djcatalog2ModelVatrates extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name', 'a.is_default', 'a.value', 'c.country_name'
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
                'a.*, c.country_name, c.country_2_code'
            )
        );
        $query->from('#__djc2_vat_rates AS a');
        
        $query->join('left', '#__djc2_countries as c ON c.id = a.country_id');
        
        // Add the list ordering clause.
        $orderCol   = $this->state->get('list.ordering');
        $orderDirn  = $this->state->get('list.direction');
    
        $query->order($db->escape($orderCol.' '.$orderDirn));
        return $query;
    }
    
}
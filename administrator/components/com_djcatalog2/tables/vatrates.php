<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access
defined('_JEXEC') or die;

class Djcatalog2TableVatrates extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__djc2_vat_rates', 'id', $db);
    }
    function bind($array, $ignore = '')
    {
        return parent::bind($array, $ignore);
    }
    public function store($updateNulls = false)
    {
        /*$date   = JFactory::getDate();
        $user   = JFactory::getUser();
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        
        
        
        $id = (int)$this->id;
        $country_id = (int)$this->country_id;
        $tax_rate = $this->value;
        $client = substr(strtoupper($this->client_type), 0, 1);
        $this->client_type = $client;
        
        $query = $db->getQuery(true);
        $where = array();
        
        $query->select('count(*)');
        $query->from('#__djc2_vat_rates');
        
        if ($id) {
            $where[] = 'id != '.$id;
        }
        
        $where[] = 'country_id='.$country_id;
        
        $where[] = 'value='.$tax_rate;
        
        $query->where($where);
        
        $db->setQuery($query);
        $count = $db->loadResult();

        if ($count > 0) {
            $this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_TAX_RULE'));
            return false;
        }*/
    
        return parent::store($updateNulls);
    }
}

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access.
defined('_JEXEC') or die;

//jimport('joomla.application.component.modeladmin');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'modeladmin.php');

class Djcatalog2ModelVatrule extends DJCJModelAdmin
{
    protected $text_prefix = 'COM_DJCATALOG2';

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function getTable($type = 'Vatrules', $prefix = 'Djcatalog2Table', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
    public function getForm($data = array(), $loadData = true)
    {
        // Initialise variables.
        $app    = JFactory::getApplication();

        // Get the form.
        $form = $this->loadForm('com_djcatalog2.vatrule', 'vatrule', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.vatrule.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }
    
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        $table = $this->getTable();
    
        if ($pk > 0)
        {
            // Attempt to load the row.
            $return = $table->load($pk);
    
            // Check for a table object error.
            if ($return === false && $table->getError())
            {
                $this->setError($table->getError());
                return false;
            }
        }
    
        // Convert to the JObject before adding other data.
        $properties = $table->getProperties(1);
        
        $item = JArrayHelper::toObject($properties, 'JObject');
        
        if (!isset($item->rates) || !is_array($item->rates)) {
            if (isset($item->id)) {
                $this->_db->setQuery('select rate_id from #__djc2_vat_rules_xref where rule_id='.(int)$item->id);
                $item->rates = $this->_db->loadColumn();
            }
        }
        
        
        if (!is_array($item->rates)) {
        	$item->rates = array();
        }
        
        if (property_exists($item, 'params'))
        {
            $registry = new JRegistry;
            $registry->loadString($item->params);
            $item->params = $registry->toArray();
        }
    
        return $item;
    }

    protected function _prepareTable(&$table)
    {
        jimport('joomla.filter.output');
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        $table->name        = htmlspecialchars_decode($table->name, ENT_QUOTES);
    }

    protected function getReorderConditions($table = null)
    {
        $condition = array();
        return $condition;
    }

    public function delete(&$cid) {
        
        //TODO
        /*
        if (count( $cid ))
        {
            $cids = implode(',', $cid);
        
            $this->_db->setQuery("SELECT COUNT(*) FROM #__djc2_items WHERE tax_rate_id IN ( ".$cids." )");
            if ($this->_db->loadResult() > 0) {
                $this->setError(JText::_('COM_DJCATALOG2_DELETE_TAXRATES_HAVE_ITEMS'));
                return false;
            }
        }*/
        
        if (parent::delete($cid)) {
            $cids = implode(',', $cid);
            $this->_db->setQuery('delete from #__djc2_vat_rules_xref where rule_id in ('.$cids.')');
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
            return true;
        }
        
        return false;
    }
}
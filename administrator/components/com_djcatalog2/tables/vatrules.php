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

class Djcatalog2TableVatrules extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__djc2_vat_rules', 'id', $db);
        $this->rates = array();
    }
    function bind($array, $ignore = '')
    {
        return parent::bind($array, $ignore);
    }
    public function load($keys = null, $reset = true)
    {
        $return = parent::load($keys, $reset);

        if ($return !== false && (int)$this->id > 0 && empty($this->rates)) {
            $db = JFactory::getDbo();
            $db->setQuery('select rate_id from #__djc2_vat_rules_xref where rule_id='.(int)$this->id);
            $this->rates = $db->loadColumn();
        }

        return $return;
    }
    public function store($updateNulls = false)
    {
        $items = $this->rates;
        unset($this->rates);

        $success = parent::store($updateNulls);
        //$this->items = $items;

        if (!$success) {
            return false;
        }

        $db = JFactory::getDbo();
        
        if ($this->id) {
            $db->setQuery('delete from #__djc2_vat_rules_xref where rule_id='.(int)$this->id);
            if (!$db->query()) {
                $this->setError($db->getErrorMsg());
                return false;
            }
        }

        if (count($items)) {
            foreach ($items as $rate_id) {
                $db->setQuery('INSERT INTO #__djc2_vat_rules_xref (rate_id, rule_id) VALUES ('.$rate_id.', '.$this->id.')');
                if (!$db->query()) {
                    $this->setError($db->getErrorMsg());
                    return false;
                }
            }
        }

        $this->rates = $items;
        unset($items);

        return true;

    }
}

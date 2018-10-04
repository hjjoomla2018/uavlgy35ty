<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

class JFormFieldDjcvatrates extends JFormFieldList
{
    protected $type = 'Djcvatrates';

    protected function getOptions()
    {
        $options = array();
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('vr.*');
        $query->from('#__djc2_vat_rates AS vr');
        
        $query->select('c.country_name, c.country_2_code');
        $query->join('left', '#__djc2_countries AS c ON c.id = vr.country_id');
        
        $query->order('c.country_name ASC, vr.name ASC, vr.value ASC');
        
        $db->setQuery($query);
        
        $rates = $db->loadObjectList();


        // Build the options list from the list of folders.
        if (is_array($rates))
        {
            foreach ($rates as $rate)
            {
                $options[] = JHtml::_('select.option', $rate->id, $rate->name . ' ['.$rate->country_2_code.'] = ' . number_format($rate->value).'%');
            }
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

use Joomla\Registry\Registry;

defined ('_JEXEC') or die('Restricted access');

class DJC2FiltersModuleHelper {
    
    static $attributes = array();
    static $counters = array();
    
    public static function getData($params) {
        
        $hash = $params->get('show_counter');
        $hash .= ':' . serialize($params->get('fieldgroups'));
        $hash .= ':' . $params->get('catsw');
        $hash .= ':' . serialize($params->get('categories'));
        
        $hash = md5($hash);
        
        $attributes = self::getAttributes($params, $hash);
        $counters = ($params->get('show_counter', 0) == '1') ? self::getCountersPrecise($attributes) : self::getCountersApprox($hash);
        
        $app = JFactory::getApplication();
        
        $request = $app->input->getArray($_REQUEST);
        foreach($request as $param=>$value) {
            if (!array_key_exists('djcf', $request)) {
                $request['djcf'] = array();
            }
            if (strstr($param, 'f_')) {
                $qkey = substr($param, 2);
                $qval = null;
                if (is_array($value)) {
                    $qval = $value;
                } else {
                    $qval = (strstr($value,',') !== false) ? explode(',',$value) : $value;
                }
                //$qval = (strstr($value,',') !== false) ? explode(',',$value) : $value;
                unset($request[$param]);
                $request['djcf'][$qkey] = $qval;
            }
        }
        $filters = $request['djcf'];
        $globalSearch = urldecode($app->input->get( 'search','','string' ));
        $globalSearch = trim(JString::strtolower( $globalSearch ));
        if (substr($globalSearch,0,1) == '"' && substr($globalSearch, -1) == '"') {
            $globalSearch = substr($globalSearch,1,-1);
        }
        if (strlen($globalSearch) > 0 && (strlen($globalSearch)) < 3 || strlen($globalSearch) > 20) {
            $globalSearch = null;
        }
        
        $grouppedattributes = array();

        foreach ($attributes as $key=>$attribute) {
            $attributes[$key]->alias = str_replace('-', '_', $attribute->alias);
            $attributes[$key]->optionsArray = ($attribute->options) ? $attribute->options : array();
            $attributes[$key]->optionValuesArray = ($attribute->optionValues) ? $attribute->optionValues : array();
            $attributes[$key]->optionCounterArray = array();
            $attributes[$key]->selected = false;
            $attributes[$key]->selectedOptions = array();
            $attributes[$key]->selectedOptionValues = array();
            $attributes[$key]->availableOptions = 0;
            
            if ($attribute->type == 'text' && array_key_exists('t_'.$attributes[$key]->id, $counters)) {
            	$attributes[$key]->availableOptions = $counters['t_'.$attributes[$key]->id]->item_count;
            }
            
            foreach ($attributes[$key]->optionsArray as $kk => $vv) {
                if (is_array($counters) && !empty($counters)) {
                    if (array_key_exists($vv, $counters)) {
                        $attributes[$key]->optionCounterArray[] = $counters[$vv]->item_count; 
                        $attributes[$key]->availableOptions++;
                    } else {
                        $attributes[$key]->optionCounterArray[] = 0;
                    }
                } else {
                    $attributes[$key]->optionCounterArray[] = 0;
                }
            }
            if (!empty($filters[$attribute->alias])) {
                $filter = $filters[$attribute->alias];
                if (is_scalar($filter) && strpos($filter, ',') !== false) {
                    $filter = explode(',', $filter);
                }
                if (is_array($filter)) {
                    foreach($filter as $k=>$v) {
                    	if (strlen($v) > 0 ) {
                    		$attributes[$key]->selected = true;
                    		$filter[$k] = (int)$v;
                    	} else {
                    		$filter[$k] = null;
                    	}
                    }
                    //$attributes[$key]->selected = true;
                    foreach ($attribute->optionsArray as $o) {
                        if (in_array($o, $filter)){
                            $index = array_search($o, $attributes[$key]->optionsArray);
                            if (array_key_exists($index, $attributes[$key]->optionValuesArray)) {
                                $attributes[$key]->selectedOptionValues[] = $attributes[$key]->optionValuesArray[$index];
                                $attributes[$key]->selectedOptions[] = $attributes[$key]->optionsArray[$index];
                            }
                        }
                    }
                } else {
                    $attributes[$key]->selected = true;
                    foreach ($attribute->optionsArray as $o) {
                        if ($o == (int)$filter) {
                            $index = array_search($o, $attributes[$key]->optionsArray);
                            if (array_key_exists($index, $attributes[$key]->optionValuesArray)) {
                                $attributes[$key]->selectedOptionValues[] = $attributes[$key]->optionValuesArray[$index];
                                $attributes[$key]->selectedOptions[] = $attributes[$key]->optionsArray[$index];
                            }
                        }
                    }
                }
            }
            if (empty($grouppedattributes[$attribute->group_id])) {
                $grouppedattributes[$attribute->group_id] = new stdClass();
                $grouppedattributes[$attribute->group_id]->group_name = $attribute->group_name;
                $grouppedattributes[$attribute->group_id]->attributes = array();
            }
            $grouppedattributes[$attribute->group_id]->attributes[] = $attribute;
        }
        return $grouppedattributes;
    }
    private static function getAttributes(&$params, $hash) {
        if (empty(self::$attributes[$hash])) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('f.*, g.name as group_name, g.label as group_label');
            $query->from('#__djc2_items_extra_fields as f');
            $query->join('left', '#__djc2_items_extra_fields_groups as g ON g.id=f.group_id');
            
            $fieldgroups = $params->get('fieldgroups');
            $fields = $params->get('fields');
            
            $group_ids = '';
            if (!empty($fieldgroups)) {
                if (in_array(0, $fieldgroups)) {
                    $group_ids = ' and (g.id in ('.implode(',',$fieldgroups).') OR g.id IS NULL)';
                } else {
                    $group_ids = ' and (g.id in ('.implode(',',$fieldgroups).'))';
                }
            }
            
            $field_ids = '';
            if (!empty($fields)) {
            	$field_ids = ' and f.id in ('.implode(',', $fields).')';
            }
            
            $query->where('f.published = 1 and f.filterable = 1 and (f.type = \'checkbox\' or f.type = \'radio\' or f.type = \'select\' or f.type = \'text\' or f.type = \'color\' or f.type = \'multicolor\'  or f.type = \'multiselect\') '.$group_ids.$field_ids);
            $query->group('f.id');
            $query->order('IFNULL(g.ordering,0) asc, g.ordering asc, f.ordering asc');
            $db->setQuery($query);
            
            $attributes = $db->loadObjectList('id');
            
            if (count($attributes) > 0) {
                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('#__djc2_items_extra_fields_options');
                $query->where('field_id in ('.implode(',',array_keys($attributes)).')');
                $query->order('field_id asc, ordering asc');
                    
                $db->setQuery($query);
                $optionslist = $db->loadObjectList();
                
                $query = $db->getQuery(true);
                $query->select('distinct *');
                $query->from('#__djc2_items_extra_fields_values_text');
                $query->where('field_id in ('.implode(',',array_keys($attributes)).')');
                $query->order('field_id asc, value asc');
                
                $db->setQuery($query);
                $textOptionslist = $db->loadObjectList();
                    
                foreach ($attributes as $field_id => $field) {
                	$field_options = array();
                	$field_optionValues = array();
                	
                	if ($field->type == 'text') {
                		foreach($textOptionslist as $k => $option) {
                			if ($option->field_id == $field_id) {
                				$field_options[] = $option->id;
                				$field_optionValues[] = $option->value;
                			}
                		}
                	} else {
                		foreach($optionslist as $k => $option) {
                			if ($option->field_id == $field_id) {
                				$field_options[] = $option->id;
                				$field_optionValues[] = $option->value;
                			}
                		}	
                	}
                    $attributes[$field_id]->options = $field_options;//implode('|', $field_options);
                    $attributes[$field_id]->optionValues = $field_optionValues;//implode('|', $field_optionValues);
                }
            }
            self::$attributes[$hash] = $attributes;
        }
        
        return self::$attributes[$hash];
        
    }
    
    private static function getCountersPrecise($attributes) {
        $db = JFactory::getDbo();
        $app = JFactory::getApplication();
        $cparams = Djcatalog2Helper::getParams();
        $params = new Registry();
        $params->merge($cparams);

        JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models');
        $model = JModelLegacy::getInstance('Items', 'Djcatalog2Model');
        $state = $model->getState();

        $params->set('product_catalogue', 0);
        $params->set('limit_items_show', 0);
        $model->setState('params', $params);
        $model->setState('filter.catalogue', false);
        $model->setState('list.start', 0);
        $model->setState('list.limit', 0);

        $model->setState('list.select', 'i.id');
        $model->setState('list.ordering', 'i.id');
        
		$model->setState('filter.map.address', false);
		$model->setState('filter.map.radius', false);

        $filterState = $model->getState('filter.customattribute', array());
        $counters = array();

        foreach($attributes as $attribute) {
            if (!isset(self::$counters[$attribute->id])) {
                $tempState = $filterState;
            
                if (($attribute->type != 'checkbox' || $attribute->type != 'multicolor') && $attribute->filter_type != 'checkbox' && array_key_exists($attribute->alias, $tempState)) {
                    unset($tempState[$attribute->alias]);
                }
                
                $model->setState('filter.customattribute', $tempState);
                $items_query = $model->buildQuery();
                
                if ($attribute->type == 'text') {
                	$query = $db->getQuery(true);
                	$query->select('ef.id as field_id, ef.name as field_name, "" as option_id, "" as option_value, count(fv.item_id) as item_count');
                	$query->from('#__djc2_items_extra_fields_values_text as fv');
                	$query->join('inner', '#__djc2_items_extra_fields as ef on ef.id=fv.field_id AND ef.filterable = 1');
                	$query->where('ef.id = '.$attribute->id);
                	$query->join('inner', '('.$items_query.') as item_ids on fv.item_id = item_ids.id');
                	$query->group('ef.id');
                	$query->order('ef.name asc');
                	
                	$db->setQuery($query);
                	$textCounters = $db->loadObjectList('field_id');
                	
                	if (isset($textCounters[$attribute->id])) {
                		self::$counters['t_'.$attribute->id] = $textCounters;
                	}
                	
                } else {
                	$query = $db->getQuery(true);
                	$query->select('ef.id as field_id, ef.name as field_name, opt.id as option_id, opt.value as option_value, count(fv.item_id) as item_count');
                	$query->from('#__djc2_items_extra_fields_values_int as fv');
                	$query->join('inner', '#__djc2_items_extra_fields as ef on ef.id=fv.field_id');
                	$query->join('inner', '#__djc2_items_extra_fields_options as opt on opt.field_id=ef.id and opt.id=fv.value');
                	$query->where('ef.id = '.$attribute->id);
                	$query->join('inner', '('.$items_query.') as item_ids on fv.item_id = item_ids.id');
                	$query->group('ef.id, opt.value');
                	$query->order('ef.name asc');
                	
                	$db->setQuery($query);
                	$intCounters = $db->loadObjectList('option_id');
                	
                	self::$counters[$attribute->id] = $intCounters;
                }
            }

            if (isset(self::$counters[$attribute->id]) && count(self::$counters[$attribute->id])) {
                foreach (self::$counters[$attribute->id] as $k=>$v) {
                    $counters[$k] = $v;
                }
            } else if (isset(self::$counters['t_'.$attribute->id]) && count(self::$counters['t_'.$attribute->id])) {
            	foreach (self::$counters['t_'.$attribute->id] as $k=>$v) {
            		$counters['t_'.$k] = $v;
            	}
            }
        }

        return $counters;
    }
    
    private static function getCountersApprox($hash) {
        if (empty(self::$counters[$hash])) {
        	self::$counters[$hash] = array();
            $db = JFactory::getDbo();
            $app = JFactory::getApplication();
            $cparams = Djcatalog2Helper::getParams();
            $params = new Registry();
            $params->merge($cparams);
            
            JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models');
            $model = JModelLegacy::getInstance('Items', 'Djcatalog2Model');
            $state = $model->getState();
            
            $params->set('product_catalogue', 0);
            $params->set('limit_items_show', 0);
            $model->setState('params', $params);
            $model->setState('filter.catalogue', false);
            //$model->setState('filter.customattribute', array());
            $model->setState('list.start', 0);
            $model->setState('list.limit', 0);
            
            $model->setState('list.select', 'i.id');
            $model->setState('list.ordering', 'i.id');
            
            $items_query = $model->buildQuery();
            
            
            $query = $db->getQuery(true);
            $query->select('ef.id as field_id, ef.name as field_name, opt.id as option_id, opt.value as option_value, count(fv.item_id) as item_count');
            $query->from('#__djc2_items_extra_fields_values_int as fv');
            $query->join('inner', '#__djc2_items_extra_fields as ef on ef.id=fv.field_id');
            $query->join('inner', '#__djc2_items_extra_fields_options as opt on opt.field_id=ef.id and opt.id=fv.value');
            $query->join('inner', '('.$items_query.') as item_ids on fv.item_id = item_ids.id');
            $query->group('ef.id, opt.value');
            $query->order('ef.name asc');
            
            $db->setQuery($query);
            $intCounters = $db->loadObjectList('option_id');
            
            $query = $db->getQuery(true);
            $query->select('ef.id as field_id, ef.name as field_name, "" as option_id, "" as option_value, count(fv.item_id) as item_count');
            $query->from('#__djc2_items_extra_fields_values_text as fv');
            $query->join('inner', '#__djc2_items_extra_fields as ef on ef.id=fv.field_id AND ef.filterable = 1');
            $query->join('inner', '('.$items_query.') as item_ids on fv.item_id = item_ids.id');
            $query->group('ef.id');
            $query->order('ef.name asc');
            
            $db->setQuery($query);
            $textCounters = $db->loadObjectList('field_id');
            
            foreach($intCounters as $k=>$v) {
            	self::$counters[$hash][$k] = $v;
            }
            foreach($textCounters as $k=>$v) {
            	self::$counters[$hash]['t_'.$k] = $v;
            }
            
            //self::$counters[$hash] = $db->loadObjectList('option_id');
        }
        
        return self::$counters[$hash];
    }
}
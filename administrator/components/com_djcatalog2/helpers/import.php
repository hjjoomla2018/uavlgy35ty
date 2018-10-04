<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die();

class Djcatalog2ImportHelper {
	static $countries = array();
	static $states = array();
	static $translations = false;
	static $translationsQueries = array();
	
	public static function parseCSV($filename, $separator = ",", $enclosure = "\"") {
		$rows = array();
		if(($handle = fopen($filename, "r")) !== FALSE) {
			$headers = fgetcsv($handle, 0, $separator, $enclosure);
			if($headers !== FALSE) {
				while(($data = fgetcsv($handle, 0, $separator, $enclosure)) !== false) {
					$row = array();
					for($i = 0; $i < count($headers); $i++) {
						if(array_key_exists($i, $data)) {
							$row[$headers[$i]] = trim($data[$i]);
						}
					}
					$rows[] = $row;
				}
			}
			fclose($handle);
		}
		return $rows;
	}
	
	public static function storeRecords($rows, $model, $type, $defaults = array()) {
		$db = JFactory::getDbo();
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$img_import_source = JPath::clean(JPATH_ROOT . $params->get('import_images_path', '/media/djcatalog2/import/images'));
		$att_import_source = JPath::clean(JPATH_ROOT . $params->get('import_files_path', '/media/djcatalog2/import/files'));
		
		$inserted = 0;
		$updated = 0;
		$ignored = 0;
		$failed = 0;
		
		$messages = array('message'=>array(), 'warning' => array(), 'error'=>array());
		
		$messages['message'][] = JText::_('COM_DJCATALOG2_IMPORT_SUMMARY_'.strtoupper($type));
		
		$table_name = false;
		switch($type) {
			case 'item' : 		$table_name = '#__djc2_items'; break;
			case 'category' : 	$table_name = '#__djc2_categories'; break;
			case 'producer' : 	$table_name = '#__djc2_producers'; break;
		}
		
		$basic_columns = $db->getTableColumns($table_name, true);
		$extra_fields = ($type=='item') ? self::getExtraFields() : false;
		
		$app = JFactory::getApplication();
		
		$limit = $app->input->getInt('import_limit', 200);
		$limit_start = $app->input->getInt('import_start', 0);
		
		$total = count($rows);

		$rows = array_slice($rows, $limit_start, $limit, true);

		foreach ($rows as $key=>$row) {
			// By default, initially, each record is considered as new 
			$new = true;
			// If ID has been supplied and is greater than 0,
			// let's load it first into $old_row variable
			$old_row = null;
			
			if ($type == 'item') {
				self::prepareCustomConfigValues($row);
			}
			
			if (isset($row['id']) && (int)$row['id'] > 0) {
				$old_row = $model->getItem($row['id']);
			}
			
			// If the record exists in DB,
			// fill-in any missing data in CSV record
			if (!empty($old_row) && $old_row->id > 0)
			{
				$new = false;
				foreach($old_row as $k=>$v) {
					if (!isset($row[$k])) {
						$row[$k] = $v;
					}
				}
			} 
			else if (isset($row['id']) && (int)$row['id'] > 0) {
				//$new = true;
				// TODO: this should be handled by JTable object, but JModelAdmin::save() returns true even if record hasn't been stored in DB
				// dummy insert
				$db->setQuery('INSERT INTO '.$table_name.' (id) VALUES ('.(int)$row['id'].')');
				$db->query();
			}
			else {
				$row['id'] = 0;
				$row['alias'] = !isset($row['alias']) ? null : $row['alias'];
			}
		
			if (empty($row['name'])) {
				$ignored++;
				continue;
			}
		
			foreach($defaults as $column => $value) {
				if (!isset($row[$column])) {
					$row[$column] = trim($value);
				}
			}
		
			$img_list = null;
			if (isset($row['images'])) {
				$img_list = explode(',', $row['images']);
				unset($row['images']);
			}
			
			$att_list = null;
			if (isset($row['files'])) {
				$att_list = explode(',', $row['files']);
				unset($row['files']);
			}
			
			// establish custom fields when importing products
			if ($type == 'item') {
				JFactory::getApplication()->input->set('jform', null);
				$jform = array();
				
				if ($extra_fields && count($extra_fields)) {
					JFactory::getApplication()->input->set('attribute', null);
					$attributes = self::prepareExtraFieldsValues($row, $extra_fields);
					if (!empty($attributes)) {
						JFactory::getApplication()->input->set('attribute', $attributes);
					}
				}
				
				if (isset($row['group_id'])) {
					$group_ids = false;
					if (is_array($row['group_id'])) {
						$group_ids = $row['group_id'];
					} else {
						$group_ids = array($row['group_id']);
					}
					$jform['group_id'] = $group_ids;
				}
				
				/*
				if (isset($row['country']) && !empty($row['country'])) {
					if (!(is_numeric($row['country']) && floatval($row['country']) == intval(floatval($row['country'])))) {
						$row['country'] = self::getCountryByName($row['country']);	
					}
				}
				
				$country_id = !empty($row['country']) ? $row['country'] : 0;
				if (isset($row['state']) && !empty($row['state'])) {
					if (!(is_numeric($row['state']) && floatval($row['state']) == intval(floatval($row['state'])))) {
						$row['state'] = self::getStateByName($row['state'], $country_id);
					}
				}*/
				
				JFactory::getApplication()->input->set('jform', $jform);
			}
			
			// remove unnecessary attributes from the record
			foreach ($row as $k=>$v) {
				if (array_key_exists($k, $basic_columns) == false) {
					unset($row[$k]);
				}
			}

			if (!$model->save($row)) {
				$messages['error'][] = JText::_('COM_DJCATALOG2_IMPORT_ERROR_ROW').': ['.($key+1).', '.$row['name'].']. '.$model->getError();
				$failed++;
				continue;
			}
		
			$last_id = $model->getState($model->getName() . '.id');
			if ($last_id > 0) {
				if (!empty($img_list)) {
					self::storeMedias($last_id, $type, $img_list, $img_import_source, DJCATIMGFOLDER, '#__djc2_images');
				}
				if (!empty($att_list)) {
					self::storeMedias($last_id, $type, $att_list, $att_import_source, DJCATATTFOLDER, '#__djc2_files');
				}
			}
		
			if ($new) {
				$inserted++;
			} else {
				$updated++;
			}
		}
		
		JFactory::getApplication()->input->set('attribute', null);
		JFactory::getApplication()->input->set('jform', null);

		$messages['message'][] = JText::sprintf('COM_DJCATALOG2_IMPORT_ROWS', $limit, $limit_start, count($rows), $total);
		
		$messages['message'][] = JText::_('COM_DJCATALOG2_IMPORT_INSERTED').$inserted;
		$messages['message'][] = JText::_('COM_DJCATALOG2_IMPORT_UPDATED').$updated;
		
		if ($ignored > 0) {
			$messages['warning'][] = JText::_('COM_DJCATALOG2_IMPORT_IGNORED').$ignored;
		}
		if ($failed > 0) {
			$messages['error'][] = JText::_('COM_DJCATALOG2_IMPORT_FAILED').$failed;
		}
		
		return $messages;
	}
	
	public static function storeMedias($item_id, $type, $files, $source_path, $target_path, $table_name) {
		$db = JFactory::getDbo();
		
		$destination = DJCatalog2FileHelper::getDestinationFolder($target_path, $item_id, $type);
		$sub_path = DJCatalog2FileHelper::getDestinationPath($item_id, $type);
		if (!JFolder::exists($destination)) {
			$destExist = JFolder::create($destination, 0755);
		} else {
			$destExist = true;
		}
		if ($destExist && !empty($files)) {
			$ordering = 1;
			foreach($files as $file) {
				$file = trim($file);
				if ($file && JFile::exists($source_path.DS.$file)){
					$obj = new stdClass();
					$obj->id = null;
					$obj->fullname = DJCatalog2FileHelper::createFileName($file, $destination);
					$obj->name = JFile::stripExt($obj->fullname);
					$obj->ext = JFile::getExt($obj->fullname);
					$obj->item_id = $item_id;
					$obj->path = $sub_path;
					$obj->fullpath = $sub_path.'/'.$obj->fullname;
					$obj->type = $type;
					$obj->caption = JFile::stripExt($file);
					$obj->ordering = $ordering++;
		
					if (JFile::copy($source_path.DS.$file, $destination.DS.$obj->fullname)) {
						$db->insertObject( $table_name, $obj, 'id');
					}
				}
			}
		}
	}
	
	public static function getExtraFields() {
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('f.*, concat("_", f.alias) as field_column');
		$query->from('#__djc2_items_extra_fields AS f');
		$query->order('f.group_id asc, f.ordering asc');
		
		//echo str_replace('#_', 'jos', (string)$query);die();
		$db->setQuery($query);
		$fields = ($db->loadObjectList('field_column'));

		if (count($fields)) {
			$db->setQuery('SELECT * FROM #__djc2_items_extra_fields_options ORDER BY field_id ASC, ordering ASC');
			$optionList = $db->loadObjectList();
			
			foreach($fields as $field_column => $field) {
				$field_id = $field->id;
				foreach ($optionList as $optionRow) {
					if ($optionRow->field_id == $field_id) {
						if (empty($field->optionlist)) {
							$fields[$field_column]->optionlist = array();
						}
						$fields[$field_column]->optionlist[] = $optionRow;
					}
				}
			}
			
			return $fields;
		}
		
		return false;
	}
	
	public static function prepareExtraFieldsValues(&$item, &$fields) {
		$values = array();
		foreach ($fields as $colname => $field) {
			if (isset($item[$colname])) {
				if (trim($item[$colname]) !== '') {
					if (!isset($item['group_id']) || !is_array($item['group_id'])) {
						$current = isset($item['group_id']) ? $item['group_id'] : false;
						$item['group_id'] = array();
						if ($current) {
							$item['group_id'][] = $current;
						}
					}
					
					$item['group_id'][] = $field->group_id;
					
					if (empty($field->optionlist)) {
						// assuming that it's not select/radio/checkbox field
						$values[$field->id] = $item[$colname];
					} else {
						// it must be select/radio/checkbox field
						$option_values = array();
						$option_names = explode(',', $item[$colname]);
						foreach ($option_names as $k=>$v) {
							$option_value = trim($v);
							foreach($field->optionlist as $option_key => $option) {
								if (JString::strtolower($option_value) == JString::strtolower(trim($option->value))) {
									$option_values[] = (int)$option->id;
								}
							}
						}
						// try by checking option ids
                        if (empty($option_values)) {
                            foreach ($option_names as $k=>$v) {
                                $option_value = trim($v);
                                foreach($field->optionlist as $option_key => $option) {
                                    if (is_numeric($option_value) && $option_value == $option->id) {
                                        $option_values[] = (int)$option->id;
                                    }
                                }    
                            }
                        }
                        $option_values = array_unique($option_values);
						$values[$field->id] = $option_values;
					}
				}
				unset($item[$colname]);
			}
		}
		
		if (is_array($item['group_id'])) {
			$item['group_id'] = array_unique($item['group_id']);
		}
		
		return $values;
	}
	
	public static function prepareCustomConfigValues(&$row) {
		$db = JFactory::getDbo();
		
		if (self::$translations === false) {
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djc2_import_configs')->where('published=1')->order('ordering asc');
			$db->setQuery($query);
			
			self::$translations = $db->loadObjectList();
		}
		
		if (empty(self::$translations)) {
			return;
		}
		
		foreach (self::$translations as $translation) {
			$k = trim($translation->csv_name);
			$v = trim($translation->target_name);
			
			if (isset($row[$k])) {
				$value = $row[$k];
				
				if ($translation->is_db == 1 && trim($value) != '') {
					if (empty($translation->db_name) || empty($translation->db_lookup_column) || empty($translation->db_value_column)) {
						continue;
					}
					
					try {
						$query = $db->getQuery(true);
						//$query->select($db->quoteName(trim($translation->db_lookup_column)));
						$query->select($db->quoteName(trim($translation->db_value_column)));
						$query->from(trim($translation->db_name));
						
						$operator = $translation->db_operator;
						if (!$operator) {
							$query->where($db->quoteName(trim($translation->db_lookup_column)) .' = '.$value);
						} else if ($operator == '1') {
							$query->where($db->quoteName(trim($translation->db_lookup_column)) .' LIKE '.$db->quote($value));
						} else if ($operator == '2') {
							$query->where($db->quoteName(trim($translation->db_lookup_column)) .' LIKE '.$db->quote('%'.$value));
						} else if ($operator == '3') {
							$query->where($db->quoteName(trim($translation->db_lookup_column)) .' LIKE '.$db->quote('%'.$value.'%'));
						}
						
						if ($translation->db_where_clause != '') {
							$query->where($translation->db_where_clause);
						}
						
						$query_hash = md5((string)$query);
						if (isset(self::$translationsQueries[$query_hash])) {
							$value = self::$translationsQueries[$query_hash];
						} else {
							$db->setQuery($query);
							$value = self::$translationsQueries[$query_hash] = $db->loadResult();
						}
						
					} catch(Exception $e) {
						continue;
					}
					
				}
				
				if ($translation->html_wrapper != '') {
					$value = '<' . $translation->html_wrapper . '>' . $value . '</' . $translation->html_wrapper . '>';
				}
				
				if ($translation->merging != 'R' && !empty($row[$v])) {
					if ($translation->merging == 'B') {
						$value = (empty($row[$v])) ? $value : $value.' '.$row[$v];
					} else if ($translation->merging == 'A') {
						$value = (empty($row[$v])) ? $value : $row[$v].' '.$value;
					}
				}
				
				$row[$v] = $value;
				//unset($row[$k]);
			}
		}
	}
	
	public static function getCountryByName($name) {
		if (empty($name)) {
			return false;
		}
		
		$name = JString::strtolower(JString::trim($name));
		
		if (empty(self::$countries)) {
			$db= JFactory::getDbo();
			$db->setQuery('select country_name, id from #__djc2_countries order by country_name asc');
			self::$countries = $db->loadObjectList('country_name');
		}
		
		if (isset(self::$countries[$name])) {
			return self::$countries[$name]->id;
		}
		
		$db->setQuery('select lower(country_name) as country_name, id from #__djc2_countries where lower(country_name) like'.$db->quote('%'.$db->escape($name).'%').' LIMIT 1');
		$result = $db->loadObject();
		if (!empty($result)) {
			self::$countries[$name] = $result;
			return self::$countries[$name]->id;
		}
		
		return false;
	}
	
	public static function getStateByName($name, $country_id = 0) {
		if (empty($name)) {
			return false;
		}
	
		$name = JString::strtolower(JString::trim($name));
		
		$where = ((int)$country_id > 0) ? ' country_id='.(int)$country_id.' ' : ' 1 ';
	
		if (empty(self::$states)) {
			$db= JFactory::getDbo();
			$db->setQuery('select lower(name) as name, id from #__djc2_countries_states WHERE '.$where.' order by name asc');
			self::$states = $db->loadObjectList('name');
		}
	
		if (isset(self::$states[$name])) {
			return self::$states[$name]->id;
		}
		
		$db->setQuery('select country_name, id from #__djc2_countries where '.$where.' AND lower(country_name) like'.$db->quote('%'.$db->escape($name).'%').' LIMIT 1');
		$result = $db->loadObject();
		if (!empty($result)) {
			self::$countries[$name] = $result;
			return self::$countries[$name]->id;
		}
	
		return false;
	}
	
}
<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
class plgDJCatalog2Customfield extends JPlugin {
	
	public static $attributes = array();
	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	function onPrepareItemDescription( &$row, &$params, $page=0, $context = 'item')
	{
		$app = JFactory::getApplication();
		if (empty(self::$attributes)) {
			$db = JFactory::getDbo();
			
			$query = $db->getQuery(true);
			
			$query->select('f.*, group_concat(fo.id separator \'|\') as options');
			$query->from('#__djc2_items_extra_fields as f');
			$query->join('LEFT', '#__djc2_items_extra_fields_options as fo ON fo.field_id=f.id');
				
			$query->where('f.published = 1');
			$query->group('f.id');
			$query->order('f.group_id asc, f.ordering asc');
			
			$db->setQuery($query);
			self::$attributes = $db->loadObjectList();
		}
		
		//echo '<pre>'.print_r(self::$attributes,true).'</pre>';
		
		//$regex	= '/{djc2customfield\s+(.*?)}/i';
		$regex = '#{djc2customfield\s([a-z0-9_]+?)([^}]*?)}#iU';
		$regexGrp = '#{djc2fieldgroup\s([a-z0-9_]+?)([^}]*?)}#iU';
		
		
		$row->_nulledExtrafields = array();
		$nullExtraFields = array();

		preg_match_all($regex, $row->description, $matches, PREG_SET_ORDER);
		preg_match_all($regexGrp, $row->description, $matchesGrp, PREG_SET_ORDER);

		if ($matchesGrp) {
			foreach ($matchesGrp as $matchGrp) {
				$output = null;
				$rows = array();
				$group_id = trim($matchGrp[1]);
				$layout = 'table';
				if (isset($matchGrp[2])) {
					$attrs = self::parseAttributes(trim($matchGrp[2]));
					if (isset($attrs['layout'])) {
						$layout = trim($attrs['layout']);
					}
				}
				
				foreach (self::$attributes as $attribute) {
					if ($attribute->group_id == $group_id) {
						$item_attrib = '_ef_'.$attribute->alias;
						
						if (!empty($row->$item_attrib)) {
							$attributeData = $row->$item_attrib;
							if ($layout == 'list') {
								$rows[] = '<li class="djc_attribute"><span class="djc_label">'.$attribute->name.'</span>: <span class="djc_value">'.$this->renderAttribute($attribute, $attributeData, false).'</span></li>';
							} else if ($layout == 'divs') {
								$rows[] = '<div class="djc_attribute"><span class="djc_label">'.$attribute->name.'</span>: <span class="djc_value">'.$this->renderAttribute($attribute, $attributeData, false).'</span></div>';
							}  else if ($layout == 'inline') {
								$rows[] = '<span class="djc_value">'.$this->renderAttribute($attribute, $attributeData, false).'</span>';
							}
							else {
								$rows[] = '<tr class="djc_attribute"><td class="djc_label">'.$attribute->name.'</td><td class="djc_value">'.$this->renderAttribute($attribute, $attributeData, false).'</td></tr>';
							}
						}
						
						$nullExtraFields[] = $item_attrib;
					}
				}
				if (!empty($rows)){
					if ($layout == 'list') {
						$output .= '<div class="djc_attributes"><ul class="djc_attributes_list">';
						$output .= implode(' ',$rows);
						$output .= '</ul></div>';
					} else if ($layout == 'divs') {
						$output .= '<div class="djc_attributes">';
						$output .= implode(' ',$rows);
						$output .= '</div>';
					} else if ($layout == 'inline') {
						$output .= '<div class="djc_attributes">';
						$output .= implode(', ',$rows);
						$output .= '</div>';
					} else {
						$output .= '<div class="djc_attributes"><table class="table table-condensed">';
						$output .= implode(' ',$rows);
						$output .= '</table></div>';
					}
					
					$row->description = preg_replace('/'.preg_quote($matchGrp[0]).'/', $this->escapeValue($output), $row->description, 1);
				}
			}
		}
		
		// No matches, skip this
		if ($matches) {
			foreach ($matches as $match) {
		
				$matcheslist =  explode(',',$match[1]);

				if (!array_key_exists(1, $matcheslist)) {
					$matcheslist[1] = null;
				}
		
				$attrib = trim($matcheslist[0]);
				$item_attrib = '_ef_'.$attrib;
				$output = null;
				
				$show_label = true;
				$unset_var = true;
					
				if (isset($match[2])) {
					$attrs = self::parseAttributes(trim($match[2]));
					if (isset($attrs['label']) && $attrs['label'] == '0') {
						$show_label = false;
					}
					if (isset($attrs['unset']) && $attrs['unset'] == '0') {
						$unset_var = false;
					}
				}
				
				if (!empty($row->$item_attrib)) {
					
					foreach (self::$attributes as $attribute) {
						if ($attribute->alias == $attrib) {
							$attributeData = $row->$item_attrib;
							$output .= $this->renderAttribute($attribute, $attributeData, $show_label);
						}
					}
					$row->_nulledExtrafields[] = $attrib;
					$row->_nulledExtrafields[] = $item_attrib;
					
					if ($unset_var) {
						unset($row->$item_attrib);
					}
				}
				$row->description = preg_replace("|$match[0]|", $this->escapeValue($output), $row->description, 1);
			}
		}
		
		if (count($nullExtraFields) > 0) {
			foreach($nullExtraFields as $item_attrib) {
				if (isset($row->$item_attrib)) {
					unset($row->$item_attrib);
				}
 			}
		}
		
		return true;
		
	}
	
	public static function parseAttributes($string) {
		$attr = array();
		$retarray = array();
	
		// Let's grab all the key/value pairs using a regular expression
		preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);
	
		if (is_array($attr))
		{
			$numPairs = count($attr[1]);
	
			for ($i = 0; $i < $numPairs; $i++) {
				$retarray[$attr[1][$i]] = $attr[2][$i];
				}
			}

			return $retarray;
	}
	
	public static function renderAttribute($attribute, $attributeData, $show_label){
		$output = '';	
		if (is_array($attributeData)) {
			$attributeData = implode(', ', $attributeData);
		}
		if ($show_label) {
			$output .= '<span class="djc_attribute '.preg_replace('#[^0-9a-zA-Z\-]#', '_', strtolower(trim($attribute->name))).'"><span class="djc_attribute-label">'.$attribute->name.': </span><span class="djc_attribute-value">'.$attributeData.'</span></span>';
		} else {
			$output .= '<span class="djc_attribute '.preg_replace('#[^0-9a-zA-Z\-]#', '_', strtolower(trim($attribute->name))).'"><span class="djc_attribute-value">'.$attributeData.'</span></span>';
		}
		
		return $output;
	}
	
	public function escapeValue($value) {
		$value = addcslashes($value, '\\');
		$value = preg_replace('/\$(\d)/', '\\\$$1', $value);
		return $value;
	}
	
}



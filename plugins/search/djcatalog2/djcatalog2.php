<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

class plgSearchDjcatalog2 extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	function onContentSearchAreas() {
		static $areas = array(
			'djcatalog2' => 'PLG_SEARCH_DJCATALOG2_DJCATALOGITEMS'
			);
			return $areas;
	}
	
	function onContentSearch( $text, $phrase='', $ordering='', $areas=null )
	{
		$app = JFactory::getApplication();
		$db		= JFactory::getDBO();
		$searchText = $text;
	
		require_once(JPATH_SITE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'route.php');
	
		if (is_array( $areas )) {
			if (!array_intersect( $areas, array_keys( $this->onContentSearchAreas() ) )) {
				return array();
			}
		}
	
		// load plugin params info
	 	$plugin = JPluginHelper::getPlugin('search', 'djcatalog2');
	 	$pluginParams = $this->params;
	
		$limit = $pluginParams->def( 'search_limit', 50 );
	
		$text = trim( $text );
		if ( $text == '' ) {
			return array();
		}
		
	
		
		$query = $db->getQuery(true);
		$query->select('i.id AS id, i.alias as alias, i.name AS title, i.intro_desc AS intro, i.created as created, c.id AS catid, c.name AS category, c.alias as catalias, i.description as text, i.metakey as metakey, i.metadesc as metadesc, p.name as producer_name, "2" as browsernav');
		$query->from('#__djc2_items AS i');
		
		$where = '';
		$textSearch = array();
		
		switch ($phrase)
		{
			case 'exact':
				$textSearch[] = 'LOWER(i.name) LIKE '.$db->quote( '%'.$db->escape( $text, true ).'%', false );
				$textSearch[] = 'LOWER(i.description) LIKE '.$db->quote( '%'.$db->escape( $text, true ).'%', false );
				$textSearch[] = 'LOWER(i.intro_desc) LIKE '.$db->quote( '%'.$db->escape( $text, true ).'%', false );
				$textSearch[] = 'LOWER(i.metadesc) LIKE '.$db->quote( '%'.$db->escape( $text, true ).'%', false );
				$textSearch[] = 'LOWER(i.metakey) LIKE '.$db->quote( '%'.$db->escape( $text, true ).'%', false );
				$textSearch[] = 'LOWER(c.name) LIKE '.$db->quote( '%'.$db->escape( $text, true ).'%', false );
				$textSearch[] = 'LOWER(p.name) LIKE '.$db->quote( '%'.$db->escape( $text, true ).'%', false );
				
				$optionsSearch = 
				     ' select i.id '
					.' from #__djc2_items as i '
					.' inner join #__djc2_items_extra_fields_values_int as efv on efv.item_id = i.id'
					.' inner join #__djc2_items_extra_fields as ef on ef.id = efv.field_id and ef.searchable = 1 '
					.' inner join #__djc2_items_extra_fields_options as efo on efo.id = efv.value and lower(efo.value) like '.$db->quote( '%'.$db->escape( $text, true ).'%', false )
					.' union '
					. 'select i.id '
					.' from #__djc2_items as i '
					.' inner join #__djc2_items_extra_fields_values_text as efv on efv.item_id = i.id'
					.' inner join #__djc2_items_extra_fields as ef on ef.id = efv.field_id and ef.searchable = 1 and lower(efv.value) like '.$db->quote( '%'.$db->escape( $text, true ).'%', false )
					;
					
					
				$query->join('LEFT', '('.$optionsSearch.') AS customattribute_search ON customattribute_search.id = i.id');
				$textSearch[] = 'i.id = customattribute_search.id';
				
				$where = ' ( '.implode( ' OR ', $textSearch ).' ) ';
				break;
		
			case 'all':
			case 'any':
			default:
				$words	= explode(' ', $text);
				$textSearches = array();
				foreach ($words as $k=>$word)
				{
					$textSearch = array();
					$textSearch[] = 'LOWER(i.name) LIKE '.$db->quote( '%'.$db->escape( $word, true ).'%', false );
					$textSearch[] = 'LOWER(i.description) LIKE '.$db->quote( '%'.$db->escape( $word, true ).'%', false );
					$textSearch[] = 'LOWER(i.intro_desc) LIKE '.$db->quote( '%'.$db->escape( $word, true ).'%', false );
					$textSearch[] = 'LOWER(i.metadesc) LIKE '.$db->quote( '%'.$db->escape( $word, true ).'%', false );
					$textSearch[] = 'LOWER(i.metakey) LIKE '.$db->quote( '%'.$db->escape( $word, true ).'%', false );
					$textSearch[] = 'LOWER(c.name) LIKE '.$db->quote( '%'.$db->escape( $word, true ).'%', false );
					$textSearch[] = 'LOWER(p.name) LIKE '.$db->quote( '%'.$db->escape( $word, true ).'%', false );
					
					$optionsSearch = 
					     ' select i.id '
						.' from #__djc2_items as i '
						.' inner join #__djc2_items_extra_fields_values_int as efv on efv.item_id = i.id'
						.' inner join #__djc2_items_extra_fields as ef on ef.id = efv.field_id and ef.searchable = 1 '
						.' inner join #__djc2_items_extra_fields_options as efo on efo.id = efv.value and lower(efo.value) like '.$db->quote( '%'.$db->escape( $word, true ).'%', false )
						.' union '
						. 'select i.id '
						.' from #__djc2_items as i '
						.' inner join #__djc2_items_extra_fields_values_text as efv on efv.item_id = i.id'
						.' inner join #__djc2_items_extra_fields as ef on ef.id = efv.field_id and ef.searchable = 1 and lower(efv.value) like '.$db->quote( '%'.$db->escape( $word, true ).'%', false )
						;
						
						
					$query->join('LEFT', '('.$optionsSearch.') AS customattribute_search_'.$k.' ON customattribute_search_'.$k.'.id = i.id');
					$textSearch[] = 'i.id = customattribute_search_'.$k.'.id';
					
					$textSearches[]	= implode(' OR ', $textSearch);
				}
				$where	= '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $textSearches) . ')';
				break;
		}
		
		
		$query->join('left', '#__djc2_categories AS c ON c.id = i.cat_id');
		$query->join('left', '#__djc2_producers AS p ON p.id = i.producer_id');
		
		
		$query->where($where.' AND i.published=1 AND c.published=1');
		
		$query->group('i.id');
		
		switch ( $ordering ) {
			case 'alpha':
				$order = 'i.name ASC';
				break;
		
			case 'category':
			case 'popular':
			case 'newest':
			case 'oldest':
			default:
				$order = 'i.name DESC';
		}
		
		$query->order($order);
		
		$db->setQuery( $query, 0, $limit );
		
		//echo str_replace('#_','jos',$query);
		
		$rows = $db->loadObjectList();
		
		$count = count( $rows );
		for ( $i = 0; $i < $count; $i++ )
		{
			$rows[$i]->href 	= JRoute::_(DJCatalogHelperRoute::getItemRoute($rows[$i]->id.':'.$rows[$i]->alias, $rows[$i]->catid.':'.$rows[$i]->catalias));
			$rows[$i]->section 	= JText::_('PLG_SEARCH_DJCATALOG2_DJCATALOGITEMS').': '.$rows[$i]->category;
			
			// because extra attributes are also taken into accout, we have to trick checkNoHTML function
			$rows[$i]->__term = $searchText;
		}
		$return = array();

		foreach($rows as $key => $section) {
			if(searchHelper::checkNoHTML($section, $searchText, array('title', 'text', 'intro', 'metadesc', 'metakey', 'producer_name', '__term'))) {
				$return[] = $section;
			}
		}
		return $return;
	}
}


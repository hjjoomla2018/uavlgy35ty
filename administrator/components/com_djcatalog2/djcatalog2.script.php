<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined('_JEXEC') or die('Restricted access');

class com_djcatalog2InstallerScript {
	function update($parent) {
		$config = JFactory::getConfig();
		$db = JFactory::getDbo();
		$db->setQuery('show tables');
		$tables = $db->loadColumn();
		$db_prefix = $config->get('dbprefix');
		
		/* In v.2.3.rc.3 we added category params feature, 
		 * but forgot to add 'params' column declaration to the SQL installation script.
		 * Since there were few schema updates since that modification
		 * and Joomla is not able to 'go back', we need to make sure that 'params' column exists
		 */
		
		if (count($tables) && in_array($db_prefix.'djc2_categories', $tables)) {		
			$db->setQuery('SHOW COLUMNS FROM #__djc2_categories');
			$category_columns = $db->loadColumn(0);
			if (!in_array('params', $category_columns)) {
				$db->setQuery('ALTER TABLE #__djc2_categories ADD `params` TEXT');
				$db->query();
			}
		}
		
		/*
		 * since v.3.2.beta.1
		 * splitting field values from old single table into separate tables
		 */
		$old_table 	= $db_prefix.'djc2_items_extra_fields_values';
		$text_table = $db_prefix.'djc2_items_extra_fields_values_text';
		$int_table 	= $db_prefix.'djc2_items_extra_fields_values_int';
		
		
		// if all three table exist then we should perform the upgrade
		if (in_array($old_table, $tables) && in_array($text_table, $tables) && in_array($int_table, $tables)) {
			$db->setQuery('select count(*) from #__djc2_items_extra_fields_values');
			$old_count = $db->loadResult();
			
			$db->setQuery('select count(*) from #__djc2_items_extra_fields_values_text');
			$text_count = $db->loadResult();
			
			$db->setQuery('select count(*) from #__djc2_items_extra_fields_values_int');
			$int_count = $db->loadResult();
			
			$errors = array();
			
			// is there anything to migrate?
			if ($old_count > 0) {
				/* 
				 * if the new _text table isn't empty then probably something went wrong before
				 * so we don't migrate any data
				 */
				if ($text_count == 0) {
					$db->setQuery('insert ignore into #__djc2_items_extra_fields_values_text '
								.' (`id`, `item_id`, `field_id`, `value`)'
								.' select v.id, v.item_id, v.field_id, v.value'
								.' from #__djc2_items_extra_fields_values as v '
								.' inner join #__djc2_items_extra_fields as f on f.id=v.field_id '
								.' where (type=\'html\' or type=\'text\' or type=\'textarea\')');
					$success = $db->query();
					if(!$success && $db->getErrorNum() != 1060) {
						$errors[] = $db->getErrorMsg(true);
					}
				}
				
				/* 
				 * if the new _int table isn't empty then probably something went wrong before
				 * so we don't migrate any data
				 */
				if ($int_count == 0) {
					$db->setQuery('insert ignore into #__djc2_items_extra_fields_values_int '
								.' (`id`, `item_id`, `field_id`, `value`)'
								.' select v.id, v.item_id, v.field_id, v.value'
								.' from #__djc2_items_extra_fields_values as v '
								.' inner join #__djc2_items_extra_fields as f on f.id=v.field_id '
								.' where (type=\'select\' or type=\'radio\' or type=\'checkbox\')');
					$db->query();
					$success = $db->query();
					if(!$success && $db->getErrorNum() != 1060) {
						$errors[] = $db->getErrorMsg(true);
					}
				}
			}
			
			// if during the migration there haven't occurred any errors, remove the old table.
			if (count($errors) == 0) {
				$db->setQuery('drop table #__djc2_items_extra_fields_values');
				$db->query();
			}
		}
	}
	
	function preflight($type, $parent)
	{
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('s.extension_id, s.version_id');
		$query->from('#__schemas AS s');
		$query->join('INNER', '#__extensions AS e ON e.extension_id=s.extension_id');
		$query->where('e.element = '.$db->quote('com_djcatalog2'));
		$query->where('e.type = '.$db->quote('component'));
		$db->setQuery($query);
		
		$schema = $db->loadObject();
		
		// Schema ver. "3.4" was renamed to "3.4.0-2014-05-21" 
		// They are technically the same, so we need a workaround
		if (!empty($schema) && $schema->version_id == '3.4') {
			$uquery = $db->getQuery(true);
			$uquery->update('#__schemas')->set('version_id='.$db->quote('3.4.0-2014-05-21'))->where('extension_id='.$schema->extension_id);
			$db->setQuery($uquery);
			$db->execute();
		}
	}

	function postflight($type, $parent)
	{
		$extFolder = JPath::clean(JPATH_ROOT.'/media/djextensions');
		if (!JFolder::exists($extFolder)) {
			JFolder::create($extFolder);
		}
		
		$folders = array();
		$folders[] = array(
				'src' => JPath::clean(JPATH_ROOT.'/media/djcatalog2/magnific'), 
				'dst' => JPath::clean(JPATH_ROOT.'/media/djextensions/magnific')
		);
		$folders[] = array(
				'src' => JPath::clean(JPATH_ROOT.'/media/djcatalog2/jquery.ui'),
				'dst' => JPath::clean(JPATH_ROOT.'/media/djextensions/jquery.ui')
		);
		
		foreach ($folders as $folder) {
			if (JFolder::exists($folder['src'])) {
				JFolder::move($folder['src'], $folder['dst']);
			}
		}
		
		if ($type == 'update') {
			require_once(JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djcatalog2/lib/djlicense.php'));
			DJLicense::setUpdateServer('Catalog2');
		}
	}
}
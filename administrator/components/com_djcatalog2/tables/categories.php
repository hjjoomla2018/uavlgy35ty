<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

// No direct access
defined('_JEXEC') or die;
class Djcatalog2TableCategories extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_categories', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}
		
		if(empty($array['alias'])) {
			$array['alias'] = $array['name'];
		}
		$array['alias'] = JApplication::stringURLSafe($array['alias']);
		if(trim(str_replace('-','',$array['alias'])) == '') {
			$array['alias'] = JFactory::getDate()->format('Y-m-d-H-i-s');
		}
		
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		if (!$this->id) {
			if (!intval($this->created)) {
				$this->created = $date->toSql();
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
		}	
		
		$table = JTable::getInstance('Categories', 'Djcatalog2Table');
		
		$task = $app->input->get('task');
		
		if ($app->isSite() || $task == 'import' || $task == 'save2copy') {
			if ($table->load(array('alias'=>$this->alias)) && ($table->id != $this->id || $this->id==0)) {
				$db->setQuery('select alias from #__djc2_categories where id != '.$this->id.' and alias like '.$db->quote($db->escape($this->alias).'%').' order by alias asc');
				$aliases = $db->loadColumn();
				$suffix = 2;
				while(in_array($this->alias.'-'.$suffix, $aliases)) {
					$suffix++;
				}
				$this->alias = $this->alias.'-'.$suffix;
			}
		} else {
			if ($params->get('seo_advanced', 0) == '1') {
				if ($table->load(array('alias'=>$this->alias)) && ($table->id != $this->id || $this->id==0)) {
					$this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_ALIAS'));
					return false;
				}
			} else {
				if ($table->load(array('alias'=>$this->alias,'parent_id'=>$this->parent_id)) && ($table->id != $this->id || $this->id==0)) {
					$this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_ALIAS'));
					return false;
				}
			}
		}
		return parent::store($updateNulls);
	}
}

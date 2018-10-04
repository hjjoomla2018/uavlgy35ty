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

class Djcatalog2ModelUnit extends JModelAdmin
{
	protected $text_prefix = 'COM_DJCATALOG2';

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getTable($type = 'Units', $prefix = 'Djcatalog2Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_djcatalog2.unit', 'unit', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_djcatalog2.edit.unit.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
		
		if (empty($table->id)) {
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__djc2_units');
				$max = $db->loadResult();
		
				$table->ordering = $max+1;
			}
		}
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}
	
	public function save($data) {
		$return = parent::save($data);
		if ($return) {
			if (isset($data['is_default']) && (int)$data['is_default'] == 1) {
				$id = (int)$this->getState($this->getName() . '.id');
				if ($id > 0) {
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->update('#__djc2_units')->set('is_default=0')->where('id !='.(int)$id);
					$db->setQuery($query);
					
					return $db->execute();
				}
			}
		}
		return $return;
	}

	public function delete(&$pks)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$pks = (array) $pks;
		$table = $this->getTable();
		
		// Include the plugins for the delete events.
		JPluginHelper::importPlugin($this->events_map['delete']);
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($table->is_default) {
					unset($pks[$i]);
					
					JLog::add(JText::_('COM_DJCATALOG2_ERROR_CANNOT_DELETE_DEFAULT_ITEM'), JLog::WARNING, 'jerror');
					
					return false;
				}
				else if ($this->canDelete($table))
				{
					$context = $this->option . '.' . $this->name;
					
					// Trigger the before delete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
					
					if (in_array(false, $result, true))
					{
						$this->setError($table->getError());
						
						return false;
					}
					
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						
						return false;
					}
					
					$query = $this->_db->getQuery(true);
					$query->update('#__djc2_items')->set('unit_id=0')->where('unit_id='.$pk);
					$this->_db->setQuery($query);
					$this->_db->execute();
					
					// Trigger the after event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();
					
					if ($error)
					{
						JLog::add($error, JLog::WARNING, 'jerror');
						
						return false;
					}
					else
					{
						JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
						
						return false;
					}
				}
			}
			else
			{
				$this->setError($table->getError());
				
				return false;
			}
		}
		
		// Clear the component's cache
		$this->cleanCache();
		
		return true;
	}
}
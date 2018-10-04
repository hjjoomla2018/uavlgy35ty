<?php
use Joomla\Registry\Registry;

/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class Djcatalog2ViewField extends JViewLegacy {
	
	protected $fieldtype;
	protected $suffix;
	protected $fieldId;
	protected $field;
	protected $fieldoptions;
	protected $fieldparams;
	
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		
		$this->fieldtype = $app->input->get('fieldtype', null, 'string');
		$this->fieldId = $app->input->get('fieldId', 0, 'int');
		$this->suffix = $app->input->get('suffix', null, ' string');
		
		$db = JFactory::getDbo();
		
		$this->fieldparams = new Registry();
		
		if ($this->fieldId > 0) {
			$query = $db->getQuery(true);
			$query->select('*')->from('#__djc2_items_extra_fields')->where('id='.(int)$this->fieldId);
			$db->setQuery($query);
			$this->field = $db->loadObject();
			
			if ($this->field) {
				$query = $db->getQuery(true);
				$query->select('*')->from('#__djc2_items_extra_fields_options')->where('field_id='.(int)$this->fieldId)->order('ordering ASC');
				$db->setQuery($query);
				$this->fieldoptions = $db->loadObjectList();
				
				if (!empty($this->field->params)) {
					$this->fieldparams = new Registry($this->field->params);
				}
			}
		}
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}	
		parent::display($tpl);
	}
	
}
?>
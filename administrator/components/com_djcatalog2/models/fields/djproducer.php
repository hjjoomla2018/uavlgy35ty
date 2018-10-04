<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die();
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');


class JFormFieldDjproducer extends JFormField {
	
	protected $type = 'Djproducer';
	
	protected function getInput()
	{
		$attr = '';

		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		
		$user = JFactory::getUser();
		$db	= JFactory::getDBO();
		
		$user_id = false;
		
		if (!empty($this->element['validate'])) {
			if (!empty($this->element['validate_user'])) {
				$user_id = (int)$this->element['validate_user'];
			} else {
				$user_id = (int)$user->id;
			}
		}

		$where = ($user_id !== false) ? 'WHERE created_by='.(int)$user_id : '';
		
		$query = "SELECT * FROM #__djc2_producers ".$where." ORDER BY name";
		
		$db->setQuery($query);
		$producers = $db->loadObjectList();
		
		$out = '';
		if (count($producers) > 1 || empty($this->element['validate'])) {
			$options = array();
			$options[] = JHTML::_('select.option', '','- '.JText::_('COM_DJCATALOG2_SELECT_PRODUCER').' -' );
			foreach($producers as $producer){
				$options[] = JHTML::_('select.option', $producer->id, $producer->name);
				
			}
			$out = JHTML::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value);
		} else {
			$producer_id = 0;
			$producer_value = '- '.JText::_('COM_DJCATALOG2_NOT_AVAILABLE').' -';
			if (count($producers) == 1){
				$producer_id = $producers[0]->id;
				$producer_value = $producers[0]->name;
			}
			$out = '<input type="text" readonly="readonly"  value="'.$producer_value.'" class="inputbox input readonly" disabled="true"/>';
			$out .= '<input type="hidden" name="'.$this->name.'" value="'.$producer_id.'" id="'.$this->id.'" />';
		}
		return ($out);
	}
}
?>
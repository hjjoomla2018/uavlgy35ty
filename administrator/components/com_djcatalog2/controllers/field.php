<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

class Djcatalog2ControllerField extends JControllerForm {
	public function save($key = null, $urlVar = null) {
		/*echo '<pre>';
		print_r($_POST);
		print_r($_FILES);
		die();*/
		return parent::save($key, $urlVar);
	}
	/*
	function getForm() {
		$itemId = JRequest::getVar('itemId',0);
		$groupId = JRequest::getVar('groupId',0);
		$out = null;
		$db = JFactory::getDbo();
		if ($groupId > 0){
			$query = $db->getQuery(true);
			$query->select('f.*');
			$query->from('#__djc2_items_extra_fields AS f');
			
			$query->select('GROUP_CONCAT(v.value SEPARATOR \'|\') AS field_value');
			$query->join('LEFT','#__djc2_items_extra_fields_values AS v ON f.id=v.field_id AND v.item_id='.(int)$itemId);
			$query->where('f.group_id='.(int)$groupId);
			$query->group('f.id');
			$query->order('f.ordering');
			
			$db->setQuery($query);
			$fields = ($db->loadObjectList());

			if (count($fields)) {
				$out .= '<div class="adminformlist">';
				foreach ($fields as $k=>$v) {
					$input = null;
					switch ($v->type) {
						case 'text': {
							$input = '
									<div class="control-label">
										<label for="attribute_'.$v->id.'">
										'.$v->name.'
										</label>
									</div>
									<div class="controls">
										<input size="40" id="attribute_'.$v->id.'" type="text" name="attribute['.$v->id.']" value="'.htmlspecialchars($v->field_value).'" />
									</div>
								';
							break;
						}
						case 'textarea': {
							$input = '
									<div class="control-label">
										<label for="attribute_'.$v->id.'">
										'.$v->name.'
										</label>
									</div>
									<div class="controls">
										<textarea rows="3" cols="30" id="attribute_'.$v->id.'" name="attribute['.$v->id.']">'.htmlspecialchars($v->field_value).'</textarea>
									</div>
								';
							break;
						}
						case 'html': {
							$editor = JFactory::getEditor();
							$input = '
									<div class="control-label">	
										<label for="attribute_'.$v->id.'">
											'.$v->name.'
										</label>
									</div>
									<div class="controls">
										'.$editor->display( 'attribute['.$v->id.']', $v->field_value, '100%', '250', '0', '0',false).'
									</div>
									';
							break;
						}
						case 'select': {
							$db->setQuery('SELECT * FROM #__djc2_items_extra_fields_options WHERE field_id='.(int)$v->id.' ORDER BY ordering ASC');
							$options = $db->loadObjectList();
							$optionList = '<option value="">---</option>';
							foreach ($options as $option) {
								$selected = ($option->id == $v->field_value) ? 'selected="selected"' : '';
								$optionList .= '<option '.$selected.' value="'.$option->id.'">'.htmlspecialchars($option->value).'</option>';
							}
							$input = '
									<div class="control-label">
										<label for="attribute_'.$v->id.'">'.$v->name.'</label>
									</div>
									<div class="controls">
										<select id="attribute_'.$v->id.'" name="attribute['.$v->id.']">'.$optionList.'</select>
									</div>
								';
							break;
						}
						case 'checkbox': {
							$db->setQuery('SELECT * FROM #__djc2_items_extra_fields_options WHERE field_id='.(int)$v->id.' ORDER BY ordering ASC');
							$options = $db->loadObjectList();
							$optionList = null;
							$values = explode('|', $v->field_value);
							$i = 1;
							foreach ($options as $option) {
								$selected = (in_array($option->id, $values)) ? 'checked="checked"' : '';
								$optionList .= '
									<input id="attribute_'.$v->id.'-'.$i.'" type="checkbox" '.$selected.' name="attribute['.$v->id.'][]" value="'.$option->id.'">
									<label for="attribute_'.$v->id.'-'.$i.'">'.htmlspecialchars($option->value).'</label>
									';
								$i++;
							}
							$input = '
									<div class="control-label">
										<label>'.$v->name.'</label>
									</div>
									<div class="controls">
										<fieldset id="attribute_'.$v->id.'-" class="checkbox">
											'.$optionList.'
										</fieldset>
									</div>
							';
							break;
						}
					case 'radio': {
							$db->setQuery('SELECT * FROM #__djc2_items_extra_fields_options WHERE field_id='.(int)$v->id.' ORDER BY ordering ASC');
							$options = $db->loadObjectList();
							$optionList = null;
							$i = 0;
							foreach ($options as $option) {
								$selected = ($option->id == $v->field_value) ? 'checked="checked"' : '';
								$optionList .= '
									<input id="attribute_'.$v->id.'-'.($i).'" type="radio" '.$selected.' name="attribute['.$v->id.']" value="'.$option->id.'">
									<label for="attribute_'.$v->id.'-'.$i.'" for="attribute_'.$v->id.''.'-'.'-lbl">'.htmlspecialchars($option->value).'</label>';
								$i++;
							}
							$input = '
									<div class="control-label">
										<label>'.$v->name.'</label>
									</div>
									<div class="controls">
										<fieldset id="attribute_'.$v->id.'-" class="required radio">
											'.$optionList.'
										</fieldset>
									</div>
							';
							break;
						}
						default: break;
					}
					$out .= '<div class="control-group">'.$input.'</div>';
				}
				
				$out .= '</div>';
			} else {
				$out = JText::_('COM_DJCATALOG2_NO_FIELDS_IN_GROUP');
			}
		} else {
			$out = JText::_('COM_DJCATALOG2_CHOOSE_FIELDGROUP_FIRST');
		}
		echo $out;exit;
	}*/
}
?>

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die();
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');


class JFormFieldDjcuser extends JFormField {
	
	protected $type = 'Djcuser';
	
	protected function getInput()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$id    = isset($this->element['id']) ? $this->element['id'] : null;
		$cssId = '#' . $this->getId($id, $this->element['name']);
		
		$limit = $this->multiple ? 0 : 1;
		$context = (isset($this->element['context'])) ? $this->element['context'] : null;
		
		$urlVars = 'index.php?option=com_djcatalog2&task=users.searchAjax&context=' . trim($context);
		$url = $app->isSite() ? JUri::root() . $urlVars : JUri::base() . $urlVars;
		
		$chosenAjaxSettings = new Registry(
			array(
				'selector'      => $cssId,
				'type'          => 'GET',
				'url'           => $url,
				'dataType'      => 'json',
				'jsonTermKey'   => 'like',
				'minTermLength' => 2,
				'disable_search_threshold' => 20
			)
		);
		
		$chosenSettings = array();
		if ($limit > 0) {
			$chosenSettings['max_selected_options'] = (int)$limit;
		}
		$chosenSettings['disable_search_threshold'] = 0;
		$chosenSettings['placeholder_text_multiple'] = JText::_('COM_DJCATALOG2_CONFIG_AJAX_USER_PLACEHOLDER');
		$chosenSettings['placeholder_text_single'] = JText::_('COM_DJCATALOG2_CONFIG_AJAX_USER_PLACEHOLDER');
		$chosenSettings = new Registry($chosenSettings);
		
		JHtml::_('formbehavior.chosen', $cssId, null, $chosenSettings);
		JHtml::_('formbehavior.ajaxchosen', $chosenAjaxSettings);
		
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$attr = '';
		
		$class = $this->element['class'] ? (string) $this->element['class'] : '';
		$class .= $this->element['required']=='true' ? ' required' : '';
		
		$attr .= 'class="'.$class.'"';
		$name = $this->name;
		if (isset($this->element['multiple']) && $this->element['multiple'] == 'true') {
			$attr .= ' multiple="true" size="10"';
		} else {
			// such situations should not happen, but if they do (it's possible) we need to assign at least one group of fields
			if (is_array($this->value)) {
				$newValue = 0;
				foreach ($this->value as $v) {
					if ($v > 0) {
						$newValue = $v;
						break;
					}
				}
				$this->value = $newValue;
			}
		}
		
		if ($this->onchange != '') {
			$attr .= ' onchange="'.trim($this->onchange).'"';
		}
		
		$db = JFactory::getDbo();
		$db->setQuery('SELECT id AS value, name AS text FROM #__users ORDER BY name');
		$users = $db->loadObjectList();
		$options = array();
		
		if (!isset($this->element['multiple']) || $this->element['multiple'] != 'true') {
			$options[] = JHTML::_('select.option', '' , JText::_('COM_DJCATALOG2_LIST_PLEASE_SELECT'));
		}
		
		$selected = array();
		
		if (!empty($this->value)) {
			if (is_array($this->value)) {
				foreach ($users as $user) {
					if ( in_array($user->value, $this->value)) {
						$selected[] = $user->text;
						$options[] = JHtml::_('select.option', $user->value, $user->text);
					}
				}
			} else {
				foreach ($users as $user) {
					if ( $user->value == $this->value ) {
						$selected[] = $user->text;
						$options[] = JHtml::_('select.option', $user->value, $user->text);
						break;
					}
				}
			}
		}
		
		$out = JHtml::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $this->value, $this->id);
		
		return ($out);
		
	}
}
?>
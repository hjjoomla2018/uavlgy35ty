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


class JFormFieldDjclabel extends JFormField {
	
	protected $type = 'Djclabel';
	
	protected function getInput()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$id    = isset($this->element['id']) ? $this->element['id'] : null;
		$cssId = '#' . $this->getId($id, $this->element['name']);
		
		$chosenAjaxSettings = new Registry(
			array(
				'selector'      => $cssId,
				'type'          => 'GET',
				'url'           => JUri::root() . 'index.php?option=com_djcatalog2&task=labels.searchAjax',
				'dataType'      => 'json',
				'jsonTermKey'   => 'like',
				'minTermLength' => 2,
				'disable_search_threshold' => 20
			)
		);
		
		$limit = $app->isSite() ? $params->get('fed_labels_limit', 3) : 0;
		
		$chosenSettings = array();
		if ($limit > 0) {
			$chosenSettings['max_selected_options'] = (int)$limit;
		}
		$chosenSettings['disable_search_threshold'] = 1;
		$chosenSettings['placeholder_text_multiple'] = JText::_('COM_DJCATALOG2_CONFIG_AJAX_LABEL_PLACEHOLDER');
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
		
		$db = JFactory::getDbo();
		$db->setQuery('SELECT id AS value, name AS text FROM #__djc2_labels ORDER BY ordering ASC');
		$labels = $db->loadObjectList();
		$options = array();
		
		if (!isset($this->element['multiple']) || $this->element['multiple'] != 'true') {
			$options[] = JHTML::_('select.option', '' , JText::_('COM_DJCATALOG2_LIST_PLEASE_SELECT'));
		}
		
		$selected = array();
		
		if (!empty($this->value)) {
			foreach ($labels as $label) {
				if ( in_array($label->value, $this->value)) {
					$selected[] =$label->text;
					$options[] = JHTML::_('select.option', $label->value, $label->text);
				}
			}
		}
		
		$out = JHtml::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $this->value, $this->id);
		
		return ($out);
		
	}
}
?>
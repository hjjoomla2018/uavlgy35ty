<?php
/**
* @package DJ-Catalog2
* @copyright Copyright (C) 2010 Blue Constant Media LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
*/

use Joomla\Registry\Registry;

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldDJItems extends JFormFieldList
{
	public $type = 'DJItems';

	/**
	 * Flag to work with nested tag field
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	public $isNested = null;

	/**
	 * com_tags parameters
	 *
	 * @var    JRegistry
	 * @since  3.1
	 */
	protected $comParams = null;

	/**
	 * Constructor
	 *
	 * @since  3.1
	 */
	public function __construct()
	{
		parent::__construct();

		// Load com_tags config
		$this->comParams = JComponentHelper::getParams('com_djcatalog2');
	}

	/**
	 * Method to get the field input for a tag field.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.1
	 */
	protected function getInput()
	{
		// Get the field id
		$id    = isset($this->element['id']) ? $this->element['id'] : null;
		$cssId = '#' . $this->getId($id, $this->element['name']);
		
		$method = isset($this->element['ajax']) ? 'ajax' : 'static';


		$this->enableField($cssId, false, $method);

		if (!is_array($this->value) && !empty($this->value))
		{
			if (is_string($this->value))
			{
				$this->value = explode(',', $this->value);
			}
		}

		$input = parent::getInput();

		return $input;
	}

	protected function getOptions()
	{
		$options = array();
		$user = JFactory::getUser();
		
		$method = isset($this->element['ajax']) ? 'ajax' : 'static';
		
		$db = JFactory::getDbo();
		if ($method == 'ajax') {
			$values = $this->value;
			if (!is_array($values) && !empty($values))
			{
				if (is_string($values))
				{
					$values = explode(',', $values);
				}
			}
			
			if (is_array($values) && count($values)) {
				$db->setQuery('select id as value, concat(name, " ", " [", id, "]") as text from #__djc2_items WHERE id IN ('.implode(',', $values).') order by name asc');
				$options = $db->loadObjectList();
			}
			
		} else {
			$db->setQuery('select id as value, concat(name, " ", " [", id, "]") as text from #__djc2_items order by name asc');
			$options = $db->loadObjectList();
		}
		
		return $options;
	}
	
	public static function enableField($selector='#jform_product_id', $allowCustom = true, $type = 'static')
	{
		if ($type == 'ajax') {
			// Tags field ajax
			$chosenAjaxSettings = new Registry(
					array(
							'selector'    => $selector,
							'type'        => 'GET',
							'url'         => JUri::base(true). '/index.php?option=com_djcatalog2&task=findItemByName',
							'dataType'    => 'json',
							'jsonTermKey' => 'like'
					)
			);
			
			JHtml::_('formbehavior.ajaxchosen', $chosenAjaxSettings);
		} else {
			JHtml::_('behavior.combobox');
			JHtml::_('formbehavior.chosen', $selector, null, array('placeholder_text_multiple' => JText::_('COM_DJCATALOG2_TYPE_EMAIL_OR_SELECT')));
		}
		
		// Allow custom values ?
		if ($allowCustom)
		{
			JFactory::getDocument()->addScriptDeclaration("
				(function($){
					$(document).ready(function () {

						var customTagPrefix = '';//'#new#';

						// Method to add tags pressing enter
						$('" . $selector . "_chzn input').keyup(function(event) {

							// Tag is greater than 3 chars and enter pressed
							if (this.value.length >= 3 && (event.which === 13 || event.which === 188)) {
								
								this.value = this.value.replace(',', '');

								// Search an highlighted result
								var highlighted = $('" . $selector . "_chzn').find('li.active-result.highlighted').first();

								// Add the highlighted option
								if ((event.which === 13 || event.which === 188) && highlighted.text() !== '')
								{
									// Extra check. If we have added a custom tag with this text remove it
									var customOptionValue = customTagPrefix + highlighted.text();
									$('" . $selector . " option').filter(function () { return $(this).val() == customOptionValue; }).remove();

									// Select the highlighted result
									var tagOption = $('" . $selector . " option').filter(function () { return $(this).html() == highlighted.text(); });
									tagOption.attr('selected', 'selected');
								}
								// Add the custom tag option
								else
								{
									var customTag = this.value;

									// Extra check. Search if the custom tag already exists (typed faster than AJAX ready)
									var tagOption = $('" . $selector . " option').filter(function () { return $(this).html() == customTag; });
									if (tagOption.text() !== '')
									{
										tagOption.attr('selected', 'selected');
									}
									else
									{
										var option = $('<option>');
										option.text(this.value).val(customTagPrefix + this.value);
										option.attr('selected','selected');

										// Append the option an repopulate the chosen field
										$('" . $selector . "').append(option);
									}
								}

								this.value = '';
								$('" . $selector . "').trigger('liszt:updated');
								event.preventDefault();

							}
						});
					});
				})(jQuery);
				"
			);
		}
	}
}

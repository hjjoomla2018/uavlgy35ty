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

class JFormFieldDjccountrystate extends JFormField {
	
	protected $type = 'Djccountrystate';
	
	protected function getInput()
	{
		$app = JFactory::getApplication();
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$attr = '';
		
		$class = $this->element['class'] ? (string) $this->element['class'] : '';
		$class .=(isset($this->element['required']) && $this->element['required'] == 'true') ? ' required' : '';

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
		
		$country = empty($this->element['country']) ? false : (int)$this->element['country'];
		$country_field = empty($this->element['country_field']) ? false : $this->element['country_field'];
		
		/*$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('a.id AS value, a.name AS text, a.country_id');
		$query->from('#__djc2_countries_states AS a');
		$query->join('inner', '#__djc2_countries AS c ON a.country_id = c.id');
		if ($country) {
			$query->where('a.country_id = '.$country);
		}
		$query->where('a.published=1 AND c.published=1');
		$query->order('c.is_default DESC, c.country_name ASC, a.name ASC');
		$db->setQuery($query);
		
		$states = $db->loadObjectList();
		*/
		$options = array();
		
		$default_label = (isset($this->element['required']) && $this->element['required'] == 'true') ? JText::_('COM_DJCATALOG2_CHOOSE_STATE') : JText::_('COM_DJCATALOG2_CONFIG_NONE');
		$default_value = (isset($this->element['required']) && $this->element['required'] == 'true') ? '' : '0';
		
		$options[] = JHtml::_('select.option', $default_value, '- '.$default_label.' -', array('option.text' => 'text', 'option.key' => 'value', 'attr' => 'data-parent=""', 'option.attr' => 'data-parent'));
		
		/*foreach ($states as $state) {
			$options[] = JHTML::_('select.option', $state->value, $state->text, array('option.text' => 'text', 'option.key' => 'value', 'attr' => 'data-parent="'.$state->country_id.'"', 'option.attr' => 'data-parent'));
		}*/
		
		//$out = JHtml::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $this->value, $this->id);
		
		$attribs = array();
		$attribs['id'] = $this->id;
		$attribs['list.attr'] = $attr;
		$attribs['list.translate'] = false;
		$attribs['option.key'] = 'value';
		$attribs['option.text'] = 'text';
		$attribs['option.attr'] = 'data-parent';
		$attribs['list.select'] = $this->value;
		
		$out = JHtml::_('select.genericlist', $options, $name, $attribs);
		
		/*if ($country_field) {
			$script = 'jQuery(document).ready(function(){
				var country_select = jQuery("#'.$country_field.'");
				var state_select = jQuery("#'.$this->id.'");
				var init_val = "'.($this->value ? $this->value : $default_value).'";
				var show_null = '.($app->isAdmin() ? 'true' : 'false').';
				if (country_select.length > 0) {
					country_select.change(function(){
						var value = jQuery(this).val();
						if (!(show_null && (value == "" || value == "0"))) {
							state_select.find("option").removeAttr("selected");
							state_select.find("option").each(function(){
								var attr = jQuery(this).attr("data-parent");
								if (attr == "" || attr == value) {
									jQuery(this).css("display", "");
									jQuery(this).removeAttr("disabled");
								} else {
									jQuery(this).css("display", "none");
									jQuery(this).attr("disabled", "disabled");
								}
							});
						} else if (show_null) {
							state_select.find("option").css("display", "").removeAttr("disabled");
						}
						
						country_select.trigger("liszt:updated");
						state_select.trigger("liszt:updated");
					});
						
					country_select.trigger("change");
					
					state_select.val(init_val);
					state_select.trigger("liszt:updated");
				}
			});';
			JFactory::getDocument()->addScriptDeclaration($script);
		}*/
		
		$url = null;
		if ($app->isAdmin()) {
			$url = JRoute::_('index.php?option=com_djcatalog2&task=getStatesByCountry', false);
		} else {
			$url = JRoute::_('index.php?option=com_djcatalog2&task=getStatesByCountry', false);
		}
		
		$script = 'jQuery(document).ready(function(){
			var ajax;
			var country_select = jQuery("#'.$country_field.'");
			var state_select = jQuery("#'.$this->id.'");
			var init_val = "'.($this->value ? $this->value : $default_value).'";
			var show_null = '.($app->isAdmin() ? 'true' : 'false').';
			var country = '.($country ? $country : 0).';
			
			function loadStates(country) {
				if(ajax && ajax.readyState != 4){
					ajax.abort();
				}			
				ajax = jQuery.ajax({
					type: "GET",
					url : "'.$url.'",
					data: "country= " + country + "&format=raw&ts=" + Date.now()
				}).done(function(response) {
					var resp;
					try {
						resp =  jQuery.parseJSON(response);
					} catch (e) {
						return;
					}
					for (var i in resp) {
						if (resp.hasOwnProperty(i)) {
							var new_opt = jQuery("<option value=\""+resp[i].id+"\" />");
							if (resp[i].id == init_val) {
								new_opt.attr("selected", "selected");
							}
							state_select.append(new_opt);
							new_opt.text(resp[i].name);
						}
					}
					
					state_select.trigger("liszt:updated");
				});
			}
		
			if (country_select.length > 0) {
				country_select.change(function(){
					state_select.find("option").each(function(){
						if (jQuery(this).val() != "" && jQuery(this).val() != "0") {
							jQuery(this).remove();
						}
					});
					loadStates(jQuery(this).val());
				});
				
				country_select.trigger("change");
			} else {
				loadStates(country);
			}
		});';
		
		JFactory::getDocument()->addScriptDeclaration($script);
		
		return ($out);
		
	}
}
?>
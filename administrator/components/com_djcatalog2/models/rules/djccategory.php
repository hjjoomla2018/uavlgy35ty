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
defined('JPATH_PLATFORM') or die;

class JFormRuleDjccategory extends JFormRule
{
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$allowed_categories = array();
		if (!empty($element['allowed_categories'])) {
			if (!is_array($element['allowed_categories'])) {
				$allowed_categories = explode(',', $element['allowed_categories']);
			}
		}
		if (empty($allowed_categories)) {
			return true;
		}
		
		if (is_scalar($value)) {
			// Check each value and return true if we get a match
			foreach ($allowed_categories as $option)
			{
				if ((int)$value == (int)$option)
				{
					return true;
				}
			}
		} else if (is_array($value) || empty($value)) {
			$required = (empty($element['required'])) ? false : $element['required'];
			if ((empty($value) || count($value) == 0 )&& $required != 'true' && $required != 'required') {
				return true;
			}
			$value = array_unique($value);
			// If at least one category is invalid, return false
			$is_valid = false;
			foreach ($value as $selected) {
				if (!in_array($selected, $allowed_categories) && (int)$selected > 0) {
					return false;
				} else if ((int)$selected >= 0) {
					$is_valid = true;
				}
			}
			
			return $is_valid;
		}
		
		return false;
		
	}
}

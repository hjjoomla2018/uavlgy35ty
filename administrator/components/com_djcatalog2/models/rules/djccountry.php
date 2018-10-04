<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die();
defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

class JFormRuleDjccountry extends JFormRule
{
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		if (!$value) {
			return !($element['required'] == 'true');
		}
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('id')->from('#__djc2_countries')->where('id='.(int)$value.' AND published=1');
		$db->setQuery($query);
		
		return ($db->loadResult() > 0 ? true : false);
	}
}


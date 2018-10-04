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

class JFormRuleDjccountrystate extends JFormRule
{
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		if (!$value) {
			return !($element['required'] == 'true');
		}
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query->select('cs.id')->from('#__djc2_countries_states AS cs')->where('cs.id='.(int)$value.' AND cs.published=1');
		$query->join('inner', '#__djc2_countries AS c ON c.id=cs.country_id AND (c.published=1 OR c.is_default=1)');
		$db->setQuery($query);
		
		return ($db->loadResult() > 0 ? true : false);
	}
}


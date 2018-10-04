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

class JFormRuleDjcproducer extends JFormRule
{
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();

		$user_id = ($form->getValue('created_by')) ? (int)$form->getValue('created_by') : $user->id;

		$db->setQuery('select id from #__djc2_producers where created_by='.(int)$user_id);
		$user_producers = $db->loadColumn();
		
		if (in_array((int)$value, $user_producers)) {
			return true;
		}
		
		if (count($user_producers) == 0 && (int)$value == 0) {
			return true;
		}
		
		return false;
		
	}
}

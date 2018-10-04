<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// No direct access
defined('_JEXEC') or die;

class Djcatalog2TableCartFields extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__djc2_cart_extra_fields', 'id', $db);
	}
	function bind($array, $ignore = '')
	{	
		if(empty($array['alias'])) {
			$array['alias'] = $array['name'];
		}
		$array['alias'] = JFilterOutput::stringURLSafe($array['alias']);
		$array['alias'] = trim(str_replace('-','_',$array['alias']));
		if(trim(str_replace('_','',$array['alias'])) == '') {
			$array['alias'] = JFactory::getDate()->format('Y_m_d_H_i_s');
		}
		
		if (in_array($array['type'], array('checkbox', 'select', 'radio', 'text'))) {
			//$array['searchable'] = '0';
		} else {
			$array['filterable'] = '0';			
		}
		
		return parent::bind($array, $ignore);
	}
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		
		$table = JTable::getInstance('CartFields', 'Djcatalog2Table');
		if ($table->load(array('alias'=>$this->alias)) && ($table->id != $this->id || $this->id==0)) {
			$this->setError(JText::_('COM_DJCATALOG2_ERROR_UNIQUE_ALIAS'));
			return false;
		}
		return parent::store($updateNulls);
	}
}


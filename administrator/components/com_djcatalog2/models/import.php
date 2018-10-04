<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.helper');
jimport( 'joomla.application.component.model');


class DJCatalog2ModelImport extends JModelLegacy {

	protected $_categories;
	protected $_producers;
	protected $_users;
	protected $_fieldgroups;
	protected $_acl;
	
	function __construct()
	{
		parent::__construct();
	}
	public function getCategories(){
		if(empty($this->_categories)) {
			$query = "SELECT * FROM #__djc2_categories ORDER BY name";
			$this->_categories = $this->_getList($query,0,0);
		}
		return $this->_categories;
	}
	
	public function getProducers(){
		if(empty($this->_producers)) {
			$query = "SELECT * FROM #__djc2_producers ORDER BY name";
			$this->_producers = $this->_getList($query,0,0);
		}
		return $this->_producers;
	}
	public function getUsers() {
		if(empty($this->_users)) {
			$query = "SELECT * FROM #__users ORDER BY name";
			$this->_users = $this->_getList($query,0,0);
		}
		return $this->_users;
	}
	public function getFieldgroups() {
		if(empty($this->_fieldgroups)) {
			$query = "SELECT * FROM #__djc2_items_extra_fields_groups ORDER BY name";
			$this->_fieldgroups = $this->_getList($query,0,0);
		}
		return $this->_fieldgroups;
	}
	
	public function getACL(){
		if(empty($this->_acl)) {
			$query = "SELECT id, title FROM #__viewlevels ORDER BY ordering ASC, id ASC";
			$this->_acl = $this->_getList($query,0,0);
		}
		return $this->_acl;
	}
}
?>

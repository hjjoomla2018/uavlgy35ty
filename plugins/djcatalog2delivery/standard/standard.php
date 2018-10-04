<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport('joomla.plugin.plugin');

class plgDjcatalog2deliveryStandard extends JPlugin {
	
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	protected function isAllowed($plgInfo, $type = 'djcatalog2delivery') {
		if (empty($plgInfo)) return  false;
		return (bool)($plgInfo->plugin == $this->_name && $this->_type == $type);
	}
	
	public function onContentPrepareForm($form, $data) {
		if ($form->getName() != 'com_djcatalog2.delivery') {
			return;
		}
		
		$plugin = '';
		
		if (!empty($data) && !empty($data->plugin)) {
			$plugin = $data->plugin;
		} else {
			$jform = JFactory::getApplication()->input->get('jform', array(), 'array');
			if (!empty($jform) && isset($jform['plugin'])) {
				$plugin = $jform['plugin'];
			}
		}
		
		if ($plugin != $this->_name) {
			return true;
		}
		
		return $form->loadFile(dirname(__FILE__).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'configuration.xml', false);
	}
	
	public function onDJC2BeforeSaveOrder($context, $table, $isNew, $plgInfo) {
		if ($context != 'com_djcatalog2.checkout.delivery' || !$this->isAllowed($plgInfo)) {
			return;
		}
	}
	
	public function onDJC2AfterSaveOrder($context, $table, $isNew, $plgInfo) {
		if ($context != 'com_djcatalog2.checkout.delivery' || !$this->isAllowed($plgInfo)) {
			return;
		}
	}
	
	public function onDJC2CheckoutDetailsDisplay($context, $plgInfo) {
		if ($context != 'com_djcatalog2.checkout.delivery' || !$this->isAllowed($plgInfo)) {
			return;
		}
		
		$html = '';
		if (trim(strip_tags($plgInfo->description)) != '' ) {
			$html = '<h4>'.$plgInfo->name.'</h4>';
			$html .= $plgInfo->description;
		}
		
		return $html;
	}
	
	public function onDJC2OrderDetailsDisplay($context, $order, $plgInfo) {
		if ($context != 'com_djcatalog2.order.delivery' || !$this->isAllowed($plgInfo)) {
			return;
		}
		
		$html = '';
		if (trim(strip_tags($plgInfo->description)) != '' ) {
			$html = '<h4>'.$plgInfo->name.'</h4>';
			$html .= $plgInfo->description;
		}
		
		return $html;
	}
}



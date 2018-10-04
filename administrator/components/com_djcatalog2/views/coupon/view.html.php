<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class Djcatalog2ViewCoupon extends JViewLegacy {
	
	protected $state;
	protected $item;
	protected $form;
	
	public function display($tpl = null)
	{
		
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
				
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		
		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);

		$text = $isNew ? JText::_( 'COM_DJCATALOG2_NEW' ) : JText::_( 'COM_DJCATALOG2_EDIT' );
		
		$title = JText::_('COM_DJCATALOG2_COUPON');
		JToolBarHelper::title($title, 'generic.png');
		
		JToolBarHelper::apply('coupon.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('coupon.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::custom('coupon.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		JToolBarHelper::custom('coupon.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		JToolBarHelper::cancel('coupon.cancel', 'JTOOLBAR_CANCEL');
	}
}
?>
<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');

class DJMediatoolsViewImages extends JViewLegacy
{
	protected $images;
	protected $stylesheets;
	protected $resmushed;
	
	function display($tpl = null)
	{
		JHtml::_('behavior.framework');
		
		$document = JFactory::getDocument();
        $document->addScript(JURI::root(true) . "/administrator/components/com_djmediatools/views/images/images.js");
        
        JToolBarHelper::title(JText::_('COM_DJMEDIATOOLS').' ›› '.JText::_('COM_DJMEDIATOOLS_SUBMENU_IMAGES_CACHE'), 'slides');
        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration('.icon-48-slides { background-image: url(components/com_djmediatools/assets/icon-48-slides.png); }');
        
        JToolBarHelper::preferences('com_djmediatools', 550, 900);
		
        $this->images = JFolder::files(JPATH_ROOT.DS.'media'.DS.'djmediatools'.DS.'cache', '.', true, false, array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX'));
        
        $this->stylesheets = JFolder::files(JPATH_ROOT.DS.'media'.DS.'djmediatools'.DS.'css', '.', false, false, array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX'));
        
        $db = JFactory::getDbo();
        $db->setQuery("select count(*) from #__djmt_resmushit");
		$this->resmushed = $db->loadResult();
        
        $version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
		}
		parent::display($tpl);
	}
}

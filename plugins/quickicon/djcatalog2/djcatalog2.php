<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die;

class plgQuickiconDjcatalog2 extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function onGetIcons($context)
	{
		if ($context != $this->params->get('context', 'mod_quickicon')) {
			return;
		}
		
		$icons = array();
		
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$icons[] = array(
				'link' => 'index.php?option=com_djcatalog2',
				'image' => 'djcatalog2/quickicon/quickicon-djcatalog.png',
				'text' => JText::_('PLG_QUICKICON_DJCATALOG2'),
				'id' => 'plg_quickicon_djcatalog2'
			);
		} else {
			$icons[] = array(
				'link' => 'index.php?option=com_djcatalog2',
				'image' => 'database',
				'text' => JText::_('PLG_QUICKICON_DJCATALOG2'),
				'id' => 'plg_quickicon_djcatalog2'
			);
		}
		
		return $icons;

	}
}

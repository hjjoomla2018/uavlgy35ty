<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
defined('_JEXEC') or die('Restricted access');

define ('DJCATIMGFOLDER', JPATH_SITE.DS.'media'.DS.'djcatalog2'.DS.'images');
define ('DJCATIMGURLPATH', JURI::base().'media/djcatalog2/images');

define ('DJCATATTFOLDER', JPATH_SITE.DS.'media'.DS.'djcatalog2'.DS.'files');
define ('DJCATATTURLPATH', JURI::base().'media/djcatalog2/files');

define('DJCATFOOTER', '<div style="text-align: center; padding: 2px 0px;"><a style="font-size:10px;" target="_blank" href="http://dj-extensions.com">Joomla! extensions &amp; templates</a></div>');
define('DJCATCOMPONENTPATH',JPATH_BASE.DS.'components'.DS.'com_djcatalog2');

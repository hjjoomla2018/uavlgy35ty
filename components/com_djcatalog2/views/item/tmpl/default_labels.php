<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$layout = new JLayoutFile('com_djcatalog2.labels', null, array('component'=> 'com_djcatalog2'));
echo $layout->render(array('item' => $this->item));

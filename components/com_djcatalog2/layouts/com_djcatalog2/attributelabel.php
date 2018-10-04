<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access'); 

$attribute = $displayData['attribute'];

if ($attribute->imagelabel != '') {
	echo '<img class="djc_attribute-imglabel" alt="'.htmlspecialchars($attribute->name).'" src="'.JURI::base().$attribute->imagelabel.'" />';
} else {
	echo '<span class="djc_attribute-label">'.htmlspecialchars($attribute->name).'</span>';
}


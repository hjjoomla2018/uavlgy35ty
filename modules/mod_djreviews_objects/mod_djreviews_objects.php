<?php
/**
 * @package DJ-Reviews
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

$groups = $params->get('rating_group', array());
$all = false;

if (!is_array($groups) || empty($groups)) {
	$groups = array(0);
}

require_once(JPath::clean(dirname(__FILE__).'/helper.php'));
require_once(JPath::clean(JPATH_ROOT.'/components/com_djreviews/helpers/djreviews.php'));
DJReviewsHelper::setAssets(0);

$data = DJReviewsObjectsModuleHelper::getItems($groups, $params);


//echo '<pre>'.print_r($data,true).'</pre>';

require(JModuleHelper::getLayoutPath('mod_djreviews_objects'));
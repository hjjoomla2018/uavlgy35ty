<?php
/**
 * @version $Id: category.php 48 2015-01-29 16:33:53Z szymon $
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
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

// No direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djmediatools/models/category.php');
require_once JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djmediatools/models/items.php');

class DJMediatoolsModelCategoryform extends DJMediatoolsModelCategory
{
	
	public function validate($form, $data, $group = null) {
		
		if(!(isset($data['id']) && $data['id'] > 0)) {
			// set default values for front-end creation of new albums
			$data['id'] = 0;
			$data['published'] = 1;
			$data['source'] = 'component';
			$data['folder'] = '';
		}
		
		return parent::validate($form, $data, $group);
	}
	
}

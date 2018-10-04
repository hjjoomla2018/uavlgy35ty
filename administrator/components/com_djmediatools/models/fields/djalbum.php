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

defined('_JEXEC') or die();
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldDJAlbum extends JFormField {
	
	protected $type = 'DJAlbum';
	
	protected function getInput()
	{
		$app = JFactory::getApplication();
		
		$attr = '';

		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		
		$disable_default = ($this->element['disable_default'] == 'true') ? true : false;
		$disable_self = ($this->element['disable_self'] == 'true') ? true : false;
		$only_component = ($this->element['only_component'] == 'true') ? true : false;
		
		if($app->isAdmin()) {
			JModelLegacy::addIncludePath(JPATH_ROOT.'/administrator/components/com_djmediatools/models', 'DJMediaToolsModel');
		} else {
			JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_djmediatools/models', 'DJMediaToolsModel');
		}
		
		$model = JModelLegacy::getInstance('Categories', 'DJMediaToolsModel', array('ignore_request' => true));
		$options = $model->getSelectOptions($disable_default, $disable_self, 0, $only_component);
		
		$html = JHTML::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value);
		
		return ($html);
		
	}
}
?>
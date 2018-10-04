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
 
defined ('_JEXEC') or die; 

if(isset($this->slides)) {

	jimport( 'joomla.application.module.helper' );

	$mid = $this->category->id.'c';
	$slides = $this->slides;
	$navigation = $this->navigation;
	$params = $this->params;

	require JModuleHelper::getLayoutPath('mod_djmediatools', $params->get('layout', 'slideshow'));

} else if (!isset($this->subcategories) && !count($this->subcategories)) { ?>
	<p><?php echo JText::_('COM_DJMEDIATOOLS_EMPTY_CATEGORY'); ?></p>
<?php } ?>
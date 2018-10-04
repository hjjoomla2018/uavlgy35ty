<?php
/**
 * @version $Id: modulehelper.php 24 2013-12-10 11:56:48Z michal $
 * @package JMFramework
 * @copyright Copyright (C) 2012 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Michal Olczyk - michal.olczyk@design-joomla.eu
 *
 * JMFramework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JMFramework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JMFramework. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.cms.module.helper' );

class DJModuleHelper extends JModuleHelper {
    public static function renderModules($position, $chrome = 'none', $row_suffix = '', $grid_layout = 12) {
        if (!$position) return false;
        
        $renderer = JFactory::getDocument()->loadRenderer('module');

        $app = JFactory::getApplication();
        $frontediting = $app->getCfg('frontediting', 1);
        
        $version = new JVersion;
        
        $user = JFactory::getUser();
        $canEdit = $user->id && $frontediting && !($app->isAdmin() && $frontediting < 2) && $user->authorise('core.edit', 'com_modules');
        $correctVersion = (bool)version_compare($version->getShortVersion(), '3.2.0', '>=');
        $menusEditing = ($frontediting == 2) && $user->authorise('core.edit', 'com_menus');
        
        $bootstrap_row_counter = $grid_layout;
        
        $html = '';
        if ($modules = parent::getModules( $position )) {
            $attribs['style'] = $chrome;
            
            $html .= '<div class="'.$position.' count_'.count($modules).'">';
            
            $count = count($modules);
            
            for ($i = 0; $i < $count; $i++) {
                $module_params = new JRegistry;
                $module_params->loadString($modules[$i]->params);
                $bootstrap_size = (int)$module_params->get('bootstrap_size', 0);
                $span_size = ($bootstrap_size == 0) ? $grid_layout : $bootstrap_size;
                
                $className = $position.'-in';
                if ($bootstrap_size >=0 && $bootstrap_size <= $grid_layout) {
                    $className .= ' span'.$bootstrap_size;
                }
                
                $module_content = $renderer->render($modules[$i], $attribs, null);
                
                $module_html  = '<div class="'.$className.'">';
                $module_html .= '<div class="'.$position.'-bg'.'">';
                $module_html .= $module_content;
                $module_html .= '</div>';
                $module_html .= '</div>';
                
                if ($correctVersion && $app->isSite() && $canEdit && trim($module_content) != '' && $user->authorise('core.edit', 'com_modules.module.' . $modules[$id]->id))
                {
                    $displayData = array('moduleHtml' => &$module_html, 'module' => $modules[$i], 'position' => $position, 'menusediting' => $menusEditing);
                    JLayoutHelper::render('joomla.edit.frontediting_modules', $displayData);
                }
                
                if ($bootstrap_row_counter == $grid_layout) {
                    $html .= '<div class="row'.$row_suffix.'">';
                }
                
                $html .= $module_html;
                $bootstrap_row_counter -= $span_size;
                if ($i < $count-1 && $bootstrap_row_counter > 0) {
                    $next_module_params = new JRegistry;
                    $next_module_params->loadString($modules[$i+1]->params);
                    $next_bootstrap_size = (int)$next_module_params->get('bootstrap_size', '0');
                    $next_span_size = ($next_bootstrap_size == 0) ? $grid_layout : $next_bootstrap_size;
                    
                    if ((int)($bootstrap_row_counter - $next_span_size) < 0) {
                        $bootstrap_row_counter -= $next_span_size;
                        $html .= '</div>';
                    }
                } else {
                     $html .= '</div>';
                }
                $bootstrap_row_counter;
                if ($bootstrap_row_counter <= 0){
                    $bootstrap_row_counter = $grid_layout;
                }                
                
            }
            $html .= '</div>';
        }
        return $html;
    }
}
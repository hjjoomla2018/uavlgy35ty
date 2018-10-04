<?php
/**
* @version $Id: djcatalog2.php 492 2015-07-21 08:19:14Z michal $
* @package DJ-Catalog2
* @copyright Copyright (C) 2010 Blue Constant Media LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer $Author: michal $ Michal Olczyk - michal.olczyk@design-joomla.eu
*
*
*** the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ-Catalog2 is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ-Catalog2. If not, see <http://www.gnu.org/licenses/>.
*
*/


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
class plgContentDJCatalog2 extends JPlugin{
	
	protected static $init = false;
	protected static $objects = array('item' => array(), 'category' => array(), 'producer' => array());
	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
    
    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        // Don't run this plugin for backend
        $app = JFactory::getApplication();
        if($app->isAdmin()) return true;
        
        // Don't run this plugin when the content is being indexed
        if ($context == 'com_finder.indexer') {
            return true;
        }
        
        if (!self::$init) {
        	$this->import();
        	self::$init = true;
        }
        
        $validTypes = array('item', 'category', 'producer');
        $regexLink = '#{djc2link\s([^}]*?)}#iU';
        $matchesLink = array();
        
        preg_match_all($regexLink, $row->text, $matchesLink, PREG_SET_ORDER);
        
        if ($matchesLink) {
        	foreach ($matchesLink as $matchLink) {
        		$output = null;
        		$attrs = self::parseAttributes(trim($matchLink[1]));
        		
        		$label = (empty($attrs['label'])) ? false : $attrs['label'];
        		$type = (empty($attrs['type'])) ? false : $attrs['type'];
        		$id = (empty($attrs['id'])) ? false : $attrs['id'];
        		
        		if (!$type || !$id || !in_array($type, $validTypes)) {
        			$row->text = preg_replace("|$matchLink[0]|", addcslashes($output, '\\'), $row->text, 1);
        			continue;
        		}
        		
        		$output = self::getLink($type, $id, $label);
        		$row->text = preg_replace("|$matchLink[0]|", addcslashes($output, '\\'), $row->text, 1);
        	}
        }
    }
    
    protected static function getLink($type, $id, $label = null) {
    	$html = '';
    	
    	$db = JFactory::getDbo();
    	
    	if ($type == 'item') {
    		$object = null;
    		if (isset(self::$objects[$type][$id])) {
    			$object = self::$objects[$type][$id];
    		} else {
    			$db->setQuery('select i.id, i.name, i.alias, i.cat_id, c.alias as cat_alias from #__djc2_items as i left join #__djc2_categories as c on c.id = i.cat_id where i.id='.(int)$id);
    			$object = $db->loadObject();
    			self::$objects[$type][$id] = $object;
    		}
    		
    		if (!empty($object)) {
    			$label = $label ? $label : $object->name;
    			$html = '<a href="'.JRoute::_(DJCatalogHelperRoute::getItemRoute($object->id.':'.$object->alias, $object->cat_id.':'.$object->cat_alias)).'">'.$label.'</a>';
    		}	
    	} else if ($type == 'category') {
    		$object = null;
    		if (isset(self::$objects[$type][$id])) {
    			$object = self::$objects[$type][$id];
    		} else {
    			$db->setQuery('select i.id, i.name, i.alias from #__djc2_categories as i where i.id='.(int)$id);
    			$object = $db->loadObject();
    			self::$objects[$type][$id] = $object;
    		}
    		
    		
    		if (!empty($object)) {
    			$label = $label ? $label : $object->name;
    			$html = '<a href="'.JRoute::_(DJCatalogHelperRoute::getCategoryRoute($object->id.':'.$object->alias)).'">'.$label.'</a>';
    		}
    	} else if ($type == 'producer') {
    		$object = null;
    		if (isset(self::$objects[$type][$id])) {
    			$object = self::$objects[$type][$id];
    		} else {
    			$db->setQuery('select i.id, i.name, i.alias from #__djc2_producers as i where i.id='.(int)$id);
    			$object = $db->loadObject();
    			self::$objects[$type][$id] = $object;
    		}
    		
    		
    		if (!empty($object)) {
    			$label = $label ? $label : $object->name;
    			$html = '<a href="'.JRoute::_(DJCatalogHelperRoute::getProducerRoute($object->id.':'.$object->alias)).'">'.$label.'</a>';
    		}
    	}
    	
    	return $html;
    }
    
    public static function parseAttributes($string) {
    	$attr = array();
    	$retarray = array();
    
    	// Let's grab all the key/value pairs using a regular expression
    	preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);
    
    	if (is_array($attr))
    	{
    		$numPairs = count($attr[1]);
    
    		for ($i = 0; $i < $numPairs; $i++) {
    			$retarray[$attr[1][$i]] = $attr[2][$i];
    		}
    	}
    
    	return $retarray;
    }

    protected function import() {
        if(!defined('DS')) {
            define ('DS', DIRECTORY_SEPARATOR);
        }
        require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'defines.djcatalog2.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'route.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'html.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'theme.php');
        require_once(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_djcatalog2'.DS.'lib'.DS.'categories.php');
        require_once(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'image.php');
        
        DJCatalog2ThemeHelper::setThemeAssets();
        
        $lang = JFactory::getLanguage();
        $lang->load('com_djcatalog2', JPATH_ROOT, 'en-GB', false, false);
        $lang->load('com_djcatalog2', JPATH_ROOT.DS.'components'.DS.'com_djcatalog2', 'en-GB', false, false);
        $lang->load('com_djcatalog2', JPATH_ROOT, null, true, false);
        $lang->load('com_djcatalog2', JPATH_ROOT.DS.'components'.DS.'com_djcatalog2', null, true, false);
    } 
}



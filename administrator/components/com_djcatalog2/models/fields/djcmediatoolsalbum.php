<?php
/**
 * @package DJ-MediaTools
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die();
defined('JPATH_BASE') or die;
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldDJCMediaToolsAlbum extends JFormField {
	
	protected $type = 'DJCMediaToolsAlbum';
	
	protected function getInput()
	{
		$html = '';
		$modelPath = JPath::clean(JPATH_ADMINISTRATOR.'/components/com_djmediatools/models/categories.php');
		if (!JFile::exists($modelPath)) {
			$html = '<input type="text" class="readonly" readonly="readonly" value="'.JText::_('COM_DJCATALOG2_DJMT_NOT_INSTALLED').'" />';
			$html.= '<input type="hidden" name="'.$this->name.'" value="" />';
			$html.= ' <a class="btn button btn-success" target="_blank" href="https://www.dj-extensions.com/dj-mediatools/?ref=djc2config">Get DJ-MediaTools</a>';
		} else {
			require_once $modelPath;
			$html = $this->getAlbums();
			$html.= ' <a class="btn button btn-success" target="_blank" href="'.JRoute::_('index.php?option=com_djmediatools&view=categories').'">'.JText::_('COM_DJCATALOG2_DJMT_ALBUMS').'</a>';
		}
		return ($html);
	}
	
	protected function getAlbums() {
		$attr = '';
			
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		
		$categories = new DJMediatoolsModelCategories();
		
		$categories->setState('filter.search', '');
		$categories->setState('filter.published', '');
		$categories->setState('filter.category', '');
		$categories->setState('list.ordering','a.ordering');
		$categories->setState('list.direction','asc');
		$categories->setState('list.start', 0);
		$categories->setState('list.limit', 0);
		//$only_component = (JRequest::getVar('view')=='item' && JRequest::getVar('option')=='com_djmediatools' ? true : false);
		
		$options = array();
		$options[] = JHTML::_('select.option', '0', JText::_('JNONE'),'value','text');
		 
		$items = $categories->getItems();
		$level = 0;
		
		$disabled = false;
		foreach ($items as $item) {
			if ($item->source != 'djcatalog2gallery') 
			{
				continue;
			}
		
			$options[] = JHTML::_('select.option', $item->id, '['.$item->id.'] '.$item->title.' - ','value','text', $disabled);
		}
		
		if (count($options) == 1) {
			$options[0] = JHTML::_('select.option', '0', JText::_('COM_DJCATALOG2_DJMT_NO_SRC_ALBUMS'),'value','text');
		}
		
		$html = JHTML::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value);
		return $html;
	}
}
?>
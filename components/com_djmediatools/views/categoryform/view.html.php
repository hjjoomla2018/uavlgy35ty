<?php
/**
 * @version $Id: view.html.php 40 2014-09-08 14:28:34Z szymon $
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

defined ('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.application.component.model');

class DJMediatoolsViewCategoryForm extends JViewLegacy {
	
	protected $form;
	protected $item;
	protected $state;
	protected $params;
	
	function display($tpl = null) {
		
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		
		// Initialiase variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');
		$this->params = JComponentHelper::getParams( 'com_djmediatools' );
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$authorised = false;
		if (empty($this->item->id)) {
			$authorised = $user->authorise('core.create', 'com_djmediatools');
		}
		else {
			if ($user->authorise('core.edit', 'com_djmediatools')) {
				$authorised = true;
				if($this->item->source != 'component') { // only custom items source managing is supported on the front-end
					$app->redirect(JUri::root(true).'/administrator/index.php?option=com_djmediatools&view=category&layout=edit&id='.$this->item->id);
					return true;
				}
			} else {
				$ownerId	= (int) $this->item->created_by;
				if (!$user->guest && $ownerId == $user->id && $user->authorise('core.edit.own', 'com_djmediatools')) {
					$authorised = true;
				}
			}
		}
		
		if ($authorised !== true) {
			if ((bool)$user->guest && empty($this->item->id)) {
				$return_url = base64_encode(JURI::getInstance()->toString());
				$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$return_url, false), JText::_('COM_DJMEDIATOOLS_PLEASE_LOGIN'));
				return true;
			} else {
				$app->enqueueMessage(JText::_('COM_DJMEDIATOOLS_NO_PERMISSIONS'), 'error');
				$app->redirect(JRoute::_(DJMediatoolsHelperRoute::getCategoriesRoute(), false));
				return true;
			}
		}
		
		$lang = JFactory::getLanguage();
		if ($lang->get('lang') != 'en-GB') {
			$lang->load('com_djmediatools', JPATH_ADMINISTRATOR, 'en-GB', false, false);
			$lang->load('com_djmediatools', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', false, false);
			$lang->load('com_djmediatools', JPATH_ADMINISTRATOR, null, true, false);
			$lang->load('com_djmediatools', JPATH_COMPONENT_ADMINISTRATOR, null, true, false);
		}
		
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$tpl = 'legacy';
			$btnclass = 'button';
			$btn2class = 'button-link';
		} else {
			$btnclass = 'btn btn-primary btn-large';
			$btn2class = 'btn btn-link btn-large';
		}
		
		if($this->item->id && $this->item->source == 'component') {
				
			$this->items = $this->get('Items');
				
			foreach($this->items as $item) {
				if(!$item->thumb = DJImageResizer::createThumbnail($item->image, 'media/djmediatools/cache', 200, 150, 'toHeight', 80)) {
					$item->thumb = $item->image;
				}
				if(strcasecmp(substr($item->image, 0, 4), 'http') != 0 && !empty($item->image)) {
					$item->image = JURI::root(true).'/'.$item->image;
				}
				if(strcasecmp(substr($item->thumb, 0, 4), 'http') != 0 && !empty($item->thumb)) {
					$item->thumb = JURI::root(true).'/'.$item->thumb;
				}
			}
		}

		// include uploader events and simple managing of album items
		JHTML::_('behavior.framework');
		$this->document->addScript(JURI::root(true).'/administrator/components/com_djmediatools/assets/album.js');
		$this->document->addStyleSheet(JURI::root(true).'/administrator/components/com_djmediatools/assets/forms.css');
		
		$settings = array();
		$settings['max_file_size'] = $this->params->get('upload_max_size','10240').'kb';
		$settings['chunk_size'] = $this->params->get('upload_chunk_size','1024').'kb';
		$settings['resize'] = true;
		$settings['width'] = $this->params->get('upload_width','1600');
		$settings['height'] = $this->params->get('upload_height','1200');
		$settings['quality'] = $this->params->get('upload_quality','90');
		$settings['filter'] = 'jpg,png,gif';
		$settings['onUploadedEvent'] = 'injectUploaded';
		$settings['onAddedEvent'] = 'startUpload';
		//$settings['debug'] = true;
		$this->uploader = DJUploadHelper::getUploader('uploader', $settings);
		
		if(JRequest::getVar('tmpl')=='component') {
		
			$function = JRequest::getVar('f_name');
			
			$this->button = "
			
					<script type='text/javascript'>
						function save2insert(cover) {
			
							if (document.formvalidator.isValid(document.id('item-form'))) {
								
								// update field with editor content 
								document.id('jform_description').set('value', Joomla.editors.instances['jform_description'].getValue());
								".$this->form->getField('description')->save()."
								
								document.getElementById('item-form').task.value='categoryform.save';
					
								var loader = new Element('div', {
									styles: {
										background: '#fff url(components/com_djmediatools/assets/bigloader.gif) center center no-repeat',
										position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', 'z-index': 9999
									}
								});
								loader.fade('hide');
					
								document.id('item-form').set('send',{
									onRequest: function(){
										loader.inject(document.id(document.body));
										loader.fade(0.8);
									},
									onSuccess: function(responseText){
						
										var rsp = responseText.trim();
										if(rsp){
											var json = rsp;
											if(rsp.charAt(0) != '[' && rsp.charAt(0) != '{'){
												json = rsp.match(/{.*?}/);
												if(json && json[0]){
													json = json[0];
												}
											}
			
											if(json && typeof json == 'string'){
												json = JSON.decode(json);
											}
											
											if (json && window.parent) window.parent.".$function."(json.id,json.image,json.title, cover);
											else alert(json);
											
											loader.destroy();
										}
						
						
									},
									onFailure: function(){
										loader.destroy();
									}
								});
						
								document.id('item-form').send();
							}
							else {
								alert('".$this->escape(JText::_('COM_DJMEDIATOOLS_VALIDATION_FORM_FAILED'))."');
							}
						}
			
				
					</script>
			
			";
			//if (window.parent) window.parent.".$function."('".$item->id."','".$item->image ? $item->image : $item->thumb."','".$this->escape($item->title)."');
			//die('dupa po');
				
			//die($this->button);
			$this->button .= '
					<div class="modalAlbum">
						<input type="hidden" name="tmpl" value="component" />
						<input type="hidden" name="f_name" value="'.$function.'" />
						<input type="button" class="'.$btnclass.'" value="'.JText::_('COM_DJMEDIATOOLS_MODAL_SAVE_INSERT').'" onclick="return save2insert();" />
						<input type="button" class="'.$btnclass.' hasTip" title="::'.JText::_('COM_DJMEDIATOOLS_INSERT_LINKED_COVER_DESC').'" value="'.JText::_('COM_DJMEDIATOOLS_MODAL_SAVE_INSERT_COVER').'" onclick="return save2insert(true);" />
						<a class="'.$btn2class.'" href="index.php?option=com_djmediatools&amp;view=categories&amp;layout=modal&amp;tmpl=component&amp;f_name='.$function.'">'.JText::_('JCANCEL').'</a>
					</div>';
		} else {
		
			$this->_prepareDocument();
		}
		
        parent::display($tpl);
	}
	
	protected function _prepareDocument() {
		
		$app	= JFactory::getApplication();
		
		//$title = ($this->item->id > 0) ? JText::sprintf('COM_DJCATALOG2_ITEM_EDIT_HEADING', $this->item->name) : JText::_('COM_DJCATALOG2_ITEM_SUBMISSION_HEADING');
		
		JHTML::_('behavior.framework');
		
		$title = $this->params->get('page_title', '');
		
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

	}

}





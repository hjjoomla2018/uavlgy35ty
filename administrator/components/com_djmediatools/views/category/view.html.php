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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');
class DJMediatoolsViewCategory extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;
	protected $plgParams;

	public function display($tpl = null)
	{
		// Initialiase variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');
		
		$this->plgParams = $this->get('plgParams');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
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
					if(!$item->thumb = DJImageResizer::createThumbnail($item->image, 'media/djmediatools/cache', 200, 150, 'crop', 70)) {
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
		JHtml::_('behavior.framework');
		$this->document->addScript(JURI::base(true).'/components/com_djmediatools/assets/album.js');
		
		$params = JComponentHelper::getParams( 'com_djmediatools' );
		
		$settings = array();
		$settings['max_file_size'] = $params->get('upload_max_size','10240').'kb';
		$settings['chunk_size'] = $params->get('upload_chunk_size','1024').'kb';
		$settings['resize'] = true;
		$settings['width'] = $params->get('upload_width','1600');
		$settings['height'] = $params->get('upload_height','1200');
		$settings['quality'] = $params->get('upload_quality','90');
		$settings['filter'] = 'jpg,png,gif';
		$settings['onUploadedEvent'] = 'injectUploaded';
		$settings['onAddedEvent'] = 'startUpload';
		//$settings['debug'] = true;
		$this->uploader = DJUploadHelper::getUploader('uploader', $settings);
		
		if(JRequest::getVar('tmpl')!='component') {
			$this->addToolbar();
		} else {
			$function = JRequest::getVar('f_name');
			
			//die('dupa przed');
			
			$this->button = "
				
				<script type='text/javascript'>
					function save2insert(cover) {
						
						if (document.formvalidator.isValid(document.id('item-form'))) {
							
							// update field with editor content 
							document.id('jform_description').set('value', Joomla.editors.instances['jform_description'].getValue());
							".$this->form->getField('description')->save()."
								
							document.getElementById('item-form').task.value='category.save';
							
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
									
										if (window.parent) window.parent.".$function."(json.id,json.image,json.title, cover);
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
		}
		
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$canDo		= true; //ContactHelper::getActions($this->state->get('filter.category'));

		$text = $isNew ? JText::_( 'COM_DJMEDIATOOLS_NEW' ) : JText::_( 'COM_DJMEDIATOOLS_EDIT' );
		JToolBarHelper::title(   JText::_( 'COM_DJMEDIATOOLS_CATEGORY' ).': <small><small>[ ' . $text.' ]</small></small>', 'category-add' );
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-category-add { background-image: url(components/com_djmediatools/assets/icon-48-category-add.png); }');
		
		// Built the actions for new and existing records.
		if ($isNew)  {
			
			
			// For new records, check the create permission.
			//if ($canDo->get('core.create')) {
				JToolBarHelper::apply('category.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('category.save', 'JTOOLBAR_SAVE');
				//JToolBarHelper::custom('category.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			//}

			JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				//if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId)) {
					JToolBarHelper::apply('category.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('category.save', 'JTOOLBAR_SAVE');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					//if ($canDo->get('core.create')) {
						JToolBarHelper::custom('category.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					//}
				//}
			}

			// If checked out, we can still save
			//if ($canDo->get('core.create')) {
				//JToolBarHelper::custom('category.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			//}

			JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CLOSE');
		}

	}
}

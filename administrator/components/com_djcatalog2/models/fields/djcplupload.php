<?php

defined('JPATH_PLATFORM') or die;
/**
 * Form field that renders PLUploader for uploading files in the back-end
 *
 * example usage:
 * <field name="file" type="djcplupload" label="file" multiple_files="false" limit="1" preview="true" caption="false" download="false" extensions="jpg,png" />
 */
class JFormFieldDjcplupload extends JFormField
{
	protected $type = 'Djcplupload';
	
	protected function getInput()
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		
		JHtml::_('bootstrap.framework');
		JHtml::_('jquery.ui', array('core', 'sortable'));
		
		$document->addScript(JURI::root(true).'/media/djextensions/jquery.ui/minified/jquery.ui.widget.min.js');
		$document->addScript(JURI::root(true).'/media/djextensions/jquery.ui/minified/jquery.ui.button.min.js');
		$document->addScript(JURI::root(true).'/media/djextensions/jquery.ui/minified/jquery.ui.progressbar.min.js');
		$document->addScript(JURI::root(true).'/components/com_djcatalog2/assets/upload/plupload.full.js');
		$document->addScript(JURI::root(true).'/components/com_djcatalog2/assets/upload/djcplupload/script.js');
		$document->addStyleSheet(JUri::root(true).'/components/com_djcatalog2/assets/upload/djcplupload/style.css');
		
		// TODO
		//$document->addScript(JURI::root(true).'/components/com_djcatalog2/assets/upload/jquery.ui.plupload/jquery.ui.plupload.js');
		
		$browse_button 	= (!empty($this->element['browse_button'])) ? JText::_($this->element['browse_button']) : JText::_('COM_DJCATALOG2_PLUPLOAD_BROWSE');
		$upload_button 	= (!empty($this->element['upload_button'])) ? JText::_($this->element['upload_button']) : JText::_('COM_DJCATALOG2_PLUPLOAD_UPLOAD');
		
		$multiple 		= (bool)(!empty($this->element['multiple_files']) && $this->element['multiple_files'] == 'true');
		$limit 			= (isset($this->element['limit']) ? (int)$this->element['limit'] : 1);
		$extensions 	= (!empty($this->element['extensions'])) ? (string)$this->element['extensions'] : 'jpg,png,bmp,gif,pdf,tif,tiff,txt,csv,doc,docx,xls,xlsx,xlt,pps,ppt,pptx,ods,odp,odt,rar,zip,tar,bz2,gz2,7z';
		$max_size	 	= (!empty($this->element['max_size'])) ? ((int)($this->element['max_size'])).'kb' : '10mb';
		$required 		= (bool)$this->required;
		$readonly 		= (bool)$this->readonly;
		$class 			= (!empty($this->element['class'])) ? $this->element['class'] : 'input';
		if ($required) {
			$class .= ' required';
		}
		$preview 		= (bool)(!empty($this->element['preview']) && $this->element['preview'] == 'true');
		$download 		= (bool)( (!empty($this->element['download']) && $this->element['download'] == 'true') || empty($this->element['download']) );
		$sortable 		= (bool)( (!empty($this->element['sortable']) && $this->element['sortable'] == 'true') || empty($this->element['sortable']) );
		$caption 		= (bool)( (!empty($this->element['caption']) && $this->element['caption'] == 'true' || empty($this->element['caption'])));
		
		$browse_button_id 	= $this->id.'_browse';
		$upload_button_id 	= $this->id.'_upload';
		$container_id 		= $this->id.'_container';
		$filelist_id 		= $this->id.'_files';
		$console_id 		= $this->id.'_console';
		$url 				= JUri::base(true).'/index.php?option=com_djcatalog2&task=multiupload&tmpl=component&upload_id='.$this->id;
		$download_url		= JUri::base(true).'/index.php?option=com_djcatalog2&task=' . ($app->isClient('site') ? 'download' : 'item.download') . '&format=raw&fid=';
		$preview_url		= JUri::root(true) . '/media/djcatalog2/tmp/';
		$moxie_swf			= JUri::root(true).'/components/com_djcatalog2/assets/upload/Moxie.swf';
		$moxie_xap			= JUri::root(true).'/components/com_djcatalog2/assets/upload/Moxie.xap';
		
		$existing_files = array();
		
		$settings = array(
			'id'			=> $this->id,
			'root_path'		=> JUri::root(true),
			'browse_button' => $browse_button_id,
			'upload_button' => $upload_button_id,
			'container' 	=> $container_id,
			'file_list'		=> $filelist_id,
			'console'		=> $console_id,
			'url' 			=> $url,
			'download_url' 	=> $download_url,
			'preview_url' 	=> $preview_url,
			'moxie_swf'		=> $moxie_swf,
			'moxie_xap'		=> $moxie_xap,
			'extensions'	=> $extensions,
			'multiple'		=> $multiple,
			'required'		=> $required,
			'preview'		=> $preview,
			'caption'		=> $caption,
			'sortable'		=> $sortable,
			'download'		=> $download,
			'chunk_size'	=> '1024kb',
			'limit'			=> $multiple ? $limit : 1,
			'max_size'		 => $max_size,
			//'total'			=> count($existing_files)
		);
		
		//$this->value = '[{"id":1,"fullname":"o_1bnsk4gbl7en1uh71h2kn92jntj.jpg","caption":"img3.jpg","url":"/balex/media/djcatalog2/tmp/o_1bnsk4gbl7en1uh71h2kn92jntj.jpg","size":1194532},{"id":2,"fullname":"o_1bnsk4gblerq1mgeq83adgc7bk.jpg","caption":"img7.jpg","url":"/balex/media/djcatalog2/tmp/o_1bnsk4gblerq1mgeq83adgc7bk.jpg","size":158112},{"id":3,"fullname":"o_1bnsk4gbl104c1ib8aqr1555bqtl.jpg","caption":"img8.jpg","url":"/balex/media/djcatalog2/tmp/o_1bnsk4gbl104c1ib8aqr1555bqtl.jpg","size":88189}]';
		if (is_array($this->value) && count($this->value)) {
			$this->value = json_encode($this->value);
		}
		
		$jslang = "
			jQuery(function(){
			
				plupload.addI18n({
					'Select files' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_HEADER'))."',
					'Add files to the upload queue and click the start button.' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_DESC'))."',
					'Filename' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_FILENAME'))."',
					'Status' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_STATUS'))."',
					'Size' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_SIZE'))."',
					'Add Files' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_ADD_FILES'))."',
					'Stop current upload' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_STOP_CURRENT_UPLOAD'))."',
					'Start uploading queue' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_START_UPLOADING_QUEUE'))."',
					'Uploaded %d/%d files': '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_UPLOADED_N_FILES'))."',
					'N/A' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_NA'))."',
					'Drag files here.' : '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_DRAG_AND_DROP_TEXT'))."',
					'Stop Upload': '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_STOP_UPLOAD'))."',
					'Start Upload': '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_START_UPLOAD'))."',
					'%d files queued': '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_N_FILES_QUEUED'))."',
					'File extension error.': '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_EXT_ERROR'))."',
					'File size error.': '".addslashes(JText::_('COM_DJCATALOG2_UPLOADER_SIZE_ERROR'))."'
				});
			});
		";
		
		$document->addScriptDeclaration($jslang);
		
		$js ='
    			jQuery(document).ready(function(){
					DJCPLUpload.prototype.lang = {
						DOWNLOAD_BTN: "'.JText::_('COM_DJCATALOG2_DOWNLOAD').'",
						LIMIT_REACHED: "'.JText::_('COM_DJCATALOG2_PLUP_LIMIT_REACHED').'"
					};
    				var DJCPLUpload_'.$this->id.' = new DJCPLUpload('.json_encode($settings).');
    			});
    			';
		
		$document->addScriptDeclaration($js);
		
		$html = '
    			<div id="'.$container_id.'">
	    			<span id="'.$browse_button_id.'" class="button btn">'.$browse_button.'</span>
					<!--<span id="'.$upload_button_id.'" class="button btn">'.$upload_button.'</span>-->
				</div>
				<div>
					<p id="'.$console_id.'"></p>
					<div id="'.$filelist_id.'" class="djcupload_file_list">Your browser doesn\'t have Flash, Silverlight or HTML5 support.</div>
					<textarea name="'.$this->name.'" id="'.$this->id.'" style="display: none;" class="'.$class.'" '
						.( $required ? 'required="required"' : '' )
						.( $readonly ? 'readonly="readonly"' : '' ).'>'.trim($this->value).'</textarea>
				</div>
    	';
						
						return $html;
	}
}

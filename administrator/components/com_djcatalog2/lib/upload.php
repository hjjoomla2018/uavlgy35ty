<?php
/**
 * @package DJ-MediaTools
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class DJCatalog2UploadHelper {
	
	static $scripts_included = false;
	
	static $blacklist = array('php','php3','php4', 'php5', 'php6', 'phtml', 'pht', 'shtml','asa','cer','asax','swf','xap','sh','js');
	
	// default uploader settings
	private static $settings = array(
		'max_file_size' => '10mb',
		'chunk_size' => '1mb',
		'resize' => true, // resize image before upload - for html5 runtime image resizing is only possible on Firefox 3.5+ (with fixed quality) and Chrome
		'width' => 1920, // max resize image width
		'height' => 1920, //max resize image height
		'quality' => 100, // resize quality
		'filter' => 'jpg,gif,png', // Filter to apply when the user selects files. This is currently file extension filter
		'debug' => false,
		'url' => null,
		// events	
		'onUploadedEvent' => null,
		'onAddedEvent' => null
	);
	
	public static function getUploader($id = 'uploader', $settings = array()) {
		
		$jrequest = JFactory::getApplication()->input;
		$params = JComponentHelper::getParams('com_djcatalog2');
		
		$settings = array_merge(self::$settings, $settings);
		$debug = $settings['debug'];
		$config = JFactory::getConfig();
		
		$doc = JFactory::getDocument();
		//$doc->addStyleSheet('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/smoothness/jquery-ui.min.css');
		//$doc->addStyleSheet('//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css');
		if ($params->get('jquery_ui', true)) {
			$doc->addStyleSheet(JURI::root(true).'/media/djextensions/jquery.ui/themes/base/minified/jquery-ui.min.css');
		}
		$doc->addStyleSheet(JURI::root(true).'/components/com_djcatalog2/assets/upload/jquery.ui.plupload/css/jquery.ui.plupload.css');
		//JHTML::_('behavior.framework', true);
		$version = new JVersion;
		if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
			$doc->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js');
			$doc->addScript(JURI::root(true).'/components/com_djcatalog2/assets/upload/jquery.noconflict.js');
		} else {
			JHtml::_('bootstrap.framework');
			JHtml::_('jquery.ui', array('core', 'sortable'));
		}
		// 1.10.2 is dedicated for current PLUpload version
		// 1.9.2 is Joomla's core jQuery 
		//$doc->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js');
		//$doc->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js');
		//$doc->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');
		
		if ($params->get('jquery_ui', true)) {
			$doc->addScript(JURI::root(true).'/media/djextensions/jquery.ui/minified/jquery.ui.widget.min.js');
		}
		if ($params->get('jquery_ui', true)) {
			$doc->addScript(JURI::root(true).'/media/djextensions/jquery.ui/minified/jquery.ui.button.min.js');
		}
		if ($params->get('jquery_ui', true)) {
			$doc->addScript(JURI::root(true).'/media/djextensions/jquery.ui/minified/jquery.ui.progressbar.min.js');
		}
		
		//$doc->addScript('//bp.yahooapis.com/2.4.21/browserplus-min.js');
		$doc->addScript(JURI::root(true).'/components/com_djcatalog2/assets/upload/plupload.full.js');
		$doc->addScript(JURI::root(true).'/components/com_djcatalog2/assets/upload/jquery.ui.plupload/jquery.ui.plupload.js');
		
		$component = $jrequest->get('option');
		$url = $settings['url'];
		if(!$url) $url = JURI::base(true).'/index.php?option='.$component.'&task=multiupload&tmpl=component';

		$js = "			
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
		
		if (self::$scripts_included == false) {
			$doc->addScriptDeclaration($js);
			self::$scripts_included = true;
		}
		
		$js = 'jQuery(function(){ ';
			if($debug) $js .= "
				function eventlog() {
					var str = '';			
					plupload.each(arguments, function(arg) {
						var row = '';			
						if (typeof(arg) != 'string') {
							plupload.each(arg, function(value, key) {
								// Convert items in File objects to human readable form
								if (arg instanceof plupload.File) {
									// Convert status to human readable
									switch (value) {
										case plupload.QUEUED:
											value = 'QUEUED';
											break;			
										case plupload.UPLOADING:
											value = 'UPLOADING';
											break;			
										case plupload.FAILED:
											value = 'FAILED';
											break;			
										case plupload.DONE:
											value = 'DONE';
											break;
									}
								}			
								if (typeof(value) != 'function') {
									row += (row ? ', ': '') + key + '=' + value;
								}
							});			
							str += row + ' ';
						} else { 
							str += arg + ' ';
						}
					});			
					console.log(str);
				}
				
			";
			$js .= "
				var uploader$id = jQuery('#$id').plupload({
					// General settings
					//runtimes : 'gears,browserplus,html5,silverlight,flash,html4',
					runtimes: 'html5,flash,silverlight,html4',
					url : '$url',
					max_file_size : '".$settings['max_file_size']."',
					chunk_size : '".$settings['chunk_size']."',
					unique_names : true,
					dragdrop: true,
					rename: false,
					sortable: false,
					";
			if($settings['resize']) $js .= "
					// Resize images on clientside if we can
					resize : {width : ".$settings['width'].", height : ".$settings['height'].", quality : ".$settings['quality']."},";
			$js .= "
					// Specify what files to browse for
					filters : [
						{title : 'Allowed files', extensions : '".$settings['filter']."'}
					],

					// Flash settings
					//flash_swf_url : '".JURI::root(true)."/components/$component/assets/upload/plupload.flash.swf',
					flash_swf_url : '".JURI::root(true)."/components/$component/assets/upload/Moxie.swf',

					// Silverlight settings
					//silverlight_xap_url : '".JURI::root(true)."/components/$component/assets/upload/plupload.silverlight.xap',
					silverlight_xap_url : '".JURI::root(true)."/components/$component/assets/upload/Moxie.xap',";
			if($debug) $js .= "
					// PreInit events, bound before any internal events
					preinit: {
						Init: function(up, info) {
							".($debug ? "eventlog('[Init]', 'Info:', info, 'Features:', up.features);":"")."
						},
						UploadFile: function(up, file) {
							".($debug ? "eventlog('[UploadFile]', file);":"")."
			
							// You can override settings before the file is uploaded
							// up.settings.url = 'upload.php?id=' + file.id;
							// up.settings.multipart_params = {param1: 'value1', param2: 'value2'};
						}
					},";
			$js .= "
					// Post init events, bound after the internal events
					init: {
						FilesAdded: function(up, files) {
							// Called when files are added to queue
							".($debug ? "eventlog('[FilesAdded]');":"")."
							".($settings['onAddedEvent'] ? $settings['onAddedEvent'].'(up,files);':'');
			if($debug) $js .= "
							plupload.each(files, function(file) {
								eventlog('  File:', file);
							});";
			$js .= "
						},
						FileUploaded: function(up, file, info) {
							// Called when a file has finished uploading
							".($debug ? "eventlog('[FileUploaded] File:', file, 'Info:', info);":"")."
							".($settings['onUploadedEvent'] ? 'return '.$settings['onUploadedEvent'].'(up,file,info);':'')."
						}";
			if($debug) $js .= ",				
						Refresh: function(up) {
							// Called when upload shim is moved
							".($debug ? "eventlog('[Refresh]');":"")."
						},
						StateChanged: function(up) {
							// Called when the state of the queue is changed
							".($debug ? "eventlog('[StateChanged]', up.state == plupload.STARTED ? 'STARTED': 'STOPPED');":"")."
						},
						QueueChanged: function(up) {
							// Called when the files in queue are changed by adding/removing files
							".($debug ? "eventlog('[QueueChanged]');":"")."
						},
						UploadProgress: function(up, file) {
							// Called while a file is being uploaded
							".($debug ? "eventlog('[UploadProgress]', 'File:', file, 'Total:', up.total);":"")."
						},
						FilesRemoved: function(up, files) {
							// Called when files where removed from queue
							".($debug ? "eventlog('[FilesRemoved]');":"")."
			
							plupload.each(files, function(file) {
								".($debug ? "eventlog('  File:', file);":"")."
							});
						},						
						ChunkUploaded: function(up, file, info) {
							// Called when a file chunk has finished uploading
							".($debug ? "eventlog('[ChunkUploaded] File:', file, 'Info:', info);":"")."
						},
						Error: function(up, args) {
							// Called when a error has occured
			
							// Handle file specific error and general error
							if (args.file) {
								".($debug ? "eventlog('[error]', args, 'File:', args.file);":"")."
							} else {
								".($debug ? "eventlog('[error]', args);":"")."
							}
						}";
			$js .= "
					}
				});
			});
		";
		
		$doc->addScriptDeclaration($js);
		
		$html = '<div id="'.$id.'"><p>Your browser doesn\'t support HTML5, Flash, Silverlight and even HTML4! Please upgrade your browser!</p></div>';
		
		return $html;
	}
	
	public static function upload() {
		
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();
		
		$params = JComponentHelper::getParams( 'com_djcatalog2' );
		
		$whitelist_img = explode(',', $params->get('allowed_image_types', 'jpg,png,bmp,gif'));
		$whitelist_att = explode(',', $params->get('allowed_attachment_types', 'jpg,png,bmp,gif,pdf,tif,tiff,txt,csv,doc,docx,xls,xlsx,xlt,pps,ppt,pptx,ods,odp,odt,rar,zip,tar,bz2,gz2,7z'));
		
		$whitelist = array_merge($whitelist_img, $whitelist_att);
		foreach($whitelist as $key => $extension) {
			if (!in_array(trim($extension), DJCatalog2FileHelper::$blacklist)) {
				$whitelist[$key] = strtolower(trim($extension));
			}
		}
		
		// HTTP headers for no cache etc
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		
		// Settings
		//$targetDir = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'djc2upload';
		//$targetDir = JFactory::getConfig()->get('tmp_path') . DIRECTORY_SEPARATOR . 'djc2upload';
		$targetDir = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'djcatalog2' . DIRECTORY_SEPARATOR . 'tmp';
		//$targetDir = 'uploads';
		
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 12 * 3600; // Temp file age in seconds
		
		// 5 minutes execution time
		@set_time_limit(5 * 60);
		
		// Uncomment this one to fake upload time
		// usleep(5000);
		
		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		
		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
		
		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);
		
			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;
		
			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}
		
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
		
		if (JFolder::exists($targetDir)) {
			JFolder::create($targetDir);
		}
		
		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);
		
		// Remove old temp files
		if ($cleanupTargetDir) {
			if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
				while (($file = readdir($dir)) !== false) {
					$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
		
					// Remove temp file if it is older than the max age and is not the current file
					if (filemtime($tmpfilePath) < time() - $maxFileAge && $tmpfilePath != "{$filePath}.part") {
						@unlink($tmpfilePath);
					}
				}
				closedir($dir);
			} else {
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}
		}
		
		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		
		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];
		
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = @fopen($_FILES['file']['tmp_name'], "rb");
		
					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						jexit('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					@fclose($in);
					@fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else
					jexit('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = @fopen("php://input", "rb");
		
				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					jexit('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
		
				@fclose($in);
				@fclose($out);
			} else
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		
		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off
			rename("{$filePath}.part", $filePath);
			
			$arr = explode(".", $filePath);
			$ext = strtolower(end($arr));
			
			if (!in_array($ext, $whitelist) || preg_match('/\.(php|shtml|pht|asp)/i', $filePath)) {
				@unlink($filePath);
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Wrong file format."}, "id" : "id"}');
			}
			
			if(stristr($filePath, '.jpg') || stristr($filePath, '.png') || stristr($filePath, '.gif') || stristr($filePath, '.jpeg')){
				$imgInfo = getimagesize($filePath);
				if(!isset($imgInfo[2])) { // not an image
					@unlink($filePath);
					jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "File is not an image."}, "id" : "id"}');
				}
			}
			
			$infected = 0;
			$fhandler = fopen($filePath, "r");
			while (!feof($fhandler))
			{
				// Get the current line that the file is reading
				$fline = fgets($fhandler) ;
				if(preg_match('/eval(\s)*\(/i', $fline)) {
					$infected++;
					break;
				} else if(stristr($fline, "base64")) {
					$infected++;
					break;
				} else if(stristr($fline, "<?php")) {
					$infected++;
					break;
				} /*else if(stristr($fline, "<?")) {
					$infected++;
					break;
				}*/
			}
			fclose($fhandler);
				
			if($infected){
				@unlink($filePath);
				jexit('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "File is not accepted."}, "id" : "id"}');
			}
		}
		
		jexit('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
		
	}
		
}
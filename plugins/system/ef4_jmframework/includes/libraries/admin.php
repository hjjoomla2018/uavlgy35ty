<?php
/**
 * @version $Id: admin.php 163 2017-10-17 12:48:27Z szymon $
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

/**
 * Utility class for rednering template's back-end form.
 */

class JMFAdminTemplate extends JMFTemplate {

	protected function setup() {

		$app = JFactory::getApplication();
		$styleid = $app->input->get('id', null, 'int');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('params');
		$query->from('#__template_styles');
		$query->where('id='.$styleid);
		$db->setQuery($query);
		$params = $db->loadResult();
		$this->params = new JRegistry($params);
		$tplarray = $this->params->toArray();

		// determine the direction
		$this->direction = $this->params->set('direction', $this->document->direction);

		// handle JM Option Groups
		foreach ($tplarray as $param => $value) {
			if (is_string($value) && strstr($value,';')) {
				$parts = explode(';', $value);
				$this->params->set($param, $parts[0]);
			}
		}

		$this->defaults = new JRegistry();
		$default_settings_file = JPath::clean(JPATH_ROOT . '/templates/' . JMF_TPL . '/templateDefaults.json');
		if (JFile::exists($default_settings_file)) {
			$this->defaults->loadFile($default_settings_file, 'JSON');
		}
	}

	/**
	 * Setting the params for Layout Builder
	 */
	public function postSetUp() {

		$this->params->set('logo','');
		$this->params->set('logoText', JText::_('PLG_SYSTEM_JMFRAMEWORK_LAYOUTBUILDER_LOGO'));
		$this->params->set('templateWidthType','1');

		// get columns classes
		$s = $this->getLayoutConfig('#scheme','lcr');
		$l = $this->params->get('columnLeftWidth', '3');
		$r = $this->params->get('columnRightWidth', '3');
		$c = 12 - $l - $r;

		$class = array();
		$class['content'] = "span$c\" data-column=\"c\" data-scheme=\"".$this->getLayoutConfig('#scheme','lcr');
		$class['left'] = "span$l\" data-column=\"l";
		$class['right'] = "span$r\" data-column=\"r";

		// tablet and mobile screens for layout builder
		foreach(array('xtablet','tablet','mobile') as $screen) {
			$sclass = $this->getColumnClasses($screen, 12, ($screen == 'mobile' ? 12 : 6), ($screen == 'mobile' ? 12 : 6), $screen);
			foreach($class as $col => $cls) {
				$class[$col] .= '" data-'.$screen.'="'.$sclass[$col];
			}
		}

		$this->params->set('class', $class);

	}

	/**
	 * Utility method for internal framework's AJAX calls. Not for template developers
	 */
	public function ajax(){

		$app = JFactory::getApplication();

		$jmajax = $app->input->getCmd('jmajax');
		$task = $app->input->getCmd('jmtask');

		if($jmajax == 'layout') { // Layout builder tasks

			$layout = $app->input->getCmd('jmlayout');

			switch($task) {
				case 'display':
					echo $this->renderScheme($layout);
					break;
				case 'save':
					echo self::preSave();
					echo self::saveLayout($layout);
					break;
				case 'copy':
					echo self::copyLayout($layout, $app->input->getCmd('jmcname'));
					break;
				case 'remove':
					echo self::removeLayout($layout);
					break;
				case 'setdefault':
					echo self::setDefault($layout);
					break;
				case 'getdefault':
					echo self::getDefault($layout);
					break;
				case 'load_assigns':
					echo self::displayAssigns($layout);
					break;
				case 'save_assigns':
					echo self::saveAssigns($layout);
					break;
				case 'load_params':
					echo self::loadParams($layout);
					break;
				default: echo self::renderAlert(JText::_('PLG_SYSTEM_JMFRAMEWORK_UNKNOWN_TASK'), 'error', 'json');
			}

			// close application
			$app->close();

		} else if ($jmajax == 'config') {

			switch($task) {
				case 'upload' :
					echo self::uploadConfig();
					break;
				case 'purge_cache' :
					echo self::purgeCache();
					break;
				default: echo self::renderAlert(JText::_('PLG_SYSTEM_JMFRAMEWORK_UNKNOWN_TASK')); break;
			}

			$app->close();

		} else if ($jmajax == 'plupload') {

			switch ($task) {
				case 'upload_font' :
					if (!defined('JMF_TPL')) {
						header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
						header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
						header("Cache-Control: no-store, no-cache, must-revalidate");
						header("Cache-Control: post-check=0, pre-check=0", false);
						header("Pragma: no-cache");
						echo '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "'.JText::_('PLG_SYSTEM_JMFRAMEWORK_UNKNOWN_TEMPLATE_ERROR').'"}, "id" : "id"}';
						$app->close();
					}
					$target = 'templates' . DIRECTORY_SEPARATOR . JMF_TPL . DIRECTORY_SEPARATOR.'fonts';
					return self::handlePlUpload($target);
					break;
				default: echo self::renderAlert(JText::_('PLG_SYSTEM_JMFRAMEWORK_UNKNOWN_TASK')); break;
			}

			$app->close();
		}

	}

	/**
	 * Utility method for uploading files, e.g. custom fonts. Not for template developers
	 */
	public static function handlePlUpload($targetDir, $cleanupTarget = false) {
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();

		// HTTP headers for no cache etc
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");


		$cleanupTargetDir = $cleanupTarget; // Remove old files
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

		$targetDir .= DIRECTORY_SEPARATOR.JFile::stripExt($fileName);

		$relativeDir = $targetDir;

		$targetDir = JPATH_ROOT . DIRECTORY_SEPARATOR . $targetDir;
		if (JFolder::exists($targetDir) == false ) {
			JFolder::create($targetDir);
		}

		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			/*$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;

			$fileName = $fileName_a . '_' . $count . $fileName_b;*/

			JFile::delete($targetDir . DIRECTORY_SEPARATOR . $fileName);
		}

		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		/*
		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);
		 */

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
			rename("{$filePath}.part", $filePath);
		}

		jexit('{"jsonrpc" : "2.0", "result" : null, "id" : "id", "file": "'.$fileName.'", "filename": "'.JFile::stripExt($fileName).'", "ext": "'.JFile::getExt($fileName).'", "dir": "'.$relativeDir.'"}');
	}

	/**
	 * Utility method for uploading template configuration file. Not for template developers
	 */
	public static function uploadConfig() {
		$user   = JFactory::getUser();
		$result = new JObject;
		$actions = JAccess::getActionsFromFile(JPATH_ADMINISTRATOR . '/components/com_templates/access.xml', "/access/section[@name='component']/");
		$app = JFactory::getApplication();

		foreach ($actions as $action) {
			$result->set($action->name, $user->authorise($action->name, 'com_templates'));
		}


		if ($result->get('core.edit')) {
			jimport('joomla.filesystem.file');
			jimport('joomla.filesystem.folder');

			$files = $app->input->files->get('jmconfig_file', array());

			$files = array('jmconfig_file' => $files);

			$template = $app->input->get('jmconfig_template', null);
			if (!JFolder::exists(JPATH_ROOT.DS.'templates'.DS.$template.DS.'assets'.DS.'config')) {
				JFolder::create(JPATH_ROOT.DS.'templates'.DS.$template.DS.'assets'.DS.'config');
			}
			if (array_key_exists('jmconfig_file', $files) && $template) {
				if (JFile::upload($files['jmconfig_file']['tmp_name'], JPATH_ROOT.DS.'templates'.DS.$template.DS.'assets'.DS.'config'.DS.$files['jmconfig_file']['name'])) {
					return true;
				}
			}
		}
	}

	/**
	 * Utility method for purging template cache folder. Not for template developers
	 */
	public static function purgeCache() {

		$user = JFactory::getUser();
		if (!$user->authorise('core.admin', 'com_templates')){
			echo JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN');
			exit(0);
		}

		$files = JFolder::files(JMF_TPL_PATH.'/cache', '.', false, false, array('index.html', '.svn', 'CVS', '.DS_Store', '__MACOSX'));
		$errors = array();
		if (count($files) > 0) {
			foreach ($files as $file) {
				if (!JFile::delete(JMF_TPL_PATH.'/cache/'.$file)){
					$errors[] = $file;
				}
			}
		}
		if (count($errors) > 0) {
			echo JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CONFIG_DELETE_CACHE_FAILED', count($errors));
		} else {
			echo JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CONFIG_DELETE_CACHE_SUCCESS', count($files));
		}

	}

	/**
	 * Set of actions that are being taking before saving template's configuration.
	 */
	public static function preSave(){
		if (defined('JMF_TPL')) {
			// set flag for Theme Customiser
			$jconf	= JFactory::getConfig();
			$cookie_path = ($jconf->get('cookie_path') == '') ? JUri::root(true) : $jconf->get('cookie_path');
			JFactory::getApplication()->input->cookie->set('JMTH_TIMESTAMP_'.JMF_TPL, -1, 0, $cookie_path);

			// dump CSS sheets which were made from LESS files
			$css_files = JFolder::files(JPath::clean(JPATH_ROOT.'/templates/'.JMF_TPL.'/css'), '\.css$');
			$less_files = JFolder::files(JPath::clean(JPATH_ROOT.'/templates/'.JMF_TPL.'/less'), '\.less$');

			$style_id = JFactory::getApplication()->input->getInt('id', 0);
			$suffix = ($style_id > 0) ? '.'.$style_id : '';

			foreach ($less_files as $less) {
				$name = JFile::stripExt($less);
				/*if (in_array($name.'.css', $css_files)) {
					JFile::delete(JPath::clean(JPATH_ROOT.'/templates/'.JMF_TPL.'/css/').$name.'.css');
				}*/
				if (in_array($name.$suffix.'.css', $css_files)) {
					JFile::delete(JPath::clean(JPATH_ROOT.'/templates/'.JMF_TPL.'/css/').$name.$suffix.'.css');
				}
			}
		}
		return null;
	}

	/**
	 * Saving layout settings to the file in JSON format.
	 */
	protected static function saveLayout($layout){

		$file = JPath::clean(JMF_TPL_PATH . '/assets/layout/' . $layout . '.json');

		if (!is_dir(dirname($file))) {
			JFolder::create(dirname($file));
		}

		$params = new JRegistry();
		$params->loadObject($_POST);

		$data = $params->toString();
		if (!@JFile::write($file, $data)) {
			return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CAN_NOT_WRITE_TO_FILE', $file),'error','json');
		}

		// saving layout default settings
		$dfile = JPath::clean(JMF_TPL_PATH . '/assets/layout/' . $layout . '.def');
		@JFile::write($dfile, $data);
		// end saving default settings

		return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_LAYOUT_SAVED', $layout), 'success','json');
	}

	/**
	 * Setting default layout for current template style
	 * @param (string) name of the layout to set as a default for menu assignment
	 */
	protected static function setDefault($default){

		$app = JFactory::getApplication();
		$styleid = $app->input->get('id', null, 'int');

		$file = JPath::clean(JMF_TPL_PATH . '/assets/style/assigns-' . $styleid . '.json');

		if (!is_dir(dirname($file))) {
			JFolder::create(dirname($file));
		}

		$assigns = new JRegistry;
		// get current layout assigns settings
		if(JFile::exists($file)) {
			$assigns->loadString(JFile::read($file));
		}

		$arr_assigns = $assigns->toArray();

		// unset assigns for default layout
		foreach($arr_assigns as $id => $layout){
			if($layout == $default) unset($arr_assigns[$id]);
		}
		// set default layout
		$arr_assigns[0] = $default;

		$assigns = new JRegistry;
		$assigns->loadArray($arr_assigns);
		$data = $assigns->toString();
		if (!@JFile::write($file, $data)) {
			return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CAN_NOT_WRITE_TO_FILE', $file),'error','json');
		}

		return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_LAYOUT_HAS_BEEN_SET_AS_DEFAULT', $default), 'success','json');
	}

	/**
	 * Getting default layout for current template style
	 * @param (string) name of the current layout to set as a default (backward compatibility)
	 */
	protected static function getDefault($current) {

		$app = JFactory::getApplication();
		$styleid = $app->input->get('id', null, 'int');

		$file = JPath::clean(JMF_TPL_PATH . '/assets/style/assigns-' . $styleid . '.json');

		if (!is_dir(dirname($file))) {
			JFolder::create(dirname($file));
		}

		$assigns = new JRegistry;
		// get current layout assigns settings
		if(JFile::exists($file)) {
			$assigns->loadString(JFile::read($file));
		} else {
			$assigns->set(0, $current);
			$data = $assigns->toString();
			if (!@JFile::write($file, $data)) {
				return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CAN_NOT_WRITE_TO_FILE', $file),'error','json');
			}
		}

		$arr_assigns = $assigns->toArray();
		$data = array();
		$data['layout'] = isset($arr_assigns[0]) ? $arr_assigns[0] : $current;

		return self::renderAlert('Getting default layout', 'success','json', $data);
	}

	/**
	 * Rendering layout menu assignments for Layout Builder
	 * @param (string) name of the layout to render menu assignment
	 */
	protected static function displayAssigns($layout){

		$app = JFactory::getApplication();
		$styleid = $app->input->get('id', null, 'int');

		$file = JPath::clean(JMF_TPL_PATH . '/assets/style/assigns-' . $styleid . '.json');

		if (!is_dir(dirname($file))) {
			JFolder::create(dirname($file));
		}

		$assigns = new JRegistry;
		// get current layout assigns settings
		if(JFile::exists($file)) {
			$assigns->loadString(JFile::read($file));
		}
		$assigns = $assigns->toArray();
		if(!isset($assigns[0])) $assigns[0] = $layout;

		// check if this is a default template style
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('home')->from('#__template_styles')->where('id='.$styleid);
		$db->setQuery($query);
		$is_default_style = $db->loadResult();

		$assigns_tpl = JPath::clean(JMF_FRAMEWORK_PATH.'/includes/assets/admin/layouts/layoutbuilder_assigns.php');
		ob_start();
		if (JFile::exists($assigns_tpl)) {
			include($assigns_tpl);
		} else {
			echo self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_MISSING_FILE', $assigns_tpl),'error');
		}
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Saving layout menu assignments to the file in JSON format.
	 */
	protected static function saveAssigns($layout){

		$app = JFactory::getApplication();
		$styleid = $app->input->get('id', null, 'int');

		$file = JPath::clean(JMF_TPL_PATH . '/assets/style/assigns-' . $styleid . '.json');

		if (!is_dir(dirname($file))) {
			JFolder::create(dirname($file));
		}

		$assigns = new JRegistry;
		// get current layout assigns settings
		if(JFile::exists($file)) {
			$assigns->loadString(JFile::read($file));
		}
		$arr_assigns = $assigns->toArray();

		if(!isset($arr_assigns[0])) $arr_assigns[0] = $layout;
		$default = $arr_assigns[0];

		$sent_assigns = new JRegistry;
		$sent_assigns->loadArray($_POST);
		$sent_assigns = $sent_assigns->toArray();

		// unassign checked menu items, because this is a default layout
		if($layout == $default) {
			foreach($sent_assigns as $id) {
				if(isset($arr_assigns[$id])) unset($arr_assigns[$id]);
			}
		// assign checked menu items to current layout
		} else {
			// first remove all assignments for current layout
			foreach($arr_assigns as $id => $assigned_layout){
				if($assigned_layout == $layout) unset($arr_assigns[$id]);
			}
			// then add sent assignments
			foreach($sent_assigns as $id) {
				$arr_assigns[$id] = $layout;
			}
		}

		$assigns = new JRegistry;
		$assigns->loadArray($arr_assigns);
		$data = $assigns->toString();
		if (!@JFile::write($file, $data)) {
			return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CAN_NOT_WRITE_TO_FILE', $file),'error','json');
		}

		return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_ASSIGNS_SAVED', $layout), 'success','json');
	}

	/**
	 * Loading layout configuration in JSON format
	 * @param (string) name of the layout
	 */
	protected static function loadParams($layout){

		$file = JPath::clean(JMF_TPL_PATH . '/assets/layout/' . $layout . '.json');
		$params = new JRegistry;
		if(JFile::exists($file)) {
			$params->loadString(JFile::read($file));
		} else {
			$dfile = JPath::clean(JMF_TPL_PATH . '/assets/layout/' . $layout . '.def');
			$params->loadString(JFile::read($dfile));
		}

		$defaults = new JRegistry();
		$default_settings_file = JPath::clean(JMF_TPL_PATH . '/templateDefaults.json');
		if (JFile::exists($default_settings_file)) {
			$defaults->loadFile($default_settings_file, 'JSON');
		}

		$params->def('#tmplWidth', $defaults->get('JMfluidGridContainerLg'));
		$params->def('#tmplSpace', $defaults->get('JMbaseSpace'));

		$data = $params->toString();

		return $data;
	}

	/**
	 * Copying layout method provided by Layout Builder
	 */
	protected static function copyLayout($layout, $cname)
	{
		//safe name
		$cname = JApplication::stringURLSafe($cname);

		if (!$layout || !$cname) {
			return self::renderAlert(JText::_('PLG_SYSTEM_JMFRAMEWORK_UNKNOW_ACTION'), 'json');
		}

		$path = JPath::clean(JMF_TPL_PATH . '/tpl/');
		$source  = $path . $layout . '.php';
		$file	= $path . $cname . '.php';

		$settingpath = JPath::clean(JMF_TPL_PATH . '/assets/layout/');
		$settingfile = $settingpath . $cname . '.def';

		$params = new JRegistry();
		$params->loadObject($_POST);

		$data = $params->toString();

		if (!is_dir($settingpath)) {
			JFolder::create($settingpath);
		}

		if (!@JFile::write($settingfile, $data)) {
			return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CAN_NOT_WRITE_TO_FILE', $settingfile),'error', 'json');
		}

		// Check if original file exists
		if (JFile::exists($source)) {
			// Check if the desired file already exists
			if (!JFile::exists($file)) {
				if (!JFile::copy($source, $file)) {
					return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CAN_NOT_WRITE_TO_FILE', $file),'error', 'json');
				}
			} else {
				return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_LAYOUT_ALREADY_EXISTS', $cname),'error', 'json');
			}
		} else {
			return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_MISSING_BLOCK_FILE', $source),'error', 'json');
		}

		return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_LAYOUT_SAVED', $cname),'success', 'json', array('layout' => $cname));
	}

	/**
	 * Layout removal method provided by Layout Builder
	 */
	protected static function removeLayout($layout)
	{
		if (!$layout) {
			return self::renderAlert(JText::_('PLG_SYSTEM_JMFRAMEWORK_UNKNOW_ACTION'), 'error', 'json');
		}

		if($layout == 'default') return self::renderAlert(JText::_('PLG_SYSTEM_JMFRAMEWORK_CANT_REMOVE_DEFAULT_LAYOUT'), 'warning', 'json');

		$file = JPath::clean(JMF_TPL_PATH . '/tpl/' . $layout . '.php');
		$settingfile = JPath::clean(JMF_TPL_PATH . '/assets/layout/' . $layout);

		$return = false;
		if (!JFile::exists($file)) {
			return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_MISSING_BLOCK_FILE', $file),'error','json');
		}

		if (!@JFile::delete($file)) {
			return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CANT_DELETE_FILE', $file),'error','json');
		} else {
			@JFile::delete($settingfile. '.json');
			@JFile::delete($settingfile. '.def');

			$app = JFactory::getApplication();
			$styleid = $app->input->get('id', null, 'int');

			$file = JPath::clean(JMF_TPL_PATH . '/assets/style/assigns-' . $styleid . '.json');

			if (!is_dir(dirname($file))) {
				JFolder::create(dirname($file));
			}

			$assigns = new JRegistry;
			// get current layout assigns settings
			if(JFile::exists($file)) {
				$assigns->loadString(JFile::read($file));
			}
			$arr_assigns = $assigns->toArray();
			$default = $arr_assigns[0];
			// if removed layout is set as defult we need to set new default layout
			if($default == $layout) {
				$default = 'default';
				$arr_assigns[0] = $default;
			} else {
				// unset assigns for removed layout or default layout
				foreach($arr_assigns as $id => $removed){
					if($layout == $removed) unset($arr_assigns[$id]);
				}
			}

			$assigns = new JRegistry;
			$assigns->loadArray($arr_assigns);
			$data = $assigns->toString();
			if (!@JFile::write($file, $data)) {
				return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_CAN_NOT_WRITE_TO_FILE', $file),'error','json');
			}

			return self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_LAYOUT_REMOVED', $layout), 'success', 'json', array('layout' => $layout, 'default_layout' => $default));
		}
	}

	/**
	 * Rendering layout scheme for Layout Builder purposes.
	 */
	public function renderScheme($layout){

		$html = '';
		$path = JPath::clean(JMF_TPL_PATH . '/tpl/' . $layout . '.php');

		if (JFile::exists($path)) {
			$html = $this->renderBlock($layout, true);
		} else {
			$html = self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_MISSING_BLOCK_FILE', $layout),'error');
		}

		$bshowto = '<div class="jmlink control-group">'.JText::_('PLG_SYSTEM_JMFRAMEWORK_CONFIG_BOOTSTRAP_HOWTO_LABEL').'</div>';

		$excluded = $this->renderExcludedBlocks();

		$html = str_replace('</body>', $bshowto.$excluded . '</body>', $html);

		return $html;
	}

	/**
	 * Rendering blocks excluded from the current layout.
	 * Layout Builder allows to include those blocks into the layout with simple drag&drop function
	 */
	private function renderExcludedBlocks(){

		ob_start();

		echo '<div id="jm_layoutbuilder_excluded_blocks" class="jm_layoutbuilder_excluded_blocks">';
		echo '<h2>'. JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_EXCLUDED_BLOCKS') .'</h2>';

		$path = JPath::clean(JMF_TPL_PATH . '/tpl/blocks');
		$files = JFolder::files($path, '\.php');

		foreach($files as $file) {

			$block_name = trim(JFile::stripExt($file));
			if(!in_array($block_name, $this->blocks) && !in_array($block_name, $this->front_blocks)) {

				$this->renderBlock($block_name);

			}
		}

		echo '</div>';

		$html = ob_get_contents();
		ob_end_clean();

		return $this->_parseScheme($html);
	}

	/**
	 * Rendering blocks of current layout for Layout Builder purposes.
	 */
	public function renderBlock($block_name, $is_scheme = false) {

		if(in_array($block_name, $this->front_blocks)) return;
		if(!$is_scheme) $this->front_blocks[] = $block_name;

		$block = ($is_scheme) ? $block_name : 'blocks/'.$block_name;
		$layout_file = JPath::clean(JMF_TPL_PATH.'/tpl/'.$block.'.php');
		if (!JFile::exists($layout_file)) {
			// if block doesn't exist in the template check the default plugin blocks
			$layout_file = JPath::clean(JMF_FRAMEWORK_PATH.'/includes/assets/template/'.$block_name.'.php');
		}

		ob_start();
		if (JFile::exists($layout_file)) {
			include($layout_file);
		} else {
			echo self::renderAlert(JText::sprintf('PLG_SYSTEM_JMFRAMEWORK_MISSING_BLOCK_FILE', $layout_file),'error');
		}
		$html = ob_get_contents();
		ob_end_clean();

		if($is_scheme) {
			// parse layout
			return $this->_parseScheme($html);
		} else {
			$param = $this->getLayoutConfig('block#'.$block_name);
			$class = (!empty($param) ? (@$param->fixedWidth ? ' fixed-width' : '' ) : '');
			$class.= (!empty($param) ? (@$param->fullWidth ? ' full-width' : '' ) : '');
			echo '<div class="jm_layoutbuilder_block'.$class.'" data-block="'.$block_name.'">' . $html . '</div>';
		}
	}

	/**
	 * JDoc parser. Before Joomla replaces the jdoc inclusions it needs to be replaced with Layout Builder elements.
	 */
	protected function _parseScheme($html)
	{
		$html = preg_replace_callback('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', array($this, '_parseSchemeJDoc'), $html);
		return $html;
	}

	/**
	 * JDoc renderer. Replacing the joomla jdoc inclusion with Layout Builder elements.
	 */
	protected function _parseSchemeJDoc($matches)
	{
		$type = $matches[1];
		if ($type == 'head') {
			return $matches[0];
		}
		$options = empty($matches[2]) ? array() : JUtility::parseAttributes($matches[2]);
		$options['type'] = $type;
		if (!isset($options['name'])) {
			$options['name'] = $options['type'];
		}

		return self::_renderElement($options);
	}

	/**
	 * Layout Builder element renderer
	 */
	protected static function _renderElement($options = array()){

		$default = '';
		$class = 'jm_layoutbuilder_element type-'.$options['type'];
		if(!isset($options['data-name'])) $class.= ' jm_layoutbuilder_constpos';
		else $default = 'data-name="'.$options['data-name'].'"';

		$extra = ($options['type']=='modules' ? ' class="jm_layoutbuilder_el_name hasTooltip" title="'. JText::_('PLG_SYSTEM_JMFRAMEWORK_MODULE_POSITION_NAME') .'"' : '');

		ob_start();
		echo '<div class="'.$class.'" '.$default.'>'
				.'<h4'.$extra.'>'.$options['name'].'</h4>'
				.($options['type'] == 'message' ? '<br /><span class="modules-chrome">' . JText::_('PLG_SYSTEM_JMFRAMEWORK_MESSAGE_SECTION_DESC') . '</span>':'')
				.($options['type'] == 'component' ? '<br /><span class="modules-chrome">' . JText::_('PLG_SYSTEM_JMFRAMEWORK_CONTENT_SECTION_DESC') . '</span>':'')
				.(isset($options['style']) ? '<br /><span class="modules-chrome hasTooltipBottom" title="'. JText::_('PLG_SYSTEM_JMFRAMEWORK_MODULES_CHROME') . '">' . $options['style'] . '</span>':'')
			.'</div>';
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Layout Builder module position renderer
	 */
	public function renderModules($position, $chrome = 'none', $grid_layout = 12) {

		$options = array();
		$options['type'] = 'modules';
		$options['data-name'] = $position;
		$options['name'] = parent::getPosition($position);
		//$options['size'] = JText::_('PLG_SYSTEM_JMFRAMEWORK_MODULE_BOOTSTRAP_SIZE');
		$options['style'] = $chrome;

		$html = '<div class="'.$this->getClass($position).'">';
		$html.= self::_renderElement($options);
		$html.= '</div>';

		return $html;
	}

	/**
	 * Layout Builder Flexiblock renderer
	 */
	public function renderFlexiblock($name, $chrome = 'none', $cols = 6, $grid_layout = 12) {

		$defpos = array();
		for($i = 1; $i <= $cols; $i++) $defpos[] = $name.'-'.$i;
		$poss = $defpos;

		$splparams = array();
		for ($i = 1; $i <= $this->maxgrid; $i++) {
			$param = $this->getLayoutConfig('column' . $i . '#' . $name);
			if (empty($param)) {
				break;
			} else {
				$splparams[] = $param;
			}
		}

		//we have data - configuration saved
		if (!empty($splparams)) {
			$poss = array();
			foreach ($splparams as $i => $splparam) {
				$param = (object)$splparam;
				$poss[] = isset($param->position) ? $param->position : $defpos[$i];
			}

		} else {
			foreach ($poss as $i => $pos) {
				$splparams[$i] = '';
			}
		}

		$inits = array();
		foreach ($defpos as $i => $dpos) {
			$inits[$i] = $this->parseInfo(isset($vars[$dpos]) ? $vars[$dpos] : '');
		}

		$infos = array();
		foreach ($splparams as $i => $splparam) {
			$infos[$i] = !empty($splparam) ? $this->parseInfo($splparam) : $inits[$i];
		}

		$defwidths = $this->extractKey($inits, 'width');
		$deffirsts = $this->extractKey($inits, 'first');

		$widths = $this->extractKey($infos, 'width');
		$firsts = $this->extractKey($infos, 'first');
		$others = $this->extractKey($infos, 'others');

		//optimize default width if needed
		$this->optimizeWidth($defwidths, $cols);
		$this->optimizeWidth($widths, $cols);

		$visibility = array(
				'name' => $name,
				'vals' => $this->extractKey($infos, 'hidden'),
				'deft' => $this->extractKey($inits, 'hidden'),
		);

		$spldata = array(
				' data-flexiblock="', $name, '"',
				' data-name="', implode(',', $defpos), '"',
				' data-chrome="', $chrome,'"',
				' data-visible="', $this->htmlattr($visibility), '"',
				' data-osizes="', $this->htmlattr($defwidths), '"',
				' data-sizes="', $this->htmlattr($widths), '"',
				' data-ofirsts="', $this->htmlattr($deffirsts), '"',
				' data-firsts="', $this->htmlattr($firsts), '"',
				' data-others="', $this->htmlattr($others), '"'
		);

		$default = $widths[$this->dscreen];

		$options = array();
		$options['style'] = $chrome;
		$options['type'] = 'modules';

		$html = '<div class="row-fluid jm-flexiblock jm-'.$name.'" '.implode('', $spldata).'>';
		foreach($poss as $i => $pos) {

			$options['name'] = $pos;
			$options['data-name'] = $name.'-'.$i;

			$html.= '<div class="span'.$default[$i].'">';
			$html.= self::_renderElement($options);
			$html.= '</div>';
		}
		$html.= '</div>';

		return $html;
	}

	/**
	 * Getting information about blocks, template positions, etc.
	 */
	public function parseInfo($posinfo = array())
	{
		//convert to array
		if (empty($posinfo)) {
			$posinfo = array();
		} else {
			$posinfo = is_array($posinfo) ? $posinfo : get_object_vars($posinfo);
		}

		// init empty result
		$result = array();
		foreach ($this->screens as $screen) {
			$result[$screen] = array();
		}

		$defcls = isset($posinfo[$this->dscreen]) ? $posinfo[$this->dscreen] : '';

		foreach ($result as $screen => &$info) {
			//class presentation string
			$cls = isset($posinfo[$screen]) ? $posinfo[$screen] : '';

			//extend other screen
			if (!empty($defcls) && $screen != $this->dscreen) {
				$cls = $this->addclass($cls, $defcls);
			}
			//if isset
			if (!empty($cls)) {
				//check if this position is hidden
				$hidden = $this->hasclass($cls, 'hidden');
				if ($hidden) {
					$cls = $this->removeclass($cls, 'hidden');
				}

				//check if this position is first position
				$first = $this->hasclass($cls, 'first-span');
				if ($first) {
					$cls = $this->removeclass($cls, 'first-span');
				}

				//check for width of this position
				$width = $this->maxgrid;
				if(preg_match($this->spanX, $cls, $match)){
					$match = array_filter($match, 'is_numeric');
					$width = array_pop($match);
					$width = is_numeric($width) ? $width : $this->maxgrid;
				}

				if (intval($width) > 0) {
					$width = $this->convertWidth($width, $screen);
				}

				//other class
				$others = trim(preg_replace($this->spanX, ' ', $cls));
			} else {
				$hidden = 0;
				$first = 0;
				$width = 0;
				$others = '';
			}

			$info['hidden'] = $hidden;
			$info['first'] = $first;
			$info['width'] = $width;
			$info['others'] = $others;
		}

		return $result;
	}

	function convertWidth($width, $screen)
	{
		//convert back - width of mobile should be [33%,] 50% and 100%
		//there might be some case when we enter the width of other screen ( < 12) => return 100% (12)
		return in_array($screen, array('mobile', 'tablet')) ? ($width < 12 ? 12 : floor(12 * $width / 100)) : $width;
	}

	function optimizeWidth(&$widths, $newcols = false)
	{
		foreach ($widths as $screen => &$width) {
			if (array_sum($width) < $this->maxgrid || $width[0] == 0) { //test if default empty width
				$widths[$screen] = $this->genWidth($screen, $newcols ? $newcols : count($width));
			}
		}
	}

	/**
	 *  Overridden JMFTemplate::countFlexiblock() function for Layout Builder purpose
	 */
	public function countFlexiblock($name, $cols = 4) {
		return true;
	}

	/**
	 * Overriden JMFTemplate::getClass() function for Layout Builder purpose
	 */
	public function getClass($name, $cls = array())
	{
		$params = $this->getLayoutConfig($name, '');

		$cinfo = $oinfo = $this->parseVisibility(is_string($cls) ? array($this->dscreen => $cls) : (is_array($cls) ? $cls : array()));
		if (!empty($params)) {
			$cinfo = $this->parseVisibility($params);
		}

		$data = '';
		$visible = array(
				'name' => $name,
				'vals' => $this->extractKey(array($cinfo), 'hidden'),
				'deft' => $this->extractKey(array($oinfo), 'hidden')
		);

		if (empty($params)) {
			if (is_string($cls)) {
				$data = ' ' . $cls;
			} else if (is_array($cls)) {
				$params = (object)$cls;
			}
		}

		if(!empty($params)){
			foreach ($this->maxcol as $screen => $span) {
				if(!empty($params->$screen)){
					$data .= ' data-' . $screen . '="' . trim($params->$screen) . '"';
				}
			}

			$dscreen = $this->dscreen;
			if(!empty($data)){
				$data = (isset($params->$dscreen) ? ' ' . $params->$dscreen : '') . '"' . substr($data, 0, strrpos($data, '"'));
			}
		}

		//remove hidden class
		$data = preg_replace('@("|\s)?hidden(\s|")?@iU', '$1$2', $data);

		return $data . '" data-visible="' . $this->htmlattr($visible) . '" '. (strstr($name, 'block#') ? 'data-block-visible="':'data-others="' . $this->htmlattr($this->extractKey(array($oinfo), 'others')));
	}

	/**
	 * Overriden JMFTemplate::getPosition() function for Layout Builder purpose
	 */
	function getPosition($name)
	{
		return parent::getPosition($name) . '" data-name="' . $name;
	}

	/**
	 * Setting up elements visibility for Layout Builder configuration
	 */
	function parseVisibility($posinfo = array())
	{
		//convert to array
		if (empty($posinfo)) {
			$posinfo = array();
		} else {
			$posinfo = is_array($posinfo) ? $posinfo : get_object_vars($posinfo);
		}

		// init empty result
		$result = array();
		foreach ($this->screens as $screen) {
			$result[$screen] = array();
		}

		foreach ($result as $screen => &$info) {
			//class presentation string
			$cls = isset($posinfo[$screen]) ? $posinfo[$screen] : '';

			//if isset
			if (!empty($cls)) {
				//check if this position is hidden
				$hidden = 'hidden' && $this->hasclass($cls, 'hidden');
				if ($hidden) {
					$cls = $this->removeclass($cls, 'hidden');
				}

				//other class
				$others = trim($cls);
			} else {
				$hidden = 0;
				$others = '';
			}

			$info['hidden'] = $hidden;
			$info['others'] = $others;
		}

		return $result;
	}

	/**
	 *  Extract a value key from object
	 **/
	function extractKey($infos, $key)
	{
		// init empty result
		$result = array();
		foreach ($this->screens as $screen) {
			$result[$screen] = array();
		}

		foreach ($infos as $i => $screens) {
			foreach ($screens as $screen => $info) {
				$result[$screen][$i] = $info[$key];
			}
		}

		return $result;
	}

	/**
	 *  Utility function - check if a HTML class is exist in a HTML class list
	 **/
	function hasclass($clsname, $cls)
	{
		return intval(strpos(' ' . $clsname . ' ', ' ' . $cls . ' ') !== false);
	}

	/**
	 *  Utility function - remove a HTML class in a HTML class list
	 **/
	function removeclass($clsname, $cls)
	{
		return preg_replace('/(^|\s)' . $cls . '(?:\s|$)/', '$1', $clsname);
	}

	/**
	 *  Utility function - remove a HTML class in a HTML class list
	 **/
	function addclass($clsname, $cls)
	{
		$haswidth = preg_match($this->spanX, $clsname);
		if ($haswidth) {
			$cls = trim(preg_replace($this->spanX, ' ', $cls));
		}

		$cls = explode(' ', $cls);

		foreach ($cls as $cl) {
			if (!$this->hasclass($clsname, $cl)) {
				$clsname .= ' ' . $cl;
			}
		}

		return implode(' ', array_unique(explode(' ', $clsname)));
	}

	/**
	 *  Utility function - changing JSON object to quoted string
	 **/
	function htmlattr($obj)
	{
		return htmlentities(json_encode($obj), ENT_QUOTES);
	}

	/**
	 * Utility method that renders messages. Not for template developers.
	 */
	public static function renderAlert($msg, $type = '', $format = 'html', $json_data = array()) {

		if(!in_array($type, array('error','success'))) $type = 'warning';

		$alert = '';

		if($format == 'json') {

			$alert = json_encode(array(
				'msg' => $msg,
				'type' => $type,
				'data' => $json_data
			));

		} else {

			$alert = '<div class="alert alert-block alert-'.$type.'">';
			$alert.= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
			$alert.= $msg;
			$alert.= '</div>';
		}

		return $alert;
	}

	/**
	 * Returns available module position list
	 * @return mixed
	 */
	public static function getModulePositions(){

		require_once JPATH_ADMINISTRATOR.'/components/com_modules/helpers/modules.php';

		JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_modules/helpers/html');
		$positions = JHtml::_('modules.positions', 0);

		// Add custom position to options
		$customGroupText = JText::_('COM_MODULES_CUSTOM_POSITION');

		// Build field
		$attr = array(
				'id'		  => 'jm_layoutbuilder_mod_pos_select',
				'list.select' => '',
				'list.attr'   => 'class="jm_layoutbuilder_mod_pos_select" '
				.' size="10"'
		);

		if(key_exists('', $positions)) unset($positions['']);

		return JHtml::_('select.groupedlist', $positions, 'jm_layoutbuilder_mod_pos_select', $attr);

	}

	/**
	 * Make ghost functions - not needed for Layout Builder
	 */
	public function checkModules($condition) {
		return true;
	}

	public function countModules($condition) {
		return true;
	}

	public function cacheStyleSheet($generator) {
		return false;
	}

	public function addStyleSheet($path, $type = 'text/css', $media = null, $attribs = array()) {
		return true;
	}

	/*
	public function addCompiledStyleSheet($path) {
		return true;
	}*/

	public function addStyleDeclaration($content, $type = 'text/css'){
		return true;
	}

	public function addScript($url, $type = "text/javascript", $defer = false, $async = false)
	{
		return true;
	}

	public function addScriptDeclaration($content, $type = 'text/javascript')
	{
		return true;
	}

	public function displayComponent(){
		return true;
	}

	public function displayMessage(){
		return true;
	}
}

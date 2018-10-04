<?php
/**
 * @version $Id: djjquerymonster.php 11 2016-09-19 10:43:19Z szymon $
 * @package DJ-jQueryMonster
 * @copyright Copyright (C) 2012-2015 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-jQueryMonster is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-jQueryMonster is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-jQueryMonster. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die ;

// import library dependencies
jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');

class plgSystemDJjQueryMonster extends JPlugin {

	protected $jquery = '';
	protected $jqueryui = '';
	protected $jquerycss = '';
	protected $noconflict = '';
	protected $check = true;
	protected $debug = false;
	protected $_debug = array();

	public function __construct(&$subject, $config) {

		parent::__construct($subject, $config);

		$this->loadLanguage();
		
		$this->debug = $this->params->get('debug', 0) ? true : false;
		
		$this->_enabled = true;
	}
	
	function onAfterRoute() {

		$app = JFactory::getApplication();

		if (JFactory::getDocument()->getType() !== 'html' || $app->isAdmin()) {
			$this->check = false;
			return;
		}

		if (!$this->params->get('jquery', 0)) {
			return;
		}
		$document = JFactory::getDocument();

		$version = $this->params->get('version', '1.8');
		if($version == 'customver') {
			$version = trim($this->params->get('customver', '1.8'));
		} else {
			// backward compatibility
			$subversion = $this->params->get('subversion', '');
			if ($subversion != '') {
				$version = '.' . $subversion;
			}
		}

		$protocol = $this->params->get('protocol', 'none');
		$protocol = ($protocol == 'none') ? '' : $protocol . ':';

		$compressed = '';
		if ($this->params->get('compression', 1)) {
			$compressed = '.min';
		}

		// jQuery
		if ($version == 'joomla') {
			$this->jquery = JURI::root(true) . '/media/jui/js/jquery'.$compressed.'.js';
		} else if ($version == 'custom') {
			$custompath = trim($this->params->get('custompath', ''));
			if ($custompath)
				$this->jquery = $custompath;
		} else {
			$this->jquery = $protocol . "//ajax.googleapis.com/ajax/libs/jquery/" . $version . "/jquery" . $compressed . ".js";
		}

		if (!empty($this->jquery)) {
			$document->addScript("DJHOLDER_JQUERY");
		}

		// no conflict
		$document->addScript("DJHOLDER_NOCONFLICT");
		if ($version == 'joomla') {
			$this->noconflict = JURI::root(true) . '/media/jui/js/jquery-noconflict.js';
		} else {
			$this->noconflict = JURI::root(true) . "/plugins/system/djjquerymonster/assets/jquery.noconflict.js";
		}
		
		$app->set('jQuery', true);

		if (!$this->params->get('jqueryui', 0)) {
			return;
		}

		$uiversion = $this->params->get('uiversion', '1.9.2');
		if($uiversion == 'customver') {
			$uiversion = trim($this->params->get('customuiver', '1.9.2'));
		} else {
			// backward compatibility
			$uisubversion = $this->params->get('uisubversion', '');
			if ($uisubversion != '') {
				$uiversion = '.' . $uisubversion;
			}
		}
		
		// jQuery UI
		if ($uiversion == 'joomla') {
			$this->jqueryui = JURI::root(true) . '/media/jui/js/jquery.ui.core'.$compressed.'.js';
		} else if ($uiversion == 'custom') {
			$custompath = trim($this->params->get('customuipath', ''));
			if ($custompath)
				$this->jqueryui = $custompath;
		} else {
			$this->jqueryui = $protocol . "//ajax.googleapis.com/ajax/libs/jqueryui/" . $uiversion . "/jquery-ui" . $compressed . ".js";
		}

		if (!empty($this->jqueryui)) {
			$document->addScript("DJHOLDER_JQUERYUI");
		}
		
		$uitheme = $this->params->get('uitheme', 'base');
		
		// jQuery UI theme
		if ($uitheme != 'none') {
			if ($uitheme == 'custom') {
				$custompath = trim($this->params->get('uithemecustom', ''));
				if ($custompath)
					$this->jquerycss = $custompath;
			} else {
				$this->jquerycss = $protocol . "//ajax.googleapis.com/ajax/libs/jqueryui/" . $uiversion . "/themes/" . $uitheme . "/jquery-ui.css";
			}

			if (!empty($this->jquerycss)) {
				$document->addStyleSheet("DJHOLDER_CSS");
			}
		}

	}

	function onBeforeCompileHead() {

		if (!$this->check) {
			return;
		}

		$app = JFactory::getApplication();
		$document = JFactory::getDocument();

		if ($app->isAdmin())
			return;
		
		$this->debug('onBeforeCompileHead event START');
		
		// get scripts and stylesheets from JDocument header data
		$headerdata = $document->getHeadData();
		$scripts = $headerdata['scripts'];
		$styles = $headerdata['styleSheets'];
		$headerdata['scripts'] = array();
		$headerdata['styleSheets'] = array();
		
		$this->debug('JDocument::_scripts array before cleaning and ordering', array_keys($scripts));
		$this->debug('JDocument::_styleSheets array before cleaning and ordering', array_keys($styles));
		
		// removing scripts in JDocument::_scripts array based on remove scripts list
		$removes = trim((string)$this->params->get('removescripts', ''));
		if ($removes) {
			$removes = array_map('trim', (array) explode("\n", $removes));
		
			$removed = array();
			foreach ($scripts as $url => $type) {
				$remove = false;
				if ($removes) {
					foreach ($removes as $script) {
						if (stripos($url, $script) !== false) {
							$remove = true;
						}
					}
				}
				if($remove) {
					$removed[] = $url;
					unset($scripts[$url]);
				}
			}
			$this->debug('Removed scripts based on "remove scripts" list', $removed);
		}
		
		// check if ef4 framework compression is used
		$tplparams = $app->getTemplate(true)->params;
		$ef4compress = (defined('JMF_JQUERYMONSTER') && !(JMF_THEMER_MODE || $tplparams->get('devmode',0) || JDEBUG || $app->input->get('option')=='com_config')) ? true : false;
		$ef4compressJS = ($ef4compress && $tplparams->get('jsCompress','0')=='1') ? true : false;
		$ef4compressCSS = ($ef4compress && $tplparams->get('cssCompress','0')=='1') ? true : false;
				
		// if ef4 js compression is active we need to clean up the scripts here
		//if($ef4compressJS) { // in fact we can clean the scripts no matter compression in on or off
			
			
			// cleaning up the scripts in JDocument::_scripts array
			$protects = trim((string)$this->params->get('protectscripts', ''));
			if ($protects) {
				$protects = array_map('trim', (array) explode("\n", $protects));
			}
			foreach ($scripts as $url => $type) {
				if(!empty($this->jquery) && preg_match('#([~\\\/a-zA-Z0-9_:\.-]*)jquery[.-]no[.-]*[cC]onflict\.js#', $url)) {
					$this->debug('jQuery noConflict script removed', $url);
					unset($scripts[$url]);
				} else if(!empty($this->jquery) && preg_match('#([~\\\/a-zA-Z0-9_:\.-]*)\/jquery([0-9\.-]|core|min|pack|latest)*?\.js#', $url, $match)) {
					$protect = false;
					if ($protects) {
						foreach ($protects as $script) {
							if (stripos($match[0], $script) !== false) {
								$protect = true;
							}
						}
					}
					if (!$protect) {
						$this->debug('jQuery script removed', $url);
						unset($scripts[$url]);
					} else {
						$this->debug('jQuery script founded, but it\'s protected from removal', $url);
					}
				} else if (!empty($this->jqueryui) && preg_match('#([~\\\/a-zA-Z0-9_:\.-]*)jquery[\.-]ui([0-9\.-]|core|sortable|custom|min|pack)*?\.js#', $url)) {
					$this->debug('jQuery UI script removed', $url);
					unset($scripts[$url]);
				}
			}
		//}
		
		// remove mootools and joomla modal if it's safe
		$keepforviews = array('form', 'itemform', 'additem');
		$keepforcoms = $this->params->get('keepfor', array());
		if($this->params->get('remove_mootools', 0) && $app->input->get('tmpl') != 'component' &&  !JDEBUG
				&& !in_array($app->input->get('view'), $keepforviews) && !in_array($app->input->get('option'), $keepforcoms) ) {
			
			$removeModal = $this->params->get('remove_modal', 0) ? true : false;
			$removeMootools = true;
			
			foreach ($scripts as $url => $type) {
				if(strstr($url, 'media/system/js/modal.js') !== false) {
					if($removeModal) {
						// removing modal script
						$this->debug('Modal script removed', $url);
						unset($scripts[$url]);
						// removing modal stylesheet
						foreach ($styles as $url => $type) {
							if(strstr($url, 'media/system/css/modal.css') !== false) {
								$this->debug('Modal style sheet removed', $url);
								unset($styles[$url]);
								break;
							}
						}
						// removing modal initialization script
						$qpath = preg_quote("jQuery(function($) { SqueezeBox.initialize({}); SqueezeBox.assign($('a.modal').get(), { parse: 'rel' }); }); function jModalClose() { SqueezeBox.close(); }");
						$qpath = str_replace(' ', '\s*', $qpath);
						foreach($headerdata['script'] as $type => $script) {
							if($type == 'text/javascript') {
								if($this->debug && preg_match_all("/$qpath/", $script, $matches)) {
									$this->debug('Modal initialization script/s removed', $matches[0]);
								}
								$headerdata['script'][$type] = preg_replace("/$qpath/", "", $script);
								break;
							}
						}
					} else {
						$this->debug('Mootools script can\'t be removed, because modal script is loaded');
						$removeMootools = false;
					}
					break;
				}
			}
			
			if($removeMootools) {
				foreach ($scripts as $url => $type) {
					if(strstr($url, 'media/system/js/mootools-core.js') !== false 
					|| strstr($url, 'media/system/js/mootools-more.js') !== false) {
						$this->debug('Mootools script removed', $url);
						unset($scripts[$url]);
					}
				}
			}
		}
		
		$skips = array_map('trim', (array) explode("\n", $tplparams->get('skipCompress')));
		foreach($skips as $key => $skip) if(empty($skip)) unset($skips[$key]);
		
		// first jquery, jquery-noconflict and jquery ui
		foreach ($scripts as $url => $type) {
			
			if (preg_match('#DJHOLDER_#s', $url)) {
				
				$newurl = $url;
				
				// if ef4 js compression is active we need to replace holders here
				if($ef4compressJS) {
					
					$compress = true;
					
					if(preg_match('#DJHOLDER_JQUERY$#', $url) && !empty($this->jquery) && !$this->isExternal($this->jquery)) {
						
						foreach($skips as $skip) {
							
							if(stristr($this->jquery, $skip)!==false) {
								$compress = false;
								break;
							}
						}
						// we don't replace the script if it's excluded from compression
						if($compress) {
							$this->debug('DJHOLDER_JQUERY replaced for EF4 JS compression', $this->jquery);
							$newurl = preg_replace('#DJHOLDER_JQUERY#', $this->jquery, $url, 1);
						} else {
							$this->debug('DJHOLDER_JQUERY not replaced, because script is excluded from EF4 JS compression', $this->jquery);
						}
						
					} else if(preg_match('#DJHOLDER_NOCONFLICT#', $url)) {
						
						foreach($skips as $skip) {
							
							if(stristr($this->noconflict, $skip)!==false) {
								$compress = false;
								break;
							}
						}
						// we don't replace the script if it's excluded from compression
						if($compress) {
							$this->debug('DJHOLDER_NOCONFLICT replaced for EF4 JS compression', $this->noconflict);
							$newurl = preg_replace('#DJHOLDER_NOCONFLICT#', $this->noconflict, $url, 1);
						} else {
							$this->debug('DJHOLDER_NOCONFLICT not replaced, because script is excluded from EF4 JS compression', $this->noconflict);
						}
						
					} else if(preg_match('#DJHOLDER_JQUERYUI#', $url) && !empty($this->jqueryui) && !$this->isExternal($this->jqueryui)) {
						
						foreach($skips as $skip) {
							//$this->debug("URL: ".$url."\nSKIP: ".$skip."\nCMP: ".(strstr($url, $skip)!==false ? 'TRUE':'FALSE'));
							if(stristr($this->jqueryui, $skip)!==false) {
								$compress = false;
								break;
							}
						}
						// we don't replace the script if it's excluded from compression
						if($compress) {
							$this->debug('DJHOLDER_JQUERYUI replaced for EF4 JS compression', $this->jqueryui);
							$newurl = preg_replace('#DJHOLDER_JQUERYUI#', $this->jqueryui, $url, 1);
						} else {
							$this->debug('DJHOLDER_JQUERYUI not replaced, because script is excluded from EF4 JS compression', $this->jqueryui);
						}
					}					
				}
				
				$headerdata['scripts'][$newurl] = $type;
				unset($scripts[$url]);
			}
		}
		
		// then mootools and all system scripts
		$qpath = preg_quote('media/system/js/', '/');
		foreach ($scripts as $url => $type) {
			if (preg_match('#' . $qpath . '#s', $url)) {
				$headerdata['scripts'][$url] = $type;
				unset($scripts[$url]);
			}
		}
		
		// then all joomla UI scripts
		$qpath = preg_quote('media/jui/js/', '/');
		foreach ($scripts as $url => $type) {
			if (preg_match('#' . $qpath . '#s', $url)) {
				$headerdata['scripts'][$url] = $type;
				unset($scripts[$url]);
			}
		}

		// and all other scripts
		foreach ($scripts as $url => $type) {
			$headerdata['scripts'][$url] = $type;
		}
		
		// if ef4 css compression is active we need to clean up the stylesheets here
		if($ef4compressCSS && !empty($this->jquerycss)) {
			
			foreach ($styles as $url => $type) {
				if (preg_match('#([\\\/a-zA-Z0-9_:\.-]*)jquery[\.-]ui([0-9\.-]|core|custom|min|pack)*?\.css#', $url)) {
					$this->debug('jQuery UI style sheet removed', $url);
					unset($scripts[$url]);
				}
			}
		}
		
		foreach ($styles as $url => $type) {
			
			// if ef4 css compression is active we need to replace holders here
			if($ef4compressCSS && preg_match('#DJHOLDER_CSS#', $url) && !empty($this->jquerycss) && !$this->isExternal($this->jquerycss)) {
					
				$compress = true;
			
				foreach($skips as $skip) {
					
					if(stristr($this->jquerycss, $skip)!==false) {
						$compress = false;
						break;
					}
				}
				// we don't replace the stylesheet if it's excluded from compression
				if($compress) {
					$this->debug('DJHOLDER_CSS replaced for EF4 CSS compression', $this->jquerycss);
					$url = preg_replace('#DJHOLDER_CSS#', $this->jquerycss, $url, 1);
				} else {
					$this->debug('DJHOLDER_CSS not replaced, because style sheet is excluded from EF4 CSS compression', $this->jquerycss);
				}
			}
			
			$headerdata['styleSheets'][$url] = $type;
		}
		
		
		$this->debug('JDocument::_scripts array after cleaning and ordering', array_keys($headerdata['scripts']));
		$this->debug('JDocument::_styleSheets array after cleaning and ordering', array_keys($headerdata['styleSheets']));
		
		$this->debug('onBeforeCompileHead event END');
		
		$document->setHeadData($headerdata);
	}

	function onAfterRender() {

		if (!$this->check) {
			return;
		}

		$app = JFactory::getApplication();
		$body = JResponse::getBody();

		if ($this->params->get('jquery', 0)) {
			
			$this->debug('onAfterRender event START');

			$matches = array();
			if (preg_match_all('#[^}^;^\n^>]*(jQuery|\$)\.noConflict\((true|false|)\);#', $body, $matches, PREG_SET_ORDER) > 0) {
				
				$qjavascript = preg_quote('<script type="text/javascript">', '/');

				foreach ($matches as $match) {
					$qmatch = preg_quote($match[0], '#');

					if (preg_match('#(' . $qjavascript . '[\S\s]*?' . $qmatch . ')#', $body)) {
						$this->debug('JQuery noConflict inline script removed', $match[0]);
						$body = preg_replace('#' . $qmatch . '#', '', $body, 1);
					}
				}

				$body = preg_replace('#<script type="text/javascript">\s*</script>#', '', $body, -1);
			}
			
			if($this->debug && preg_match_all('#src="([~\\\/a-zA-Z0-9_:\.-]*)jquery[.-]no[.-]*[cC]onflict\.js"#', $body, $matches)) {
				$this->debug('JQuery noConflict script/s removed', $matches[0]);
			}
			$body = preg_replace('#src="([~\\\/a-zA-Z0-9_:\.-]*)jquery[.-]no[.-]*[cC]onflict\.js"#', '_DJCLEAN_', $body);

			// removing scripts based on remove scripts list
			$removes = trim((string)$this->params->get('removescripts', ''));
			if ($removes) {
				
				$removes = array_map('trim', (array) explode("\n", $removes));
				$removed = array();
				
				foreach($removes as $url) {

					$matches = array();
					if (preg_match_all('#<script[^>]+src="([^"]*'.preg_quote($url).'[^"]*)"[^<]*</script>#', $body, $matches, PREG_SET_ORDER) > 0) {
						foreach ($matches as $match) {
							$qmatch = preg_quote($match[0], '/');
							//$this->debug('TO REMOVE', '<textarea>'.$match[1].'</textarea>');
							$removed[] = $match[1];
							$body = preg_replace('#' . $qmatch . '#', '', $body, 1);
						}
					}
				}
				
				if(count($removed)) $this->debug('Scripts removed based on "remove scripts" list directly in the body of JResponse', $removed);
			}
			
			$protects = trim((string)$this->params->get('protectscripts', ''));
			if ($protects) {
				$protects = array_map('trim', (array) explode("\n", $protects));
			}

			$matches = array();
			if (preg_match_all('#src="([~\\\/a-zA-Z0-9_:\.-]*)\/jquery([0-9\.-]|core|min|pack|latest)*?\.js"#', $body, $matches, PREG_SET_ORDER) > 0) {
				foreach ($matches as $match) {
					$qmatch = preg_quote($match[0], '/');
					$protect = false;
					if ($protects) {
						foreach ($protects as $script) {
							if (stripos($match[0], $script) !== false) {
								$protect = true;
							}
						}
					}
					if (!$protect) {
						$this->debug('JQuery script removed', $match[0]);
						$body = preg_replace('#' . $qmatch . '#', '_DJCLEAN_', $body, 1);
					} else {
						$this->debug('jQuery script founded, but it\'s protected from removal', $match[0]);
					}
				}
			}
			//print_r($matches);
			if (!empty($this->jquery)) {
				if($this->debug && preg_match('#([\\\/a-zA-Z0-9_:\.-]*)DJHOLDER_JQUERY"#', $body)) {
					$this->debug('DJHOLDER_JQUERY replaced directly in the body of JResponse', $this->jquery);
				}
				$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)DJHOLDER_JQUERY"#', $this->jquery.'"', $body, 1);
			}
			if (!empty($this->noconflict)) {
				if($this->debug && preg_match('#([\\\/a-zA-Z0-9_:\.-]*)DJHOLDER_NOCONFLICT"#', $body)) {
					$this->debug('DJHOLDER_NOCONFLICT replaced directly in the body of JResponse', $this->noconflict);
				}
				$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)DJHOLDER_NOCONFLICT"#', $this->noconflict.'"', $body, 1);
			}
			if ($this->params->get('jqueryui', 0)) {
				if($this->debug && preg_match_all('#src="([~\\\/a-zA-Z0-9_:\.-]*)jquery[\.-]ui([0-9\.-]|core|sortable|custom|min|pack)*?\.js"#', $body, $matches)) {
					$this->debug('JQuery UI script/s removed', $matches[0]);
				}
				$body = preg_replace('#src="([~\\\/a-zA-Z0-9_:\.-]*)jquery[\.-]ui([0-9\.-]|core|sortable|custom|min|pack)*?\.js"#', '_DJCLEAN_', $body);
				if (!empty($this->jqueryui)) {
					if($this->debug && preg_match('#([\\\/a-zA-Z0-9_:\.-]*)DJHOLDER_JQUERYUI"#', $body)) {
						$this->debug('DJHOLDER_JQUERYUI replaced directly in the body of JResponse', $this->jqueryui);
					}
					$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)DJHOLDER_JQUERYUI"#', $this->jqueryui.'"', $body, 1);
				}
				
				if($this->debug && preg_match_all('#href="([\\\/a-zA-Z0-9_:\.-]*)jquery[\.-]ui([0-9\.-]|core|custom|min|pack)*?\.css"#', $body, $matches)) {
					$this->debug('JQuery UI style sheet/s removed', $matches[0]);
				}
				$body = preg_replace('#href="([\\\/a-zA-Z0-9_:\.-]*)jquery[\.-]ui([0-9\.-]|core|custom|min|pack)*?\.css"#', '_DJCLEAN_', $body);

				if (!empty($this->jquerycss)) {
					if($this->debug && preg_match('#([\\\/a-zA-Z0-9_:\.-]*)DJHOLDER_CSS"#', $body)) {
						$this->debug('DJHOLDER_CSS replaced directly in the body of JResponse', $this->jquerycss);
					}
					$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)DJHOLDER_CSS"#', $this->jquerycss.'"', $body, 1);
				}
				
				$body = preg_replace('#[\s]*<link[^>]*_DJCLEAN_[^>]*/>[\s]*\n#', "\n", $body);
			}
			$body = preg_replace('#[\s]*<script[^>]*_DJCLEAN_[^>]*></script>[\s]*\n#', "\n", $body);
			
			$this->debug('onAfterRender event END');
		}
		
		$this->renderDebug($body);
		
		JResponse::setBody($body);

		return true;
	}

	private function isExternal($url) {

		if(substr($url, 0, 2) === '//'){
			$url = JURI::getInstance()->getScheme() . ':' . $url;
		}
		
		if (preg_match('/^https?\:/', $url)) {
			if (strpos($url, JURI::base()) === false){
				// external resource
				return true;
			}
		}
		
		return false;
	}
	
	private function debug($msg, $data = null) {
		
		if(!$this->debug) return;
		
		$this->_debug[] = array('msg'=>$msg, 'data'=>$data);
	}
	
	private function renderDebug(&$body) {
		
		if(!$this->debug) return;
		
		$html = '
		<script type="text/javascript">
			function toggleDJQData(name)
			{
				var e = document.getElementById(name);
				e.style.display = (e.style.display == \'none\') ? \'block\' : \'none\';
			}
		</script>
		';
		
		$html .= '<h3>DJ-JQUERYMONSTER PLUGIN DEBUG INFORMATION</h3>';
		
		foreach($this->_debug as $no => $debug) {
			
			$html .= '<h4>'.($no+1).'. '.$debug['msg'];
			
			if(!empty($debug['data'])) {
				if(is_array($debug['data'])) {
					$html .= ' <a href="#" onclick="toggleDJQData(\'djqdebug-'.$no.'\'); return false;" class="btn btn-mini">toggle data</a></h4>';
					$html .= ' <pre id="djqdebug-'.$no.'" style="display: none;">'.print_r($debug['data'], true).'</pre>';
				} else {
					$html .= ' <code>'.$debug['data'].'</code></h4>';
				}
			} else {
				$html .= '</h4>';
			}
		}
		
		$html = '<div class="container well">'.$html.'</div>';
		
		$body = str_replace('</body>', $html . '</body>', $body);
	}
}

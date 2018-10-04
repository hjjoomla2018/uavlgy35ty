<?php
/**
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: https://dj-extensions.com
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

defined('_JEXEC') or die('Restricted access');

class DJLicense{
	
	public static function getSubscription($name){
		
		if (!in_array('curl', get_loaded_extensions())) {
			return self::renderAlert(JText::_('COM_DJREVIEWS_DJLIC_CURL_NOT_INSTALLED'), 'error');
		}
		
		$app	= JFactory::getApplication();
		
		$ext = 'pkg_dj-'.strtolower($name);
		
		self::setUpdateServer($name);
		
		$ch = curl_init();
		$db = JFactory::getDBO();
		$query = "SELECT manifest_cache FROM #__extensions WHERE element ='".$ext."'";
		$db->setQuery($query);
		$result = $db->loadResult();
		
		// we change ext name to component, because package don't have assigned license
		$ext = 'com_dj'.strtolower($name);
		
		if(!$result) { // in case package wasn't installed check the version of the component
			$query = "SELECT manifest_cache FROM #__extensions WHERE element ='".$ext."'";
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		
		$mc = json_decode($result);
		$version = $mc->version;
		$config = JFactory::getConfig();
		
		$secret_file = JFile::makeSafe('license_'.$config->get('secret').'.txt');
		$license_file = JPath::clean(dirname(__FILE__).'/../'.$secret_file);
		
		if(JFile::exists($license_file)){
			$license = JFile::read($license_file);
		} else {
			$license = '';
		}
		
		curl_setopt($ch, CURLOPT_URL,'https://dj-extensions.com/index.php?option=com_djsubscriptions&view=checkDomain&license='.$license.'&ext='.$ext.'&v='.$version.'');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$u = JFactory::getURI();
		
		curl_setopt ($ch, CURLOPT_REFERER, $u->getHost());
		
		$contents = curl_exec ($ch);
		
		if(curl_errno($ch)) {
			$err = curl_error($ch);
			@curl_close ($ch);
			return self::renderAlert(JText::_('DJUPDATER_CURL_ERROR').'<p>CURL ERROR: '.$err.'</p>', 'error');
			
		}
		curl_close ($ch);
		
		$res= explode(';', $contents);
		
		if(strstr($res[0], 'E')){
			$t = JRequest::getVar('task','');
			if($t!='license' && $t!='Savelicense'){
				if($license==''){
					//$app->enqueueMessage(JText::_('COM_DJREVIEWS_DJLIC_ENTER_LICENSE'),'Error');
				}else{
					//$app->enqueueMessage(end($res),'Error');
				}
			}
			
			if(isset($res[3])){
				$msg_type = 'Error';
				if(isset($res[4])){
					$msg_type = $res[4];
				}
				$app->enqueueMessage($res[3],$msg_type);
			}
			
			$update = '<div class="djlic_box">';
			$update .= '<div class="djlic_title">DJ-'.$name.'</div>';
			$update .= '<div class="djlic_separator"></div>';
			$update .= '<div class="djlic_line djll1">'.JText::_('COM_DJREVIEWS_DJLIC_INSTALLED_VER').' <span>'.$version.'</span></div>';
			$update .= '<div class="djlic_line djll2">'.JText::_('COM_DJREVIEWS_DJLIC_AUTHOR').' <a target="_blank" href="https://www.dj-extensions.com"><span>DJ-EXTENSIONS</span></a></div>';
			$update .= '<div class="djlic_line djll3">'.JText::_('COM_DJREVIEWS_DJLIC_LAST_VERSION_AVAILABLE').' <span>'.$res[1].'</span></div>';
			
			if($res[0]=='E4'){
				
				$update .= '<div class="djlic_expired"><div class="djlic_expired_in">';
				$update .= '<a href="https://dj-extensions.com/faq/general-faq/what-is-extension-s-license" target="_blank" >';
				$update .= '<span class="djlic_iline4">'.JText::_('COM_DJREVIEWS_DJLIC_RENEW_INFO').'</span>';
				$update .='</a>';
				$update .= '<p><code>'.$license.'</code></p>';
				$update .= '<p><span class="djlic_iline5">'.JText::_('COM_DJREVIEWS_DJLIC_INVALID_INFO').'</span></p>';
				$update .= '<p><a class="btn btn-warning" href="https://dj-extensions.com/my-licenses" target="_blank">'.JText::_('COM_DJREVIEWS_DJLIC_RENEW_YOUR_LICENSE_KEY_HERE').'</a></p>';
				$update .= '<span class="djlic_iline6">'.JText::_('COM_DJREVIEWS_DJLIC_OR').'</span>';
				$update .= '<span class="djlic_iline1">'.JText::_('COM_DJREVIEWS_DJLIC_ENTER_ANOTHER_LICENSE_CODE_FOR').'</span>';
				$update .= '<span class="djlic_iline2"><code>'.$u->getHost().'</code></span>';
				
				$update .= '<input id="license" type="text" name="license" class="input input-large" placeholder="'. JText::_('DJUPDATER_PASTE_KEY').'" /> ';
				$update .= '<button id="register" class="btn btn-info" href="#">'.JText::_('DJUPDATER_REGISTER_KEY').'</button> ';
				$update .= '<a class="btn btn-warning" target="_blank" href="https://dj-extensions.com/pricing">'.JText::_('DJUPDATER_BUY_LICENSE').'</a><br />';
				
				$update .= '<span class="djlic_icon"></span>';
				$update .='</div></div>';
				
			}else{
				$update .= '<div class="djlic_invalid">';
				
				$update .= '<span class="djlic_iline1">'.JText::_('COM_DJREVIEWS_DJLIC_ENTER_LICENSE_CODE_FOR').'</span>';
				$update .= '<span class="djlic_iline2"><code>'.$u->getHost().'</code></span>';
				
				$update .= '<input id="license" type="text" name="license" class="input input-large" placeholder="'. JText::_('DJUPDATER_PASTE_KEY').'" /> ';
				$update .= '<button id="register" class="btn btn-info" href="#">'.JText::_('DJUPDATER_REGISTER_KEY').'</button> ';
				$update .= '<a class="btn btn-warning" target="_blank" href="https://dj-extensions.com/pricing">'.JText::_('DJUPDATER_BUY_LICENSE').'</a><br /><br />';
				
				$update .= '<span class="djlic_iline3">'.JText::_('COM_DJREVIEWS_DJLIC_INVALID_INFO').'</span>';
				
				$update .= '<span class="djlic_icon"></span>';
				
				$update .='</div>';
			}
			
			$update .= '<div style="clear:both"></div></div>';
			
			
			$js = "
				jQuery(document).ready(function(){
				
					var button = jQuery('#register');
					var loader = jQuery('<i class=\"icon-refresh djspin\" />');
				
					button.click(function(e){
						button.prop('disabled', true);
						button.prepend(loader);
						e.preventDefault();
				
						jQuery.ajax({
							data: {
								option: '".$ext."',
								task: 'license.save',
								extension: '".$name."',
								license: jQuery('#license').val()
							}
						}).done(function(data) {
							button.closest('.djlic_invalid, .djlic_expired').before(jQuery(data));
							setTimeout(function(){ location.reload(); }, 1000);
						})
						.fail(function() {
							alert( 'connection error' );
							button.prop('disabled', false);
							loader.detach();
						});
					});
									
				});
			";
			
			JFactory::getDocument()->addScriptDeclaration($js);
			
			return $update;
			
		}else{
			
			if(isset($res[5])){
				$msg_type = 'Error';
				if(isset($res[6])){
					$msg_type = $res[6];
				}
				$app->enqueueMessage($res[5],$msg_type);
			}
			
			$update_avaible = version_compare($version, $res[3], '<');
			
			$update = '<div class="djlic_box">';
			$update .= '<div class="djlic_title">DJ-'.$name.'</div>';
			$update .= '<div class="djlic_separator"></div>';
			$update .= '<div class="djlic_line djll1">'.JText::_('COM_DJREVIEWS_DJLIC_INSTALLED_VER').' <span>'.$version.'</span></div>';
			$update .= '<div class="djlic_line djll2">'.JText::_('COM_DJREVIEWS_DJLIC_AUTHOR').' <a target="_blank" href="https://www.dj-extensions.com"><span>DJ-EXTENSIONS</span></a></div>';
			$update .= '<div class="djlic_line djll3">'.JText::_('COM_DJREVIEWS_DJLIC_LAST_AVAILABLE_VER').' <span>'.$res[3].'</span></div>';
			$update .= '<div class="djlic_separator"></div>';
			
			if($update_avaible){
				$update .= '<div class="djlic_line djll4 update"><button id="update" class="btn btn-large btn-success"> '.JText::_('DJUPDATER_UPDATE').' DJ-'.$name.' </button></div>';
			}
			
			$update .= '<div class="djlic_valid">';
			
			$update .= '<span class="djlic_vline1">'.JText::sprintf('DJUPDATER_VALID_LICENSE_INFO', $license, date("d.m.Y", strtotime($res[2]))).'</span>';
			$update .= '<span class="djlic_icon"></span>';
			
			$update .='</div>';
			
			$update .= '';
			$update .= '<div style="clear:both"></div></div>';
			
			$js = "
				jQuery(document).ready(function(){
				
					var button = jQuery('#update');
					var loader = jQuery('<i class=\"icon-refresh djspin\" />');
				
					if(button.length) {
						button.click(function(e){
				
							e.preventDefault();
				
							if(confirm('".addslashes(JText::sprintf('DJUPDATER_CONFIRM_UPDATE_MESSAGE', 'DJ-'.$name))."')) {
								
								button.prop('disabled', true);
								button.prepend(loader);
								
								jQuery.ajax({
									url: 'index.php',
									method: 'post',
									data: {
										option: 'com_installer',
										view: 'install',
										task: 'install.install',
										installtype: 'url',
										'".JSession::getFormToken()."': 1,
										install_url: 'https://dj-extensions.com/index.php?option=com_djsubscriptions&view=getUpdate&license=$license&ext=$ext&v=".$version."'
									}
								}).done(function(data) {
									var hidden = jQuery('<div class=\"hidden\">'+data+'</div>');
									jQuery(document.body).append(hidden);
											
									var message = hidden.find('.alert');
									if(!message.length) message = data;
									button.closest('.update').before(message);
									setTimeout(function(){ location.reload(); }, 1000);
											
									hidden.remove();
								})
								.fail(function() {
									alert( 'connection error' );
									button.prop('disabled', false);
									loader.detach();
								});
							}
						});
					}
				});
			";
			
			JFactory::getDocument()->addScriptDeclaration($js);
			
			return $update;
		}
	}
	
	public static function renderAlert($msg, $type = '', $title = '') {
		
		if(!in_array($type, array('success', 'error', 'info', ''))) $type = 'info';
		
		$html = 	'<div class="alert alert-'.$type.'">'
			.		(!empty($title) ? '<h3>'.$title.'</h3>' : '')
			.		'<div class="alert-body">'.$msg.'</div>'
				.	'</div>';
				
				return $html;
	}
	
	public static function setUpdateServer($name) {
		
		if(empty($name)) return;
		// update the update server information for package
		$db = JFactory::getDbo();
		$config = JFactory::getConfig();
		$secret_file = JFile::makeSafe('license_'.$config->get('secret').'.txt');
		$license_file = JPath::clean(JPATH_ROOT.'/administrator/components/com_dj'.strtolower($name).'/'.$secret_file);
		
		if(JFile::exists($license_file)){
			$license = JFile::read($license_file);
		}else{
			$license = '';
		}
		
		$query = "SELECT extension_id, manifest_cache FROM #__extensions WHERE element='pkg_dj-".strtolower($name)."' AND type='package' ";
		$db->setQuery($query);
		$pkg = $db->loadObject();
		
		if($pkg) {
			$mc = json_decode($pkg->manifest_cache);
			$version = $mc->version;
			
			$extra_query = 'license='.$license.'&v='.$version.'&site='.JURI::root();
			
			$db->setQuery("SELECT COUNT(*) FROM #__update_sites WHERE name='DJ-".$name."' AND type='extension'");
			if ($db->loadResult() > 0) {
				$db->setQuery("UPDATE #__update_sites SET
	                    extra_query='".addslashes($extra_query)."'
	                    WHERE name='DJ-".$name."' AND type='extension'");
			} else {
				$db->setQuery("INSERT INTO #__update_sites (`name`, `type`, `location`, `enabled`, `extra_query`) VALUES
	                    ('DJ-".$name."', 'extension', 'https://dj-extensions.com/api/getUpdateInfo?extension=dj".strtolower($name).".xml', 1, '".addslashes($extra_query)."')");
				$db->query();
				
				$update_site_id = $db->insertid();
				$db->setQuery("INSERT INTO #__update_sites_extensions (`update_site_id`, `extension_id`)
						VALUES (".$update_site_id.", ".$pkg->extension_id.")");
			}
			$db->query();
		}
	}
}
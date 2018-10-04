
/**
 * @version $Id: upload.js 726 2017-10-27 08:33:56Z michal $
 * @package DJ-Catalog2
 * @subpackage DJ-Catalog2
 * @copyright Copyright (C) 2012 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Michał Olczyk michal.olczyk@design-joomla.eu
 */

function DJC2PlUploadStartUploadImage(up,files) {
	return DJC2PlUploadStartUpload(up, files, 'image');
}

function DJC2PlUploadStartUploadFile(up,files) {
	return DJC2PlUploadStartUpload(up, files, 'file');
}

function DJC2PlUploadStartUpload(up, files, prefix) {
	
	var wrapper = jQuery('#djc_uploader_'+prefix+'_items');
	var total = wrapper.find('.djc_uploader_item').length;
	var limit = parseInt(wrapper.attr('data-limit'));
	
	var limitreached = false;
	
	if (total + files.length >= limit && limit >= 0) {
		var remaining = limit - total;
		var toRemove = files.length - remaining;
		
		if (toRemove > 0 && files.length > 0){
			limitreached = true;
			for (var i = files.length-1; i >= 0; i--) {
				if (toRemove <= 0) {
					break;
				}
				up.removeFile(up.files[i]);					
				toRemove--;
			}		
		}					   				
	}
	
	if (limitreached) {
		alert(DJCatalog2UploaderVars.lang.limitreached);
	}
	
	up.start();
}

function DJC2PlUploadInjectUploadedImage(up,file,info) {
	var prefix = 'image';
	
	var response = JSON.parse(info.response); 
	if(response.error) {
		file.status = plupload.FAILED;
		file.name += ' - ' + response.error.message;
		jQuery('#'+file.id).addClass('ui-state-error');
		jQuery('#'+file.id).find('td.plupload_file_name').first().text(jQuery('#'+file.id).find('td.plupload_file_name').first().text() +  ' - ' + response.error.message);
		return false;
	}
	
	var html = '<td class="center ordering_handle"><span class="sortable-handler" style="cursor: move;"><i class="icon-move"></i></span></td>';
	html += '<td class="center"><img src="'+DJCatalog2UploaderVars.url+'media/djcatalog2/tmp/'+file.target_name+'" alt="'+file.name+'" />';
	//html += '<td class="center">'+file.name;
	html += '<input type="hidden" name="'+prefix+'_file_id[]" value="0" />';
	html += '<input type="hidden" name="'+prefix+'_file_name[]" value="'+file.target_name+'" />';
	html += '</td>';
	html += '<td><input type="text" class="djc_uploader_caption inputbox input input-medium" name="'+prefix+'_caption[]" value="'+DJCatalog2MUStripExt(file.name)+'" /></td>';
	html += '<td class="center"><input type="checkbox" onchange="DJCatalog2UPExcludeCheckbox(this);" /><input type="hidden" name="'+prefix+'_exclude[]" value="0" class="djc_hiddencheckbox" /></td>';
	html += '<td class="center"><button class="button btn djc_uploader_remove_btn">'+DJCatalog2UploaderVars.lang.remove+'</button></td>';
	
	var item = jQuery('<tr />',{'class':'djc_uploader_item', html: html});
	DJCatalog2MUInitItemEvents(item);
	
	// add uploaded image to the list and make it sortable
	item.appendTo(jQuery('#djc_uploader_'+prefix+'_items'));
	//this.DJCatalog2MUUploaders['djc_uploader_'+prefix].addItems(item);
	this.DJCatalog2MUUploaders['djc_uploader_'+prefix].append(item);
	up.removeFile(file);
	
	return true;
}

function DJC2PlUploadInjectUploadedFile(up,file,info) {
	var prefix = 'file';
	
	var response = JSON.parse(info.response); 
	if(response.error) {
		file.status = plupload.FAILED;
		file.name += ' - ' + response.error.message;
		jQuery('#'+file.id).addClass('ui-state-error');
		jQuery('#'+file.id).find('td.plupload_file_name').first().text( jQuery('#'+file.id).find('td.plupload_file_name').first().text() + ' - ' + response.error.message);
		return false;
	}
	
	var html = '<td class="center ordering_handle"><span class="sortable-handler" style="cursor: move;"><i class="icon-move"></i></span></td>';
	html += '<td class="center">'+file.name;
	html += '<input type="hidden" name="'+prefix+'_file_id[]" value="0">';
	html += '<input type="hidden" name="'+prefix+'_file_name[]" value="'+file.target_name+'">';
	html += '</td>';
	html += '<td>';
	
	if (DJCatalog2UploaderVars.valid_captions.length > 0) {
		html += '<select class="djc_uploader_caption inputbox input input-medium" name="'+prefix+'_caption[]">';
		for (var i = 0; i < DJCatalog2UploaderVars.valid_captions.length; i++) {
			html += DJCatalog2UploaderVars.valid_captions[i];
		}
		html += '</select>';
	} else {
		html += '<input type="text" class="djc_uploader_caption inputbox input input-medium" name="'+prefix+'_caption[]" value="'+DJCatalog2MUStripExt(file.name)+'" />';
	}
	
	if (DJCatalog2UploaderVars.attachment_groups.length == 0) {
		html += '<input type="hidden" name="'+prefix+'_group_label[]" value="" />';
	}
	
	html += '</td>';
	
	html += '<td style="border-left: none;">';
	
	if (DJCatalog2UploaderVars.attachment_groups.length > 0) {
		html += '<select class="djc_uploader_caption inputbox input input-medium" name="'+prefix+'_group_label[]">';
		for (var i = 0; i < DJCatalog2UploaderVars.attachment_groups.length; i++) {
			html += DJCatalog2UploaderVars.attachment_groups[i];
		}
		html += '</select>';
	} 
	
	html += '</td>';
	
	if (DJCatalog2UploaderVars.acls.length > 0) {
		html += '<td>';
		html += '<select class="djc_uploader_acl inputbox input input-medium" name="'+prefix+'_access[]">';
		for (var i = 0; i < DJCatalog2UploaderVars.acls.length; i++) {
			html += DJCatalog2UploaderVars.acls[i];
		}
		html += '</select>';
		html += '</td>';
	} 
	
	html += '<td class="center">';
	
	if (DJCatalog2UploaderVars.client == 1) {
		html +='<input type="text" class="djc_uploader_hits inputbox input input-small" name="'+prefix+'_hits[]" value="0" readonly="readonly" />';	
	} else {
		html +='<span>0</span>';
	}
	
	html += '</td>';
	html += '<td class="center"><button class="button btn djc_uploader_remove_btn">'+DJCatalog2UploaderVars.lang.remove+'</button></td>';
	
	var item = jQuery('<tr />',{'class':'djc_uploader_item', html: html});
	DJCatalog2MUInitItemEvents(item);
	
	// add uploaded image to the list and make it sortable
	item.appendTo(jQuery('#djc_uploader_'+prefix+'_items'));
	
	if (typeof jQuery != 'undefined') {
		if (typeof jQuery(document).chosen != 'undefined') {
			jQuery('select.djc_uploader_caption').chosen({"disable_search_threshold":10,"allow_single_deselect":true});
		}
	}
	
	//this.DJCatalog2MUUploaders['djc_uploader_'+prefix].addItems(item);
	this.DJCatalog2MUUploaders['djc_uploader_'+prefix].append(item);
	
	up.removeFile(file);
	
	return true;
}

function DJCatalog2MUInitItemEvents(item) {
	item = jQuery(item);
	if(!item) return;
	item.find('.djc_uploader_remove_btn').on('click',function(){
		item.detach();
		return false;
	});
	item.find('input').each(function(){
		var input = jQuery(this);
		input.on('focus',function(){
			item.addClass('active');
		});
		input.on('blur',function(){
			item.removeClass('active');
		});
	});
}

function DJCatalog2MUStripExt(filename) {
	
	var pattern = /\.[^.]+$/;
	return filename.replace(pattern, "");	
}

function DJCatalog2UPExcludeCheckbox(element){
	var p = element.parentNode;
	var inputs = p.getElementsByClassName('djc_hiddencheckbox');
	if (inputs.length == 0) {
		return false;
	}

	for (var k in inputs) {
		if (inputs.hasOwnProperty(k) && typeof inputs[k].type != 'undefined' && typeof inputs[k].name != 'undefined') {
			if (typeof element.checked != 'undefined' && element.checked) {
				inputs[k].value = '1';
			} else {
				inputs[k].value = '0';
			}
		}
	}
	return false;
}

function DJCatalog2UPAddUploader(suffix, wrapper_id){
	
	var wrapper = jQuery('#djc_uploader_'+suffix+'_items');
	var total = wrapper.find('.djc_uploader_item').length + wrapper.find('.djc_uploader_item_simple').length;
	var limit = parseInt(wrapper.attr('data-limit'));
	
	if (total >= limit && limit >= 0) {
		return false;				   				
	}
	
    var copy = jQuery('#djc_uploader_simple_'+suffix).clone().appendTo(jQuery('#' + wrapper_id + '_items'));
    
    copy.css('display', '');
    
    return false;
}

jQuery(document).ready(function(){
	var DJCatalog2MUUploaders = [];
	
	var uploaders = jQuery('.djc_uploader');
	uploaders.each(function(){
		var element = jQuery(this);
		id = element.attr('id');
		if (id) {
			instance = jQuery('#'+id + ' .djc_uploader_items').first();
			instance.sortable({
				axis:'y',
				cursor: 'move',
				items: 'tr',
				handle: '.sortable-handler',
				cancel: 'a,.btn,input'
				
			});
			
			DJCatalog2MUUploaders[id] = instance;
		}
	});
	
	window.DJCatalog2MUUploaders = DJCatalog2MUUploaders;
	
	jQuery('.djc_uploader_item').each(function(){
		DJCatalog2MUInitItemEvents(jQuery(this));
	});
});
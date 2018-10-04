
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @subpackage DJ-MediaTools galleryGrid layout
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */

function startUpload(up,files) {
	
	//up.settings.buttons.start = false;
	up.start();
	//console.log(up);
}
function injectUploaded(up,file,info) {
	
	var response = JSON.decode(info.response);
	if(response.error) {
		//console.log(file.status);
		file.status = plupload.FAILED;
		file.name += ' - ' + response.error.message;
		document.id(file.id).addClass('ui-state-error');
		document.id(file.id).getElement('td.plupload_file_name').appendText(' - ' + response.error.message);
		//up.removeFile(file);
		return false;
	}
	var root = document.id(up.settings.container).getProperty('data-root');
	var item = document.id('albumItemsWrap').getElement('.albumItem').clone();
	
	item.getElement('img').setProperty('src', root+'/media/djmediatools/upload/'+file.target_name);
	item.getElement('[name="item_image[]"]').set('value', file.target_name+';'+file.name);
	item.getElement('[name="item_title[]"]').set('value', stripExt(file.name));
	item.getElement('.video-icon').destroy();
	item.removeClass('hide');
	
	initItemEvents(item);
	// add uploaded image to the list and make it sortable
	item.inject(document.id('albumItems'), 'bottom');
	this.album.addItems(item);
	
	return true;
}

window.injectAlbumVideo = function injectVideo(video) {
	
	var thumb = video.thumbnail.replace(/^administrator\//, '');  
	
	var item = document.id('albumItemsWrap').getElement('.albumItem').clone();
	
	item.getElement('img').setProperty('src', thumb);
	item.getElement('[name="item_image[]"]').set('value', video.thumbnail+';;'+video.embed);
	item.getElement('[name="item_title[]"]').set('value', video.title);
	item.removeClass('hide');
	
	initItemEvents(item);
	// add video to the list and make it sortable
	item.inject(document.id('albumItems'), 'bottom');
	this.album.addItems(item);
	
};

function initItemEvents(item) {
	
	if(!item) return;
	item.getElement('.delBtn').addEvent('click',function(e){
		e.preventDefault();
		item.set('tween',{duration:'short',transition:'expo:out'});
		item.tween('width',0);
		(function(){item.dispose();}).delay(250);
		this.deleted = item;
	});
	item.getElements('input').each(function(input){
		input.addEvent('focus',function(){
			item.addClass('active');
		});
		input.addEvent('blur',function(){
			item.removeClass('active');
		});
	});
}

function stripExt(filename) {
	
	var pattern = /\.[^.]+$/;
	return filename.replace(pattern, "");	
}

window.addEvent('domready', function(){

	this.album = new Sortables('albumItems',{
		clone: true,
		revert: {duration:'short',transition:'expo:out'},
		opacity: 0.3
	});
	
	$$('.albumItem').each(function(item){
		initItemEvents(item);
	});
});
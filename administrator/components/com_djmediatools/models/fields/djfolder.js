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

function updateFolderList(field_id) {
	
	var folder = document.id(field_id);
	var button = document.id(field_id+'_button');
	var list = document.id(field_id+'_list');
	list.slide('out');
	var loader = new Element('img', { src: 'components/com_djmediatools/assets/ajax-loader.gif', 'class': 'ajax-loader' });
	
	new Request({
		url: 'index.php?option=com_djmediatools&view=category&tmpl=component',
		method: 'post',
		data: 'task=getfolderlist&folder='+encodeURIComponent(folder.value)+'&root='+encodeURIComponent(folder.get('data-root')),
		onRequest: function(){
			loader.inject(button, 'after');
		},
		onSuccess: function(responseText){
			loader.dispose();
			list.getElement('.djfolderlist').set('html', responseText);
			
			list.getElements('.djfolderlist a').each(function(el){
				el.addEvent('click', function(e){
					e.preventDefault();
					folder.value = this.get('data-folder');
					updateFolderList(field_id);
				});
			});
			
			list.slide('in');
		},
		onFailure: function(){
			loader.dispose();
			alert('connection error');
		}
	}).send();
	
	return false;
}

function addFolder(field_id) {
	
	var folder = document.id(field_id);
	var button = document.id(field_id+'_addbutton');
	var newFolder = document.id(field_id+'_add');
	var loader = new Element('img', { src: 'components/com_djmediatools/assets/ajax-loader.gif', 'class': 'ajax-loader' });
	
	new Request({
		url: 'index.php?option=com_djmediatools&view=category&tmpl=component',
		method: 'post',
		data: 'task=createfolder&folder='+encodeURIComponent(folder.value)+'&name='+encodeURIComponent(newFolder.value)+'&root='+encodeURIComponent(folder.get('data-root')),
		onRequest: function(){
			loader.inject(button, 'after');
		},
		onSuccess: function(responseText){
			loader.dispose();
			if(responseText) {
				var patt='/success/';
				if(patt.test(responseText)){
					newFolder.value = '';
					updateFolderList(field_id);
				} else {
	        		alert(responseText);
	        	}
			}
		},
		onFailure: function(){
			loader.dispose();
			alert('connection error');
		}
	}).send();
	
	return false;
	
}

window.addEvent('domready', function(){
	
	$$('.djfolder').each(function(el){
		el.set('slide',{duration: 'short', resetHeight: true});
		el.slide('hide');
	});
	
});
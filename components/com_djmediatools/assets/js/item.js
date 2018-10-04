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

!(function($){

var timer = null;

function centerImage(){
	
	clearTimeout(timer);
	
	var timer = setTimeout(function(){
	
		var wrapper = $('#djmediatools .dj-album-image').first();
		var image = wrapper.find('div,img').first();
		if(image.length) {
			var margin = (wrapper.height() - image.height()) / 2;
			if(margin > 0) {
				image.css('margin-top', margin);
			} else {
				image.css('margin-top', 0);
			}
		}
	}, 50);
}

$(document).ready(function(){

	var uri = window.location+'';
	var pos = uri.lastIndexOf('=item&');
	if(pos < 0) pos = uri.lastIndexOf('/media/');
	pos += 1;
	
	if(window.parent != window) {
		window.parent.location.hash = uri.substr(pos);
	} else { // redirect to open in modal window
		window.stop();
		window.location = $('#djmediatools').first().attr('data-album-url') + '#' + uri.substr(pos);
	}
	
	$('a:not(.dj-album-navi a)').each(function(){
		$(this).attr('target','_parent');
	});
	$('a[href^="http"]').each(function(){
		$(this).attr('target','_blank');
	});
});

$(window).load(function(){
	
	$(window).on('resize',centerImage);
	$(window).trigger('resize');
	$(window).focus();
	
});

$(window).on('keydown',function(event){
	var navi = null,
		key = 'which' in event ? event.which : event.keyCode;
	if(key == 39) {
		navi = $('#djmediatools a.dj-next').first();
	} else if(key == 37) {
		navi = $('#djmediatools a.dj-prev').first();
	}
	if(navi && navi.length) {
		window.location = navi.attr('href');
	}
});

})(jQuery);
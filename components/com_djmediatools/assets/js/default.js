/**
 * @version $Id: default.js 16 2013-07-30 09:59:57Z szymon $
 * @package DJ-MediaTools
 * @subpackage DJ-MediaTools galleryGrid layout
 * @copyright Copyright (C) 2012 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */

window.addEvent('domready',function(){
	
	var titles = document.id('djmediatools').getElements('.showOnOver');
	
	if(titles) {
		
		titles.each(function(element){
			element.set('tween', {duration: 'short', link: 'cancel'});
			element.fade('hide');
			element.getParent().addEvents({
				'mouseenter': function(){ element.fade('in') },
				'mouseleave': function(){ element.fade('out') }
			});
		});
		
	}
	
	
});

/**
 * @version 3.x
 * @package DJ-Catalog2
 * @copyright Copyright (C) 2013 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Michał Olczyk michal.olczyk@design-joomla.eu
 *
 */

function DJCatMatchModules(className, setLineHeight, reset) {
	var maxHeight = 0;
	var divs = null;
	if (typeof(className) == 'string') {
		divs = jQuery(document.body).find(className);
	} else {
		divs = className;
	}
	if (divs.length > 1) {
		jQuery(divs).each(function(index, element) {
			if (reset == true) {
				jQuery(element).css('height', '');
			}
			maxHeight = Math.max(maxHeight, parseInt(jQuery(element).height()));
		});
		
		jQuery(divs).css('height', maxHeight + 'px');
		
		if (setLineHeight) {
			jQuery(divs).css('line-height', maxHeight + 'px');
		}
	}
}

this.DJCatImageSwitcher = function (){
	var mainimagelink = jQuery('#djc_mainimagelink');
	var mainimage = jQuery('#djc_mainimage');
	var thumbs = jQuery('#djc_thumbnails').find('img');
	var thumblinks = jQuery('#djc_thumbnails').find('a');
	
	if(mainimagelink.length > 0 && mainimage.length > 0) {
		jQuery(mainimagelink).unbind('click');
		jQuery(mainimagelink).click(function(evt) {
			
			var rel = jQuery(mainimagelink).attr('rel');
			jQuery('#' + rel).trigger('click');
			if (window.MooTools) {
				document.id(rel).fireEvent('click', document.id(rel));
			}
			
			/*if(!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
				return false;
			}
			return true;*/
			
			return false;
		});
	}
	
	if (!mainimage.length || !mainimagelink.length || !thumblinks.length || !thumbs.length) return false;
	
	jQuery(thumblinks).each(function(index,thumblink){
		//var fx = new Fx.Tween(mainimage, {link: 'cancel', duration: 200});

		jQuery(thumblink).click(function(event){
			event.preventDefault();
			//new Event(element).stop();
			
			var img = new Image();
			img.onload = (function() {
				mainimage.fadeIn(300);
			});
			
			mainimage.fadeOut({
				duration: 300,
				start: function() {
					mainimagelink.attr('href', jQuery(thumblink).attr('href'));
					
					mainimagelink.attr('title', jQuery(thumblink).attr('title'));
					mainimagelink.attr('rel', 'djc_lb_'+index);
					
					img.src = jQuery(thumblink).prop('rel');
					mainimage.attr('alt', jQuery(thumblink).attr('title'));
				},
				complete: function(){
					mainimage.attr('src', img.src);
				}
			});
			
			return false;
		});
	});
}; 

this.DJCatContactForm = function(){
	// contact form handler
	var contactform = jQuery('#contactform');
	var makesure = jQuery('#djc_contact_form');
	var contactformButton = jQuery('#djc_contact_form_button');
	var contactformButtonClose = jQuery('#djc_contact_form_button_close');
	
	if (contactform.length && makesure.length) {
		
		if (window.location.hash == 'contactform' || window.location.hash == '#contactform') {
			contactform.slideDown(200, function(){
				jQuery('html, body').animate({
                    scrollTop: jQuery('#contactform').offset().top
                }, 200);
			});	
		} else if (contactformButton.length) {
			contactform.hide();
		}
		if (contactformButton.length) {
			contactformButton.click(function(event) {
				event.preventDefault();
				contactform.slideDown();
				
				if (contactform.is(':hidden') == false) {
					jQuery('html, body').animate({
	                    scrollTop: jQuery('#contactform').offset().top
	                }, 200);
				}
			});
		}
		if (contactformButtonClose.length) {
			contactformButtonClose.click(function(event){
				event.preventDefault();
				contactform.slideUp(200, function(){
					jQuery('html, body').animate({
	                    scrollTop: jQuery('#djcatalog').offset().top
	                }, 200);
				});
			});
		}
	}
};

this.DJCatAdvSearch = function() {
	var advSearchToggle = jQuery('.djc_adv_search_toggle');
	var advSearchWrapper = jQuery('#djc_additional_filters');
	if (advSearchToggle.length > 0) {
		if (!advSearchWrapper) {
			advSearchToggle.css('display', 'none');
		} else {
			var cookieVal = document.cookie.match('(^|;) ?' + 'djcAdvSearch' + '=([^;]*)(;|$)');
            var visible = cookieVal ? cookieVal[2] : null;

            if (visible != 1) {
				advSearchWrapper.hide();
			}
			
			advSearchToggle.click(function(event){
				advSearchWrapper.toggle();
				
				setTimeout(function(){
					var expires = new Date();
		            expires.setTime(expires.getTime() + (1 * 24 * 60 * 60 * 1000));
		            
					if (advSearchWrapper.is(':hidden') == false) {
						document.cookie = 'djcAdvSearch' + '=' + '1' + ';expires=' + expires.toUTCString();
						jQuery('html, body').animate({
		                    scrollTop: advSearchWrapper.offset().top
		                }, 200);
					} else {
						document.cookie = 'djcAdvSearch' + '=' + '0' + ';expires=' + expires.toUTCString();
					}
				}, 200);
			});
		}
	}
};

jQuery(document).ready(function(){
	DJCatImageSwitcher();
	DJCatContactForm();
	DJCatAdvSearch();
});

window.addEvent('domready', function(){
	
	// add to cart form handler
	var cart_forms = document.id(document.body).getElements('form.djc_form_addtocart');
	if (cart_forms.length > 0) {
		
		var cart_popup = new Element('div', {'id': 'djc_cart_popup', 'class' : 'djc_cart_popup', 'rel' : '{handler: \'clone\', size: {x: \'100%\', y: \'100%\'}, onOpen: function() {this.win.addClass(\'djc_cart_modal\'); this.overlay.addClass(\'djc_cart_modal\'); window.addEvent(\'resize\', function(){ this.resize({x: Math.max(Math.floor(window.getSize().x / 2), 400), y: Math.max(Math.floor(window.getSize().y / 4), 200)}, true); }.bind(this) ); window.fireEvent(\'resize\'); }, onClose: function(){this.win.removeClass(\'djc_cart_modal\'); this.overlay.removeClass(\'djc_cart_modal\');}}'});
		//var cart_popup = new Element('div', {'id': 'djc_cart_popup', 'class' : 'djc_cart_popup', 'rel' : '{handler: \'clone\', size: {x: \'100%\', y: \'auto\'}, onOpen: function() {this.win.addClass(\'djc_cart_modal\'); this.overlay.addClass(\'djc_cart_modal\'); window.addEvent(\'resize\', function(){ this.resize({x: Math.max(Math.floor(window.getSize().x / 2), 400)}, true); }.bind(this) ); window.fireEvent(\'resize\'); }, onClose: function(){this.win.removeClass(\'djc_cart_modal\'); this.overlay.removeClass(\'djc_cart_modal\');}}'});
		var cart_wrap = new Element('div', {'id': 'djc_cart_popup_wrap', 'style': 'display: none;'});
		var cart_loader = new Element('div', {'id': 'djc_cart_popup_loader', 'style': 'display: none;', 'html': '<span></span>'});
		cart_wrap.adopt(cart_popup);
		
		document.id(document.body).adopt(cart_loader);
		document.id(document.body).adopt(cart_wrap);
		
		cart_forms.each(function(el, pos){
			el.addEvent('submit', function(evt){
				var request = el.get('send');
				request.onSuccess = function(responseText, responseXML) {
					cart_loader.setStyle('display', 'none');
					var response = JSON.parse(responseText);
					var popup_instance = document.id('djc_cart_popup');
					popup_instance.innerHTML = '<p>' + response.message + '</p>';
					SqueezeBox.fromElement(popup_instance, {parse: 'rel'});
					
					if (typeof response.basket_count != 'undefined') {
						document.id(document.body).getElements('strong.djc_mod_cart_items_count').each(function(count_el){
							count_el.innerHTML = response.basket_count;
						});
						var basket_items = document.id(document.body).getElements('.mod_djc2_cart_contents');
						var basket_is_empty = document.id(document.body).getElements('.mod_djc2cart_is_empty');
						
						if (basket_items) {
							if (response.basket_count > 0) {
								basket_items.setStyle('display', 'block');
							} else {
								basket_items.setStyle('display', 'none');
							}
						}
						
						if (basket_is_empty) {
							if (response.basket_count > 0) {
								basket_is_empty.setStyle('display', 'none');
							} else {
								basket_is_empty.setStyle('display', 'block');
							}
						}
					}
					
				};
				request.onFailure = function(xhr) {
					cart_loader.setStyle('display', 'none');
				};
				el.set('send', {method: 'post', url: el.action + '?ajax=1'});
				cart_loader.setStyle('display', 'block');
				el.send();
				return false;
			});
		});
	}
});

var DJCatMatchBackgrounds = function(){
	
	//DJCatMatchModules('.djc_subcategory_bg', false, true);
	DJCatMatchModules('.djc_thumbnail', true, true);
	
	if (jQuery('.djc_subcategory_row').length > 0) {
		jQuery('.djc_subcategory_row').each(function(){
			var row = jQuery(this);
			var elements = row.find('.djc_subcategory_bg');
			if (elements.length > 0) {
				DJCatMatchModules(elements, false, true);
			}
		});
	}
	
	if (jQuery('.djc_item_row').length > 0) {
		jQuery('.djc_item_row').each(function(){
			var row = jQuery(this);
			var elements = row.find('.djc_item_bg');
			if (elements.length > 0) {
				DJCatMatchModules(elements, false, true);
			}
		});
	}
};

jQuery(window).on('load', function(){
	DJCatMatchBackgrounds();
	
	var tabHash = window.location.hash;
	
	var tabTogglers = jQuery('.djc_tabs li.nav-toggler');
	var tabPanels = jQuery('.djc_tabs .tab-pane');
	
	if (tabTogglers.length) {
		
		tabTogglers.each(function(index){
			var tab = jQuery(this);
			tab.on('click', 'a', function(e){
				//e.preventDefault();
				tab.siblings().removeClass('active');
				tab.addClass('active');
				jQuery(tabPanels[index]).siblings().removeClass('active');
				jQuery(tabPanels[index]).addClass('active');
			});
			
			// open tab from url hash
			if (this.id  && ('#' + this.id) == decodeURIComponent(tabHash)) {
				tab.find('a').trigger('click');
			}
		});
	}
	
	var accTogglers = jQuery('.djc_tabs .accordion-toggle');
	
	if (accTogglers.length) {
		
		accTogglers.each(function(index){
			var acc = jQuery(this);
			acc.on('click', function(e){
				//e.preventDefault();
				accTogglers.removeClass('active');
				acc.addClass('active');
			});
			// open tab from url hash
			if (this.href  && (this.href) == tabHash) {
				acc.trigger('click');
			}
		});
	}
		
	var filterModules = jQuery('.mod_djc2filters');
	if (filterModules.length > 0) {
		filterModules.each(function(){
			var togglers = jQuery(this).find('.djc_tab_toggler');
			var contents = jQuery(this).find('.djc_tab_content');
			
			if (togglers.length) {
		
				togglers.each(function(index){
					var toggler = jQuery(this);
					toggler.on('click', function(e){
						if(toggler.hasClass('active')) return false;
						togglers.removeClass('active');
						toggler.addClass('active');
						contents.hide();
						jQuery(contents[index]).slideDown();
					});
					if(index == 0) {
						toggler.addClass('active');
					} else {
						jQuery(contents[index]).hide();
					}
				});
			}
		});
	}
});

jQuery(window).on('resize', function(){
	DJCatMatchBackgrounds();
});



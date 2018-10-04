/**
 * @version $Id$
 * @package DJ-MediaTools
 * @subpackage DJ-MediaTools slider layout
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */
(function($){

this.DJImageSlider = new Class({

    initialize: function(settings, options){

        var slider_size = 10;
        var loaded_images = 0;
        var max_slides = 0;
        var current_slide = 0;
        var slider = 'slider' + settings.id;
        var autoplay = options.auto;
        var stop = 0;
        var show_nav = 0;
		var is_fading = false;
        
        $('djslider' + settings.id).fade('hide');
        
        var slides = $('slider'+ settings.id).getChildren('li');
        
        if (Browser.ie8) { // only for IE8
			var visibles = new Array();
			for (var i = 0; i < settings.visible_slides; i++) {
				visibles[i] = slides[i];
				visibles[i].fade('hide');
			}
		}
		
        slides.each(function(){
            slider_size += settings.slide_size;
            loaded_images++;
        });
        
        max_slides = loaded_images - settings.visible_slides;
		
        var slideImages;
		switch(settings.slider_type) {
			case 'up':
				$(slider).setStyle('position', 'relative');
				$(slider).setStyle('top', 0);
            	$(slider).setStyle('height', slider_size);
            	slideImages = new Fx.Tween(slider, {
					property: 'top', 
           			duration: options.duration,
                	transition: options.transition,
                	link: 'cancel'
            	});
				break;
			case 'down':
				$(slider).setStyle('position', 'absolute');
				$(slider).setStyle('bottom', 0);
            	$(slider).setStyle('height', slider_size);
            	slideImages = new Fx.Tween(slider, {
					property: 'bottom', 
           			duration: options.duration,
                	transition: options.transition,
                	link: 'cancel'
            	});
				break;
			case 'left':
				$(slider).setStyle('position', 'relative');
				$(slider).setStyle('left', 0);
            	$(slider).setStyle('width', slider_size);
            	slideImages = new Fx.Tween(slider, {
					property: 'left', 
                	duration: options.duration,
                	transition: options.transition,
                	link: 'cancel'
            	});
				break;
			case 'right':
				$(slider).setStyle('position', 'absolute');
				$(slider).setStyle('right', 0);
            	$(slider).setStyle('width', slider_size);
            	slideImages = new Fx.Tween(slider, {
					property: 'right', 
                	duration: options.duration,
                	transition: options.transition,
                	link: 'cancel'
            	});
				break;
			case 'fade':
			case 'ifade':
			default:
				slides.setStyle('position', 'absolute');
				slides.setStyle('top', 0);
				slides.setStyle('left', 0);
				$(slider).setStyle('width', settings.slide_size);
				slides.setStyle('opacity',0);
				slides.setStyle('visibility','hidden');
				slides[0].setStyle('opacity',1);
				slides[0].setStyle('visibility','visible');
				slides.set('tween',{property: 'opacity', duration: options.duration});
				break;
		}
        
		// navigation effects
		if (settings.show_buttons==2) {
			var play = new Fx.Tween('play' + settings.id, {
				property: 'opacity', 
				duration: 200,
				link: 'cancel'
			}).set('opacity',0);
			var pause = new Fx.Tween('pause' + settings.id, {
				property: 'opacity', 
				duration: 200,
				link: 'cancel'
			}).set('opacity',0);
		}
		if (settings.show_arrows==2) {
			var nextFx = new Fx.Tween('next' + settings.id, {
				property: 'opacity', 
				duration: 200,
				link: 'cancel'
			}).set('opacity',0);
			var prevFx = new Fx.Tween('prev' + settings.id, {
				property: 'opacity', 
				duration: 200,
				link: 'cancel'
			}).set('opacity',0);
		}
		if (settings.show_indicators == 2) {
			var indicatorsFx = new Fx.Tween('cust-navigation' + settings.id, {
				property: 'opacity', 
				duration: 200,
				link: 'cancel'
			}).set('opacity',0);
		}
		
        if(settings.show_arrows) $('next' + settings.id).addEvents({
        	'click': function(){
	            if (settings.show_buttons==2) hideNavigation();
	            nextSlide();
        	},
        	'keydown': function(){
        		var key = 'which' in event ? event.which : event.keyCode;
        		if(key == 13 || key == 32) { // space bar or enter key
        			nextSlide();
        			event.preventDefault();
					event.stopPropagation();
        		}
        	}
        });        
        if(settings.show_arrows) $('prev' + settings.id).addEvents({
        	'click': function(){
	            if (settings.show_buttons==2) hideNavigation();
	            prevSlide();
            },
            'keydown': function(){
            	var key = 'which' in event ? event.which : event.keyCode;
        		if(key == 13 || key == 32) { // space bar or enter key
        			prevSlide();
        			event.preventDefault();
					event.stopPropagation();
        		}
        	}
        });
        if(settings.show_buttons) $('play' + settings.id).addEvents({
        	'click': function(){
	            changeNavigation();
	            autoplay = 1;
        	},
        	'keydown': function(){
        		var key = 'which' in event ? event.which : event.keyCode;
        		if(key == 13 || key == 32) { // space bar or enter key
        			changeNavigation();
    	            autoplay = 1;
    	            if(settings.show_buttons) $('pause' + settings.id).focus();
    	            event.preventDefault();
					event.stopPropagation();
        		}
        	}
        });        
        if(settings.show_buttons) $('pause' + settings.id).addEvents({
        	'click': function(){
        		changeNavigation();
        		autoplay = 0;
        	},
        	'keydown': function(){
        		var key = 'which' in event ? event.which : event.keyCode;
        		if(key == 13 || key == 32) { // space bar or enter key
        			changeNavigation();
    	            autoplay = 0;
    	            if(settings.show_buttons) $('play' + settings.id).focus();
    	            event.preventDefault();
					event.stopPropagation();
        		}
        	}
        });  
		
		$('djslider-loader' + settings.id).addEvents({
            'mouseenter': function(){
                if (settings.show_buttons==2) showNavigation();
				if (settings.show_arrows==2) {
					nextFx.start(1);
					prevFx.start(1);
				}
				if (settings.show_indicators == 2) {
					indicatorsFx.start(1);
				}
				stop = 1;
            },
            'mouseleave': function(){
                if (settings.show_buttons==2) hideNavigation();
				if (settings.show_arrows==2) {
					nextFx.start(0);
					prevFx.start(0);
				}
				if (settings.show_indicators == 2) {
					indicatorsFx.start(0);
				}
				stop = 0;
            },
            'swipe': function(event){
				if(event.direction == 'left') {
					nextSlide();
				} else if(event.direction == 'right') {
					prevSlide();
				}
			},
			'focus': function(e) {
				$('djslider-loader' + settings.id).fireEvent('mouseenter');
			},
			'keydown': function() {				
				var key = 'which' in event ? event.which : event.keyCode;
				if(key == 37 || key == 39) {
					if(key == 39) nextSlide();
					else prevSlide();
					event.preventDefault();
					event.stopPropagation();
				}
			}
        });
		
		if($('cust-navigation' + settings.id)) {
			var buttons = $('cust-navigation' + settings.id).getElements('.load-button');
			buttons.each(function(el,index){
				el.addEvents({
					'click': function(){
						if (!is_fading && !el.hasClass('load-button-active')) {
							loadSlide(index);
						}
					},
					'keydown': function(){
		        		var key = 'which' in event ? event.which : event.keyCode;
		        		if(key == 13 || key == 32) { // space bar or enter key
		        			if (!is_fading && !el.hasClass('load-button-active')) {
								loadSlide(index);
								event.preventDefault();
								event.stopPropagation();
							}
		        		}
		        	}
				});
			});
		}
		
		function getSize(element){
			
			 return element.measure(function(){return this.getSize();});
		}
		
		function responsive(){
			
			updateTabindex();
			
			var wrapper = $('djslider-loader' + settings.id).getParent();
			var parentWidth = getSize(wrapper).x;
			parentWidth -= wrapper.getStyle('padding-left').toInt();
			parentWidth -= wrapper.getStyle('padding-right').toInt();
			
			var maxWidth = $('djslider' + settings.id).getStyle('max-width').toInt();
			var size = getSize($('djslider' + settings.id));
			var newSliderWidth = size.x;
			
			if(newSliderWidth > parentWidth) {
				newSliderWidth = parentWidth;
			} else if(newSliderWidth <= parentWidth && newSliderWidth < maxWidth){
				newSliderWidth = (parentWidth > maxWidth ? maxWidth : parentWidth);
			}
			
        	var ratio = size.x / size.y;
			var newSliderHeight = newSliderWidth / ratio;
			
			$('djslider' + settings.id).setStyle('width', newSliderWidth);
			$('djslider' + settings.id).setStyle('height', newSliderHeight);
			
			switch(settings.slider_type) {
				case 'up':
				case 'down':
					var space = slides[0].getStyle('padding-bottom').toInt();
					settings.slide_size = (newSliderHeight + space) / settings.visible_slides;
					slider_size = loaded_images * settings.slide_size + loaded_images;
			        $(slider).setStyle('height', slider_size);
			        
			        slides.setStyle('width', newSliderWidth);
					slides.setStyle('height', settings.slide_size - space);
					slideImages.set(-settings.slide_size * current_slide);
					break;
					
				case 'left':
				case 'right':
					var space = slides[0].getStyle('padding-right').toInt();
			    	settings.slide_size = (newSliderWidth + space) / settings.visible_slides;
			    	slider_size = loaded_images * settings.slide_size + loaded_images;
			        $(slider).setStyle('width', slider_size);
			        
			        slides.setStyle('width', settings.slide_size - space);
					slides.setStyle('height', newSliderHeight);
					slideImages.set(-settings.slide_size * current_slide);					
					break;
					
				case 'fade':
				case 'ifade':
				default:
					$(slider).setStyle('width', newSliderWidth);
					slides.setStyle('width', newSliderWidth);
					slides.setStyle('height', newSliderHeight);
					break;
			}
		    
		    if(settings.show_buttons || settings.show_arrows) {
				
				button_pos = 0;
				nav_pos = 0;
				
				// get some vertical space for navigation	
				if(settings.show_buttons || settings.show_arrows) button_pos = $('navigation' + settings.id).getPosition('djslider' + settings.id).y;
								
				if(button_pos < 0) {
					$('djslider-loader' + settings.id).setStyle('padding-top', -button_pos);										
				} 	
				
				buttons_height = 0;
				if(settings.show_arrows) {
					buttons_height = getSize($('next' + settings.id)).y;
					buttons_height = Math.max(buttons_height,getSize($('prev' + settings.id)).y);
				}
				if(settings.show_buttons) {
					buttons_height = Math.max(buttons_height,getSize($('play' + settings.id)).y);
					buttons_height = Math.max(buttons_height,getSize($('pause' + settings.id)).y);
				}
				button_pos += buttons_height;
				
				padding = button_pos - newSliderHeight;
				//console.log(padding);
				if(padding > 0) {
						$('djslider-loader' + settings.id).setStyle('padding-bottom', padding);
				}
	        	
				// put navigation inside the slider if it's wider than window 
				if(settings.show_buttons || settings.show_arrows) {
	        		buttons_margin = $('navigation' + settings.id).getStyle('margin-left').toInt() + $('navigation' + settings.id).getStyle('margin-right').toInt();
					if(buttons_margin < 0 && window.getSize().x < getSize($('navigation' + settings.id)).x - buttons_margin) {
					
						$('navigation' + settings.id).setStyle('margin-left',0);
						$('navigation' + settings.id).setStyle('margin-right',0);
					}
				}				
			}
		}
		
		function updateActiveButton(active){
			if($('cust-navigation' + settings.id)) buttons.each(function(button,index){
				button.removeClass('load-button-active');
				if(index==active) button.addClass('load-button-active');
			});			
		}
		
		function nextSlide(){
			if (current_slide < max_slides) loadSlide(current_slide + 1);
			else loadSlide(0);
        }
        
        function prevSlide(){
			if (current_slide > 0) loadSlide(current_slide - 1);
			else loadSlide(max_slides);
        }
        	
		function loadSlide(index) {
			if(current_slide == index) return;
			
			if (settings.slider_type == 'fade' || settings.slider_type == 'ifade') {
				if(is_fading) return;
				is_fading = true;
				prev_slide = current_slide;
				current_slide = index;
				makeFade(prev_slide);				
			} else {
				current_slide = index;
				slideImages.start(-settings.slide_size * current_slide);
			}
			
			updateTabindex();
			updateActiveButton(current_slide);
		}
		
		function makeFade(prev_slide){
			slides[current_slide].setStyle('visibility','visible');
			slides[current_slide].get('tween').start(1);
			slides[prev_slide].get('tween').start(0).chain(function(){
				slides[prev_slide].setStyle('visibility','hidden');
				is_fading = false;
			});
		}
		
        function hideNavigation(){
            if (!autoplay) {
                play.start(stop, 0).chain(function(){
                    if (!show_nav) 
                        $('play' + settings.id).setStyle('display', 'none');
                });
            }
            else {
                pause.start(stop, 0).chain(function(){
                    if (!show_nav) 
                        $('pause' + settings.id).setStyle('display', 'none');
                });
            }
            show_nav = 0;
        }
        
        function showNavigation(){
            if (!autoplay) {
                $('play' + settings.id).setStyle('display', 'block');
                play.start(stop, 1);
            }
            else {
                $('pause' + settings.id).setStyle('display', 'block');
                pause.start(stop, 1);
            }
            show_nav = 1;
        }
        function changeNavigation(){
            if (autoplay) {
                $('pause' + settings.id).setStyle('display', 'none');
                if (settings.show_buttons==2) pause.set('opacity',0);
                $('play' + settings.id).setStyle('display', 'block');
                if (settings.show_buttons==2) play.set('opacity',1);
            }
            else {
                $('play' + settings.id).setStyle('display', 'none');
                if (settings.show_buttons==2) play.set('opacity',0);
                $('pause' + settings.id).setStyle('display', 'block');
                if (settings.show_buttons==2) pause.set('opacity',1);
            }
        }
        
        function slidePlay(){
            setTimeout(function(){
                if (autoplay && !stop) 
                    nextSlide();
                slidePlay();
            }, options.delay);
        }
		
		function sliderLoaded(){
			// hide loader and show slider
			$('djslider-loader' + settings.id).setStyle('background','none');
			
			$('djslider' + settings.id).fade('in');
			
			if (Browser.ie8) { // only for IE8
				visibles.each(function(el){
					if(el) el.fade('in');
				});
			}
			
			responsive();
			
			if(settings.show_buttons) {
				
				play_width = getSize($('play' + settings.id)).x;
				$('play' + settings.id).setStyle('margin-left',-play_width/2);
				pause_width = getSize($('play' + settings.id)).x;
				$('pause' + settings.id).setStyle('margin-left',-pause_width/2);
				
				if(autoplay) {
					$('play' + settings.id).setStyle('display','none');
				} else {
					$('pause' + settings.id).setStyle('display','none');
				}
			}
			
			// start autoplay
			slidePlay();
		}
		
		function updateTabindex() {
			
			slides.each(function(slide, index){
				var focusable = slide.getElements('a[href], input, select, textarea, button');
				if(current_slide <= index && index < current_slide + settings.visible_slides) { // visible
					focusable.each(function(el){
						el.removeProperty('tabindex');
					});
				} else { // not visible
					focusable.each(function(el){
						el.setProperty('tabindex', -1);
					});
				}
	        });
		}
		
		if(settings.preload) sliderLoaded.delay(settings.preload);
		else window.addEvent('load', sliderLoaded);
		
		window.addEvent('resize', responsive);        
    }
    
});

})(document.id);
/**
 * @version 3.x
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */

var DJC2ScrollOffset = (typeof window.DJC2ScrollOffset !== 'undefined') ? window.DJC2ScrollOffset : 0;

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
			maxHeight = Math.max(maxHeight, parseInt(jQuery(element).outerHeight()));
		});
		
		jQuery(divs).css('height', maxHeight + 'px');
		
		if (setLineHeight) {
			jQuery(divs).css('line-height', maxHeight + 'px');
		}
	}
}

this.DJCatImageSwitcher = function (){
	jQuery('.djc_image_switcher').each(function(){
		var wrapper = jQuery(this);
		
		var mainimagelink = wrapper.find('[data-target="main-image-link"]');
		var mainimage = mainimagelink.find('img'); 
		var thumbs = wrapper.find('[data-toggle="image-thumbs"]').find('img'); 
		var thumblinks = wrapper.find('[data-toggle="image-thumbs"]').find('a');
		
		if(mainimagelink.length > 0 && mainimage.length > 0) {
			jQuery(mainimagelink).unbind('click');
			jQuery(mainimagelink).click(function(event) {
				event.preventDefault();
				var rel = jQuery(mainimagelink).attr('data-thumb');
				thumblinks.filter('[data-thumb="'+rel+'"]').trigger('click');
				return false;
			});
		}

		if (!mainimage.length || !mainimagelink.length || !thumblinks.length || !thumbs.length) return false;
		
		mainimagelink = jQuery(mainimagelink.first());
		mainimage = jQuery(mainimage.first());
		
		jQuery(thumblinks).each(function(index, thumblink){
			jQuery(thumblink).find('img').click(function(event){
				event.stopPropagation();
				var img = new Image();
				img.onload = (function() {
					mainimage.fadeIn(300);
				});
				mainimage.fadeOut({
					duration: 300,
					start: function() {
						mainimagelink.attr('href', jQuery(thumblink).attr('href'));
						mainimagelink.attr('title', jQuery(thumblink).attr('title'));
						mainimagelink.attr('data-thumb', index);
						
						img.src = jQuery(thumblink).attr('data-large');
						
						mainimage.attr('alt', jQuery(thumblink).attr('title'));
					},
					complete: function(){
						mainimage.attr('src', img.src);
					}
				});
				return false;
			});
		});
	});
};

(function($){
	window.DJCatOrderSummary = function(deliveries, payments) {
		this.delivery_id = 0;
		this.payment_id = 0;
		
		if (deliveries.length) {
			for (var i = 0; i < deliveries.length; i++) {
				if (deliveries[i].checked) {
					this.delivery_id = deliveries[i].value;
				}
			}
		}
		if (payments.length) {
			for (var i = 0; i < payments.length; i++) {
				if (payments[i].checked) {
					this.payment_id = payments[i].value;
				}
			}
		}
		
		this.djc_delivery_extra = $("div.djc_delivery_details");
		this.djc_payment_extra = $("div.djc_payment_details");
		
		var self = this;
		
		if (self.djc_delivery_extra.length) {
			self.djc_delivery_extra.each(function(){
				var element = $(this);
				if (element.attr('data-id') == self.delivery_id) {
					element.css('display', '');
				} else {
					element.css('display', 'none');
				}
			});
		}
		if (self.djc_payment_extra.length) {
			self.djc_payment_extra.each(function(){
				var element = $(this);
				if (element.attr('data-id') == self.payment_id) {
					element.css('display', '');
				} else {
					element.css('display', 'none');
				}
			});
		}
		
		$('#djc_ordersummary').css('opacity', 0.3);
		
		$.ajax({
			url : 'index.php?option=com_djcatalog2&task=cart.getSummary&ts=' + Date.now(),
			data: 'payment=' + self.payment_id + '&' + 'delivery=' + self.delivery_id
		}).done(function(data){
			try {
				var response = $.parseJSON(data);
				
				if (response.data) {
					if (response.error == 0) {
						var prices = response.data;
						$('#djc_summary_gross').html(prices.products);
						$('#djc_summary_delivery').html(prices.delivery);
						$('#djc_summary_payment').html(prices.payment);
						$('#djc_summary_total').html(prices.total);
					} else if (response.error_message) {
						alert(response.error_message);
						if (deliveries.length) {
							deliveries.each(function(){
								$(this).prop('checked', false);
							});
						}
						if (payments.length) {
							payments.each(function(){
								$(this).prop('checked', false);
							});
						}
					}
				}
			} catch (e) {
					
			}
		}).always(function(){
			$('#djc_ordersummary').css('opacity', 1);
		});
	};
})(jQuery);

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
                    scrollTop: jQuery('#contactform').offset().top - DJC2ScrollOffset
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
	                    scrollTop: jQuery('#contactform').offset().top - DJC2ScrollOffset
	                }, 200);
				}
			});
		}
		if (contactformButtonClose.length > 0) {
			contactformButtonClose.click(function(event){
				event.preventDefault();
				
				var isIframe = false;
				try {
					isIframe = window.self !== window.top;
				} catch (e) {
					isIframe = true;
				}
				
				if (isIframe) {
					jQuery(window.parent.document).find('button.mfp-close').trigger('click');
				} else {
					contactform.slideUp(200, function(){
						jQuery('html, body').animate({
		                    scrollTop: jQuery('#djcatalog').offset().top - DJC2ScrollOffset
		                }, 200);
					});
				}
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
		                    scrollTop: advSearchWrapper.offset().top - DJC2ScrollOffset
		                }, 200);
					} else {
						document.cookie = 'djcAdvSearch' + '=' + '0' + ';expires=' + expires.toUTCString();
					}
				}, 200);
			});
		}
	}
};

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

function DJCatSelectCustomerUser(input) {
	var id =jQuery(input).val();
	if (id != 0 && id != '') {
		window.location.href= window.DJC2BaseUrl + '/index.php?option=com_djcatalog2&task=cart.selectCheckoutUser&user_id=' + id;
	}
}

function DJCatUpdateDeliveryMethods(state) {
	var deliveryMethods = jQuery('input.djc_delivery_method');
	var countryId = (state == 1) ? jQuery('.djc_country.billing').val() : jQuery('.djc_country.delivery').val();
	var postCode = (state == 1) ? jQuery('.djc_postcode.billing').val() : jQuery('.djc_postcode.delivery').val().trim();

	deliveryMethods.each(function(){
		var attrs = JSON.parse(jQuery(this).attr('data-options'));
		var validCountry = false;
		var validPostcode = false
		
		if (attrs.countries.length > 0) {
			if (countryId) {
				jQuery.each(attrs.countries, function(i, dCountryId){
					if (dCountryId == countryId) {
						validCountry = true;
					}
				});
			} else {
				validCountry = false;
			}
		} else {
			validCountry = true;
		}

		if (attrs.postcodes.length > 0) {
			if (postCode) {
				if (attrs.postcodes.length == 1) {
					if (attrs.postcodes[0] == postCode) {
						validPostcode = true;
					}
				} else {
					if (postCode >= attrs.postcodes[0] && postCode <= attrs.postcodes[1]) {
						validPostcode = true;
					} else {
						validPostcode = false;
					}
				}
			} else {
				validPostcode = false;
			}
		} else {
			validPostcode =true;
		}
		
		if (!validCountry || !validPostcode) {
			jQuery(this).removeAttr('checked').hide();
			jQuery('label[for="'+this.id+'"]').hide();
			jQuery('input.djc_payment_method').removeAttr('checked');
		} else {
			jQuery(this).show();
			jQuery('label[for="'+this.id+'"]').show();
		}
	});
}

function DJCatUpdatePaymentMethods(state) {
	var paymentMethods = jQuery('input.djc_payment_method');
	var countryId = (state == 1) ? jQuery('.djc_country.billing').val() : jQuery('.djc_country.delivery').val();
	var postCode = (state == 1) ? jQuery('.djc_postcode.billing').val() : jQuery('.djc_postcode.delivery').val().trim();

	paymentMethods.each(function(){
		var attrs = JSON.parse(jQuery(this).attr('data-options'));
		var validCountry = false;
		var validPostcode = false
		
		if (attrs.countries.length > 0) {
			if (countryId) {
				jQuery.each(attrs.countries, function(i, dCountryId){
					if (dCountryId == countryId) {
						validCountry = true;
					}
				});
			} else {
				validCountry = false;
			}
		} else {
			validCountry = true;
		}

		if (attrs.postcodes.length > 0) {
			if (postCode) {
				if (attrs.postcodes.length == 1) {
					if (attrs.postcodes[0] == postCode) {
						validPostcode = true;
					}
				} else {
					if (postCode >= attrs.postcodes[0] && postCode <= attrs.postcodes[1]) {
						validPostcode = true;
					} else {
						validPostcode = false;
					}
				}
			} else {
				validPostcode = false;
			}
		} else {
			validPostcode =true;
		}
		
		if (!validCountry || !validPostcode) {
			jQuery(this).removeAttr('checked').hide();
			jQuery('label[for="'+this.id+'"]').hide();
		} else {
			jQuery(this).show();
			jQuery('label[for="'+this.id+'"]').show();
		}
	});
}


(function($){
	$(document).ready(function(){
		// image switcher, contact form, advanced search module
		DJCatImageSwitcher();
		DJCatContactForm();
		DJCatAdvSearch();
		
		var DJCatQty = {
				initWrapper: function(element) {
					var input = $(element);
					if (input.prop('hasQtyBtns')) return;
					
					var type = input.attr('data-type') == 'flo' ? 'flo' : 'int';
					var minVal = input.attr('data-min') ? input.attr('data-min') : 1;
					var maxVal = input.attr('data-max') ? input.attr('data-max') : 0;

					if (type == 'int') {
						minVal = parseInt(minVal);
						maxVal = parseInt(maxVal);
					} else {
						minVal = parseFloat(minVal);
						maxVal = parseFloat(maxVal);
					}
					
					var allowEmpty = (input.attr('data-allowempty') == '1');
					var step = (typeof input.attr('data-step') != 'undefined') ? input.attr('data-step') : 1;
					var precision = (typeof input.attr('data-precision') != 'undefined') ? input.attr('data-precision') : 0;
					var unit = (typeof input.attr('data-unit') != 'undefined') ? input.attr('data-unit') : '';

					input.prop('qtyCfg', {
						type: type,
						minVal: minVal,
						maxVal: maxVal,
						allowEmpty: allowEmpty,
						step: type == 'int' ? parseInt(step) : parseFloat(step),
						precision: precision,
						unit: unit
					});
					
					input.prop('hasQtyBtns', true);
					
					//this.setMarkup(input);
					this.bindEvents(input);
				},
				
				setMarkup: function(input) {
					var props = input.prop('qtyCfg');
					
					var html1 = '<span data-toggle="dec" class="btn djc_qty_btn djc_qty_dec">-</span>';
					var html2 = '<span data-toggle="inc" class="btn djc_qty_btn djc_qty_inc">+</span>';
					
					input.wrap('<div class="djc_qty input-append input-prepend"></div>');
					input.parent().append('<span class="add-on">'+props.unit+'</span>');
					input.parents('.djc_qty').prepend($(html1)).append($(html2));
				},
				
				bindEvents: function(input) {
					var self = this;
					input.on('keyup', function(){
						self.validate(input, false);
					});
					input.on('change click', function(){
						self.validate(input, true);
					});
					
					var btns = input.parents('.djc_qty').find('.djc_qty_btn');
					
					btns.on('qty:click', function(event){
						var props = input.prop('qtyCfg');
						
						var current = props.type=='int' ? parseInt(input.val()) : parseFloat(input.val());
						
						if (isNaN(current) /*|| current == 0*/) {
							if (props.allowEmpty) {
								input.val('');
							} else {
								input.val(props.minVal);
							}
							return;
						}
						
						var action = $(this).attr('data-toggle');
						if (action != 'inc' && action != 'dec') {
							return false;
						}

						if (action == 'inc') {
							if (props.maxVal > 0) {
								if ( current == props.maxVal || current+props.step > props.maxVal ) {
									current = props.maxVal;
								} else {
									current = parseFloat( (current + props.step).toFixed(props.precision) );
								}
							} else {
								current = parseFloat((current + props.step).toFixed(props.precision));
							}
						} else {
							if ( current == props.minVal || current-props.step < props.minVal ) {
								current = props.minVal;
							} else {
								current = parseFloat( (current - props.step).toFixed(props.precision) );
							}
						};
						input.val(current);
						//self.validate(input, true);
						input.trigger('change');
					});
					
					var interval = null;
					btns.on('mousedown touchstart', function(event){
						event.preventDefault();
						event.stopPropagation();
						var btn = $(this);
						btn.trigger('qty:click');
						interval = setInterval(function(){
							btn.trigger('qty:click');
						}, 600);
					}).on('mouseup mouseleave touchend', function(event){
						clearInterval(interval);
					});
				},
				
				validate: function(input, full) {
					full = (typeof full != 'undefined' && full) ? true : false;
					var props = input.prop('qtyCfg');
					//var value = (props.type == 'int') ? parseInt(input.val()) : parseFloat(input.val());
					var value = input.val();
					var numVal = (props.type == 'int') ? parseInt(value) : parseFloat(value);
					
					if (props.type == 'int') {
						var validNo = new RegExp(/^\d$/);
						var restricted = new RegExp(/[^\d+]/g);
						if (validNo.test(value) == false) {
							value = value.replace(restricted, '');
						}
						numVal = parseInt(value);
					} else {
						var validNo = new RegExp(/^(\d+|\d+\.\d+)$/);
						var semiValidNo = new RegExp(/^\d+|\.$/);
						var wrongDec = new RegExp(/\,/g);
						var restricted = new RegExp(/[^\d+\.]/g);
						
						value = value.replace(wrongDec, ".");
						
						if (validNo.test(value) == false) {
							if (full || semiValidNo.test(value) == false) {
								value = value.replace(restricted, '');
							}
						}
						numVal = parseFloat(value);
					}
					
					if (full) {
						if (numVal < props.minVal) {
							value = props.minVal;
						} else if (props.maxVal > 0 && props.maxVal < numVal) {
							value = props.maxVal;
						}
						
						numVal = parseFloat(value);
						if (props.step > 0.0000) {
							var stepQty = parseFloat(props.minVal.toFixed(props.precision));
							var tmp = stepQty;
							while ( tmp < numVal) {
								stepQty += props.step;
								tmp  = parseFloat(stepQty.toFixed(props.precision));
							}
							value = stepQty;
						}
						
						value = (props.type == 'int') ? parseInt(value) : parseFloat(value).toFixed(props.precision);
					}
					
					input.val(value);
				}
			};

		// producer modals
		$(document).ready(function(){
			$('#djcatalog').on('ajaxFilter:loadItems', function(){
				$(this).find('a[data-toggle="modal"]').each(function(){
					var link = $(this);
					$(link.attr('data-target')).on('show.bs.modal', function() {
						 $('body').addClass('modal-open');
						 var modalBody = $(this).find('.modal-body');
						 modalBody.find('iframe').remove();
						 modalBody.prepend('<iframe class="iframe" src="'+link.attr('data-href')+'" name="'+link.attr('data-modaltitle')+'" height="600px"></iframe>');
					}).on('shown.bs.modal', function() {
						 var modalHeight = $('div.modal:visible').outerHeight(true),
							modalHeaderHeight = $('div.modal-header:visible').outerHeight(true),
							modalBodyHeightOuter = $('div.modal-body:visible').outerHeight(true),
							modalBodyHeight = $('div.modal-body:visible').height(),
							modalFooterHeight = $('div.modal-footer:visible').outerHeight(true),
							padding = this.offsetTop,
							maxModalHeight = ($(window).height()-(padding*2)),
							modalBodyPadding = (modalBodyHeightOuter-modalBodyHeight),
							maxModalBodyHeight = maxModalHeight-(modalHeaderHeight+modalFooterHeight+modalBodyPadding);
						 var iframeHeight = $('.iframe').height();
						 if (iframeHeight > maxModalBodyHeight){
							$('.modal-body').css({'max-height': maxModalBodyHeight, 'overflow-y': 'auto'});
							$('.iframe').css('max-height', maxModalBodyHeight-modalBodyPadding);
						 }
					}).on('hide.bs.modal', function () {
						 $('body').removeClass('modal-open');
						 $('.modal-body').css({'max-height': 'initial', 'overflow-y': 'initial'});
						 $('.modalTooltip').tooltip('destroy');
					});
				});
			});
		});
		
		// product compare
		$([document, $('#djcatalog')]).on('ready ajaxFilter:loadItems', function(){
			var compare_forms = $('form.djc_form_compare');
			if (compare_forms.length > 0) {
				var items = [];

				$.ajax({
					url: window.DJC2BaseUrl + '/index.php?option=com_djcatalog2&task=item.getProductsToCompare',
					type: 'get'
				}).done(function(data){
					var contents;
					try {
						contents = JSON.parse(data);
					} catch(e){
						// do nothing
						return;
					}
					for (var i in contents) {
						if (contents.hasOwnProperty(i)) {
							items.push(contents[i]);
						}
					}
					
					var checkboxes = $('input[name="item_id_chk"]');
					checkboxes.each(function(){
						var val = parseInt($(this).val());
						
						if ( items.indexOf(val) >= 0 ) {
							$(this).attr('checked', 'checked');
						} else {
							$(this).removeAttr('checked');
						}
					});
					
					if (items.length > 1) {
						//$('a.djc_compare_btn').css('display', '');
						$('a.djc_compare_btn').removeAttr('disabled');
					}
				});
				
				compare_forms.each(function(pos) {
					var el = $(this);
					
					var checkboxes = el.find('input[name="item_id_chk"]');
					checkboxes.change(function(){
						var new_value = $(this).is(':checked') ? 'item.addToCompare' : 'item.removeFromCompare';
						
						el.find('input[name="task"]').first().val(new_value);
						el.trigger('submit');
					});
					
					el.on('submit', function(evt){
						
						var post_url = el.attr('action');
						if (post_url.indexOf('?') == -1) {
							post_url = post_url + '?ajax=1';
						} else {
							post_url = post_url + '&ajax=1';
						}
						$.ajax({
							url: post_url,
							type: 'post',
							data: el.serialize()
						}).done(function(data){
							
							var response = JSON.parse(data);
							
							if (typeof response.error == 'undefined') {
								return;
							}
							
							var alertContainer = $('<div />', {'class': 'alert djc_alert djc_compare_alert', html: response.message});
							
							if (response.error) {
								var checkBox = el.find('input[name="item_id_chk"]').first();
								if (checkBox.is(':checked')) {
									checkBox.removeAttr('checked');
								} else {
									checkBox.attr('checked', 'checked');
								}
								alertContainer.addClass('alert-error');
								
								$(document.body).append(alertContainer);
								setTimeout(function(){alertContainer.remove();}, 3000);
							} else {
								alertContainer.addClass('alert-success');
								if ( Object.keys(response.items).length > 1) {
									//$('a.djc_compare_btn').css('display', '');
									$('a.djc_compare_btn').removeAttr('disabled');
								} else {
									//$('a.djc_compare_btn').css('display', 'none');
									$('a.djc_compare_btn').attr('disabled', 'disabled');
								}
							}
							
						}).always(function(){
						});
						
						return false;
					});
				});
				
				compare_forms.css('display', '');
			}
		});
		
		// cart pop-up
		$(document).ready(function(){
			var cart_popup = $('<div id="djc_cart_popup" class="modal hide fade"><div class="modal-body djc_cart_popup">'
				+'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><div></div></div></div>');
			var cart_loader = $('<div id="djc_cart_popup_loader" style="display: none"><span></span></div>');
			
			$(document.body).append(cart_loader);
			$(document.body).append(cart_popup);
		});
		
		// cart quantity change events
		$(document).ready(function(){
			var cartUpdateTimeout;
			
			$('.djc_cart_table input.djc_qty_input').on('change keyup', function(){
				clearTimeout(cartUpdateTimeout);
				var input = $(this);
				if (input.val() != '') {
					cartUpdateTimeout = setTimeout(function(){
						input.parents('form').css('opacity', 0.3);
						input.parents('form').trigger('submit');
					}, 1500);
				}
			});
		});
		
		// checkout page, payments and deliveries
		$(document).ready(function(){
			var djc_deliveries = $("input.djc_delivery_method");
			var djc_delivery_toggle = $('#jform_djcatalog2delivery_delivery_to_billing');
			var djc_delivery_wrapper = $('#djc_delivery_wrapper');
			var djc_delivery_fields = $('#djc_delivery_fields');
			var djc_delivery_toggle_state = 1;
			var djc_payments = $("input.djc_payment_method");
			var djc_payment_labels = $("label.djc_payment_method");
			
			if (djc_delivery_toggle.length > 0){
				djc_delivery_toggle_state = djc_delivery_toggle.value;
			}
			
			if (djc_deliveries.length > 0) {
				djc_deliveries.on("change", function(evt){
					var delivery_id = this.value;
					var use_delivery = $(this).attr('data-shippment');
					
					if (djc_payments.length > 0) {
						djc_payments.each(function(position){
							var element = $(this);
							var label = $(djc_payment_labels[position]);
							var xref = element.attr("data-delivery");
							var valid = false;
							if (xref == "") {
								valid = true;
							} else {
								xref = xref.split(',');
								for (var i=0; i<xref.length; i++) {
									if (xref[i] == delivery_id) {
										valid = true;
									}
								}
							}
		
							if (valid) {
								element.css('display', '');
								label.css('display', '');
							} else {
								element.prop("checked", false);
								element.css('display', 'none');
								label.css('display', 'none');
							}
							element.change;
						});	
					}
					
					setTimeout(function(){
						DJCatOrderSummary(djc_deliveries, djc_payments);
					}, 200);
					
					if (djc_delivery_wrapper.length > 0  && djc_delivery_toggle.length > 0) {
						if (use_delivery == 1) {
							if (djc_delivery_toggle_state == 1) {
								djc_delivery_toggle.value = 1;
							} else {
								djc_delivery_toggle.value = 0;
							}
							djc_delivery_wrapper.css('display', '');
						} else {
							djc_delivery_toggle.value = 1;
							djc_delivery_toggle.trigger('change', djc_delivery_toggle);
							djc_delivery_wrapper.css('display', 'none');
						}
					}
				});
			}
			
			if (djc_payments.length) {
				djc_payments.on("change", function(evt){
					DJCatOrderSummary(djc_deliveries, djc_payments);
				});
			}
			
			if (djc_delivery_toggle.length && djc_delivery_fields.length) {
				djc_delivery_toggle.on('change', function(evt){
					if (this.value == 1) {
						djc_delivery_toggle_state = 1;
						djc_delivery_fields.css('display', 'none');
						djc_delivery_fields.find('input, select, textarea').each(function(){
							$(this).prop('disabled', true);
						});
					} else {
						djc_delivery_toggle_state = 0;
						djc_delivery_fields.css('display', '');
						djc_delivery_fields.find('input, select, textarea').each(function(){
							$(this).prop('disabled', false);
						});
					}
					
					DJCatUpdateDeliveryMethods(djc_delivery_toggle_state);
					DJCatUpdatePaymentMethods(djc_delivery_toggle_state);
				});
			}
			
			if (djc_delivery_toggle.length) {
				djc_delivery_toggle.trigger('change', djc_delivery_toggle);
			}
			
			if (djc_deliveries.length) {
				djc_deliveries.each(function(){
					if (this.checked) {
						$(this).trigger('change', this);
					}
				});
			} else if (djc_payments.length) {
				djc_payments.each(function(){
					if (this.checked) {
						$(this).trigger('change', this);
					}
				});
			}
			
			$('#djc_checkout_form').find('.djc_country, .djc_postcode').on('keyup change', function(event){
				DJCatUpdateDeliveryMethods(djc_delivery_toggle_state);
				DJCatUpdatePaymentMethods(djc_delivery_toggle_state);
			});
		});
		
		//$(document).ready(function(){
		$([document, $('#djcatalog')]).on('ready ajaxFilter:loadItems', function(){
			
			$('input.djc_qty_input').each(function(){
				DJCatQty.initWrapper(this);
			});
			
			// add to cart form handler
			var cart_forms = $('form.djc_form_addtocart');
			if (cart_forms.length > 0) {

				var cart_popup = $('#djc_cart_popup');
				var cart_loader = $('#djc_cart_popup_loader');
				
				$('#djc_cart_popup').on('shown.bs.modal', function () {
					var modalHeight = $('div.modal:visible').outerHeight(true);
					var padding = document.getElementById('djc_cart_popup').offsetTop;
					var offset = ($(window).height()-modalHeight) / 2;
					
					$('#djc_cart_popup').css('top', offset+'px');
				});
				
				cart_forms.each(function(pos) {
					var el = $(this);
					
					if (el.attr('data-noajax') == '1') {
						return;
					} else if (el.hasClass('djc_multi_addtocart')) {
						var submitter = $('form.djc_addtocart_submitter');
						if (submitter.length < 1) {
							return;
						}
						var itemId = el.find('input[name="item_id"]').val();
						el.find('input.djc_qty_input').change(function(){
							var targetInput = submitter.find('input[name="quantity['+itemId+']"]');
							var val = $(this).val();
							if (val != '' && (parseFloat(val) > 0.0000 || parseInt(val) > 0)) {
								if (targetInput.length > 0) {
									targetInput.val($(this).val());
								} else {
									submitter.append($('<input type="hidden" name="quantity['+itemId+']" value="'+$(this).val()+'" />'));
								}
							} else if (targetInput.length) {
								targetInput.remove();
							}
							
							if (submitter.find('input[name^="quantity"]').length > 0) {
								submitter.find('[type="submit"]').removeAttr('disabled');
							} else {
								submitter.find('[type="submit"]').attr('disabled', 'disabled');
							}
						});
					}
					
					el.on('submit', function(evt){
						cart_loader.css('display', 'block');
						
						var post_url = el.attr('action');
						if (post_url.indexOf('?') == -1) {
							post_url = post_url + '?ajax=1';
						} else {
							post_url = post_url + '&ajax=1';
						}
						$.ajax({
							url: post_url,
							type: 'post',
							data: el.serialize()
						}).done(function(data){
							//var response = $.parseJSON(data);
							var response = JSON.parse(data);
							
							cart_popup.find('.modal-body > div').first().html(response.message);
							cart_popup.modal();
							
							if (typeof response.basket_count != 'undefined') {
								$('strong.djc_mod_cart_items_count').each(function(){
									$(this).html(response.basket_count);
								});
								var basket_items = $('.mod_djc2_cart_contents');
								var basket_is_empty = $('.mod_djc2cart_is_empty');
								
								if (basket_items) {
									if (response.basket_count > 0) {
										basket_items.css('display', 'block');
									} else {
										basket_items.css('display', 'none');
									}
								}
								
								if (basket_is_empty) {
									if (response.basket_count > 0) {
										basket_is_empty.css('display', 'none');
									} else {
										basket_is_empty.css('display', 'block');
									}
								}
							}
							
						}).always(function(){
							cart_loader.css('display', 'none');
						});
						
						return false;
					});
				});
			}
		});
		
	});
})(jQuery);

jQuery(window).on('load', function(){
	DJCatMatchBackgrounds();
	
	var tabHash = window.location.hash;
	
	var tabTogglers = jQuery('.djc_tabs li.nav-toggler');
	var tabPanels = jQuery('.djc_tabs .tab-pane');
	
	if (tabTogglers.length) {
		
		tabTogglers.each(function(index){
			var tab = jQuery(this);
			tab.on('click', 'a', function(e){
				e.preventDefault();
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
			
			if (acc.attr('data-collapseid') && ('#' + acc.attr('data-collapseid')) == decodeURIComponent(tabHash)) {
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
						contents.slideUp();
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


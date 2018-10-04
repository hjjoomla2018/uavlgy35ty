/**
 * @version $Id: core.uncompressed.js 53 2016-12-29 11:59:48Z michal $
 * @package DJ-Reviews
 * @copyright Copyright (C) 2014 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 * DJ-Reviews is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Reviews is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Reviews. If not, see <http://www.gnu.org/licenses/>.
 *
 */

!function($, undef) {
	var DJReviewsCore = window.DJReviewsCore = window.DJReviewsCore || {
		init: function(options) {
			var self = this;
			
			this.options=options;
			
			this.url = options.url;
			
			this.current_page = 0;

			this.formId = 'djrv_review_form' + '-' + this.options.object_id;
			this.formAnchorId = 'djrv-your-review' + '-' + this.options.object_id;
			this.listId = 'djrv-reviews-list' + '-' + this.options.object_id;
			this.ratingId = 'djrv-rating-full' + '-' + this.options.object_id;
			this.ratingAvgId = 'djrv-rating-avg' + '-' + this.options.object_id;
			
			this.addButtonClass = 'djrv_add_button';
			this.editButtonClass = 'djrv_edit_button';
			this.deleteButtonClass = 'djrv_delete_button';
			this.closeButtonClass = 'djrv_close_form_button';
			
			this.attachControls();
			
			this.refreshAll(true, false);
		},
		
		attachControls: function() {
			var self = this;
			
			this.reviewForm = $('#' + this.formId);
			this.formAnchor = $('#' + this.formAnchorId);
			this.reviewsList = $('#' + this.listId);
			this.rating = $('#' + this.ratingId);
			this.ratingAvg = $('#' + this.ratingAvgId);
			
			this.addReviewButtons = $.find('.' + this.addButtonClass);
			this.editReviewButtons = $.find('.' + this.editButtonClass);
			this.deleteReviewButtons = $.find('.' + this.deleteButtonClass);
			this.closeReviewButtons = $.find('.' + this.closeButtonClass);
			
			this.paginationLinks = $('#' + this.listId).find('.djrv_pagination a.pagenav');
			
			if (this.paginationLinks.length > 0) {
				$(this.paginationLinks).each(function(pos, link){
					$(link).unbind('click');
					$(link).click(function(event){
						event.preventDefault();
						var page = $(link).attr('data-page');
						if (page) {
							self.tweenOut(self.reviewsList);
							self.getReviews(page, true, true);
						}
					});
				});
			}
			
			if (this.deleteReviewButtons.length > 0) {
				$(this.deleteReviewButtons).each(function(pos, button){
					$(button).unbind('click');
					$(button).click(function(event){
						$(self.formAnchor).hide();
						
						self.tweenOut(self.reviewsList);
						self.tweenOut(self.rating);
						self.tweenOut(self.ratingAvg);
						
						event.preventDefault();
						$.ajax({
							type: 'GET',
							async: true,
							url : $(button).attr('href'),
							data: 'format=raw&ts=' + Date.now()
						}).done(function(response) {
							self.getReviews(self.current_page, true, false);
							self.getRating(false);
							self.getRatingAvg(false);
						});
					});
				});
			}

			if (this.reviewForm.length > 0) {
				if (this.addReviewButtons.length > 0 && this.formAnchor.length > 0) {
					$(this.addReviewButtons).each(function(pos, button) {
						$(button).unbind('click');
						$(button).click(function(event){
							event.preventDefault();
							$(self.formAnchor).hide();
							/*$(self.formAnchor).show();
							
							$('html, body').animate({
						        scrollTop: $(self.formAnchor).offset().top
						    }, 200);*/
							self.loadEditForm(0, true, false);
						});
					});
				}
				
				if (this.closeReviewButtons.length > 0 && this.formAnchor.length > 0) {
					$(this.closeReviewButtons).each(function(pos, button) {
						$(button).unbind('click');
						$(button).click(function(event){
							event.preventDefault();
							$(self.formAnchor).hide();
							$('html, body').animate({
						        scrollTop: $(self.reviewsList).offset().top
						    }, 20);
						});
					});
				}
				
				$('#' + self.formAnchorId + ' .djrv_modal-backdrop').unbind('click');
				$('#' + self.formAnchorId + ' .djrv_modal-backdrop').click(function(event){
					event.preventDefault();
					$(self.formAnchor).hide();
					$('html, body').animate({
				        scrollTop: $(self.reviewsList).offset().top
				    }, 20);
				});
				
				if (this.editReviewButtons.length > 0 && this.formAnchor.length > 0) {
					$(this.editReviewButtons).each(function(pos, button) {
						$(button).unbind('click');
						$(button).click(function(event){
							event.preventDefault();
							$(self.formAnchor).hide();
							self.tweenOut(self.reviewsList);
							self.tweenOut(self.rating);
							self.tweenOut(self.ratingAvg);
							self.loadEditForm($(button).attr('data-id'), true, false);
						});
					});
				}
				
				this.reviewForm.submit(function(event){
					event.preventDefault();
					var formData =  self.reviewForm.serialize();
					formData += '&format=json&ts=' + Date.now();
					
					self.tweenOut(self.reviewsList);
					self.tweenOut(self.reviewForm);
					self.tweenOut(self.rating);
					self.tweenOut(self.ratingAvg);
					
					$.ajax({
						type: 'POST',
						async: false,
						url : self.url,
						data : formData
					}).done(function(response) {
						var error = false;
						var resp;
						try {
							resp =  jQuery.parseJSON(response);
						} catch (e) {
							resp = {
								status: 0
							};
							error = true;
							alert(response);
						}
						
						if(resp.status==1 && !error){
							if (resp.message != '') {
								alert(resp.message);
							}
							$(self.formAnchor).hide();
							/*self.getReviews(0, false, true);
							self.getRating(false);
							self.getRatingAvg(false);*/
							self.refreshAll(true, true);
						} else if (!error) {
							alert(resp.error_message);
							self.tweenIn(self.reviewsList);
							self.tweenIn(self.reviewForm);
							self.tweenIn(self.rating);
							self.tweenIn(self.ratingAvg);
							
						} else {
							$(self.formAnchor).hide();
							self.refreshAll(false, true);
						}
						return;
					}).fail(function(xhr, status, error) {
						if (xhr.status == 403) {
							return false;
						} else if (xhr.status == 401) {
							return false;
						} else if (xhr.status == 400) {
							return false;
						}
					});
					
				});
			}
		},
		
		loadEditForm: function(reviewId, doScroll, async) {
			var self = this;
			
			var captcha = self.options.captcha_key;
			if (captcha) {
				captcha = '&captcha='+captcha;
			}
			
			var formVars = 'option=com_djreviews&view=reviewform&format=raw&id=' + reviewId + captcha + '&ts=' + Date.now();
			
			if (reviewId == 0) {
				formVars += '&object_id=' + self.options.object_id; 
			}
			
			$.ajax({
				type: 'GET',
				async: async,
				url : self.url,
				data : formVars
			}).done(function(response) {
				$(self.formAnchor).replaceWith(response);
				self.addToolTips();
				self.attachControls();
				
				if (captcha) {
					self.initRecaptcha();
				}
				
				if (typeof JFormValidator != undef) {
					try {
						document.formvalidator = new JFormValidator();
						
						var submitBtn = $(self.formAnchor).find('button.validate');
						submitBtn.click(function(event){
							event.stopPropagation();
							if (document.formvalidator.isValid($('#' + self.formId))) {
								return true;
							} else {
								var msgCont = $('#system-message-container');
								if (msgCont.length > 0) {
									var rvMsgCont = $('#djrv_msg-' + self.options.object_id);
									rvMsgCont.html(msgCont.html());
									msgCont.html(' ');
								} else {
									alert(self.options.lang.invalid_form);
								}
								return false;
							}
						});
					} catch(e) {
						//do nothing
					}
				}
				
				if (doScroll) {
					$(self.formAnchor).show();
					$('#' + self.formAnchorId + ' .djrv_modal-backdrop').addClass('in');
					$('#' + self.formAnchorId + ' .djrv_modal').addClass('in');
					self.addToolTips();
					$('html, body').animate({
				        scrollTop: $(self.formAnchor).offset().top
				    }, 20);
					self.tweenIn(self.reviewsList);
					self.tweenIn(self.rating);
					self.tweenIn(self.ratingAvg);
				}
				return;
			}).fail(function(xhr, status, error) {
				if (xhr.status == 403) {
					return false;
				} else if (xhr.status == 401) {
					return false;
				} else if (xhr.status == 400) {
					return false;
				}
			});
		},
		
		getReviews: function(page, async, doScroll) {
			var self = this;
			
			if (page == undef || !page) {
				page = 0;
			}
			
			self.current_page = page;
			
			var requestVars  ='option=com_djreviews&view=reviewslist&format=raw&id=' + self.options.object_id + '&limitstart=' + page + '&ts=' + Date.now();
			
			$.ajax({
				type: 'GET',
				async: async,
				url : self.url,
				data : requestVars
			}).done(function(response) {
				$(self.reviewsList).replaceWith(response);
				self.addToolTips();
				self.attachControls();
				
				self.tweenIn(self.reviewsList);
				if (doScroll) {
					$('html, body').animate({
				        scrollTop: $(self.reviewsList).offset().top
				    }, 20);
				}
				return;
			}).fail(function(xhr, status, error) {
				if (xhr.status == 403) {
					return false;
				} else if (xhr.status == 401) {
					return false;
				} else if (xhr.status == 400) {
					return false;
				}
			});
		},
		
		getRating: function(async) {
			var self = this;
			
			var requestVars  ='option=com_djreviews&view=rating&format=raw&id=' + self.options.object_id + '&ts=' + Date.now();
			
			$.ajax({
				type: 'GET',
				async: async,
				url : self.url,
				data : requestVars
			}).done(function(response) {
				$(self.rating).replaceWith(response);
				self.addToolTips();
				self.tweenIn(self.rating);
				
				return;
			}).fail(function(xhr, status, error) {
				if (xhr.status == 403) {
					return false;
				} else if (xhr.status == 401) {
					return false;
				} else if (xhr.status == 400) {
					return false;
				}
			});
		},
		
		getRatingAvg: function(async) {
			var self = this;
			
			if (self.ratingAvg.length == 0) {
				return;
			}
			
			var requestVars  ='option=com_djreviews&view=rating&layout=avg&format=raw&id=' + self.options.object_id + '&ts=' + Date.now();
			
			$.ajax({
				type: 'GET',
				async: async,
				url : self.url,
				data : requestVars
			}).done(function(response) {
				$(self.ratingAvg).replaceWith(response);
				self.addToolTips();
				self.tweenIn(self.ratingAvg);
				return;
			}).fail(function(xhr, status, error) {
				if (xhr.status == 403) {
					return false;
				} else if (xhr.status == 401) {
					return false;
				} else if (xhr.status == 400) {
					return false;
				}
			});
		},
		
		refreshAll: function(async, doScroll) {
			this.getRating(async);
			this.getRatingAvg(async);
			this.getReviews(0, async, doScroll);
			this.loadEditForm(0, false, true);
		},
		
		tweenIn: function(element) {
			if ($(element).length > 0) {
				$(element).css({'opacity': '1'});
			}
		},
		
		tweenOut: function(element) {
			if ($(element).length > 0) {
				$(element).css({'opacity': '0.3'});
			}
		},
		
		addToolTips: function() {
			 /*$('.djrv_tooltip').each(function() {
				var title = $(this).attr('title');
				if (title) {
					var parts = title.split('::', 2);
					$(this).data('tip:title', parts[0]);
					$(this).data('tip:text', parts[1]);
				}
			});
			var JTooltips = new Tips($('.djrv_tooltip').get(), {"maxTitleChars": 50,"fixed": false});
			*/

			$(".djrv_tooltip").popover({"html": true,"trigger": "hover focus","container": "body"});
		},
		
		initRecaptcha : function() {
			var options;
			var item = document.getElementById('djrv_captcha');
			
			if (!item) {
				return;
			}
			
			options = item.dataset ? item.dataset : {
				sitekey: item.getAttribute('data-sitekey'),
				theme:   item.getAttribute('data-theme'),
				size:    item.getAttribute('data-size')
			};
			grecaptcha.render(item, options);
		}
	}
}(jQuery);
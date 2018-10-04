(function($){
	"use strict";
	
	var DJCPLUpload = function (settings, data) {
		this.settings = $.extend( {}, this.defaults, settings );
		this.setup();
	}
	
	DJCPLUpload.prototype = {
		constructor: DJCPLUpload,
		settings: {},
		
		defaults: {
			debug: false,
			runtimes: 'html5,flash,silverlight,html4',
			max_size: '10mb',
			extensions: 'jpg,png,zip,rar',
			multiple: false,
			sortable: true,
			download: true,
			preview: false,
			caption: true,
			required: false,
			drop_element: null,
			chunk_size: '1024kb',
			limit: 1,
			url: '',
			download_url: '',
			preview_url: '',
			img_w: 80,
			img_h: 40
		},
		
		lang: {
			DOWNLOAD_BTN: 'Download',
			LIMIT_REACHED: 'Limit of files has been reached. Please first remove the files which you want to replace.'
		},
		
		uploader: null,
		total: 0,
		
		setup: function() {
			var self = this;

			this.uploader = new plupload.Uploader({
				runtimes : self.settings.runtimes,
				browse_button : self.settings.browse_button, 
				container: self.settings.container,
				url : self.settings.url,
				flash_swf_url : self.settings.moxie_swf,
				silverlight_xap_url : self.settings.moxie_xap,
				drop_element: self.settings.browse_button,
				unique_names : true,
				dragdrop: true,
				chunk_size: self.settings.chunk_size,
				
				filters : {
					max_file_size : self.settings.max_size,
					mime_types: [
						{title : "Allowed files", extensions : self.settings.extensions}
					]
				},
			
				init: {
					PostInit: function() {
						$('#' + self.settings.file_list).html('');
						
						var currentValue = $('#' + self.settings.id).val();
						if (currentValue != '') {
							try {
								var data = JSON.parse(currentValue);
								$(data).each(function(i,file){
									
									if (file.id == 0) {
										file.id = file.fullname.replace(/\./, '-');
									}
									
									self.pushFile(file);
									$('#' + file.id).find('.djcupload_in').append($(self.prepareFileWrapper(file, false, $('#' + file.id))));
									$('#' + file.id).attr('data-filedata', JSON.stringify(file));
									
									self.bindEvents($('#' + file.id));
								});
							} catch(e) {
								// do nothing
							}
						}
			
						/*$('#' + self.settings.upload_button).click(function() {
							self.uploader.start();
							return false;
						});*/
					},
			
					FilesAdded: function(up, files) {
						if (self.total + files.length > self.settings.limit && self.settings.limit > 0) {
							var remaining = self.settings.limit - self.total;
							var toRemove = files.length - remaining;
							
							if (toRemove > 0 && files.length > 0){
								for (var i = files.length-1; i >= 0; i--) {
									if (toRemove <= 0) {
										//break;
										self.pushFile(files[i]);
									} else {
										up.removeFile(files[i]);					
										toRemove--;
									}
								}		
							}
							
							self.showError(self.lang.LIMIT_REACHED);
						} else {
							plupload.each(files, function(file) {
								self.pushFile(file);
							});
						}
						
						this.start();
					},
					
					FileUploaded: function(up, file, info) {
						self.completeFileContainer(file);
					},
			
					UploadProgress: function(up, file) {
						self.updateFileContainer(file);
					},
					
					Error: function(up, err) {
						var message = err.message;
						
						if (typeof err.response != 'undefined') {
							var response = JSON.parse(err.response);
							
							if (typeof response.error != 'undefined' && typeof response.error.message != 'undefined') {
								message = response.error.message;
							}
						}
						
						if (typeof err.file != 'undefined') {
							self.removeUpFile(err.file);
							//console.log(err.file);
						}
						
						//var errMsg = (err.status || err.code) + ': ' + message;
						// CUSTOM
						var errMsg = message;
						self.showError(errMsg);
					},
								
					UploadComplete: function(up, file, undef) {
						jQuery(document).trigger('djcplupload_' + self.settings.id, [up, file, undef]);
					}
				}
			});
			
			if (this.settings.sortable) {
				$('#' + this.settings.file_list).sortable({
					axis: false,//'y',
					cursor: 'move',
					items: '.djcupload_file',
					handle: '.djcupload_in',
					cancel: 'a,.btn,input',
					update: function(event, ui) { self.setValue(); }
					
				});
			}
			
			this.uploader.init(); 
		},
		
		clearFiles: function() {
			$('#' + this.settings.file_list).html('');
		},
		
		pushFile: function(file) {
			var self = this;
			self.total++;
			
			var htmlElm = self.prepareFileContainer(file);
			
			var fileDiv = $(htmlElm);
			
			fileDiv.find('[data-action="remove"]').click(function(e){
				e.preventDefault();
				fileDiv.remove();
				self.total--;
				self.setValue();
			});
			$('#' + this.settings.file_list).append(fileDiv);
		},
		
		removeUpFile: function(file) {
			var self = this;
			$('#' + file.id).remove();
			self.total--;
			self.setValue();
		},
		
		setValue: function() {
			var jsonVal = [];
			var self = this;
			$('#' + this.settings.file_list).find('.djcupload_file').each(function(){
				var $this = $(this);
				var data = $this.attr('data-filedata');
				if (data) {
					data = JSON.parse(data);
					data.caption = $this.find('input[name="'+self.settings.id + '_file_caption[]"]').val();
					jsonVal.push(data);
				}
			});
			var value = JSON.stringify(jsonVal);
			if (value == '[]') {
				value = '';
			}
			$('#' + this.settings.id).val(value);
		},
		
		prepareFileContainer: function(file) {
			this.debug('file container prepare:', file);
			
			var wrapper_class = this.settings.sortable  ? 'djcupload_in sortable' : 'djcupload_in';
			
			var html = '<div id="' + file.id + '" class="djcupload_file"><div class="'+wrapper_class+'">';
			html += '<a href="#" data-action="remove" class="btn btn-mini">&times;</a> ';
			html += '<span class="djcupload_file_name">' + (file.name || file.fullname);
			html += ' (' + plupload.formatSize(file.size) + ')';
			html += ' <b></b></span>';
			html += '</div></div>';
			
			return html;
		},
		
		updateFileContainer: function(file) {
			this.debug('file status updated:', file);
			
			$('#' + file.id).find('b').first().html('<span>' + file.percent + '%</span>');
		},
		
		completeFileContainer: function(file) {
			this.debug('file upload completed:', file);
			
			var data = {
				id: file.id,
				file_id: 0,
				fullname: file.target_name,
				caption: file.name.replace(/\.[^.]+$/, ""),
				url: this.settings.preview_url + file.target_name,
				size: file.size
			};
			
			//$('#' + file.id).html('');
			$('#' + file.id).find('.djcupload_in').append($(this.prepareFileWrapper(data, true, $('#' + file.id))));
			$('#' + file.id).attr('data-filedata', JSON.stringify(data));
			
			this.bindEvents($('#' + file.id));
			
			this.setValue();
		},
		
		prepareFileWrapper: function(data, isNew, parent) {
			var input = '<input type="hidden" value="'+ data.file_id +'" name="'+this.settings.id+'_file_id[]" />';
			input += '<input type="hidden" value="'+ data.fullname +'" name="'+this.settings.id+'_file_name[]" />';
			
			if (this.settings.caption) {
				input += '<input type="text" value="'+ data.caption +'" name="'+this.settings.id+'_file_caption[]" />';
			} else {
				input += '<input type="hidden" value="'+ data.caption +'" name="'+this.settings.id+'_file_caption[]" />';
			}
			
			if (this.settings.preview) {
				//parent.find('.djcupload_in').css('background-image', 'url("'+this.settings.root_path+'/'+data.url+'")');
				parent.find('.djcupload_in').css('background-image', 'url("'+data.url+'")');
				//input += '<div class="djcupload_preview" style="width: '+this.settings.img_w+'px; height: '+this.settings.img_h+'px; background: url(\''+data.url +'\') center center / cover no-repeat;"></div>';
			}
			
			if (this.settings.download && !isNew && data.file_id != 0) {
				if (typeof data.download_url != 'undefined') {
					input += '<a class="djcupload_download" href="'+ data.download_url + '" target="_blank">'+ this.lang.DOWNLOAD_BTN +'</a>';
				} else {
					input += '<a class="djcupload_download" href="'+ this.settings.download_url + data.file_id + '" target="_blank">'+ this.lang.DOWNLOAD_BTN +'</a>';
				}
			}
			
			return input;
		},
		
		bindEvents: function(element) {
			var self = this;
			element.find('input[type="text"]').on('change', function(){
				self.setValue();
			});
		},
		
		showError: function(msg) {
			var msgContainer = $('<p />', {'class': 'djcupload_err_msg alert alert-error'});
			var closeBtn = $('<a />', {'class': 'close pull-right', 'href': '#', 'html': '&times;'});
			closeBtn.click(function(e){
				e.preventDefault();
				$(this).parents('.djcupload_err_msg').remove();
			});
			
			msgContainer.text(msg);
			msgContainer.append(closeBtn);
			
			$('#' + this.settings.console).append(msgContainer);
		},
		
		debug: function(msg, obj) {
			if (this.settings.debug) {
				console.log(msg);
				console.log(obj);
			}
		}
	};
	
	window.DJCPLUpload = DJCPLUpload;
})(jQuery);
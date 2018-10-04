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

(function($){
var DJFrontpage = function (options) {
	return this.initialize(options);
};

DJFrontpage.prototype = {
	constructor: DJFrontpage,
	settings: {
		moduleId: 0,
		pagstart:0,
		baseurl: 'null',
		url : '',
		showcategorytitle: 0,
		showtitle: 1,
		linktitle: 1,
		showproducer: 0,
		showprice: 0,
		showpagination: 0,
		order: 0,
		featured_only: 0,
		featured_first: 0,
		columns: 1,
		rows: 3,
		allcategories: 1,
		categories: '',
		mainimage: 'medium',
		trunc: 0,
		trunclimit: 0,
		effectduration: 300,
		showreadmore: 1,
		modal: 1,
		readmoretext: '',
		largewidth: 400,
		largeheight: 240,
		largecrop: 1,
		smallwidth: 90,
		smallheight: 70,
		smallcrop: 1,
		limit: 0
	},
	
	initialize: function(options) {
		this.settings = $.extend({}, this.settings, options);
		if (!this.settings.baseurl) return false;
		this.buildUrl();
		
		this.modulewrapper = $('#djf_mod_' + this.settings.moduleId);
		this.largeimgcontainer = ('#djfimg_' + this.settings.moduleId); 
		this.textcontainer = ('#djftext_' + this.settings.moduleId); 
		this.thumbscontainer = ('#djfgal_' + this.settings.moduleId); 
		this.categorycontainer = ('#djfcat_' + this.settings.moduleId); 
		this.paginationcontainer = ('#djfpag_' + this.settings.moduleId); 
		this.loadPage(0);
	},
	
	buildUrl: function() {
		this.settings.url = this.settings.baseurl;
		this.settings.post = 
			  'moduleId='	+	this.settings.moduleId 
			+ '&scattitle='	+	this.settings.showcategorytitle 
			+ '&stitle='	+	this.settings.showtitle
			+ '&sproducer='	+	this.settings.showproducer
			+ '&sprice='	+	this.settings.showprice
			+ '&ltitle='	+	this.settings.linktitle
			+ '&spag='		+	this.settings.showpagination
			+ '&orderby='	+	this.settings.order
			+ '&orderdir='	+	this.settings.orderdir
			+ '&featured_only='	+	this.settings.featured_only
			+ '&featured_first='	+	this.settings.featured_first
			+ '&cols='		+	this.settings.columns
			+ '&rows='		+	this.settings.rows
			+ '&catsw='		+	this.settings.allcategories
			+ '&categories='+	this.settings.categories
			+ '&trunc='		+	this.settings.trunc
			+ '&trunclimit='+	this.settings.trunclimit
			+ '&modal='+	this.settings.modal
			+ '&showreadmore='+	this.settings.showreadmore
			+ '&readmoretext='+	this.settings.readmoretext
			+ '&pagstart='	+	this.settings.pagstart
			+ '&largewidth='	+	this.settings.largewidth
			+ '&largeheight='	+	this.settings.largeheight
			+ '&largecrop='		+	this.settings.largecrop
			+ '&smallwidth='	+	this.settings.smallwidth
			+ '&smallheight='	+	this.settings.smallheight
			+ '&smallcrop='		+	this.settings.smallcrop
			;
	},
	
	ajaxResponse: function(response) {
		return this.loadPageContent(response);
		
		/*var xmltext = response;
		var xmlobject = null;
		try //Internet Explorer
		{
			xmlobject = new ActiveXObject("Microsoft.XMLDOM");
			xmlobject.async = "false";
			xmlobject.loadXML(xmltext);
		} 
		catch (e) {
			try //Firefox, Mozilla, Opera, etc.
			{
				xmlobject = (new DOMParser()).parseFromString(xmltext, "text/xml");
			} 
			catch (e) {
				alert(e.message);
			}
		}
		
		return this.loadPageContent(xmlobject);*/
	},
	
	loadPage: function(page) {
		this.settings.pagstart = (page) ? page : 0;
		this.buildUrl();
		
		var self = this;
		
		if(self.ajax && self.ajax.readyState != 4){
            self.ajax.abort();
        }
		
		self.ajax = $.ajax({
		    url: self.settings.url,
		    method: 'post',
		    dataType: 'xml',
		    encoding: 'utf-8',
		    data: self.settings.post
		}).done(function(resp) {
			self.ajaxResponse(resp);
		});
	},
	
	loadPageContent: function (xmlobject){
		var self = this;
		
		xmlobject = $(xmlobject);
		
		var contents = xmlobject.find("contents")[0];
		var content = $(contents).find("content");
		var thumbs = $(contents).find("thumb");
		if ($(contents).find("pagination").length) {
			if ($(contents).find("pagination").first()&& this.settings.showpagination > 0) {
				$(this.paginationcontainer).html($(contents).find("pagination").first().text());
			}
		}
		
		this.data = [];
		for (var i = 0; i < content.length; i++) {
			var data = $(content[i]);
			this.data[i] = {};
			this.data[i].text = data.find("text").first().text();
			this.data[i].image = data.find("image").first().text();
			this.data[i].src = data.find("src").first().text();
			if (this.settings.showcategorytitle == 1) {
				this.data[i].category = data.find("category").first().text();				
			}
		}
		
		this.thumbnails = [];
		
		$(self.thumbscontainer).animate({opacity: 0}, self.settings.effectduration);
		setTimeout(function() {
			for (var i = 0; i < self.settings.rows * self.settings.columns; i++) {
			$('#djfptd_' + self.settings.moduleId + '_' + i).html('');
			}
			for (var i = 0; i < thumbs.length; i++) {
				self.thumbnails[i] = $(thumbs[i]).text();
				$('#djfptd_' + self.settings.moduleId + '_' + i).html(self.thumbnails[i]);
			}
			$(self.thumbscontainer).animate({opacity: 1}, self.settings.effectduration);
		}, self.settings.effectduration);
		
		this.loadItem(0);
	},
	
	loadItem: function(id) {
		if (this.data[id]) {
			this.hideItem(id);
			this.showItem(id);
		} else {
			this.modulewrapper.css('display', 'none');
		}
	},
	hideItem : function(id) {
		var self = this;
		self.modulewrapper.find('.djf_img').animate({opacity: 0}, self.settings.effectduration);
		$(self.textcontainer).animate({opacity: 0}, self.settings.effectduration);
		if (this.settings.showcategorytitle == 1) {
			$(self.categorycontainer).animate({opacity: 0}, self.settings.effectduration);
		}
	},
	
	showItem : function(id) {
		var image = new Image();
		
		var self = this;
		
		image.onload =  function(){
			$(self.largeimgcontainer).html('');
			$(self.largeimgcontainer).append(image);
			$(self.largeimgcontainer).attr("href", self.data[id].src);
			
			self.modulewrapper.find('.djf_img').animate({opacity: 1}, self.settings.effectduration);
			
			$(self.textcontainer).html(self.data[id].text);
			$(self.textcontainer).animate({opacity: 1}, self.settings.effectduration);
			
			if (self.settings.showcategorytitle == 1) {
				$(self.categorycontainer).html(self.data[id].category);
				$(self.categorycontainer).animate({opacity: 1}, self.settings.effectduration);
			}
			
		};
		
		image.onerror =  function(){
			$(self.largeimgcontainer).html('');
			
			$(self.textcontainer).html(self.data[id].text);
			$(self.textcontainer).animate({opacity: 1}, self.settings.effectduration);
			
			if (self.settings.showcategorytitle == 1) {
				$(self.categorycontainer).html(self.data[id].category);
				$(self.categorycontainer).animate({opacity: 1}, self.settings.effectduration);
			}
		};
		
		setTimeout(function(){
			image.src = self.data[id].image;
		}, self.settings.effectduration);
	}
};

window.DJFrontpage = DJFrontpage;

})(jQuery);

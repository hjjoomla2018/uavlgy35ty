/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

//Set Module's Height script

function setModulesHeight() {
	var regexp = new RegExp("_mod([0-9]+)$");

	var jmmodules = jQuery(document).find('.jm-module') || [];
	if (jmmodules.length) {
		jmmodules.each(function(index,element){
			var match = regexp.exec(element.className) || [];
			if (match.length > 1) {
				var modHeight = parseInt(match[1]);
				jQuery(element).find('.jm-module-in').css('height', modHeight + 'px');
			}
		});
	}
}

//megamenu backdrop

function megamenuBackdrop() {

	var megamenu = jQuery('#jm-top-menu .dj-megamenu li.dj-up.parent'),
		wrapper = jQuery('#jm-wrapper'),
		backdrop = jQuery('<div class="jm-backdrop"></div>'),
		modlang = jQuery('#jm-top-menu .mod-languages form'),
		obname,
		observer1,
		observer2;

	function backdropObserver(obname,observed,state,element) {
		var obname = new MutationObserver(function () {
			if (typeof element === "undefined" || element === null) { 
			  element = jQuery('#jm-top-menu .mod-languages .chzn-container');
			}
			if (element.hasClass(state)) {
				backdrop.appendTo(wrapper).fadeIn(300);
			} else {
				wrapper.find(backdrop).fadeOut(300, function() { jQuery(this).remove(); });
			}
		});
		observed.each(function() {
			obname.observe(this, {
				attributes: true,
				childList: true,
				characterData: false,
				subtree: true
			})
		});
	}

	backdropObserver(observer1,megamenu,'hover',megamenu);
	backdropObserver(observer2,modlang,'chzn-with-drop');

}

// change position of description

function descPosition() {
	var description = jQuery('#djcatalog.djc_item .djc_fulltext > .container-fluid');
	var topwrap = jQuery('#djcatalog.djc_item .djc_wrap_bottom');
	var container = jQuery('<div class="djc_description"></div>');
	if (description.length > 0) {
		topwrap.before(container);
		container.append(description);
	}
}

jQuery(document).ready(function(){
	setModulesHeight();
	megamenuBackdrop();
	descPosition();
	//reviews
	jQuery('.jm-before-content .djrv_rating_avg.djreviews').appendTo('.item-page .article-info').wrap("<dd class='jm-rev'></dd>").closest('.article-info').addClass('dj-rev');

});





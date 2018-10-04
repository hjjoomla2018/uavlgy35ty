// initialization of magnific popup for all album instances
!function($){

$(document).ready(function(){
	$('.dj-slides').each(function() {
		
		$(this).magnificPopup({
	        delegate: '.dj-slide-link', // the selector for gallery item
	        type: 'image',
	        mainClass: 'mfp-img-mobile',
	        gallery: {
	          enabled: true
	        },
			image: {
				verticalFit: true,
				titleSrc: 'data-title'
			},
			iframe: {
				patterns: {
					youtube: null,
					vimeo: null,
					link: {
						index: '/',
						src: '%id%'
					}
				}
			}
	    });
	});
	
	$('.dj-slide-popup').each(function(){
		
		$(this).magnificPopup({
	        delegate: '', // the selector for gallery item
	        type: 'iframe',
	        mainClass: 'mfp-slide-popup',
			iframe: {
				patterns: {
					youtube: null,
					vimeo: null,
					link: {
						index: '/',
						src: '%id%'
					}
				}
			},
			callbacks: {
				close: function(){
					var top = $(window).scrollTop();
					window.location.hash='';
					$(window).scrollTop(top);
				}
			}
	    });
		
	});
});

}(jQuery);
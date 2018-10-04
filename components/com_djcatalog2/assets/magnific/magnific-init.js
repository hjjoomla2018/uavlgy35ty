jQuery(document).ready(function(){
	jQuery('.djc_images').each(function() {
		jQuery(this).magnificPopup({
			delegate: 'a.djimagebox', // the selector for gallery item
			type: 'image',
			mainClass: 'mfp-img-mobile',
			gallery: {
			  enabled: true
			},
			image: {
				verticalFit: true
			}
		});
	});
	jQuery('.djc_items,.djc_items_table,.djc_compare_items').each(function() {
		jQuery(this).magnificPopup({
			delegate: 'a.djimagebox', // the selector for gallery item
			type: 'image',
			mainClass: 'mfp-img-mobile',
			gallery: {
			  enabled: true
			},
			image: {
				verticalFit: true
			}
		});
	});
	jQuery('.djc_subcategories').each(function() {
		jQuery(this).magnificPopup({
			delegate: 'a.djimagebox', // the selector for gallery item
			type: 'image',
			mainClass: 'mfp-img-mobile',
			gallery: {
			  enabled: true
			},
			image: {
				verticalFit: true
			}
		});
	});
	
	jQuery(this).magnificPopup({
		delegate: 'a.legacy-img-modal', // the selector for gallery item
		type: 'image',
		mainClass: 'mfp-img-mobile',
		gallery: {
		  enabled: false
		},
		image: {
			verticalFit: true
		}
	});
	
	var frameGalleries = ['a.djc_item_preview', 'a.djc_item_preview_link', 'a.djc_item_preview_img'];
	for (var i = 0; i < frameGalleries.length; i++) {
		jQuery(frameGalleries[i]).magnificPopup({
			//delegate: 'a.djc_item_preview', // the selector for gallery item
			type: 'iframe',
			mainClass: 'mfp-frame-preview',
			gallery: {
			  enabled: true
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
			},
			callbacks: {
				close: function(){
				}
			}
		});
	}
	
	jQuery('a.djc_item_contact').magnificPopup({
		type: 'iframe',
		mainClass: 'mfp-frame-preview',
		gallery: {
		  enabled: false
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
		},
		callbacks: {
			close: function(){
			}
		}
	});
	
	jQuery('#djcatalog').on('ajaxFilter:loadItems', function(event){
		
		jQuery('.djc_images').each(function() {
			jQuery(this).magnificPopup({
				delegate: 'a.djimagebox', // the selector for gallery item
				type: 'image',
				mainClass: 'mfp-img-mobile',
				gallery: {
				  enabled: true
				},
				image: {
					verticalFit: true
				}
			});
		});
		
		jQuery('.djc_subcategories').each(function() {
			jQuery(this).magnificPopup({
				delegate: 'a.djimagebox', // the selector for gallery item
				type: 'image',
				mainClass: 'mfp-img-mobile',
				gallery: {
				  enabled: true
				},
				image: {
					verticalFit: true
				}
			});
		});
		
		jQuery('.djc_items,.djc_items_table').each(function() {
			jQuery(this).magnificPopup({
				delegate: 'a.djimagebox', // the selector for gallery item
				type: 'image',
				mainClass: 'mfp-img-mobile',
				gallery: {
				  enabled: true
				},
				image: {
					verticalFit: true
				}
			});
		});
		for (var i = 0; i < frameGalleries.length; i++) {
			jQuery('#djcatalog ' + frameGalleries[i]).magnificPopup({
				//delegate: 'a.djc_item_preview', // the selector for gallery item
				type: 'iframe',
				mainClass: 'mfp-frame-preview',
				gallery: {
				  enabled: true
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
				},
				callbacks: {
					close: function(){
					}
				}
			});
		}
		
		jQuery('a.djc_item_contact').magnificPopup({
			type: 'iframe',
			mainClass: 'mfp-frame-preview',
			gallery: {
			  enabled: false
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
			},
			callbacks: {
				close: function(){
				}
			}
		});
		
	});
});


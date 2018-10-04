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
	$(document).ready(function() {
		var djItemPriceInput = $('#jform_price');
		djItemPriceInput.on('keyup change click', function(){
			djValidatePrice(djItemPriceInput);
		});
		
		var djItemSpecialPriceInput = $('#jform_special_price');
		djItemSpecialPriceInput.on('keyup change click', function(){
			djValidatePrice(djItemSpecialPriceInput);
		});
		
		
		if ($('#jform_tax_rule_id').length > 0) {
			$('#jform_tax_rule_id').change(function(evt) {
				$('#jform_tax_rule_id').trigger("liszt:updated");
				djValidatePrice(djItemPriceInput);
				djValidatePrice(djItemSpecialPriceInput);
			});
			
			$('#jform_tax_rule_id').trigger('change');
		}
		
		var djFieldGroup = $('#jform_group_id');
		djFieldGroup.change(function(){
			djRenderForm();
		});
		
		djRenderForm();
	});

	function djValidatePrice(priceInput) {
			//var r = new RegExp("\,", "i");
			//var t = new RegExp("[^0-9\,\.]+", "i");
			//priceInput.setProperty('value', priceInput.getProperty('value').replace(r, "."));
			//priceInput.setProperty('value', priceInput.getProperty('value').replace(t, ""));
		
		
			var price = priceInput.val();
			
			// valid format
			var valid_price = new RegExp(/^(\d+|\d+\.\d+)$/);
			
			// comma instead of dot
			var wrong_decimal = new RegExp(/\,/g);
			
			// non allowed characters
			var restricted = new RegExp(/[^\d+\.]/g);
			
			// replace comma with a dot
			price = price.replace(wrong_decimal, ".");
			
			if (valid_price.test(price) == false) {
				// remove illegal chars
				price = price.replace(restricted, '');
			}
			
			if (valid_price.test(price) == false) {
				// too many dots in here
				parts = price.split('.');
				if (parts.length > 2 ) {
					price = parts[0] + '.' + parts[1];
				}
			}
			
			priceInput.val(price);
			
			taxInput = $('#' + priceInput.attr('id') + '_tax');
			if(!taxInput.length) {
				return;
			}
			
			rateInput = $('#jform_tax_rule_id');
			
			if (!rateInput.length) {
				return;
			}
			
			var inputType = taxInput.attr('data-type');
			var taxRateOption = rateInput.find('option:selected').first().text();
			
			parser = new RegExp(/.*\[(.+)\]$/);
			
			if (parser.test(taxRateOption)) {
				taxRate = parseFloat(parser.exec(taxRateOption)[1]);
				if (inputType == 'gross') {
					djPriceFromGross(taxInput, price, taxRate);
				} else if (inputType == 'net') {
					djPriceFromNet(taxInput, price, taxRate);
				}
			}
		}

	function djPriceFromGross(element, price, taxrate) {
		price = parseFloat(price);
		taxrate = parseFloat(taxrate);
		if (!price || !(taxrate >= 0)) {
			element.val('');
			return;
		}

		var netPrice = (price * 100) / (100 + taxrate);
		element.val(netPrice.toFixed(2));
	}

	function djPriceFromNet(element, price, taxrate) {
		price = parseFloat(price);
		taxrate = parseFloat(taxrate);

		if (!price || !(taxrate >= 0)) {
			element.val('');
			return;
		}

		var grossPrice = price * ((100 + taxrate)/100) ;
		element.val(grossPrice.toFixed(2));
	}
	function djRenderForm() {
		var itemId = $('#jform_id').val();
		
		if (!itemId || itemId == 0) {
			var vars = {};
		    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		        vars[key] = value;
		    });
		    if (vars['id'] > 0) {
		    	itemId = vars['id'];
		    }
		}
		var groupId= $('#jform_group_id').val();
		var groupIds = [];
		var options = $('#jform_group_id').find('option');
		
		for (var k = 0; k < options.length; k++) {
			if ($(options[k]).is(':selected')) {
				groupIds.push($(options[k]).val());
			}
		}
		
		groupId = groupIds.join(',');
		
		if ($('#itemAttributes').length > 0) {
			
			var textareas = $('#itemAttributes').find('textarea.nicEdit');
			if (textareas.length > 0) {
				textareas.each(function(){
					var textarea = $(this);
					if (textarea.nicEditor != null && textarea.nicEditor) {
						textarea.nicEditor.removeInstance(textarea.id);
						textarea.nicEditor = null;
					}
				});
			}
			
			var calendars = $('#itemAttributes').find('input.djc_calendar');
			if (calendars.length > 0) {
				calendars.each(function(){
					var calendar = $(this);
					if (typeof(calendar.hasCalendar) != 'undefined') {
						calendars.hasCalendar = null;
					}
				});
			}
			$.ajax({
				url : djc_joomla_base_url + 'index.php?option=com_djcatalog2&view=itemform&layout=extrafields&format=raw&itemId='
					+ itemId
					+ '&groupId='
					+ groupId,
				type: 'post'
				
			}).done(function(resp){
				$('#itemAttributes').html(resp);
				var textareas = $('#itemAttributes').find('textarea.nicEdit');
				if (textareas.length > 0) {
					var myNicEditor = new nicEditor();
					textareas.each(function(){
						var textarea = $(this);
						textarea.nicEditor = new nicEditor({fullPanel : false, xhtml: true, buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'ol', 'ul', 'subscript', 'superscript', 'strikethrough', 'hr', 'image', 'link', 'unlink', 'xhtml'], iconsPath: djc_joomla_base_url + 'components/com_djcatalog2/assets/nicEdit/nicEditorIcons.gif'}).panelInstance(textarea.attr('id'),{hasPanel : true});
						textarea.nicEditor.addEvent('blur',function(){
							if (textarea.nicEditor) {
								var editor = textarea.nicEditor.instanceById(textarea.id);
								if (editor) {
									editor.saveContent();
								}
							}
						});
					});
				}
				
				//var calendars = $('#itemAttributes').find('input.djc_calendar');
				var calendars = $('#itemAttributes').find('.field-calendar');
				if (calendars.length > 0) {
					calendars.each(function(){
						JoomlaCalendar.init($(this)[0]);
					});
				} else {
					calendars = $('#itemAttributes').find('input.djc_calendar');
					if (calendars.length > 0) {
						calendars.each(function(){
							var calendar = $(this);
							if (typeof(calendar.hasCalendar) === 'undefined') {
								Calendar.setup({
									inputField: calendar.attr('id'),
									ifFormat: "%Y-%m-%d",
									//ifFormat: "%Y-%m-%d %H:%M:%S",
									daFormat: "%Y-%m-%d",
									button: calendar.attr('id') + "_img",
									align: "Tl",
									singleClick: true
								});
								calendar.hasCalendar = true;
							}
						});
					}
				}
			});
		}
	}
})(jQuery);

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
		
		
		if ($('#jform_tax_rule_id')) {
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
		
		/*var allowCombinations = false;
		var parent_id = $('input[name="jform[parent_id]"]');
		if (parent_id.length > 0) {
			if (parent_id.val() == '' || parent_id.val() == 0) {
				allowCombinations = true;
			}
		}
		
		if (allowCombinations) {
			var combinationsWizard = new DJCatalog2CombinationsWizard($('#itemCombinations'));
		}*/
		

		$('.subform-repeatable-group input.validate-price').on('keyup click change', function(){
			djValidatePrice($(this));
		});
		
	    $(document).on('subform-row-add', function(event, row){
	    	$(row).find('input.validate-price').on('keyup click change', function(){
				djValidatePrice($(this));
			});
	    });
		
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
		element.val(netPrice.toFixed(4));
	}

	function djPriceFromNet(element, price, taxrate) {
		price = parseFloat(price);
		taxrate = parseFloat(taxrate);

		if (!price || !(taxrate >= 0)) {
			element.val('');
			return;
		}

		var grossPrice = price * ((100 + taxrate)/100) ;
		element.val(grossPrice.toFixed(4));
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
				url : 'index.php?option=com_djcatalog2&view=item&layout=extrafields&format=raw&itemId='
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
						textarea.nicEditor = new nicEditor({fullPanel : true, xhtml: true, iconsPath: '../components/com_djcatalog2/assets/nicEdit/nicEditorIcons.gif'}).panelInstance(textarea.attr('id'),{hasPanel : true});
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
	
	var DJCatalog2CombinationsWizard = function(wrapper, attributes, combinations, i18n) {
		this.wrapper = $(wrapper);
		this.attributes = attributes;
		this.combinations = combinations;
		this.i18n = $.extend({}, this.i18n, i18n);
		
		this.initialise();
	};
	
	DJCatalog2CombinationsWizard.prototype = {
		constructor: DJCatalog2CombinationsWizard,
		i18n: {
			TH_SKU: 'SKU',
			TH_PRICE: 'Price',
			TH_NAME: 'Name',
			TH_STOCK: 'Stock',
			TH_ATTRIBUTES: 'Attributes',
			BTN_ADD: 'Add',
			BTN_REMOVE_ALL: 'Remove All',
			BTN_REMOVE: 'Remove',
			BTN_GENERATE: 'Generate'
		},
		initialise: function() {
			//console.log(this.wrapper);
			this.prepareGenerator();
			this.prepareWrapper();
			this.fillRows();
		},
		
		prepareGenerator: function() {
			var self = this;
			var generator = $('<div />', {'class': 'djcComboGen'});
			
			var html = [];
			
			
			for (var idx in this.attributes) {
				if (!this.attributes.hasOwnProperty(idx)) {
					continue;
				}
				var check = $('<input />', {'type' : 'checkbox', 'data-toggleattribute': this.attributes[idx].id, 'id': 'combogenerator-toggle-'+this.attributes[idx].id, 'data-groupid': this.attributes[idx].group_id });
				var select = $('<select/>', {'name': 'combogenerator['+this.attributes[idx].id+'][]', 'id': 'combogenerator-'+this.attributes[idx].id, 'disabled': 'disabled', 'style': 'display:none', 'multiple': 'multiple', 'data-attribute': + this.attributes[idx].id});
				select.attr('size', 15);
				var attrHtml = '<div class="controls-label checkbox"><label for="combogenerator-toggle-'+this.attributes[idx].id + '">'+ this.attributes[idx].name + ' <small>['+this.attributes[idx].alias+']</small>' + check.prop('outerHTML') + '</label></div>';
				
				var options = [];
				/*for (var optIdx in this.attributes[idx].optionValues) {
					//options.push($('<option />', {'value': optIdx, 'html': this.attributes[idx].optionValues[optIdx]}));
					options.push('<option value="'+optIdx+'">'+this.attributes[idx].optionValues[optIdx]+'</option>');
				}*/
				
				for (var optId in this.attributes[idx].options) {
					if (!this.attributes[idx].options.hasOwnProperty(optId)) {
						continue;
					}
					var optIdx = this.attributes[idx].options[optId];
					//options.push($('<option />', {'value': optIdx, 'html': this.attributes[idx].optionValues[optIdx]}));
					options.push('<option value="'+optIdx+'">'+this.attributes[idx].optionValues[optIdx]+'</option>');
				}
				//console.log(options);
				select.html(options.join(''));
				
				attrHtml += '<div class="controls">'+select.prop('outerHTML')+'</div>';
				
				html.push(attrHtml);
			}
			
			html = '<div class="control-group">' + html.join('</div><div class="control-group">') + '</div>';
			
			var skuInput = $('<input />', {
				'id': 'combogenerator-sku',
				'name': 'combogenerator[sku]',
				'type': 'text',
				'class': 'input input-medium',
				'placeholder': this.i18n.TH_SKU + '...'
				});
			
			html += '<div class="control-group">' + 
					'<div class="control-label"><label for="combogenerator-sku">'+this.i18n.TH_SKU+'</label></div>' + 
					'<div class="controls">' + skuInput.prop('outerHTML') + '</div>' + 
					'</div>';
			
			var priceInput = $('<input />', {
				'id': 'combogenerator-price',
				'name': 'combogenerator[price]',
				'type': 'text',
				'class': 'input input-medium',
				'placeholder': this.i18n.TH_PRICE + '...',
				'value': '0.0'
				});
			
			html += '<div class="control-group">' + 
					'<div class="control-label"><label for="combogenerator-price">'+this.i18n.TH_PRICE+'</label></div>' + 
					'<div class="controls">' + priceInput.prop('outerHTML') + '</div>' + 
					'</div>';
			
			var stockInput = $('<input />', {
				'id': 'combogenerator-stock',
				'name': 'combogenerator[stock]',
				'type': 'number',
				'min': 0,
				'step': 1,
				'class': 'input input-mini',
				'placeholder': this.i18n.TH_STOCK + '...',
				'value': '0'
				});
			
			html += '<div class="control-group">' + 
					'<div class="control-label"><label for="combogenerator-stock">'+this.i18n.TH_STOCK+'</label></div>' + 
					'<div class="controls">' + stockInput.prop('outerHTML') + '</div>' + 
					'</div>';
			
			html += '<div class="control-group"><button type="button" class="btn djcComboGenBtn">'+this.i18n.BTN_GENERATE+'</button></div>';
			
			generator.html(html);

			this.wrapper.find('.djcCombinationsGenerator').append(generator);
			
			this.wrapper.find('button.djcComboGenBtn').click(function(){
				// preparing information from selected fields/attributtes
				var activeSelectors = [];
				self.wrapper.find('.djcCombinationsGenerator select').each(function(){
					var selectedOptions = $(this).find('option:selected');
					var attrId = $(this).attr('data-attribute');
					if (selectedOptions.length > 0 && attrId) {
						activeSelectors.push({id: attrId, options: selectedOptions});
					}
				});
				if (activeSelectors.length) {
					var fieldValues = {};
					$(activeSelectors).each(function(){
						var selector = $(this);
						var selectValues = [];
						
						selector[0].options.each(function(){
							selectValues.push($(this).val());
						});
						
						if (selectValues.length > 0) {
							fieldValues[selector[0].id] = selectValues;
						}
					});
					// creating a set of combinations to be inserted
					var combinations = self.createCombinations(fieldValues);
					
					var defaults = {
						sku: $('#combogenerator-sku').val(),
						price: $('#combogenerator-price').val(),
						stock: $('#combogenerator-stock').val(),
					};
					
					self.insertCombinations(combinations, defaults);
				}
			});
			
			this.wrapper.find('input[name="combogenerator[price]"]').on('keyup click change', function(){
				self.validatePrice(this);
			});
			
			this.wrapper.find('input[type="checkbox"][data-toggleattribute]').on('change', function(){
				var related = $(this).attr('data-toggleattribute');
				var relSelects = self.wrapper.find('.djcCombinationsGenerator select[data-attribute='+related+']');
				var tblSelects = self.wrapper.find('.djcCombinationsTable select[data-attribute='+related+']');/*.filter(function(){
					return $(this).val() == '';
				});*/
				
				if ($(this).is(':checked')) {
					relSelects.css('display', '').removeAttr('disabled');
					tblSelects.css('display', '').removeAttr('disabled');
					tblSelects.prev('label').css('display', '');
					relSelects.find('option').attr('selected', 'selected');
				} else {
					relSelects.css('display', 'none').attr('disabled', 'disabled');
					tblSelects.css('display', 'none').attr('disabled', 'disabled');
					tblSelects.prev('label').css('display', 'none');
				}
			});
		},
		
		createCombinations: function(fieldValues) {
			var combinations = [[]];
			
			$.each(fieldValues, function(field_id, values){
				var tmp = [];
				$.each(combinations, function(cid, combination){
					$.each(values, function(idx, value) {
						var item = [];
						item.push({'field_id': field_id, 'value': value});
						
						var x = $.merge($.merge([], combination), item);
						
						tmp.push(x);
					});
				});
				combinations = tmp;
			});
			
			return combinations;
		},
		
		insertCombinations: function(combinations, defaults) {
			var self = this;
			var toInsert = [];
			
			//console.log(combinations);
			
			// excluding combinations that already exist
			var rows = this.wrapper.find('tr.djcComboRow');
			if (rows.length > 0) {
				var existingCombinations = [];
				rows.each(function(){
					var activeSelectors = [];
					$(this).find('select').each(function(){
						var selectedOptions = $(this).find('option:selected');
						var attrId = $(this).attr('data-attribute');
						if (selectedOptions.length > 0 && attrId) {
							activeSelectors.push({id: attrId, options: selectedOptions});
						}
					});
					if (activeSelectors.length) {
						var combination = [];
						$.each(activeSelectors, function(){
							var selector = $(this);
							
							selector[0].options.each(function(){
								if ($(this).val() != '') {
									var fieldVal = {'field_id': selector[0].id, 'value': $(this).val()};
									combination.push(fieldVal);
								}
							});
						});
						
						existingCombinations.push(combination);
					}
				});
				
				if (existingCombinations.length > 0) {
					$.each(combinations, function(i){
						var found = false;
						$.each(existingCombinations, function(ii){
							if (JSON.stringify(combinations[i]) ==  JSON.stringify(existingCombinations[ii])) {
								found = true;
							}
						});
						
						if (!found) {
							toInsert.push(combinations[i]);
						}
					});
				} else {
					toInsert = combinations;
				}
			} else {
				toInsert = combinations;
			}
			
			if (toInsert.length < 1) {
				return;
			}
			
			// now we can inject rows represting remaining combinations
			//console.log(toInsert);
			$.each(toInsert, function(){
				var comboFields = $(this);
				var combo = {
					id: 0,
					sku: defaults.sku,
					price: defaults.price,
					stock: defaults.stock,
					fields: comboFields
				}
				//console.log(combo);
				self.addRow(combo);
			});
			this.combinations = $.merge(this.combinations, toInsert);
			
			this.wrapper.find('input[type="checkbox"][data-toggleattribute]').trigger('change');
		},
		
		prepareWrapper: function() {
			var self = this;
			var table = $('<table />', {'class': 'table table-striped'});
			
			var tableHtml = '<thead>' +
						'<tr>' + 
						'<th>'+self.i18n.TH_SKU+'</th>' +
						'<th>'+self.i18n.TH_PRICE+'</th>' +
						'<th>'+self.i18n.TH_STOCK+'</th>' +
						'<th>'+self.i18n.TH_ATTRIBUTES+'</th>' +
						'<th><button type="button" class="btn djcComboAdd">'+self.i18n.BTN_ADD+'</button> <button type="button" class="btn djcComboRemoveAll">'+self.i18n.BTN_REMOVE_ALL+'</button></th>' +
						'</tr>' + 
						'</thead><tbody></tbody>';
			
			table.html(tableHtml);
			self.wrapper.find('.djcCombinationsTable').append(table);
			
			self.wrapper.find('button.djcComboAdd').click(function(){
				self.addRow();
				self.wrapper.find('input[type="checkbox"][data-toggleattribute]').trigger('change');
			});
			self.wrapper.find('button.djcComboRemoveAll').click(function(){
				self.deleteRows();
			});
		},
		
		fillRows: function() {
			var self = this;
			$.each(this.combinations, function(){
				var data = $(this)[0];
				self.addRow(data);
			});
			
			this.wrapper.find('input[type="checkbox"][data-toggleattribute]').trigger('change');
		},
		
		deleteRows: function() {
			this.wrapper.find('tr.djcComboRow').remove();
		},
		
		prepareAttributes: function() {
			var html = [];
			for (var idx in this.attributes) {
				if (!this.attributes.hasOwnProperty(idx)) {
					continue;
				}
				var select = $('<select/>', {'name': 'combinations[attribute]['+this.attributes[idx].id+'][]', 'data-attribute': this.attributes[idx].id});
				var attrHtml = '<label>' + this.attributes[idx].name + '</label>';
				var options = [];
				//options.push('<option value="">--</option>');
				for (var optId in this.attributes[idx].options) {
					if (!this.attributes[idx].options.hasOwnProperty(optId)) {
						continue;
					}
					var optIdx = this.attributes[idx].options[optId];
					//options.push($('<option />', {'value': optIdx, 'html': this.attributes[idx].optionValues[optIdx]}));
					options.push('<option value="'+optIdx+'">'+this.attributes[idx].optionValues[optIdx]+'</option>');
				}
				//console.log(options);
				select.html(options.join(''));
				
				attrHtml += select.prop('outerHTML');
				
				html.push(attrHtml);
			}
			
			return html.join('');
		},
		
		addRow: function(data){
			var self = this;
			
			var row = $('<tr />', {'class': 'djcComboRow'});
			
			var skuInput = $('<input />', {
				'name': 'combinations[sku][]',
				'type': 'text',
				'class': 'input input-medium',
				'placeholder': this.i18n.TH_SKU + '...',
				'value' : (typeof data != 'undefined' ? data.sku : '')
				});
			
			var priceInput = $('<input />', {
				'name': 'combinations[price][]',
				'type': 'text',
				'class': 'input input-medium',
				'placeholder': this.i18n.TH_PRICE + '...',
				'value' : (typeof data != 'undefined' ? data.price : '0.0')
				});
			
			
			var stockInput = $('<input />', {
				'name': 'combinations[stock][]',
				'type': 'number',
				'min': 0,
				'step': 1,
				'class': 'input input-mini',
				'placeholder': this.i18n.TH_STOCK + '...',
				'value' : (typeof data != 'undefined' ? data.stock : '0')
				});
			
			var attributesHtml = this.prepareAttributes();
			
			var rowHtml = 	'<td>' +
							skuInput.prop('outerHTML') + 
							'</td>' + 
							'<td>' +
							priceInput.prop('outerHTML') + 
							'</td>' + 
							'<td>' +
							stockInput.prop('outerHTML') + 
							'</td>' + 
							'<td>' +
							attributesHtml +
							'</td>' +
							'<td>' +
							'<input type="hidden" name="combinations[id][]" value="'+ ((typeof data != 'undefined' ? data.id : '0')) + '" />' + 
							'<button type="button" class="btn djcComboRemove">'+this.i18n.BTN_REMOVE+'</button>' + 
							'</td>';
			
			row.html(rowHtml);
			
			if (typeof data != 'undefined') {
				$.each(data.fields, function(){
					var field = $(this);
					var fieldId = field[0].field_id;
					var check = self.wrapper.find('input[type="checkbox"][data-toggleattribute='+fieldId+']');
					
					row.find('select[name="combinations[attribute]['+ fieldId +'][]"]').val(field[0].value);
					
					if (check.not(':checked')) {
						check.attr('checked', 'checked');
						//check.trigger('change');
					}
				});
			}
			
			row.find('button.djcComboRemove').click(function(){
				$(this).parents('tr').remove();
			});
			
			row.find('input[name="combinations[price][]"]').on('click keyup change', function(){
				self.validatePrice(this);
			});
			
			this.wrapper.find('tbody').append(row);
		},
		
		validatePrice: function(input) {
			var value = $(input).val();
			
			var valid_price = new RegExp(/^(\d+|\d+\.\d+)$/);
			var wrong_decimal = new RegExp(/\,/g);
			var restricted = new RegExp(/[^\d+\.]/g);
			
			value = value.replace(wrong_decimal, ".");
			
			if (valid_price.test(value) == false) {
				value = value.replace(restricted, '');
			}
			
			if (valid_price.test(value) == false) {
				var parts = value.split('.');
				if (parts.length > 2 ) {
					value = parts[0] + '.' + parts[1];
				}
			}
			$(input).val(value);
		},
		
		applyGroups: function(group_ids) {
			if (group_ids == null) {
				this.wrapper.find('input[type="checkbox"][data-toggleattribute]').each(function(){
					if ( $(this).attr('data-groupid') != '0' ) {
						$(this).removeAttr('checked').trigger('change');
						$(this).parents('div.control-group').hide();
					}
				});
			} else {
				this.wrapper.find('input[type="checkbox"][data-toggleattribute]').each(function(){
					if ( $(this).attr('data-groupid') != '0' && group_ids.indexOf( $(this).attr('data-groupid') ) != -1) {
						//$(this).attr('checked', 'checked').trigger('change');
						$(this).parents('div.control-group').show();
					} else {
						$(this).removeAttr('checked').trigger('change');
						$(this).parents('div.control-group').hide();
					}
				});
			}
		}
	};
	
	window.DJCatalog2CombinationsWizard = DJCatalog2CombinationsWizard;
	
	var DJCatalog2CustomisationsWizard = function(wrapper, customisations, values, i18n) {
		this.wrapper = $(wrapper);
		this.i18n = $.extend({}, this.i18n, i18n);
		this.customisations = customisations;
		this.values = values;
		
		this.initialise();
	};
	
	DJCatalog2CustomisationsWizard.prototype = {
		constructor: DJCatalog2CustomisationsWizard,
		customisations: [],
		values: [],
		i18n: {
			TH_SKU: 'SKU',
			TH_PRICE: 'Price',
			TH_NAME: 'Name',
			TH_STOCK: 'Stock',
			TH_ATTRIBUTES: 'Attributes',
			BTN_ADD: 'Add',
			BTN_REMOVE_ALL: 'Remove All',
			BTN_REMOVE: 'Remove',
			BTN_GENERATE: 'Generate',
			LABEL_CUSTOMISATIONS: 'Customisations',
			TH_MIN_QTY: 'Min qty.',
			TH_MAX_QTY: 'Max qty.'
		},
		initialise: function() {
			//console.log(this.wrapper);
			this.prepareGenerator();
			this.prepareWrapper();
			this.fillRows();
		},
		prepareGenerator: function() {
			var self = this;
			var generator = $('<div />', {'class': 'djcCustomGen'});
			
			var select = $('<select/>', {
				'name': 'customgenerator[]', 
				'id': 'customgenerator', 
				'size': '20', 'multiple': 'multiple'
				}
			);
			
			var options = [];
			
			$.each(this.customisations, function(i,e) {
				options.push('<option value="'+e.id+'">'+e.name+'</option>');
			});
			
			select.html(options.join(''));
			
			var html =	'<div class="control-group">' +
						'<div class="control-label"><label for="customgenerator">'+this.i18n.LABEL_CUSTOMISATIONS+'</label></div>' +
						'<div class="controls">' + select.prop('outerHTML') + 
						'</div>' +
						'</div>';
			
			html += '<div class="control-group"><button type="button" class="btn djcCustomAddBtn">'+this.i18n.BTN_ADD+'</button></div>';

			generator.append(html);
			this.wrapper.find('.djcCustomisationsGenerator').append(generator);

			this.wrapper.find('button.djcCustomAddBtn').click(function(){
				var selectedOptions = self.wrapper.find('.djcCustomGen select option:selected');
				if (selectedOptions.length > 0) {
					var existingOptions = self.wrapper.find('.djcCustomisationsTable input[name="customisations[customisation_id][]"]');
					selectedOptions.each(function(){
						var selected = $(this).attr('value');
						var exists = false;
						existingOptions.each(function(){
							if ($(this).attr('value') == selected) {
								exists = true;
							}
						});
						
						if (!exists) {
							var option = {
								customisation_id: selected,
								name: $(this).text(),
								price: 0.00,
								min_quantity: 0,
								max_quantity: 0
							};
							
							$.each(self.customisations, function(){
								if (this.id == option.customisation_id) {
									option.price = this.price;
									option.min_quantity = this.min_quantity;
									option.max_quantity = this.max_quantity;
								}
							});
							
							self.addRow(option);
						}
					});
				}
			});
			
		},
		prepareWrapper: function() {
			var self = this;
			var table = $('<table />', {'class': 'table table-striped'});
			
			var tableHtml = '<thead>' +
						'<tr>' + 
						'<th>'+self.i18n.TH_NAME+'</th>' +
						'<th>'+self.i18n.TH_PRICE+'</th>' +
						'<th>'+self.i18n.TH_MIN_QTY+'</th>' +
						'<th>'+self.i18n.TH_MAX_QTY+'</th>' +
						'<th><button type="button" class="btn djcCustomRemoveAll">'+self.i18n.BTN_REMOVE_ALL+'</button></th>' +
						'</tr>' + 
						'</thead><tbody></tbody>';
			
			table.html(tableHtml);
			self.wrapper.find('.djcCustomisationsTable').append(table);
			
			self.wrapper.find('button.djcCustomRemoveAll').click(function(){
				self.deleteRows();
			});
		},
		
		fillRows: function() {
			var self = this;
			$.each(this.values, function(){
				var data = $(this)[0];
				self.addRow(data);
			});
		},
		
		deleteRows: function() {
			this.wrapper.find('tr.djcCustomRow').remove();
		},
		
		addRow: function(data){
			var self = this;
			
			var row = $('<tr />', {'class': 'djcCustomRow'});
			
			var priceInput = $('<input />', {
				'name': 'customisations[price][]',
				'type': 'text',
				'class': 'input input-medium',
				'placeholder': this.i18n.TH_PRICE + '...',
				'value' : (typeof data != 'undefined' ? data.price : '0.0')
				});
			
			var minQtyInput = $('<input />', {
				'name': 'customisations[min_quantity][]',
				'type': 'number',
				'min': 0,
				'step': 1,
				'class': 'input input-mini',
				'placeholder': this.i18n.TH_MIN_QTY + '...',
				'value' : (typeof data != 'undefined' ? data.min_quantity : '0')
				});
			
			var maxQtyInput = $('<input />', {
				'name': 'customisations[max_quantity][]',
				'type': 'number',
				'min': 0,
				'step': 1,
				'class': 'input input-mini',
				'placeholder': this.i18n.TH_MAX_QTY + '...',
				'value' : (typeof data != 'undefined' ? data.max_quantity : '0')
				});
			
			//var attributesHtml = this.prepareAttributes();
			
			var rowHtml = 	'<td>' +
							data.name + 
							'</td>' + 
							'<td>' +
							priceInput.prop('outerHTML') + 
							'</td>' + 
							'<td>' +
							minQtyInput.prop('outerHTML') + 
							'</td>' + 
							'<td>' +
							maxQtyInput.prop('outerHTML') + 
							'</td>' + 
							'<td>' +
							'<input type="hidden" name="customisations[customisation_id][]" value="'+ data.customisation_id + '" />' + 
							'<button type="button" class="btn djcCustomRemove">'+this.i18n.BTN_REMOVE+'</button>' + 
							'</td>';
			
			row.html(rowHtml);
			
			row.find('button.djcCustomRemove').click(function(){
				$(this).parents('tr').remove();
			});
			
			row.find('input[name="customisations[price][]"]').on('click keyup change', function(){
				self.validatePrice(this);
			});
			
			this.wrapper.find('tbody').append(row);
		},
		
		validatePrice: function(input) {
			var value = $(input).val();
			
			var valid_price = new RegExp(/^(\d+|\d+\.\d+)$/);
			var wrong_decimal = new RegExp(/\,/g);
			var restricted = new RegExp(/[^\d+\.]/g);
			
			value = value.replace(wrong_decimal, ".");
			
			if (valid_price.test(value) == false) {
				value = value.replace(restricted, '');
			}
			
			if (valid_price.test(value) == false) {
				var parts = value.split('.');
				if (parts.length > 2 ) {
					value = parts[0] + '.' + parts[1];
				}
			}
			$(input).val(value);
		}
	};
	
	window.DJCatalog2CustomisationsWizard = DJCatalog2CustomisationsWizard;
	
})(jQuery);
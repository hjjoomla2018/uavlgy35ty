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

var Djcartfieldtype = function(fieldtype, typeselector, fieldId) {
	return this.initialize(fieldtype, typeselector, fieldId);
};

(function ($){
	Djcartfieldtype.prototype = {
		initialize : function(fieldtype, typeselector, fieldId) {
			this.typeSelector = typeselector;
			this.fieldId = fieldId;
			this.formWrapper = $('#fieldtypeSettings');
			this.fieldtype = fieldtype;
			this.displayForm();

			var self = this;
			
			if (typeof ($('#' + this.typeSelector)) !== 'undefined') {
				$('#' + this.typeSelector).change(function(evt) {
					self.fieldtype = $('#' + self.typeSelector).val();
					self.displayForm();
				});
			}
		},
		displayForm : function() {
			var self = this;
			if (typeof (this.formWrapper) !== 'undefined') {
				$.ajax({
						url : 'index.php?option=com_djcatalog2&view=cartfield&layout=fielddata&format=raw&fieldtype='
								+ self.fieldtype
								+ '&fieldId='
								+ self.fieldId
								+ '&suffix='
								+ self.typeSelector,
						type: 'post'
						}).done(function(resp) {
							self.formWrapper.html(resp);
							
							var rows = self.formWrapper.find('tr');
							
							rows.each(function(ind, el){
								var row = $(el);
								
								row.on('moveDown', function(){
									self.moveDown(row);
								});
								row.on('moveUp', function(){
									self.moveUp(row);
								});
								
								var button = $(el).find('span.button-x');
								button.on('click', function(){
									row.remove();
								});
								
								var buttonDown = $(el).find('span.button-down');
								buttonDown.on('click', function(){
									row.trigger('moveDown');
								});
								
								var buttonUp = $(el).find('span.button-up');
								buttonUp.on('click', function(){
									row.trigger('moveUp');
								});
							});
							
						});
			}
		},
		appendOption : function() {
			var self = this;
			if (typeof ($('#DjfieldOptions')) !== 'undefined') {
				var optionInput = $('<input />');
				var optionId = $('<input />');
				var optionPosition = $('<input />');
				
				var deleteButton = $('<span />');
				var upButton = $('<span />');
				var downButton = $('<span />');
				
				optionInput.attr('name', 'fieldtype[option][]');
				optionInput.attr('type', 'text');
				optionInput.attr('size', '30');
				optionInput.attr('class', 'input-medium required');
				
				var inputs = this.formWrapper.find('input');
				var maxPos = 0;
				inputs.each(function(ind, el) {
					el = $(el);
					if (el.attr('name') == 'fieldtype[position][]') {
						if (maxPos < parseInt(el.val())) {
							maxPos = parseInt(el.val());
						}
					}
				});
				
				optionPosition.attr('name', 'fieldtype[position][]');
				optionPosition.attr('type', 'text');
				optionPosition.attr('size', '4');
				optionPosition.attr('class', 'input-mini');
				optionPosition.attr('value', parseInt(maxPos+1));
				
				optionId.attr('name', 'fieldtype[id][]');
				optionId.attr('type', 'hidden');
				optionId.attr('value', '0');
				
				deleteButton.attr('class','btn button-x');
				deleteButton.html('&nbsp;&nbsp;&minus;&nbsp;&nbsp;');
				
				downButton.attr('class','btn button-down');
				downButton.html('&nbsp;&nbsp;&darr;&nbsp;&nbsp;');
				
				upButton.attr('class','btn button-up');
				upButton.html('&nbsp;&nbsp;&nbsp;&uarr;&nbsp;&nbsp;&nbsp;');
				
				
				var optionInputCell = $('<td />');
				optionInputCell.append(optionId);
				optionInputCell.append(optionInput);
				
				var optionPositionCell = $('<td />');
				optionPositionCell.append(optionPosition);
				optionPositionCell.append(deleteButton);
				optionPositionCell.append(downButton);
				optionPositionCell.append(upButton);
				
				
				var optionRow = $('<tr />');
				optionRow.append(optionInputCell);
				optionRow.append(optionPositionCell);
				
				deleteButton.on('click', function(){
					optionRow.remove();
				});
				
				downButton.on('click', function(){
					optionRow.trigger('moveDown');
				});
				
				upButton.on('click', function(){
					optionRow.trigger('moveUp');
				});
				
				optionRow.on('moveDown',function(){
					self.moveDown(optionRow);
				});
				optionRow.on('moveUp', function(){
					self.moveUp(optionRow);
				}); 
				
				$('#DjfieldOptions').append(optionRow);
			}
		},
		moveDown:function(row) {
			var self = this;
			var tbody = $('#DjfieldOptions');
			var rows = this.formWrapper.find('tbody tr');
			var count = rows.length;
			rows.each(function(ind, el){
				if ($(row).is(el) && ind < count - 1) {
					//self.switchRows(row, rows[ind+1]);
					var tempOrder = $(row).find('input[name="fieldtype[position][]"]').val();
					var newOrder = $(rows[ind+1]).find('input[name="fieldtype[position][]"]').val();
					$(row).find('input[name="fieldtype[position][]"]').val(newOrder);
					$(rows[ind+1]).find('input[name="fieldtype[position][]"]').val(tempOrder);
					$(row).before($(rows[ind+1]));
				}
			});
		},
		moveUp:function(row) {
			var self = this;
			var tbody = $('#DjfieldOptions');
			var rows = this.formWrapper.find('tbody tr');
			var count = rows.length;
			rows.each(function(ind, el){
				if ($(row).is(el) && ind > 0) {
					//self.switchRows(row, rows[ind-1]);
					var tempOrder = $(row).find('input[name="fieldtype[position][]"]').val();
					var newOrder = $(rows[ind-1]).find('input[name="fieldtype[position][]"]').val();
					$(row).find('input[name="fieldtype[position][]"]').val(newOrder);
					$(rows[ind-1]).find('input[name="fieldtype[position][]"]').val(tempOrder);
					
					$(row).after($(rows[ind-1]));
				}
			});
		}/*,
		switchRows : function(row1, row2) {
			var inputs1 = $(row1).find('input');
			var inputs2 = $(row2).find('input');
			if (inputs1.length == inputs2.length) {
				for (var i=0; i < inputs1.length; i++) {
					if ($(inputs1[i]).attr('name') != 'fieldtype[position][]'){
						var temp = $(inputs1[i]).val();
						$(inputs1[i]).val($(inputs2[i]).val());
						$(inputs2[i]).val(temp);
					}
				}
			}
		}*/
};
})(jQuery);

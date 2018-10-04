<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$onStock = (bool)( ($this->item_cursor->onstock == 1 && $this->item_cursor->stock > 0) || $this->item_cursor->onstock == 2 );
$canCheckout = (bool)($this->params->get('cart_enabled', false) && (($onStock  && $this->item_cursor->final_price > 0.0) || $this->cart_variant_fields));
$canQuery = (bool)($this->params->get('cart_query_enabled', 1));

?>

<?php if (($canCheckout || $canQuery) && $this->item_cursor->available) {
	$return_url = base64_encode(JUri::getInstance()->__toString());
	$button_value = $canCheckout ? JText::_('COM_DJCATALOG2_ADD_TO_CART') : JText::_('COM_DJCATALOG2_ADD_TO_QUOTE_CART');
	$button_class = $canCheckout ? 'djc_addtocart_btn' : 'djc_addtoquote_btn';
	$button_disabled = '';
	
	$results = JFactory::getApplication()->triggerEvent('onDJCatalog2BeforeCart', array($this->item_cursor, $this->params, 'items.'.$this->params->get('list_layout','items')));
	foreach($results as $html){
		echo $html;
	}
	
	?>
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="djc_form_addtocart" data-itemid="<?php echo $this->item_cursor->id; ?>">
		<?php if ($this->item_cursor->parent_id == 0) { ?>
			<div class="djc_cart_variants">
			<?php if (count($this->cart_variant_fields)) {
				$button_disabled = 'disabled="disabled"';
				$button_class .= ' disabled';
				
				foreach ($this->cart_variant_fields as $field) {
					$this->variant_field_cursor = $field;
					echo $this->loadTemplate('addtocart_variantfields');
				}
			?>
			
			<?php } ?>
			</div>
			
			<?php if (count($this->all_customisations) > 0 && $this->customisations_form) {?>
			<div class="djc_cart_customisations">
				<?php foreach($this->customisations_form->getFieldset() as $field) {?>
					<?php echo $field->renderField(); ?>
				<?php } ?>
			</div>
			<?php } ?>
		<?php } ?>
		
		<div class="djc_addtocart">
		<?php 
		$unit = DJCatalog2HelperQuantity::getUnit($this->item_cursor->unit_id); 
		echo DJCatalog2HelperQuantity::renderInput($unit, $this->item_cursor, array('cart_button'=>array('type'=>'input', 'value' => $button_value, 'class' => 'btn btn-primary '.$button_class, 'attributes' => $button_disabled)));
		?>
		</div>
		
		<input type="hidden" name="option" value="com_djcatalog2" /> 
		<input type="hidden" name="task" value="cart.add" />
		<input type="hidden" name="return" value="<?php echo $return_url; ?>" />
		<input type="hidden" name="item_id" value="<?php echo (int)$this->item_cursor->id; ?>" />
		<input type="hidden" name="combination_id" value="" />

		<?php echo JHtml::_( 'form.token' ); ?>
	</form>
<?php } ?>

<?php if ($this->item_cursor->parent_id == 0 && count($this->cart_variant_fields) > 0) {
	$prevCursor = $this->item_cursor;
	foreach ($this->item_cursor->_combinations as &$combination) {
		$cursor = new stdClass();
		$cursor->price = ($combination->price == 0.0) ? $this->item->price : $combination->price;
		$cursor->final_price = ($combination->price == 0.0) ? $this->item->price : $combination->price;
		$cursor->tax_rule_id = $this->item->tax_rule_id;
		$this->item_cursor = $cursor;
		$combination->price_html = $this->loadTemplate('price');
	}
	unset($combination);
	$this->item_cursor = $prevCursor;
?>
<script>
(function($){

	var DJCatalog2CombinationSelector = function(itemId, combinations) {
		var self = this;

		self.combinations = JSON.parse(combinations);
		//console.log(self.combinations);
		self.itemId = itemId;
		self.form = $('.djc_form_addtocart[data-itemid="'+itemId+'"]');
		self.inputs = self.form.find('.djc_cart_variants').find('select,input[type="radio"]');
		self.inputOpts =  self.form.find('.djc_cart_variants').find('select option,input[type="radio"]');
		self.cartBtn =  self.form.find('.djc_addtocart input[type="submit"]');

		self.cartBtnValue = self.cartBtn.val();
		self.outOfStockInfo = '<?php echo JText::_('COM_DJCATALOG2_PRODUCT_OUT_OF_STOCK'); ?>';
		self.handleStock = <?php echo $this->params->get('cart_enabled', false) ? 'true' : 'false'; ?>;

		if (self.inputs.length < 1) {
			self.cartBtn.removeAttr('disabled').removeClass('disabled');
		} else {
			self.cartBtn.attr('disabled', 'disabled').addClass('disabled');
		}

		$('[data-fieldoption]').each(function(){
			var opt = $(this);
			var fieldId = opt.attr('data-fieldid');
			var optionId = opt.attr('data-fieldoption');
			var optionCombinations = opt.attr('data-optioncombinations');

			if (fieldId && optionId && optionCombinations) {
				self.optCombinations[optionId] = JSON.parse(optionCombinations);
			}
		});

		self.inputs.change(function(){
			var input = this;
			
			self.cartBtn.attr('disabled', 'disabled').addClass('disabled');
			self.cartBtn.val(self.cartBtnValue);
			self.form.find('input[name="combination_id"]').val('');

			setTimeout(function() {
				self.toggleOptions($(input));
				self.discoverCombinations();
	        }, 50);
		});

		self.inputs.filter('input[type=radio]').each(function(){
			$(this).mousedown(function() {
			    if (this.checked) {
			        $(this).mouseup(function(e) {
			            var radio = this;
			            setTimeout(function() {
			                radio.checked = false;
			                $(radio).trigger('change');
			            }, 5);
			            $(this).unbind('mouseup');
			        });
			    }
			});

			var lblRadio = this;
			$(lblRadio).parent('label').mousedown(function() {
			    if (lblRadio.checked) {
			        $(this).mouseup(function(e) {
			            setTimeout(function() {
			            	lblRadio.checked = false;
			                $(lblRadio).trigger('change');
			            }, 5);
			            $(this).unbind('mouseup');
			        });
			    }
			});
		});

		var selected = self.inputs.filter('select,:checked');
		if (selected.length == 0) {
			// assuming that first attribute is radio/color
			self.inputs.first().attr('checked', 'checked');
		} else {
			// assuming that we're dealing with select field
			self.inputs.first().trigger('change');
		}

		self.discoverCombinations();
	};

	DJCatalog2CombinationSelector.prototype = {
		constructor: DJCatalog2CombinationSelector,
		itemId: 0,
		combinations: {},
		optCombinations: {},
		optParams: {},
		fieldParams: {},
		getOptParams: function(input) {
			var type = input[0].nodeName;
			var optParams = (type == 'SELECT') ? input.find(':selected').attr('data-optioncombinations')  : input.attr('data-optioncombinations');
			if (typeof optParams == 'undefined' || !optParams || optParams == '') {
				false;
			}
			return JSON.parse(optParams);
		},
		getFieldParams: function(input) {
			var type = input[0].nodeName;
			var params = (type == 'SELECT') ? input.attr('data-combinations') : input.parents('.djc_cartvariant_colors').attr('data-combinations');
			if (typeof params == 'undefined' || !params || params == '') {
				return false;
			}
			return JSON.parse(params);
		},
		toggleOptions: function(input){
			var self = this;
			var fieldId = input.attr('data-fieldid');
			var value = (input.is(':checked') || input.is('select')) ? input.val() : false;

			$('table.djc_combinations-table[data-optionid]').hide();

			if (!value) {
				var firstSelected = null; 
				self.inputOpts.filter(':selected,:checked').each(function(){
					if ($(this).val() != '') {
						firstSelected = $(this);
					}
				});
				
				if (firstSelected == null) {
					self.inputOpts.removeAttr('disabled').removeClass('disabled');
				} else {
					//console.log(self.inputOpts);
					self.inputOpts.each(function(){
						var opt = $(this);
						if (opt.attr('data-fieldid') == firstSelected.attr('data-fieldid')) {
							opt.removeAttr('disabled').removeClass('disabled');
						}
					});
					firstSelected.trigger('change');
				}
			} else {
				var possibleCombinations = self.optCombinations[value];
				if (typeof possibleCombinations != 'undefined') {
					self.inputOpts.each(function(){
						var other = $(this);
						if (other.attr('data-fieldid') != fieldId) {
							var disabled = other.is(':disabled');
							
							var otherOption = other.attr('data-fieldoption');
							var otherCombinations =  self.optCombinations[otherOption];
							if (typeof otherCombinations == 'undefined') {
								disabled = true;
							} else {
								var foundMatch = false;
								$.each(possibleCombinations, function(i1, e1){
									$.each(otherCombinations, function(i2, e2){
										if (e1 == e2) {
											foundMatch = true;
											return;
										}
									});
									if (foundMatch) return;
								});

								if (!foundMatch) {
									disabled = true;
								} else {
									disabled = false;
								}
							}

							if (disabled && other.val() != '') {
								other.attr('disabled', 'disabled').addClass('disabled').removeAttr('selected').removeAttr('checked');
							} else {
								other.removeAttr('disabled').removeClass('disabled');
							}
						}
					});
				}
				
				$('table.djc_combinations-table[data-optionid='+value+']').show();
			}
		},
		discoverCombinations: function(){
			var self = this;
			var candidates = [];
			self.inputs.filter('select,:checked').each(function(){
				var possibleCombinations = self.optCombinations[$(this).val()];
				if (typeof possibleCombinations != 'undefined') {
					candidates.push(possibleCombinations);
				}
			});

			if (candidates.length == 0) {
				return;
			}

			var matches = candidates.shift().filter(function(v) {
			    return candidates.every(function(a) {
			        return a.indexOf(v) !== -1;
			    });
			});

			if (matches.length == 0) {
				// it technically should not be possible, but just in case....
			} else if (matches.length > 1) {
				// more than one combination available - means that user needs to select more options
			} else {
				// bingo!
				var combinationId = matches.shift();

				if (self.combinations[combinationId].stock < 0.0000 && self.handleStock) {
					self.cartBtn.attr('disabled', 'disabled').addClass('disabled');
					self.cartBtn.val(self.outOfStockInfo);
					self.form.find('input[name="combination_id"]').val('');
				} else {
					self.cartBtn.val(self.cartBtnValue);
					self.cartBtn.removeAttr('disabled').removeClass('disabled');
					self.form.find('input[name="combination_id"]').val(combinationId);
				}
				
				// update price wrapper
				$('div.djc_price[data-itemid='+self.combinations[combinationId].item_id+']').html(self.combinations[combinationId].price_html).show();
			}
		}
	};
	
	$(document).ready(function(){
		var combinations = '<?php echo addslashes(json_encode($this->item_cursor->_combinations))?>';
		var comboSelector = new DJCatalog2CombinationSelector(<?php echo $this->item_cursor->id; ?>, combinations);
	});
})(jQuery);
</script>
<?php } ?>

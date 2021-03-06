<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined ('_JEXEC') or die('Restricted access');

$jinput = JFactory::getApplication()->input;
?>
<div class="djc_filters_in thumbnail">
	<form name="djcatalogForm" id="djcatalogForm" method="post" action="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=search'); ?>">
		<?php if ((int)$this->params->get('show_search') > 0) { ?>
			<ul class="djc_filter_ul djc_filter_search djc_clearfix">
				<li class="djc_filter_label"><span><?php echo JText::_('COM_DJCATALOG2_SEARCH'); ?></span></li>
				<li class="djc_filter_input"><input type="text" class="inputbox" name="search" id="djcatsearch" value="<?php echo $this->escape($this->lists['search']);?>" /></li>
			</ul>
		<?php } ?>
		<?php if ($this->params->get('show_category_filter') > 0 || $this->params->get('show_producer_filter') > 0) { ?>
			<ul class="djc_filter_ul djc_filter_list djc_clearfix">
				<li class="djc_filter_label"><span><?php echo JText::_('COM_DJCATALOG2_FILTER'); ?></span></li>
				<?php if ($this->params->get('show_category_filter') > 0) { ?>
					<li class="djc_filter_input djc_filter_categories"><?php echo $this->lists['categories'];?>
					<script type="text/javascript">
					//<![CDATA[ 
					jQuery('#cid').on('change',function(evt){
						if(jQuery('#pid')) {
							options = jQuery('#pid').find('option');
							options.each(function(){
								var option = jQuery(this);
								if (option.val() == "") {
									option.attr('selected', 'true');
								} else {
									option.removeAttr('selected');
								}
							});
						}

						document.djcatalogForm.submit();
					});
					//]]>
					</script>
					</li>
				<?php } ?>
				<?php if ($this->params->get('show_producer_filter') > 0) { ?>
					<li class="djc_filter_input djc_filter_producers"><?php echo $this->lists['producers'];?></li>
					<script type="text/javascript">
						//<![CDATA[ 
						jQuery('#pid').on('change',function(evt){
							document.djcatalogForm.submit();
						});
						//]]>
					</script>
				<?php } ?>
			</ul>
		<?php } ?>
		<?php if ((int)$this->params->get('show_price_filter', 0) > 0) { ?>
			<?php 
			$price_from = str_replace(',', '.', $jinput->get('price_from', '', 'string'));
			if ($price_from != '') {
				$price_from = floatval($price_from);
			}
			$price_to = str_replace(',', '.', $jinput->get('price_to', '', 'string'));
			if ($price_to != '') {
				$price_to = floatval($price_to);
			}
			?>
			<ul class="djc_filter_ul djc_price_filter djc_clearfix">
				<li class="djc_filter_label">
					<span><?php echo JText::_('COM_DJCATALOG2_PRICE_FILTER'); ?></span>
				</li>
				<li class="djc_filter_input">
					<input class="inputbox input input-mini" id="djc_price_filter_from" type="text" value="<?php echo $price_from; ?>" name="price_from" placeholder="<?php echo JText::_('COM_DJCATALOG2_PRICE_FROM'); ?>" />
				</li>
				<li class="djc_filter_input">
					<input class="inputbox input input-mini" id="djc_price_filter_to" type="text" value="<?php echo $price_to; ?>" name="price_to" placeholder="<?php echo JText::_('COM_DJCATALOG2_PRICE_TO'); ?>"/>
				</li>
			</ul>
		<?php } ?>

		<?php if ((int)$this->params->get('show_search') > 0 || $this->params->get('show_pictures_search') > 0 ) { ?>
			<ul class="djc_filter_ul djc_filter_map_search djc_clearfix">
				<li class="djc_filter_label"><span><?php echo JText::_('COM_DJCATALOG2_SEARCH_BY_LOCATION'); ?></span></li>
				<?php if ((int)$this->params->get('show_search') > 0) {?>
					<li class="djc_filter_input"><input type="text" class="inputbox" name="search" id="djcatsearch" value="<?php echo $this->escape($this->lists['search']);?>" /></li>
				<?php } ?>
				<?php if ((int)$this->params->get('show_pictures_search') > 0) {?>
					<li><label for="djc_pic_only">
							<input id="djc_pic_only" type="checkbox" onchange="(function($,input){$('#djc_pic_only_field').val(($(input).is(':checked') ? 1:0)); DJCatalog2SubmitSearch(false);})(jQuery, this);" <?php echo (isset($this->lists['pic_only']) && $this->lists['pic_only'] == 1 ) ? 'checked="checked"' : ''; ?>/> <?php echo JText::_('COM_DJCATALOG2_SEARCH_PICTURES_ONLY_LBL');?></label>
						<input id="djc_pic_only_field" type="hidden" name="pic_only" value="<?php echo (isset($this->lists['pic_only']) && $this->lists['pic_only'] == 1 ) ? '1' : '0' ?>" />
					</li>
				<?php } ?>
			</ul>
			<ul class="djc_filter_ul djc_filter_map_search djc_filter_radius djc_clearfix">
				<li class="djc_filter_label"><span><?php echo JText::_('COM_DJCATALOG2_SEARCH_RADIUS'); ?></span></li>
				<li class="djc_filter_input">
					<select class="inputbox input input-small" name="ms_radius" id="djcatsearch_radius">
						<?php 
						$radiuses = array(1,2,5,10,25,50,100,500,1000);
						$current_radius = $jinput->getInt('ms_radius', 25);
						foreach ($radiuses as $radius) { ?>
						<option value="<?php echo $radius; ?>"<?php if ($radius == $current_radius) {echo ' selected="selected"';} ?>><?php echo $radius; ?></option>
						<?php } ?>
					</select>
				</li>
				<li class="djc_filter_input">
					<select class="inputbox input input-mini" name="ms_unit" id="djcatsearch_radius">
						<?php 
						$units = array('km', 'mi');
						$current_unit = $jinput->getString('ms_unit', 'km');
						foreach ($units as $unit) { ?>
						<option value="<?php echo $unit; ?>"<?php if ($unit == $current_unit) {echo ' selected="selected"';} ?>><?php echo $unit; ?></option>
						<?php } ?>
					</select>
				</li>
			</ul>
		<?php } ?>
		
		<?php if ((int)$this->params->get('show_location_search') || (int)$this->params->get('show_search') || (int)$this->params->get('show_price_filter')) {?>
			<ul class="djc_filter_ul djc_filter_search djc_filter_buttons djc_clearfix" >
				<li class="djc_filter_button djc_filter_button djc_filter_button_go"><input type="submit" class="button btn djc_filter_go_btn" onclick="DJCatalog2SubmitSearch(false);" value="<?php echo JText::_( 'COM_DJCATALOG2_GO' ); ?>" /></li>
				<li class="djc_filter_button djc_filter_button djc_filter_button_reset"><input type="submit" class="button btn djc_filter_reset_btn" onclick="DJCatalog2SubmitSearch(true);" value="<?php echo JText::_( 'COM_DJCATALOG2_RESET' ); ?>" /></li>
			</ul>
			<script>
				function DJCatalog2SubmitSearch(clear) {
					var form = jQuery('#djcatalogForm');
					if (clear) {
						form.find('input[type="text"]').val('');
					}
					return form.submit();
				}
			</script>
		<?php } ?>
	<?php if (!($this->params->get('show_category_filter') > 0)) { ?>
		<input type="hidden" name="cid" value="<?php echo $this->escape($jinput->get('cid', null, 'string')); ?>" />
	<?php } ?>
	<?php if (!($this->params->get('show_producer_filter') > 0)) { ?>
		<input type="hidden" name="pid" value="<?php echo $this->escape($jinput->get('pid', null, 'string')); ?>" />
	<?php } ?>
	<?php if ($jinput->getInt('aid') > 0) { ?>
        <input type="hidden" name="aid" value="<?php echo $this->escape($jinput->get('aid', null, 'string')); ?>" />
    <?php } ?>
	<input type="hidden" name="option" value="com_djcatalog2" />
	<input type="hidden" name="view" value="items" />
	<input type="hidden" name="order" value="<?php echo $this->escape($jinput->get('order',$this->params->get('items_default_order', 'i.ordering'), 'string')); ?>" />
	<input type="hidden" name="dir" value="<?php echo $this->escape($jinput->get('dir',$this->params->get('items_default_order_dir', 'asc'), 'cmd')); ?>" />
	<input type="hidden" name="task" value="search" />
	<input type="hidden" name="Itemid" value="<?php echo $jinput->getInt('Itemid'); ?>" />
	</form>
	
	<?php if (!empty($this->filter_modules)) {?>
		<p class="djc_adv_search_wrap">
			<span class="djc_adv_search_toggle"><?php echo JText::_('COM_DJCATALOG2_ADVANCED_SEARCH'); ?> <span class="icon-cog"></span></span>
		</p>
	<?php } ?>
	
</div>
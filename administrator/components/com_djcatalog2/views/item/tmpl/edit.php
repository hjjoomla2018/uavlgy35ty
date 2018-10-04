<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die();

$document = JFactory::getDocument();

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

$document->addScript(JURI::root() . "administrator/components/com_djcatalog2/views/item/item.js");
$document->addScript(JURI::root() . "components/com_djcatalog2/assets/nicEdit/nicEdit.js");

/** new Calendar setup **/
DJCatalog2HtmlHelper::initCalendarScripts();

$params = JComponentHelper::getParams('com_djcatalog2');

$net_prices = (bool)((int)$params->get('price_including_tax', 1) == 0);


?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'item.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			
			<?php echo $this->form->getField('intro_desc')->save(); ?>

			var textareas = jQuery('#itemAttributes').find('textarea.nicEdit');
			if (textareas) {
				textareas.each(function(textarea){
					if (textarea.nicEditor != null && textarea.nicEditor) {
						var editor = textarea.nicEditor.instanceById(textarea.id);
						if (editor) {
							if (editor.getContent() == "<br />") {
								editor.setContent("");
							}
							editor.saveContent();
						}
					}
				});
			}
			
			Joomla.submitform(task, document.getElementById('item-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=item&layout=edit&id='.(int) $this->item->id); ?>"
	method="post" name="adminForm" id="item-form" class="form-validate"
	enctype="multipart/form-data">
	
	<div class="form-inline form-inline-header">
		<?php echo $this->form->getControlGroup('name'); ?>
		<?php echo $this->form->getControlGroup('alias'); ?>
		<?php echo $this->form->getControlGroup('sku'); ?>
	</div>
	
	<div class="form-horizontal">
		<fieldset>
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#details" data-toggle="tab"><?php echo empty($this->item->id) ? JText::_('COM_DJCATALOG2_NEW') : JText::_('COM_DJCATALOG2_EDIT'); ?></a>
				</li>
				<li>
					<a href="#commerce" data-toggle="tab"><?php echo JText::_('COM_DJCATALOG2_COMMERCE'); ?></a>
				</li>
				<li>
					<a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING');?>
				</a>
				</li>
				<li>
					<a href="#images" data-toggle="tab"><?php echo JText::_('COM_DJCATALOG2_IMAGES'); ?></a>
				</li>
				<li>
					<a href="#files" data-toggle="tab"><?php echo JText::_('COM_DJCATALOG2_FILES'); ?></a>
				</li>
				<li>
					<a href="#location" data-toggle="tab"><?php echo JText::_('COM_DJCATALOG2_FIELDSET_LOCATION'); ?></a>
				</li>
				<li>
					<a href="#attributes" data-toggle="tab"><?php echo JText::_('COM_DJCATALOG2_ATTRIBUTES'); ?></a>
				</li>
				<?php if ($this->item->parent_id == 0 && $this->item->id) { ?>
					<li>
						<a href="#combinations" data-toggle="tab"><?php echo JText::_('COM_DJCATALOG2_COMBINATIONS'); ?></a>
					</li>
					<?php if (count($this->customisations) > 0) {?>
					<li>
						<a href="#customisations" data-toggle="tab"><?php echo JText::_('COM_DJCATALOG2_PRODUCT_CUSTOMISATIONS'); ?></a>
					</li>
					<?php } ?>
				<?php } ?>
				
				<?php $fieldSets = $this->form->getFieldsets('params'); ?>
				<?php foreach ($fieldSets as $name => $fieldSet) { ?>
					<li>
						<a href="#params-<?php echo $name; ?>" data-toggle="tab"><?php echo $fieldSet->label ? JText::_($fieldSet->label) : JText::_($fieldSet->name); ?></a>
					</li>
				<?php } ?>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="details">
					<div class="row-fluid">
						<div class="span9">
						
							<h4><?php echo $this->form->getLabel('intro_desc'); ?></h4>
							<fieldset class="adminform">
								<?php echo $this->form->getInput('intro_desc'); ?>
							</fieldset>
							
							<h4><?php echo $this->form->getLabel('description'); ?></h4>
							<fieldset class="adminform">
								<?php echo $this->form->getInput('description'); ?>
							</fieldset>
						</div>
						<div class="span3">
							<div class="form-vertical">
								<?php echo $this->form->getControlGroup('published'); ?>
								<?php echo $this->form->getControlGroup('cat_id'); ?>
								<?php echo $this->form->getControlGroup('categories'); ?>
								<?php echo $this->form->getControlGroup('producer_id'); ?>
								<?php echo $this->form->getControlGroup('parent_id'); ?>
								<?php echo $this->form->getControlGroup('access'); ?>
								<?php echo $this->form->getControlGroup('featured'); ?>
								<?php echo $this->form->getControlGroup('labels'); ?>
				
								<div class="control-group">
									<div class="control-label">
										<label><?php echo JText::_('COM_DJCATALOG2_RELATED_ITEMS'); ?> </label>
									</div>
									<div class="controls">
									<?php if (empty($this->item->id) || ($this->item->id == 0)) { ?>
										<a class="btn" href="#"
											onclick="javascript:Joomla.submitbutton('item.apply')"> <?php echo JText::_('COM_DJCATALOG2_RELATED_ITEMS_SAVE_TO_ASSIGN'); ?>
										</a>
										<?php } else { ?>
										<a class="btn" data-toggle="modal" data-target="#djc_related_modal" href="#djc_related_modal">
											<?php echo JText::_('COM_DJCATALOG2_RELATED_ITEMS_ASSIGN'); ?>
										</a>
										<?php echo JHtmlBootstrap::renderModal('djc_related_modal', array('height' => '400px', 'width' => '800px', 'bodyHeight'  => '70', 'modalWidth'  => '80', 'url' => 'index.php?option=com_djcatalog2&amp;view=relateditems&amp;item_id='.$this->item->id.'&amp;tmpl=component', 'title'=> JText::_('COM_DJCATALOG2_RELATED_ITEMS'))); ?>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="tab-pane" id="commerce">
					<div class="row-fluid">
						<div class="span12">
							<?php echo $this->form->getControlGroup('available'); ?>
							<?php /* Not supported yet ?>
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('tangible'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('tangible'); ?>
								</div>
							</div>*/ ?>
							
							<?php echo $this->form->getControlGroup('onstock'); ?>
							<?php echo $this->form->getControlGroup('stock'); ?>
							<?php echo $this->form->getControlGroup('unit_id'); ?>
							<?php echo $this->form->getControlGroup('price'); ?>
			
							<div class="control-group">
								<div class="control-label">
									<label>&nbsp;&nbsp;&raquo;&nbsp;<?php echo JText::_($net_prices ? 'COM_DJCATALOG2_PRICE_INCL_TAX' : 'COM_DJCATALOG2_PRICE_EXCL_TAX')?></label>
								</div>
								<div class="controls">
									<input type="text" class="djc_price_tax readonly inputbox input input-mini" readonly="readonly" id="jform_price_tax" data-type="<?php echo $net_prices ? 'net' : 'gross'?>" value="" />
								</div>
							</div>
							
							<?php echo $this->form->getControlGroup('special_price'); ?>
			
							<div class="control-group">
								<div class="control-label">
									<label>&nbsp;&nbsp;&raquo;&nbsp;<?php echo JText::_($net_prices ? 'COM_DJCATALOG2_PRICE_INCL_TAX' : 'COM_DJCATALOG2_PRICE_EXCL_TAX')?></label>
								</div>
								<div class="controls">
									<input type="text" class="djc_price_tax readonly inputbox input input-mini" readonly="readonly" id="jform_special_price_tax" data-type="<?php echo $net_prices ? 'net' : 'gross'?>" value="" />
								</div>
							</div>
							
							<?php echo $this->form->getControlGroup('tax_rule_id'); ?>
							<?php echo $this->form->getControlGroup('price_tier_modifier'); ?>
							<?php echo $this->form->getControlGroup('price_tier_break'); ?>
							<?php echo $this->form->getControlGroup('price_tiers'); ?>
						</div>
					</div>
				</div>
	
				<div class="tab-pane" id="publishing">
					<div class="row-fluid">
						<div class="span12">
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('heading'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('heading'); ?></div>
							</div>
							
							<div class="control-group">
								<div class="control-label"><?php echo $this->form->getLabel('metatitle'); ?></div>
								<div class="controls"><?php echo $this->form->getInput('metatitle'); ?></div>
							</div>
							
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('metadesc'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('metadesc'); ?>
								</div>
							</div>
			
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('metakey'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('metakey'); ?>
								</div>
							</div>
							
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('created'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('created'); ?>
								</div>
							</div>
			
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('created_by'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('created_by'); ?>
								</div>
							</div>
							
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('modified'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('modified'); ?>
								</div>
							</div>
			
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('modified_by'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('modified_by'); ?>
								</div>
							</div>
							
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('publish_up'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('publish_up'); ?>
								</div>
							</div>
							
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('publish_down'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('publish_down'); ?>
								</div>
							</div>
							
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('hits'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('hits'); ?>
								</div>
							</div>
							
							<div class="control-group">
								<div class="control-label">
								<?php echo $this->form->getLabel('id'); ?>
								</div>
								<div class="controls">
								<?php echo $this->form->getInput('id'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
	
				<div class="tab-pane" id="images">
					<div class="row-fluid">
						<div class="span12">
						<?php echo DJCatalog2ImageHelper::renderInput('item',JFactory::getApplication()->input->getInt('id', null), (bool)JComponentHelper::getParams('com_djcatalog2')->get('multiple_image_upload', true)); ?>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="files">
					<div class="row-fluid">
						<div class="span12">
						<?php echo DJCatalog2FileHelper::renderInput('item',JFactory::getApplication()->input->getInt('id', null), (bool)JComponentHelper::getParams('com_djcatalog2')->get('multiple_file_upload', true)); ?>
						</div>
					</div>
				</div>
				
				<div class="tab-pane" id="location">
					<div class="row-fluid">
						<div class="span12">
						<?php foreach ($this->form->getGroup('location') as $field) : ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
						</div>
					</div>
				</div>
				
				<div class="tab-pane" id="attributes">
					<div class="row-fluid">
						<div class="span12">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('group_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('group_id'); ?>
								</div>
							</div>
							<div id="itemAttributes"></div>
						</div>
					</div>
				</div>
				
				<?php if ($this->item->parent_id == 0 && $this->item->id) {?>
					<?php 
					
					$wizardLang = array(
						'TH_NAME' => JText::_('COM_DJCATALOG2_NAME'),
						'TH_SKU' => JText::_('COM_DJCATALOG2_SKU'),
						'TH_STOCK' => JText::_('COM_DJCATALOG2_STOCK'),
						'TH_PRICE' => JText::_('COM_DJCATALOG2_PRICE'),
						'LABEL_CUSTOMISATIONS' => JText::_('COM_DJCATALOG2_PRODUCT_CUSTOMISATION'),
						'TH_MIN_QTY' => JText::_('COM_DJCATALOG2_MIN_QTY'),
						'TH_MAX_QTY' => JText::_('COM_DJCATALOG2_MAX_QTY'),
						'TH_ATTRIBUTES' => JText::_('COM_DJCATALOG2_ATTRIBUTES'),
						'BTN_ADD' => JText::_('COM_DJCATALOG2_ADD_NEW'),
						'BTN_REMOVE_ALL' => JText::_('COM_DJCATALOG2_REMOVE_ALL'),
						'BTN_REMOVE' => JText::_('COM_DJCATALOG2_REMOVE_ITEM'),
						'BTN_GENERATE' => JText::_('COM_DJCATALOG2_GENERATE')
					);
					
					$document->addScriptDeclaration('jQuery(document).ready(function(){
						var djcItemCombinations = '.json_encode($this->item->combinations).';
						var djcCartAttributes = '.json_encode($this->cart_attributes).';
						var djcItemCustomisations = '.json_encode($this->item->customisations).';
						var djcCustomisations = '.json_encode($this->customisations).';
						var djcWizardI18n = '.json_encode($wizardLang).';
						
						var DJCatalog2Combinations = new DJCatalog2CombinationsWizard("#itemCombinations", djcCartAttributes, djcItemCombinations, djcWizardI18n);
						var DJCatalog2Customisations = new DJCatalog2CustomisationsWizard("#itemCustomisations", djcCustomisations, djcItemCustomisations, djcWizardI18n);
						
						jQuery("#combination-groups").change(function(){
							DJCatalog2Combinations.applyGroups(jQuery(this).val());
						});
						jQuery("#combination-groups").trigger("change");
					});');
					
					$cGroupIds = array();
					if (count($this->item->combinations)) {
						foreach($this->item->combinations as $combination) {
							foreach($combination->fields as $cfield) {
								if ($cfield->group_id != '') {
									$cGroupIds[] = (int)$cfield->group_id;
								}
							}
						}
					}
					$cGroupIds = array_unique($cGroupIds);
					
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->select('g.id, g.name');
					$query->from('#__djc2_items_extra_fields_groups AS g');
					$query->join('left', '#__djc2_items_extra_fields AS f ON f.group_id=g.id');
					$query->where('f.cart_variant=1');
					$query->group('g.id');
					$query->having('COUNT(f.id) > 0');
					$query->order('g.name');
					$db->setQuery($query);
					
					$groups = $db->loadObjectList();
					$options = array();
					foreach ($groups as $group) {
						$options[] = JHTML::_('select.option', $group->id, $group->name);
					}
					$attr = 'multiple="multiple"';
					$combinationGroups = JHtml::_('select.genericlist', $options, 'combination-groups', trim($attr), 'value', 'text', $cGroupIds);
					
					?>
					<div class="tab-pane" id="combinations">
						<div class="row-fluid">
							<div class="span12">
								<div id="itemCombinations">
									<div class="row-fluid">
										<div class="span3 djcCombinationsGenerator form-vertical">
											<div class="control-group">
												<div class="controls">
													<?php echo $combinationGroups; ?>
												</div>
											</div>
										</div>
										<div class="span9 djcCombinationsTable"></div>
									</div>
								</div>
								<input type="hidden" name="hasCombinations" value="1" />
							</div>
						</div>
					</div>
					
					
					<?php if (count($this->customisations)) {?>
					<div class="tab-pane" id="customisations">
						<div class="row-fluid">
							<div class="span12">
								<div id="itemCustomisations">
									<div class="row-fluid">
										<div class="span3 djcCustomisationsGenerator form-vertical"></div>
										<div class="span9 djcCustomisationsTable"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
					<input type="hidden" name="hasCustomisations" value="1" />
				<?php } ?>
				
				<?php echo $this->loadTemplate('params'); ?>
			</div>
		</fieldset>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<script>
(function($){
	$(document).ready(function(){
		var catId = $('#jform_cat_id');
		var categories = $('#jform_categories');

		var prev = catId.val();
		prev = (prev==null) ? 0 : prev;
		catId.attr('data-value', prev);
		
		catId.change(function(e){
			prev = $(this).attr('data-value');
			var cur = $(this).val();
			cur = (cur==null) ? 0 : cur;

			categories.find('option[value="'+prev+'"]').removeAttr('selected');
			categories.trigger("liszt:updated");
			 $(this).attr('data-value',  cur);
		});
	});
})(jQuery);
</script>

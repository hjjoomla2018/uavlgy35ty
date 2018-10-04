<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$k = 0; 
$i = 1; 
$col_count = $this->params->get('category_columns',2);
$col_width = ((100/$col_count)-0.01);
foreach($this->subcategories as $item) {
	$subcategory = $this->categories->get($item->id);
	if ($subcategory->published != 1) { 
		//continue; 
	}
	
	$subcategory->_link = DJCatalogHelperRoute::getCategoryRoute($subcategory->id.':'.$subcategory->alias);
	
	$newrow_open = $newrow_close = false;
	if ($k % $col_count == 0) $newrow_open = true;
	if (($k+1) % $col_count == 0 || count($this->subcategories) <= $k+1) $newrow_close = true;
        
	$rowClassName = 'djc_clearfix djc_subcategory_row djc_subcategory_row';
	if ($k == 0) $rowClassName .= '_first';
	if (count($this->subcategories) <= ($k + $col_count)) $rowClassName .= '_last';
	
	$colClassName ='djc_subcategory_col';
	if ($k % $col_count == 0) { $colClassName .= '_first'; }
	else if (($k+1) % $col_count == 0) { $colClassName .= '_last'; }
	else {$colClassName .= '_'.($k % $col_count);}
	
	if ($newrow_open) { $i = 1 - $i; ?>
<div class="<?php echo $rowClassName.'_'.$i; ?> djc2_cols_<?php echo $col_count ?>"><?php }
	$k++;
?>
<div class="djc_subcategory pull_left <?php echo $colClassName; ?>" style="width:<?php echo $col_width; ?>%">
	<div class="djc_subcategory_bg">
		<div class="djc_subcategory_in djc_clearfix">
			<?php if ((int)$this->params->get('image_link_subcategory', 0) != -1) { ?>
				<?php 
				$variant = 'img';
				$imgLink = (int)$this->params->get('image_link_subcategory', 0);
				if ($imgLink == 0) {
					$variant = 'link';
				} else if ($imgLink == 1) {
					$variant = 'popup';
				} 
				$layout = new JLayoutFile('com_djcatalog2.listimage', null, array('component'=> 'com_djcatalog2'));
				$imageData = array(	'item' => &$subcategory, 
									'type' => 'category', 
									'size' => 'medium', 
									'variant' => $variant, 
									'hover_img' => false,
									'context' => 'com_djcatalog2.subcategories.list', 
									'params' => &$this->params);
				echo $layout->render($imageData);
				?> 
			<?php } ?>
			
			<div class="djc_title">
				<h3>
					<a href="<?php echo JRoute::_($subcategory->_link);?>">
						<?php echo $subcategory->name; ?>
					</a>
				</h3>
			</div>
			<?php if ($this->params->get('category_show_intro') && JString::strlen(trim($subcategory->description)) > 0) {?>
			<div class="djc_description">
				<?php if ($this->params->get('category_intro_length') > 0 && $this->params->get('category_intro_trunc') == '1') {
						?><?php echo DJCatalog2HtmlHelper::trimText($subcategory->description, $this->params->get('category_intro_length'));?><?php
					}
					else {
						echo $subcategory->description; 
					}
				?>
			</div>
			<?php } ?>
			<?php if ((int)$this->params->get('subcategory_showchildren', 0) == 1) { ?>
				<?php 
				$sub_category_obj = $this->categories->get($item->id);
				if (!empty($sub_category_obj)) {
					$children = $sub_category_obj->getChildren();
					$children_count = count($children);
					if ($children_count > 0) { 
					$child_counter = 0;
					?>
					<p class="djc_subcategory_children">
						<?php foreach ($children as $child) { ?>
							<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute($child->id.':'.$child->alias)); ?>"><?php echo $child->name; ?><?php echo (isset($child->item_count)) ? '('.(int)$child->item_count.')' : ''; ?></a><?php 
						$child_counter++;
						if ($child_counter < $children_count) echo ', ';
						}
						?>
					</p>
					<?php } ?>
				<?php } ?>
			<?php } ?>
			<?php if ($this->params->get('showreadmore_subcategory')) { ?>
				<div class="clear"></div>
				<div class="djc_readon">
					<a class="btn readmore" href="<?php echo JRoute::_(DJCatalogHelperRoute::getCategoryRoute($subcategory->id.':'.$subcategory->alias)); ?>" class="readmore"><?php echo JText::_('COM_DJCATALOG2_BROWSE'); ?></a>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
<?php if ($newrow_close) { ?></div><?php } ?>
<?php } ?>
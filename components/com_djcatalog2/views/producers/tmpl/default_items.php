<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');
$user		= JFactory::getUser();
?>

<?php
$k = 0; 
$i = 1; 
$col_count = $this->params->get('producers_columns',2);
$col_width = ((100/$col_count)-0.01);

foreach ($this->items as $item) {

	$item->slug = (!empty($item->alias)) ? $item->id.':'.$item->alias : $item->id;
	$item->_link  = DJCatalogHelperRoute::getProducerRoute($item->slug);
	
	$newrow_open = $newrow_close = false;
	if ($k % $col_count == 0) $newrow_open = true;
	if (($k+1) % $col_count == 0 || count($this->items) <= $k+1) $newrow_close = true;
	        
	$rowClassName = 'djc_clearfix djc_item_row djc_item_row';
	if ($k == 0) $rowClassName .= '_first';
	if (count($this->items) <= ($k + $this->params->get('producers_columns',2))) $rowClassName .= '_last';
	
	$colClassName ='djc_item_col';
	if ($k % $col_count == 0) { $colClassName .= '_first'; }
	else if (($k+1) % $col_count == 0) { $colClassName .= '_last'; }
	else {$colClassName .= '_'.($k % $col_count);}
	$k++;
	
	if ($newrow_open) { $i = 1 - $i; ?>
	<div class="<?php echo $rowClassName.'_'.$i; ?> djc2_cols_<?php echo $col_count ?>">
	<?php }
	?>
        <div class="djc_item djc_producer_item pull_left <?php echo $colClassName; ?>" style="width:<?php echo $col_width; ?>%">
        <div class="djc_item_bg">
		<div class="djc_item_in djc_clearfix">
        <?php if ((int)$this->params->get('producers_image_link', 0) != -1) { ?>
        	<?php 
			$variant = 'img';
			$imgLink = (int)$this->params->get('producers_image_link', 0);
			if ($imgLink == 0) {
				$variant = 'link';
			} else if ($imgLink == 1) {
				$variant = 'popup';
			} 
			$layout = new JLayoutFile('com_djcatalog2.listimage', null, array('component'=> 'com_djcatalog2'));
			$imageData = array(	'item' => &$item, 
								'type' => 'producer', 
								'size' => 'medium', 
								'variant' => $variant, 
								'hover_img' => false,
								'context' => 'com_djcatalog2.producers.list', 
								'params' => &$this->params);
			echo $layout->render($imageData);
			?> 
		<?php } ?>
		<?php if ((int)$this->params->get('producers_show_name','1') > 0 ) {?>
		<div class="djc_title">
	        <h3>
	        <?php 
	        if ((int)$this->params->get('producers_show_name','1') == 2 ) {
	        	echo $item->name;
	        } else { ?>
	        	<a href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->slug)); ?>"><?php echo $item->name; ?></a>
	        <?php } ?>
	        </h3>
	    </div>
	    <?php } ?>
	    	<?php if ($this->params->get('producers_show_intro', '0') == '1' && JString::strlen(trim($item->description)) > 0) { ?>
            <div class="djc_description">
				<div class="djc_introtext">
					<?php if ($this->params->get('producers_intro_length') > 0  && $this->params->get('producers_intro_trunc') == '1' ) {
							?><p><?php echo DJCatalog2HtmlHelper::trimText($item->description, $this->params->get('producers_intro_length'));?></p><?php
						}
						else {
							echo $item->description; 
						}
					?>
				</div>
            </div>
            <?php } ?>
            <?php if ($this->params->get('producers_readmore', '0') == '1') { ?>
				<div class="clear"></div>
				<div class="djc_readon">
					<a class="btn readmore" href="<?php echo JRoute::_(DJCatalogHelperRoute::getProducerRoute($item->slug)); ?>" class="readmore"><?php echo JText::sprintf('COM_DJCATALOG2_READMORE'); ?></a>
				</div>
			<?php } ?>
         </div>
 	</div>
	<div class="djc_clear"></div>
	</div>
	<?php if ($newrow_close) { ?>
		</div>
	<?php } ?>
<?php } ?>
<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */
use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die;

?>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="control-group span6">
			<div class="controls">
				<label><?php echo JText::_('COM_DJCATALOG2_CATEGORY_BATCH_LBL'); ?></label>
				<?php echo JHtml::_('select.genericlist', $this->categories, 'batch[category]', '', 'value', 'text', null, 'batch-category'); ?>
				
				<div class="control-group radio">
					<?php echo JText::_('COM_DJCATALOG2_CATEGORY_BATCH_INFO');?>
					<div class="controls">
						<label for="batch-category-add">
							<input type="radio" id="batch-category-add" name="batch[category_moveadd]" value="a" />
							<?php echo JText::_('COM_DJCATALOG2_CATEGORY_BATCH_ADD'); ?>
						</label>
						<label for="batch-category-move">
							<input type="radio" id="batch-category-move" name="batch[category_moveadd]" value="m" />
							<?php echo JText::_('COM_DJCATALOG2_CATEGORY_BATCH_MOVE'); ?>
						</label>
					</div>
				</div>
			</div>
		</div>
		
		<div class="control-group span6">
			<div class="controls">
				<label><?php echo JText::_('COM_DJCATALOG2_PRODUCER_BATCH_LBL'); ?></label>
				<?php 
				$producers = array();
				$producers[] = ArrayHelper::toObject(array('id' => '', 'name'=>'- '.JText::_('COM_DJCATALOG2_SELECT_PRODUCER').' -', 'published' => null));
				$producers[] = ArrayHelper::toObject(array('id' => '-1', 'name'=>'- '.JText::_('JNONE').' -', 'published' => null));
				$producers = count($this->producers) ? array_merge($producers,$this->producers) : $producers;
				echo JHtml::_('select.genericlist', $producers, 'batch[producer]', '', 'id', 'name', null, 'batch-producer');
				?>
			</div>
		</div>
	</div>
</div>

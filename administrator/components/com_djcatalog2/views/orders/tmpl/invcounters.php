<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 *
 */
// no direct access
defined('_JEXEC') or die;

JHtmlBehavior::core();
JHtmlBehavior::formvalidator();

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (document.formvalidator.isValid(document.getElementById('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&view=orders&layout=invcounters&tmpl=component');?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="btn-group pull-left">
				<button class="btn btn-primary" onclick="Joomla.submitbutton('orders.save_counters');"><?php echo JText::_('JAPPLY'); ?></button>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th width="1%" class="title">
						<?php echo JText::_('COM_DJCATALOG2_INV_CNT_YEAR'); ?>
					</th>
					<th width="15%"  class="center">
						<?php echo JText::_('COM_DJCATALOG2_INV_CNT_COUNTER'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>
			<?php foreach ($this->counters as $i => $item) { ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<label for="djc_counter-<?php echo $item->year; ?>">
							<?php echo $this->escape($item->year); ?>
						</label>
					</td>
					<td class="center">
						<input id="djc_counter-<?php echo $item->year; ?>" class="input input-mini validate-number required" required="requried" aria-required="true" type="number" step="1" min="0" value="<?php echo $item->counter; ?>" name="djc_counters[<?php echo $item->year; ?>]" />
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	
		<div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>



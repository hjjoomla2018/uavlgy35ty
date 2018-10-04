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

?>
<a class="btn" type="button" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="if (document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');?>'); jQuery('#items-modal').modal('hide'); return false;} else {Joomla.submitbutton('item.batch');}">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>

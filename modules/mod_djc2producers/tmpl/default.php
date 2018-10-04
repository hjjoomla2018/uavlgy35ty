<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

$mid = $module->id;

$task = ($params->get('type', '0') == '0') ? 'search' : 'producersearch';

?>
<div class="mod_djc2producers">
<form action="index.php" method="post" name="producersForm_<?php echo $mid; ?>" id="producersForm_<?php echo $mid; ?>" >
	<input type="hidden" name="option" value="com_djcatalog2" />
	
	<?php if ($order) { ?>
	<input type="hidden" name="order" value="<?php echo $order; ?>" />
	<?php } ?>
	
	<?php if ($orderDir) {?>
	<input type="hidden" name="dir" value="<?php echo $orderDir; ?>" />
	<?php } ?>
	
	<input type="hidden" name="cid" value="<?php echo $cid; ?>" />
	<input type="hidden" name="task" value="<?php echo $task; ?>" />
    <?php
		$options = array();
		$options[] = JHTML::_('select.option', 0,JText::_('MOD_DJC2PRODUCERS_CHOOSE_PRODUCER') );
		foreach($producers as $producer){
			$options[] = JHTML::_('select.option', $producer['id'], $producer['name']);
			
		}

		echo JHTML::_('select.genericlist', $options, 'pid', 'class="inputbox mod_djc2producers_list" onchange="producersForm_'.$mid.'.submit()"', 'value', 'text', $prod_id, 'mod_djc2producers_pid');
?>
<input type="submit" style="display: none;"/>
</form>
</div>
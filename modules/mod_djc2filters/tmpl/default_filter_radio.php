<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) 2010 Blue Constant Media LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://design-joomla.eu
 * @author email contact@design-joomla.eu
 * @developer $Author: michal $ Michal Olczyk - michal.olczyk@design-joomla.eu
 *
 */

defined ('_JEXEC') or die('Restricted access');

$onchange = $autosubmit ? 'onchange="this.form.submit();"' : '';
?>

<?php foreach ($item->optionsArray as $key=>$optionId) {
	$optionAlias = JFilterOutput::stringURLSafe($item->optionValuesArray[$key]);
	$optionIdAlias = $optionId;//.':'.$optionAlias;
	$active = (in_array($optionId, $item->selectedOptions)) ? true:false;
	$checked = ($active) ? 'checked="checked"' : '';

	if ($active || $item->optionCounterArray[$key] > 0 || $item->selected) { ?>
<label><input type="radio" name="<?php echo 'f_'.$item->alias; ?>"
	value="<?php echo $optionIdAlias?>" <?php echo $checked; ?> <?php echo $onchange; ?> /> <?php echo $item->optionValuesArray[$key]; 
	if ($show_counter == 1 /*&& !$item->selected*/) {
		echo ' ['.$item->optionCounterArray[$key].']';
	}
	?></label>
<?php } ?>
<?php } ?>
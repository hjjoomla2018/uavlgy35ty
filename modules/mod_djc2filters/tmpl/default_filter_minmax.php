<?php
/**
* @version $Id: default_filter_minmax.php 417 2015-05-11 06:08:18Z michal $
* @package DJ-Catalog2
* @copyright Copyright (C) 2010 Blue Constant Media LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer $Author: michal $ Michal Olczyk - michal.olczyk@design-joomla.eu
*
*
*** the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ-Catalog2 is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ-Catalog2. If not, see <http://www.gnu.org/licenses/>.
*
*/

defined ('_JEXEC') or die('Restricted access');

?>

<?php 
$onchange = $autosubmit ? 'onchange="this.form.submit();"' : '';

$min = '';
$minSelected = '';
$minDefault = '';
$max = '';
$maxSelected = '';
$maxDefault = '';
if (count($item->optionValuesArray)) {
	sort($item->optionValuesArray);
	$min = $minDefault = $item->optionValuesArray[0];
	$max = $maxDefault = $item->optionValuesArray[count($item->optionValuesArray)-1];
}
$postData = $app->input->get('f_'.$item->alias, array(), 'array');

if (count($postData) == 2) {
	$minSelected = (strlen($postData[0]) > 0) ? $postData[0] : '';
	$maxSelected = (strlen($postData[1]) > 0) ? $postData[1] : '';
	$min = (strlen($postData[0]) > 0) ? $postData[0] : $min;
	$max = (strlen($postData[1]) > 0) ? $postData[1] : $max;
} else if ($postData = $app->input->get('f_'.$item->alias, '', 'raw')) {
	$postData = explode(',',$postData, 2);
	if (count($postData) == 2) {
		$minSelected = (strlen($postData[0]) > 0) ? $postData[0] : '';
		$maxSelected = (strlen($postData[1]) > 0) ? $postData[1] : '';
		$min = (strlen($postData[0]) > 0) ? $postData[0] : $min;
		$max = (strlen($postData[1]) > 0) ? $postData[1] : $max;
	}
}
?>

<?php if ($item->filter_type == 'minmax') {?>
<select name="<?php echo 'f_'.$item->alias?>[]" class="inputbox input input-mini djc_filter_minmax djc_filter_min" <?php echo $onchange; ?>>
<option value=""><?php echo JText::_('MOD_DJC2FILTERS_PLEASE_SELECT_FROM');  ?></option>
<?php foreach ($item->optionValuesArray as $optionValue) { ?>
	<?php $selected = ($optionValue == $minSelected) ? 'selected="selected"' : ''; ?>
	<option value="<?php echo $optionValue ?>" <?php echo $selected; ?>><?php echo $optionValue; ?></option>
<?php } ?>
</select>
<span class="djc_filter_minmax_separator"> - </span>
<select name="<?php echo 'f_'.$item->alias?>[]" class="inputbox input input-mini djc_filter_minmax djc_filter_max" <?php echo $onchange; ?>>
<option value=""><?php echo JText::_('MOD_DJC2FILTERS_PLEASE_SELECT_TO');  ?></option>
<?php foreach ($item->optionValuesArray as $optionValue) { ?>
	<?php $selected = ($optionValue == $maxSelected) ? 'selected="selected"' : ''; ?>
	<option value="<?php echo $optionValue ?>" <?php echo $selected; ?>><?php echo $optionValue; ?></option>
<?php } ?>
</select>
<?php } else {?>
<input type="text" name="<?php echo 'f_'.$item->alias?>[]" placeholder="<?php echo $minDefault; ?>" value="<?php echo $minSelected; ?>" class="inputbox input input-mini djc_filter_minmax djc_filter_min" />
<span class="djc_filter_minmax_separator"> - </span>
<input type="text" name="<?php echo 'f_'.$item->alias?>[]" placeholder="<?php echo $maxDefault; ?>" value="<?php echo $maxSelected; ?>" class="inputbox input input-mini djc_filter_minmax djc_filter_max" />
<?php } ?>

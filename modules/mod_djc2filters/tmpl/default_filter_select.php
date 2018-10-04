<?php
/**
* @version $Id: default_filter_select.php 656 2016-10-01 04:45:38Z michal $
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

$onchange = $autosubmit ? 'onchange="this.form.submit();"' : '';
?>

<select name="<?php echo 'f_'.$item->alias; ?>" class="inputbox" <?php echo $onchange;?>>
<option value=""><?php echo JText::_('MOD_DJC2FILTERS_PLEASE_SELECT');  ?></option>
<?php foreach ($item->optionsArray as $key=>$optionId) { ?>
	<?php 
	$optionAlias = JFilterOutput::stringURLSafe($item->optionValuesArray[$key]);
	$optionIdAlias = $optionId;//.':'.$optionAlias;
	$active = (in_array($optionId, $item->selectedOptions)) ? true:false;
	$selected = ($active) ? 'selected="selected"' : '';
	?>
	<?php if ($active || $item->optionCounterArray[$key] > 0 || !$show_counter) { ?>
	<option value="<?php echo $optionIdAlias; ?>" <?php echo $selected; ?>>
		<?php echo $item->optionValuesArray[$key];
		if ($show_counter == 1 /*&& !$item->selected*/) { 
			echo ' ['.$item->optionCounterArray[$key].']';
		}
	?>
	</option>
	<?php } ?>
<?php } ?>
</select>
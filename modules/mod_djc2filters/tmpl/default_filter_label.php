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

?>

<?php if (!empty($item->selectedOptions) || $item->selected) {
	$filter_query = $query;
	unset($filter_query['djcf'][$item->alias]);
	if (empty($filter_query['djcf'])) {
		unset($filter_query['cm']);
		$filter_query['task'] = 'search_reset';
	} else {
		$isEmpty = true;
		foreach ($filter_query['djcf'] as $k=>$v) {
			if (!empty($filter_query['djcf'][$k])) {
				$isEmpty = false;
				break;
			}
		}
		if ($isEmpty) {
			unset($filter_query['cm']);
		}
	}
	if (!empty($filter_query['djcf'])) {
		$filters = array();
		foreach ($filter_query['djcf'] as $a => $v) {
			if (is_array($v)){
				foreach ($v as $k=>$p) {
					$v[$k] = ($p == '') ? '' : (int)$p;
				}
				$val = implode(',', $v);
				if ($val == ',') {
					continue;
				}
				$filters['f_'.$a] = $val;
			} else if ($v != '') {
				$filters['f_'.$a] = (int)$v;
			}
		}
		unset($filter_query['djcf']);
		$filter_query = array_merge($filter_query, $filters);
	}

	$uri->setQuery($filter_query);
	?>
<a title="<?php echo JText::_('MOD_DJC2FILTERS_RESET_LABEL'); ?>"
	class="field_reset_button"
	href="<?php echo htmlspecialchars($uri->toString()); ?>"> <?php echo JText::_('MOD_DJC2FILTERS_RESET')?>
</a>
<?php } ?>
<label class="mod_djc2filters_group_label"><?php echo $item->name; ?> </label>

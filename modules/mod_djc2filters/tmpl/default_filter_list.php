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
<ul>
	<?php 
	foreach ($item->optionsArray as $key=>$optionId) {
		//$optionAlias = preg_replace('#[^0-9a-zA-Z\-]#', '_',strtolower(trim($item->optionValuesArray[$key])));
		$optionAlias = JFilterOutput::stringURLSafe($item->optionValuesArray[$key]);
		$optionIdAlias = $optionId;//.':'.$optionAlias;
		$active = (in_array($optionId, $item->selectedOptions)) ? true:false;
		$class = ($active) ? 'class="active"' : '';
		$filter_query = $query;
		$filter_query['cm'] = '0';

		if (!array_key_exists('djcf', $filter_query)) {
			$filter_query['djcf'] = array();
		}
		/*
		 if (!array_key_exists($item->alias, $filter_query['djcf']) || !is_array($filter_query['djcf'][$item->alias])) {
		$filter_query['djcf'][$item->alias] = array();
		}
		*/

		if (!array_key_exists($item->alias, $filter_query['djcf'])) {
			if ($item->type == 'checkbox') {
				$filter_query['djcf'][$item->alias] = array();
			} else {
				$filter_query['djcf'][$item->alias] = '';
			}
		} else if ($item->type == 'checkbox' && is_scalar($filter_query['djcf'][$item->alias])) {
			$filter_query['djcf'][$item->alias] = explode(',', $filter_query['djcf'][$item->alias]);
		}

		if ($active) {
			if (array_key_exists('djcf', $filter_query)) {
				if (array_key_exists($item->alias, $filter_query['djcf'])) {
					if ($item->type == 'checkbox') {
						$optionKey = array_search($optionIdAlias, $filter_query['djcf'][$item->alias]);
						if ($optionKey >= 0) {
							unset($filter_query['djcf'][$item->alias][$optionKey]);
						}
					} else {
						unset($filter_query['djcf'][$item->alias]);
					}
				}
			}
		}
		else {
			if ($item->type == 'checkbox') {
				$filter_query['djcf'][$item->alias][] = $optionIdAlias;
			} else {
		    $filter_query['djcf'][$item->alias] = $optionIdAlias;
		}
		}

		if (empty($filter_query['djcf'])) {
		unset($filter_query['cm']);
	} else {
$isEmpty = true;
foreach ($filter_query['djcf'] as $k=>$v) {
    if (!empty($filter_query['djcf'][$k])) {
        $isEmpty = false;
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
        			/*foreach ($v as $k=>$p) {
        				$v[$k] = (int)$p;
        			}*/
        			if (!empty($v)) {
        				$filters['f_'.$a] = implode(',', $v);
        			}
        		} else {
        			/*if ((int)$v) {
        				$filters['f_'.$a] = (int)$v;
        			}*/
        			$filters['f_'.$a] = $v;
        		}
        	}
        	unset($filter_query['djcf']);
        	$filter_query = array_merge($filter_query, $filters);
        }
        $uri->setQuery($filter_query);
        ?>
	<?php if ($active || $item->optionCounterArray[$key] > 0) { ?>
	<li <?php echo $class; ?>><a href="<?php echo htmlspecialchars($uri->toString()); ?>"><?php 
		echo $item->optionValuesArray[$key];
		if ($show_counter == 1) {
		?> <small>[<?php echo $item->optionCounterArray[$key] ?>]
		</small> <?php }
		?></a></li>
	<?php } ?>
	<?php } ?>
</ul>

<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');

?>

<?php 
if (count($producers) > 0) {
$jinput = JFactory::getApplication()->input;
$active = ($jinput->get('pid', null, 'int') > 0 && $jinput->get('option', '', 'string') == 'com_djcatalog2') ? $jinput->get('pid', null, 'int') : 0;
?>
<ul class="menu nav mod_djc2_producer_list">
	<?php 
	foreach($producers as $producer){
		$class = 'level0 djc_prodid-'.$producer['id'];
		if ($active > 0 && $active == (int)$producer['id']) {
			$class .= ' active current';	
		}
        
        $class = 'class="'.$class.'"';
        
		if ($params->get('type', '0') == '0') {
			$url = DJCatalogHelperRoute::getCategoryRoute($cid, $producer['id'].':'.$producer['alias']).'&cm=0';
		} else {
			$url = DJCatalogHelperRoute::getProducerRoute($producer['id'].':'.$producer['alias']);
		}
		?>
		<li <?php echo $class;?>><a href="<?php echo JRoute::_($url); ?>"><span><?php echo $producer['name']; ?></span></a></li>
		<?php 
	}
	?>
</ul>

<?php } ?>

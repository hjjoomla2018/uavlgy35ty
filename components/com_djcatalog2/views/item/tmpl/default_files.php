<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access'); ?>

<?php 

$grouppedFiles = array();

foreach($this->item->files as $file) {
	$hash = $file->group_label ? base64_encode($file->group_label) : 0;
	
	if (!isset($grouppedFiles[$hash]))
	{
		$grouppedFiles[$hash] = new stdClass();
		$grouppedFiles[$hash]->label = $file->group_label ? $file->group_label : null;
		$grouppedFiles[$hash]->files = array();
	}
	
	$grouppedFiles[$hash]->files[] = $file;

}

?>

<div class="djc_files">
<h3><?php echo JText::_('COM_DJCATALOG2_FILES'); ?></h3>
<?php foreach ($grouppedFiles as $group) {?>
	<?php if ($group->label && $group->label != '-') { ?>
	<h4 class="djc_att_group_label"><?php echo $group->label; ?></h4>
	<?php } ?>
	<ul>
	<?php foreach($group->files as $file) {?>
		<li class="djc_file">
			<a target="_blank" class="btn" href="<?php echo ('index.php?option=com_djcatalog2&format=raw&task=download&fid='.$file->id);?>">
				<span><?php echo htmlspecialchars($file->caption); ?></span>
			</a>
			<?php if ($this->params->get('show_fileinfos_item', 1) > 0) {?>
			<br />
			<span class="djc_filesize small"><?php echo $file->ext; ?> | <?php echo $file->size; ?> | <?php echo sprintf(JText::_('COM_DJCATALOG2_FILE_HITS'),$file->hits); ?></span>
			<?php } ?>
		</li>
	<?php } ?>
	</ul>
<?php } ?>

</div>
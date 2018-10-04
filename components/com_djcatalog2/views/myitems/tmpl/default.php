<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined ('_JEXEC') or die('Restricted access');
$user = JFactory::getUser();
JHtmlBehavior::core();

?>

<?php //if ($this->params->get( 'show_page_heading', 1)) { ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php //} ?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		Joomla.submitform(task, document.getElementById('submit-item-form'));
	}
</script>

<div id="djcatalog" class="djc_mylist<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

<?php if ($user->authorise('core.create', 'com_djcatalog2')) { ?>
<div class="formelm-buttons djc_form_toolbar">
	<form action="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=itemform.add'); ?>" method="post" name="submit-item-form" id="submit-item-form">
		<button type="button" onclick="Joomla.submitbutton('itemform.add')" class="button btn">
			<?php echo JText::_('COM_DJCATALOG2_ADD') ?>
		</button>
		<input type="hidden" name="task" value="" />
	</form>
	</div>
<?php } ?>

<?php if ((int)$this->params->get('fed_show_search', 1) == 1 && (count($this->items) > 0 || JFactory::getApplication()->input->getString('search'))){ ?>
<div class="djc_filters djc_clearfix">
	<?php echo $this->loadTemplate('search'); ?>
</div>
<?php } ?>

<?php if ((int)$this->params->get('fed_show_ordering', 1) == 1 && count($this->items) > 0){ ?>
<div class="djc_order djc_clearfix">
	<?php echo $this->loadTemplate('order'); ?>
</div>
<?php } ?>

<?php if (count($this->items) > 0){ ?>
	<div class="djc_items djc_clearfix">
		<?php echo $this->loadTemplate('table'); ?>
	</div>
<?php } ?>

<?php if ($this->pagination->total > 0) { ?>
<div class="djc_pagination pagination djc_clearfix">
<?php
	echo $this->pagination->getPagesLinks();
?>
</div>
<?php } ?>

<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>

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
$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2') || $user->authorise('core.admin', 'com_djcatalog2');
?>

<?php //if ($this->params->get( 'show_page_heading', 1)) { ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php //} ?>

<div id="djcatalog" class="djc_question<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

<div class="djc_attributes">
	<h2>
	<?php if ($salesman) {?>
		<a data-toggle="modal" data-target="#djc_orderform_modal" href="#djc_orderform_modal" class="btn btn-mini btn-primary pull-left djc_edit_button">
			<?php echo JText::_('COM_DJCATALOG2_EDIT'); ?>
		</a>
		<?php /*?><a class="btn btn-mini btn-primary pull-left djc_edit_button" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=questionform.edit&id='.$this->item->id); ?>"><?php echo JText::_('COM_DJCATALOG2_EDIT'); ?></a><?php */ ?>
	<?php } ?>
	<?php echo JText::_('COM_DJCATALOG2_QUESTION_DETAILS'); ?>
	</h2>
	
	<table width="100%" cellpadding="0" cellspacing="0"
		class="djc_question_details_table jlist-table table-condensed table"
		id="djc_question_details_table">
		<tr class="djc_attribute">
			<td class="djc_label"><?php echo JText::_('COM_DJCATALOG2_ORDER_DATE'); ?>
			</td>
			<td class="djc_value"><?php echo JHtml::_('date', $this->item->created_date, 'd-m-Y'); ?>
			</td>
		</tr>
		<tr class="djc_attribute">
			<td class="djc_label"><?php echo JText::_('COM_DJCATALOG2_USER_PROFILE'); ?>
			</td>
			<td class="djc_value">
			<?php if ($this->item->company) { ?>
				<strong><?php echo $this->item->company?></strong><br />
			<?php }?>
			<?php if (!empty($this->item->_name)) {?>
			<strong><?php echo $this->item->_name; ?></strong><br />
			<?php } ?>
			<?php 
				$addr = array();
				if (!empty($this->item->address)) {
					$addr['address'] = $this->item->address;
				}
				if (!empty($this->item->postcode)) {
					$addr['postcode'] = $this->item->postcode;
				}
				if (!empty($this->item->city)) {
					$addr['city'] = $this->item->city;
				}
				if (!empty($this->item->country)) {
					$addr['country'] = $this->item->country;
				}
				if (!empty($this->item->state)) {
					$addr['state'] = $this->item->state;
				}
				if (count($addr) > 0) {
					echo implode(', ', $addr).'<br />';
				}
			?>
			</td>
		</tr>
		<?php if ($this->item->customer_note) {?>
			<tr class="djc_attribute">
				<td class="djc_label"><?php echo JText::_('COM_DJCATALOG2_MESSAGE'); ?>
				</td>
				<td class="djc_value"><?php echo nl2br($this->item->customer_note); ?>
				</td>
			</tr>
		<?php } ?>
	</table>
</div>

<h2><?php echo JText::_('COM_DJCATALOG2_QUESTION_ITEMS'); ?></h2>

<div class="djc_order_items djc_clearfix">
	<?php echo $this->loadTemplate('table'); ?>
</div>

<a class="btn djc_back_to_questions_btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getQuestionsRoute());?>"><span><?php echo JText::_('COM_DJCATALOG2_BACK_TO_QUESTIONS'); ?></span></a>

<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>

<?php if ($salesman) {?>
<?php echo JHtmlBootstrap::renderModal('djc_orderform_modal', array('height' => '600px', 'url' => JRoute::_('index.php?option=com_djcatalog2&task=questionform.edit&id='.$this->item->id).'&tmpl=component', 'title'=> JText::_('COM_DJCATALOG2_QUESTION_DETAILS'))); ?>
<?php } ?>

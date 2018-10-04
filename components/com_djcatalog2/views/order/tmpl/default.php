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
 
/*$statuses = array('N', 'A', 'P', 'C', 'R', 'W', 'F');
$status_options = array();
foreach($statuses as $status) {
	$status_options[] = JHtml::_('select.option', $status, JText::_('COM_DJCATALOG2_ORDER_STATUS_'.$status));
}
*/
?>

<?php //if ($this->params->get( 'show_page_heading', 1)) { ?>
<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php //} ?>

<div id="djcatalog" class="djc_order<?php echo $this->params->get( 'pageclass_sfx' ).' djc_theme_'.$this->params->get('theme','default') ?>">

<div class="djc_attributes">
	<h2>
	<?php if ($salesman) {?>
		<a data-toggle="modal" data-target="#djc_orderform_modal" href="#djc_orderform_modal" class="btn btn-mini btn-primary pull-left djc_edit_button">
			<?php echo JText::_('COM_DJCATALOG2_EDIT'); ?>
		</a>
		<?php /*?><a class="btn btn-mini btn-primary pull-left djc_edit_button" href="<?php echo JRoute::_('index.php?option=com_djcatalog2&task=orderform.edit&id='.$this->item->id); ?>"><?php echo JText::_('COM_DJCATALOG2_EDIT'); ?></a><?php */ ?>
	<?php } ?>
	<?php echo JText::_('COM_DJCATALOG2_ORDER_DETAILS'); ?>
	</h2>
	
	<table width="100%" cellpadding="0" cellspacing="0"
		class="djc_order_details_table jlist-table table-condensed table"
		id="djc_order_details_table">
		<tr class="djc_attribute">
			<td class="djc_label"><?php echo JText::_('COM_DJCATALOG2_ORDER_NUMBER'); ?>
			</td>
			<td class="djc_value"><?php echo str_pad($this->item->order_number, 5, '0', STR_PAD_LEFT); ?>
			</td>
		</tr>
		<tr class="djc_attribute">
			<td class="djc_label"><?php echo JText::_('COM_DJCATALOG2_ORDER_DATE'); ?>
			</td>
			<td class="djc_value"><?php echo JHtml::_('date', $this->item->created_date, 'd-m-Y'); ?>
			</td>
		</tr>
		<tr class="djc_attribute">
			<td class="djc_label"><?php echo JText::_('COM_DJCATALOG2_ORDER_STATUS'); ?>
			</td>
			<td class="djc_value">
			<?php /*if ($salesman) {?>
			<select name="status_change" onchange="return DJOrderChangeStatus(this, <?php echo $this->item->id;?>);" class="input-medium">
			<?php 
				echo JHtml::_('select.options', $status_options, 'value', 'text', $this->item->status);
			?>
			</select>
			<script type="text/javascript">
				function DJOrderChangeStatus(select, id) {
					window.location.href = "<?php echo JUri::base(false).'index.php?option=com_djcatalog2&task=cart.changeStatus&oid=';?>" + id + '&status=' + document.id(select).value;
				}
			</script>
			<?php } else {*/ ?>
			<?php  echo JText::_('COM_DJCATALOG2_ORDER_STATUS_'.$this->item->status); ?>
			<?php //} ?>
			</td>
		</tr>
		<tr class="djc_attribute">
			<td class="djc_label"><?php echo JText::_('COM_DJCATALOG2_ORDER_FINAL_PRICE'); ?>
			</td>
			<td class="djc_value"><?php echo DJCatalog2HtmlHelper::formatPrice($this->item->grand_total, $this->params); ?>
			</td>
		</tr>
		<tr class="djc_attribute">
			<td class="djc_label"><?php echo JText::_('COM_DJCATALOG2_ORDER_BUYER'); ?>
			</td>
			<td class="djc_value">
				<?php if ($this->item->company) { ?>
					<strong><?php echo $this->item->company?></strong><br />
				<?php }?>
				
				<?php 
				$name = array();
				if ($this->item->firstname) {
					$name[] = $this->item->firstname;
				}
				if ($this->item->lastname) {
					$name[] = $this->item->lastname;
				}
				?>
				<?php if (count($name)) { ?>
					<strong><?php echo implode(' ', $name); ?></strong>
				<?php } ?>
				
				<a href="mailto:<?php echo $this->item->email; ?>"><?php echo $this->item->email; ?></a><br />
				
				<?php 
				$address = array();
				if ($this->item->address) {
					$address[] = $this->item->address;
				}
				if ($this->item->postcode) {
					$address[] = $this->item->postcode;
				}
				if ($this->item->city) {
					$address[] = $this->item->city;
				}
				if ($this->item->state) {
					$address[] = $this->item->state;
				}
				if ($this->item->country) {
					$address[] = $this->item->country;
				}
				?>
				
				<?php if (count($address)) {?>
					<p><?php echo implode(', ', $address); ?></p>
				<?php } ?>
			
				<?php if ($this->item->vat_id) {?>
					<p><?php echo JText::_('COM_DJCATALOG2_UP_VATID').': '.$this->item->vat_id; ?></p>
				<?php } ?>
			</td>
		</tr>
		<?php //if ($this->item->delivery_method && $this->delivery_method->shipping_details == 1) {?>
		<?php if ($this->item->delivery_to_billing == 0) {?>
		<tr class="djc_attribute">
			<td class="djc_label"><?php echo JText::_('COM_DJCATALOG2_DELIVERY_DETAILS'); ?></td>
			<td class="djc_value">
			
				<?php if ($this->item->delivery_company) { ?>
					<strong><?php echo $this->item->delivery_company?></strong><br />
				<?php }?>
				
				<?php 
				$name = array();
				if ($this->item->delivery_firstname) {
					$name[] = $this->item->delivery_firstname;
				}
				if ($this->item->delivery_lastname) {
					$name[] = $this->item->delivery_lastname;
				}
				?>
				<?php if (count($name)) { ?>
					<strong><?php echo implode(' ', $name); ?></strong>
				<?php } ?>
				
				<?php 
				$address = array();
				if ($this->item->delivery_address) {
					$address[] = $this->item->delivery_address;
				}
				if ($this->item->delivery_postcode) {
					$address[] = $this->item->delivery_postcode;
				}
				if ($this->item->delivery_city) {
					$address[] = $this->item->delivery_city;
				}
				if ($this->item->delivery_state) {
					$address[] = $this->item->delivery_state;
				}
				if ($this->item->delivery_country) {
					$address[] = $this->item->delivery_country;
				}
				?>
				
				<?php if (count($address)) {?>
					<p><?php echo implode(', ', $address); ?></p>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
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

<div class="row-fluid">

<?php if (!empty($this->payment_info)) {?>
	<div class="span6">
		<h2><?php echo JText::_('COM_DJCATALOG2_ORDER_PAYMENT_HEADING'); ?></h2>
		<div class="djc_payment_info">
			<?php echo $this->payment_info; ?>
		</div>
	</div>
<?php } ?>
<?php if (!empty($this->delivery_info)) {?>
	<div class="span6">
		<h2><?php echo JText::_('COM_DJCATALOG2_ORDER_DELIVERY_HEADING'); ?></h2>
		<div class="djc_delivery_info">
			<?php echo $this->delivery_info; ?>
		</div>
	</div>
<?php } ?>
</div>

<h2><?php echo JText::_('COM_DJCATALOG2_ORDER_ITEMS'); ?></h2>

<div class="djc_order_items djc_clearfix">
	<?php echo $this->loadTemplate('table'); ?>
</div>


<a class="button btn djc_back_to_orders_btn" href="<?php echo JRoute::_(DJCatalogHelperRoute::getOrdersRoute());?>"><span><?php echo JText::_('COM_DJCATALOG2_BACK_TO_ORDERS'); ?></span></a>

<?php 
	if ($this->params->get('show_footer')) echo DJCATFOOTER;
?>
</div>

<?php if ($salesman) {?>
<?php echo JHtmlBootstrap::renderModal('djc_orderform_modal', array('height' => '600px', 'url' => JRoute::_('index.php?option=com_djcatalog2&task=orderform.edit&id='.$this->item->id).'&tmpl=component', 'title'=> JText::_('COM_DJCATALOG2_ORDER_DETAILS'))); ?>
<?php } ?>

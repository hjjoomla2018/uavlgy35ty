<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */

defined('_JEXEC') or die('Restricted access');

$params = JComponentHelper::getParams('com_djcatalog2');
$app = JFactory::getApplication();

$user = JFactory::getUser();
$price_auth = ($params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;
$show_prices = (bool)($price_auth && (int)$params->get('cart_show_prices', 0) == 1);

/*
JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_djcatalog2'.DS.'models', 'DJCatalog2Model');
$model = JModelLegacy::getInstance('Items', 'Djcatalog2Model', array('ignore_request'=>true));
$state		= $model->getState();
$model->setState('list.start', 0);
$model->setState('list.limit', 0);
$model->setState('filter.catalogue',false);
$model->setState('list.ordering', 'i.name');
$model->setState('list.direction', 'asc');
$model->setState('filter.parent', '*');
$model->setState('filter.state', '3');
$item_ids = array();
foreach ($data['items'] as $item) {
	$item_ids[] = $item['item_id'];
}
$model->setState('filter.item_ids', $item_ids);
$order_items = $model->getItems();
*/

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
	</head>
	<body>
		<div class="djc_mail_wrap">
		<?php if (($logo = $params->get('cart_company_logo', '')) != '') { ?>
			<div class="djc_company_logo">
				<img alt="logo" src="<?php echo JUri::root(false).$logo; ?>" />
			</div>
		<?php } ?>
		<?php if (($header = $params->get('cart_email_header', '')) != '') {?>
			<div class="djc_header"><?php echo $header; ?></div>
		<?php } ?>
		
		<p class="djc_intro_text">
		<?php echo JText::sprintf('COM_DJCATALOG2_EMAIL_QUOTE_CLIENT_HEADER', $data['firstname']); ?>
		</p>
		<br />
		<table width="100%" cellpadding="0" cellspacing="0" class="djc_details_table">
			<tr>
				<td width="50%"><?php echo JText::_('COM_DJCATALOG2_DATE'); ?>
				</td>
				<td><?php echo JHtml::_('date', $data['created_date'], 'd-m-Y'); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_DJCATALOG2_USER_PROFILE'); ?>
				</td>
				<td><?php 
				if ($data['company']) { ?><strong><?php echo $data['company']?>
				</strong><br /><?php } ?>
				<?php if (!empty($data['firstname']) || !empty($data['lastname'])) {?>
				<strong><?php echo @$data['firstname'].' '.@$data['lastname']; ?></strong><br /><?php } ?>
				<a href="mailto:<?php echo $data['email']; ?>"><?php echo $data['email']; ?></a><br />
				<?php 
					$addr = array();
					if (!empty($data['address'])) {
						$addr['address'] = $data['address'];
					}
					if (!empty($data['postcode'])) {
						$addr['postcode'] = $data['postcode'];
					}
					if (!empty($data['city'])) {
						$addr['city'] = $data['city'];
					}
					if (!empty($data['country'])) {
						$addr['country'] = $data['country'];
					}
					if (!empty($data['state'])) {
						$addr['state'] = $data['state'];
					}
					if (count($addr) > 0) {
						echo implode(', ', $addr).'<br />';
					}
				?>
				<?php if (!empty($data['phone'])) { echo JText::_('COM_DJCATALOG2_UP_PHONE').': '.$data['phone'].'<br />'; } ?>
				<?php if (!empty($data['fax'])) { echo JText::_('COM_DJCATALOG2_UP_FAX').': '.$data['fax'].'<br />'; } ?>
				<?php if (!empty($data['vat_id'])) { echo JText::_('COM_DJCATALOG2_UP_VATID').': '.$data['vat_id'].'<br />'; } ?>
				
				<?php if (!empty($data['gdpr_policy'])) { 
					$policy_info = JText::sprintf('COM_DJCATALOG2_GDPR_POLICY_AGREE', $app->get('sitename'));
					if (trim($params->get('cart_gdpr_policy_info')) != '') {
						$policy_info = $params->get('cart_gdpr_policy_info');
					}
					echo '<br />'.$policy_info;
				} ?>
				
				<?php if (!empty($data['gdpr_agreement'])) { 
					$agreement_info = JText::sprintf('COM_DJCATALOG2_GDPR_AGREE', $app->get('sitename'));
					if (trim($params->get('cart_gdpr_agreement_info')) != '') {
						$agreement_info = $params->get('cart_gdpr_agreement_info');
					}
					echo '<br />'.$agreement_info;
				} ?>
				</td>
			</tr>
			<?php if ($data['customer_note']) {?>
				<tr>
					<td colspan="2"><?php echo JText::_('COM_DJCATALOG2_MESSAGE'); ?>
					</td>
				</tr>
				<tr>
					<td colspan="2"><?php echo nl2br($data['customer_note']); ?>
					</td>
				</tr>
			<?php } ?>
		</table>
		<br /><br />
		<table width="100%" cellpadding="0" cellspacing="0" class="djc_items_table">
			<thead>
				<tr>
					<th width="30%"><?php echo JText::_('COM_DJCATALOG2_CART_NAME'); ?>
					</th>
					<th align="center"><?php echo JText::_('COM_DJCATALOG2_QUANTITY'); ?>
					</th>
					<?php if ($show_prices) {?>
					<th align="center"><?php echo JText::_('COM_DJCATALOG2_PRICE'); ?>
					</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$total = 0;
				foreach($data['items'] as $item){
					$total += $item['total'];
				?>
				<tr>
					<td><?php 
					echo ($params->get('cart_display_sku', 1) && $item['sku']) ? '('.$item['sku'].') '.$item['item_name'] : $item['item_name'];
					?>
					</td>
					<td align="center"><?php echo DJCatalog2HelperQuantity::formatAmount($item['quantity'], $params).(isset($item['unit']) ? ' '.$item['unit'] : ''); ?>
					</td>
					<?php if ($show_prices) {?>
					<td align="center"><?php echo $item['total'] > 0.0 ? $item['total'] : '-'; ?></td>
					<?php } ?>
				</tr>
				
				<?php if (trim($item['additional_info']) != '' && $item['item_type'] == 'item') { ?>
					<?php $additional_info = json_decode($item['additional_info']); ?>
						<?php if ($additional_info) {?>
						<tr>
							<td colspan="<?php echo $show_prices ? 3:2; ?>">
								<?php 
								$attrs = array();
								foreach($additional_info as $label => $value) {
									$attrs[] = '<strong>'.$label.'</strong>: <span>'.($value ? $value : '---').'</span>';
								} 
								echo implode(' | ', $attrs);
								?>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
				
				<?php } ?>
				<?php if ($show_prices) {?>
				<tr>
					<td colspan="2" align="right"><?php echo JText::_('COM_DJCATALOG2_TOTAL_VALUE'); ?></td>
					<td align="center"><?php echo $total; ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<br />
		<p>
		<?php echo JText::_('COM_DJCATALOG2_EMAIL_QUOTE_CLIENT_FOOTER'); ?>
		</p>
		<?php if (($footer = $params->get('cart_email_footer', '')) != '') { ?>
			<div class="djc_footer"><?php echo $footer; ?></div>
		<?php } ?>
		</div>
	</body>
</html>

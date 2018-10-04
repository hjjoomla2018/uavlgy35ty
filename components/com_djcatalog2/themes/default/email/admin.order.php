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
		
		<h1 class="djc_heading">
			<?php echo JText::_('COM_DJCATALOG2_ORDER_NUMBER').' '.str_pad($data['order_number'], 5, '0', STR_PAD_LEFT); ?>
		</h1>
		
		<p class="djc_intro_text">
		<?php echo JText::_('COM_DJCATALOG2_EMAIL_ORDER_ADMIN_HEADER'); ?>
		</p>
		
		<br />
		<table width="100%" cellpadding="0" cellspacing="0" class="djc_details_table">
			<tr>
				<td width="50%"><?php echo JText::_('COM_DJCATALOG2_ORDER_DATE'); ?>
				</td>
				<td><?php echo JHtml::_('date', $data['created_date'], 'd-m-Y'); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_DJCATALOG2_ORDER_STATUS'); ?>
				</td>
				<td><?php echo JText::_('COM_DJCATALOG2_ORDER_STATUS_'.$data['status']); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_DJCATALOG2_ORDER_FINAL_PRICE'); ?>
				</td>
				<td><?php echo DJCatalog2HtmlHelper::formatPrice($data['grand_total'], $params); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_DJCATALOG2_ORDER_BUYER'); ?>
				</td>
				<td><?php 
				if ($data['company']) { ?><strong><?php echo $data['company']?>
				</strong><br /><?php } ?>
				<strong><?php echo $data['firstname'].' '.$data['lastname']; ?></strong><br />
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
			
			<?php //if (!empty($data['delivery_method'])) { ?>
			<?php if (isset($data['delivery_to_billing']) && $data['delivery_to_billing'] == 0) { ?>
				<tr><td colspan="2" class="djc_row_separator"> </td></tr>
				
		        <tr>
		            <td><?php echo JText::_('COM_DJCATALOG2_DELIVERY_DETAILS'); ?></td>
		            <td><?php 
					if ($data['delivery_company']) { ?><strong><?php echo $data['delivery_company']?>
					</strong><br /><?php } ?>
					<strong><?php echo $data['delivery_firstname'].' '.$data['delivery_lastname']; ?></strong><br />
					<a href="mailto:<?php echo $data['email']; ?>"><?php echo $data['email']; ?></a><br />
					<?php 
						$addr = array();
						if (!empty($data['delivery_address'])) {
							$addr['delivery_address'] = $data['delivery_address'];
						}
						if (!empty($data['delivery_postcode'])) {
							$addr['delivery_postcode'] = $data['delivery_postcode'];
						}
						if (!empty($data['delivery_city'])) {
							$addr['delivery_city'] = $data['delivery_city'];
						}
						if (!empty($data['delivery_country'])) {
							$addr['delivery_country'] = $data['delivery_country'];
						}
						if (!empty($data['delivery_state'])) {
							$addr['delivery_state'] = $data['delivery_state'];
						}
						if (count($addr) > 0) {
							echo implode(', ', $addr).'<br />';
						}
					?>
					<?php if (!empty($data['delivery_phone'])) { echo JText::_('COM_DJCATALOG2_UP_PHONE').': '.$data['delivery_phone'].'<br />'; } ?>
					</td>
				</tr>
		
			<?php } ?>
			
			<?php if ($data['customer_note']) {?>
				<tr><td colspan="2" class="djc_row_separator"> </td></tr>
				
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
					<th width="15%" align="center"><?php echo JText::_('COM_DJCATALOG2_NET_VALUE'); ?>
					</th>
					<th width="15%" align="center"><?php echo JText::_('COM_DJCATALOG2_TAX'); ?>
					</th>
					<th width="15%" align="center"><?php echo JText::_('COM_DJCATALOG2_TOTAL_VALUE'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2" align="right"><?php echo JText::_('COM_DJCATALOG2_CART_FOOTER_TOTAL'); ?>
					</td>
					<td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($data['total'], $params)?>
					</td>
					<td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($data['tax'], $params)?>
					</td>
					<td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($data['grand_total'], $params)?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				foreach($data['items'] as $item){
				?>
				<tr>
					<td><?php 
					echo ($params->get('cart_display_sku', 1) && $item['sku']) ? '('.$item['sku'].') '.$item['item_name'] : $item['item_name'];
					?>
					</td>
					<td align="center"><?php echo DJCatalog2HelperQuantity::formatAmount($item['quantity'], $params).(isset($item['unit']) ? ' '.$item['unit'] : ''); ?>
					</td>
					<td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($item['cost'], $params, false)?>
					</td>
					<td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($item['tax'], $params, false)?>
					</td>
					<td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($item['total'], $params, false)?>
					</td>
				</tr>
				
				<?php if (trim($item['additional_info']) != '' && $item['item_type'] == 'item') { ?>
					<?php $additional_info = json_decode($item['additional_info']); ?>
						<?php if ($additional_info) {?>
						<tr>
							<td colspan="5">
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
				
				<?php if (!empty($data['delivery_method'])) { ?>
					<tr><td colspan="5" class="djc_row_separator"> </td></tr>
		            <tr>
		                <td><?php 
		                echo JText::_('COM_DJCATALOG2_DELIVERY_METHOD').': '.$data['delivery_method'];
		                ?>
		                </td>
		                <td align="center"><?php echo 1; ?>
		                </td>
		                <td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($data['delivery_price'], $params, false)?>
		                </td>
		                <td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($data['delivery_tax'], $params, false)?>
		                </td>
		                <td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($data['delivery_total'], $params, false)?>
		                </td>
		            </tr>
		            
		        <?php } ?>
		        
		         <?php if (!empty($data['payment_method'])) { ?>
		         	<tr><td colspan="5" class="djc_row_separator"> </td></tr>
		            <tr>
		                <td><?php 
		                echo JText::_('COM_DJCATALOG2_PAYMENT_METHOD').': '.$data['payment_method'];
		                ?>
		                </td>
		                <td align="center"><?php echo 1; ?>
		                </td>
		                <td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($data['payment_price'], $params, false)?>
		                </td>
		                <td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($data['payment_tax'], $params, false)?>
		                </td>
		                <td align="center"><?php echo DJCatalog2HtmlHelper::formatPrice($data['payment_total'], $params, false)?>
		                </td>
		            </tr>
		        <?php } ?>
			</tbody>
		</table>
		<br />

		<p>
		<?php echo JText::_('COM_DJCATALOG2_EMAIL_ORDER_ADMIN_FOOTER'); ?>
		<a href="<?php echo JURI::base().'administrator/index.php?option=com_djcatalog2&amp;view=orders&amp;filter_search='.urlencode('id:'.$data['id']); ?>">
		<?php echo JText::_('COM_DJCATALOG2_EMAIL_ORDER_ADMIN_LINK');?></a>
		</p>
		<?php if (($footer = $params->get('cart_email_footer', '')) != '') { ?>
			<div class="djc_footer"><?php echo $footer; ?></div>
		<?php } ?>
		</div>
	</body>
</html>
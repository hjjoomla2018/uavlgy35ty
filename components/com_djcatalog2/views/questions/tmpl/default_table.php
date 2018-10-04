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

$price_auth = ($this->params->get('price_restrict', '0') == '1' && $user->guest) ? false : true;
$show_prices = (bool)($price_auth && (int)$this->params->get('cart_show_prices', 0) == 1);

$salesman = $user->authorise('djcatalog2.salesman', 'com_djcatalog2');

?>
<table width="100%" cellpadding="0" cellspacing="0" class="djc_questions_table jlist-table category table table-condensed" id="djc_questions_table">
	<thead>
		<tr>
			<th class="djc_thead djc_thead_order_date">
				<?php echo JText::_('COM_DJCATALOG2_ORDER_DATE'); ?>
			</th>
			<?php if ($salesman) {?>
				<th><?php echo JText::_('COM_DJCATALOG2_USER_PROFILE'); ?></th>
			<?php } ?>
			<th class="djc_thead djc_thead_order_status">
				<?php echo JText::_('COM_DJCATALOG2_MESSAGE'); ?>
			</th>
			<?php if ($show_prices) {?>
			<th class="djc_thead djc_thead_order_total">
				<?php echo JText::_('COM_DJCATALOG2_TOTAL_VALUE'); ?>
			</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php 
		$k = 1;
		foreach($this->items as $item) {
			$k = 1 - $k; 
			$order_url = JRoute::_(DJCatalogHelperRoute::getQuestionRoute($item->id));
			
			$name = array();
			if (!empty($item->firstname)) {
				$name[] = $item->firstname;
			}
			if (!empty($item->lastname)) {
				$name[] = $item->lastname;
			}
			
			$item->_name = (count($name) > 0) ? implode(' ', $name) : '';
			
		?>
			<tr class="cat-list-row<?php echo $k;?> djc_row<?php echo $k;?>">
				<td class="djc_td_order_date">
					<a href="<?php echo $order_url;?>"><?php echo JHtml::_('date', $item->created_date, 'd-m-Y'); ?></a>
				</td>
				<?php if ($salesman) {?>
					<td class="djc_td_order_info">
						<?php if ($item->company) { ?>
							<strong><?php echo $item->company?></strong><br />
						<?php }?>
						<?php if (!empty($item->_name)) {?>
						<strong><?php echo $item->_name; ?></strong><br />
						<?php } ?>
						
						<a href="mailto:<?php echo $item->email; ?>"><?php echo $item->email; ?></a><br />
						
						<?php 
							$addr = array();
							if (!empty($item->address)) {
								$addr['address'] = $item->address;
							}
							if (!empty($item->postcode)) {
								$addr['postcode'] = $item->postcode;
							}
							if (!empty($item->city)) {
								$addr['city'] = $item->city;
							}
							if (!empty($item->country)) {
								$addr['country'] = $item->country;
							}
							if (!empty($item->state)) {
								$addr['state'] = $item->state;
							}
							if (count($addr) > 0) {
								echo implode(', ', $addr).'<br />';
							}
						?>
					</td>
				<?php } ?>
				<td class="djc_td_order_status">
					<?php echo nl2br($item->customer_note); ?>
				</td>
				<?php if ($show_prices) {?>
				<td class="djc_td_order_total">
					<?php echo DJCatalog2HtmlHelper::formatPrice($item->grand_total, $this->params); ?>
				</td>
				<?php } ?>
			</tr>
		<?php } ?>
	</tbody>
</table>
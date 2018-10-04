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
require_once JPATH_ROOT.DS.'components'.DS.'com_djcatalog2'.DS.'helpers'.DS.'route.php';

$status = JText::_('COM_DJCATALOG2_ORDER_STATUS_'.$data['status']);
if (!empty($data['status_comment'])) {
	$status .= ' ('.$data['status_comment'].')';
}

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
		<?php echo JText::sprintf('COM_DJCATALOG2_EMAIL_ORDER_STATUS_ADMIN_HEADER', 
			$data['firstname'].' '.$data['lastname'], 
			$status,
			str_pad($data['order_number'], 5, '0', STR_PAD_LEFT)
			); ?>
		</p>
		
		<p>
		<?php echo JText::_('COM_DJCATALOG2_EMAIL_ORDER_ADMIN_FOOTER'); ?>
		<a href="<?php echo JUri::root(false).'administrator/index.php?option=com_djcatalog2&amp;view=orders&amp;filter_search=id:'.$data['id']; ?>">
		<?php echo JText::_('COM_DJCATALOG2_EMAIL_ORDER_ADMIN_LINK');?></a>
		</p>
		<?php if (($footer = $params->get('cart_email_footer', '')) != '') { ?>
			<div class="djc_footer"><?php echo $footer; ?></div>
		<?php } ?>
		</div>
	</body>
</html>

CREATE TABLE IF NOT EXISTS `#__djc2_vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djc2_vendors_customers` (
  `vendor_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  UNIQUE KEY `idx_vendor_customer` (`vendor_id`,`customer_id`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djc2_delivery_methods` ADD `free_amount` DECIMAL( 12, 2 ) DEFAULT NULL AFTER `tax_rule_id`;

ALTER TABLE `#__djc2_payment_methods` ADD `free_amount` DECIMAL( 12, 2 ) DEFAULT NULL AFTER `tax_rule_id`;

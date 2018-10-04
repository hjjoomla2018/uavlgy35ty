CREATE TABLE IF NOT EXISTS `#__djc2_delivery_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `published` tinyint(4) NOT NULL,
  `plugin` varchar(100) NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `tax_rule_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `shipping_details` tinyint(4) NOT NULL,
  `params` text,
  PRIMARY KEY (`id`),
  KEY `idx_published` (`published`),
  KEY `idx_plugin` (`plugin`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djc2_payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `published` tinyint(4) NOT NULL,
  `plugin` varchar(100) NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `tax_rule_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text,
  PRIMARY KEY (`id`),
  KEY `idx_published` (`published`),
  KEY `idx_plugin` (`plugin`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djc2_deliveries_payments` (
  `delivery_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  PRIMARY KEY (`delivery_id`,`payment_id`),
  UNIQUE KEY `payment_delivery` (`payment_id`,`delivery_id`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djc2_orders` 
ADD `payment_method_id` INT NOT NULL DEFAULT '0' AFTER `payment_method` ,
ADD `payment_price` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0' AFTER `payment_method_id` ,
ADD `payment_tax` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0' AFTER `payment_price` ,
ADD `payment_tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0' AFTER `payment_tax` ,
ADD `payment_total` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0' AFTER `payment_tax_rate` ,
ADD `delivery_method` VARCHAR( 255 ) NULL AFTER `payment_total` ,
ADD `delivery_method_id` INT NOT NULL DEFAULT '0' AFTER `delivery_method` ,
ADD `delivery_price` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0' AFTER `delivery_method_id` ,
ADD `delivery_tax` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0' AFTER `delivery_price` ,
ADD `delivery_tax_rate` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0' AFTER `delivery_tax` ,
ADD `delivery_total` DECIMAL( 10, 4 ) NOT NULL DEFAULT '0' AFTER `delivery_tax_rate`;

ALTER TABLE `#__djc2_orders`
ADD `delivery_to_billing` tinyint(4) NOT NULL AFTER `vat_id`,
ADD `delivery_firstname` varchar(100) DEFAULT NULL AFTER `delivery_to_billing`,
ADD `delivery_lastname` varchar(100) DEFAULT NULL AFTER `delivery_firstname`,
ADD `delivery_company` varchar(100) DEFAULT NULL AFTER `delivery_lastname`,
ADD `delivery_address` varchar(100) DEFAULT NULL AFTER `delivery_company`,
ADD `delivery_city` varchar(100) DEFAULT NULL AFTER `delivery_address`,
ADD `delivery_postcode` varchar(100) DEFAULT NULL AFTER `delivery_city`,
ADD `delivery_country` varchar(100) DEFAULT NULL AFTER `delivery_postcode`,
ADD `delivery_country_id` int(11) NOT NULL AFTER `delivery_country`,
ADD `delivery_phone` varchar(20) DEFAULT NULL AFTER `delivery_country_id`;

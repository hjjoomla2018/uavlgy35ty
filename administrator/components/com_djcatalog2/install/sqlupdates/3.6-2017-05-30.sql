ALTER TABLE `#__djc2_items` CHANGE `tax_rate_id` `tax_rule_id` INT(11) NOT NULL; 

CREATE TABLE IF NOT EXISTS `#__djc2_vat_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `country_id` int(11) NOT NULL,
  `client_type` char(1) NOT NULL DEFAULT 'A',
  `value` decimal(10,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_country_client` (`country_id`,`client_type`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djc2_vat_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `value` decimal(10,4) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djc2_vat_rules_xref` (
  `rule_id` int(11) NOT NULL,
  `rate_id` int(11) NOT NULL,
  UNIQUE KEY `idx_rule_rate` (`rule_id`,`rate_id`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djc2_orders` 
ADD `delivery_state` VARCHAR(100) NULL AFTER `delivery_country_id`, 
ADD `delivery_state_id` INT NULL DEFAULT '0' AFTER `delivery_state`;


CREATE TABLE IF NOT EXISTS `#__djc2_items_combinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djc2_items_combinations_fields` (
  `combination_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  UNIQUE KEY `idx_combination_field_value` (`combination_id`,`field_id`,`value`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djc2_items_extra_fields_options` ADD `params` TEXT NULL AFTER `ordering`;
ALTER TABLE `#__djc2_items_extra_fields` ADD `params` TEXT NULL AFTER `comparable`;
ALTER TABLE `#__djc2_cart_extra_fields` ADD `params` TEXT NULL AFTER `required`;
ALTER TABLE `#__djc2_items_extra_fields` ADD `cart_variant` SMALLINT NOT NULL DEFAULT '0' AFTER `comparable`;
ALTER TABLE `#__djc2_cart_extra_fields` ADD `visibility` INT NOT NULL DEFAULT '1' AFTER `required`;

CREATE TABLE IF NOT EXISTS `#__djc2_items_price_tiers` (
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  UNIQUE KEY `idx_item_quantity` (`item_id`,`quantity`)
) DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__djc2_customisations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` char(1) NOT NULL DEFAULT 'c',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_rule_id` int(11) NOT NULL,
  `price_modifier` char(1) NOT NULL DEFAULT 's',
  `required` tinyint(4) NOT NULL DEFAULT '0',
  `min_quantity` int(11) NOT NULL DEFAULT '0',
  `max_quantity` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `input_params` mediumtext,
  `params` mediumtext,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djc2_items_customisations` (
  `item_id` int(11) NOT NULL,
  `customisation_id` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `required` int(11) NOT NULL DEFAULT '0',
  `min_quantity` int(11) NOT NULL DEFAULT '0',
  `max_quantity` int(11) NOT NULL DEFAULT '0',
  `params` mediumtext,
  UNIQUE KEY `item_id` (`item_id`,`customisation_id`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djc2_items` ADD `price_tier_modifier` CHAR(1) NOT NULL DEFAULT '0' AFTER `tax_rule_id`;
ALTER TABLE `#__djc2_items` ADD `price_tier_break` CHAR(1) NOT NULL DEFAULT 'i' AFTER `price_tier_modifier`;

ALTER TABLE `#__djc2_order_items` ADD `item_type` VARCHAR(20) NOT NULL DEFAULT 'item' AFTER `id`;
ALTER TABLE `#__djc2_quote_items` ADD `item_type` VARCHAR(20) NOT NULL DEFAULT 'item' AFTER `id`;
ALTER TABLE `#__djc2_quote_items` ADD `combination_info` MEDIUMTEXT NULL AFTER `additional_info`;
ALTER TABLE `#__djc2_order_items` ADD `combination_info` MEDIUMTEXT NULL AFTER `additional_info`;
ALTER TABLE `#__djc2_quote_items` ADD `combination_id` INT NOT NULL DEFAULT '0' AFTER `item_id`;
ALTER TABLE `#__djc2_order_items` ADD `combination_id` INT NOT NULL DEFAULT '0' AFTER `item_id`;

ALTER TABLE `#__djc2_orders` ADD `token` VARCHAR(32) NULL AFTER `user_id`;
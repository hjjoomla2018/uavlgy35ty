CREATE TABLE IF NOT EXISTS `#__djc2_cart_extra_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `published` int(11) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL,
  `required` SMALLINT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_published` (`published`),
  KEY `idx_type` (`type`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djc2_cart_extra_fields_options` (
	`id` INT NOT NULL AUTO_INCREMENT, 
	`field_id` INT NOT NULL, 
	`value` VARCHAR(100) NOT NULL, 
	`ordering` INT NOT NULL, 
	PRIMARY KEY (`id`),
  	KEY `idx_field_id` (`field_id`),
  	KEY `idx_value` (`value`),
  	KEY `idx_ordering` (`ordering`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__djc2_quote_items` ADD `additional_info` TEXT AFTER `total`;
ALTER TABLE `#__djc2_order_items` ADD `additional_info` TEXT AFTER `total`; 

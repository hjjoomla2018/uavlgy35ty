ALTER TABLE `#__djc2_orders` ADD `gdpr_agreement` TINYINT NOT NULL DEFAULT '0' AFTER `customer_note`;
ALTER TABLE `#__djc2_quotes` ADD `gdpr_agreement` TINYINT NOT NULL DEFAULT '0' AFTER `customer_note`;

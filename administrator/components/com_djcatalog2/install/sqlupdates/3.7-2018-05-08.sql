ALTER TABLE `#__djc2_users` ADD `gdpr_policy` TINYINT NOT NULL DEFAULT '0' AFTER `gdpr_agreement`; 
ALTER TABLE `#__djc2_orders` ADD `gdpr_policy` TINYINT NOT NULL DEFAULT '0' AFTER `gdpr_agreement`;
ALTER TABLE `#__djc2_quotes` ADD `gdpr_policy` TINYINT NOT NULL DEFAULT '0' AFTER `gdpr_agreement`;

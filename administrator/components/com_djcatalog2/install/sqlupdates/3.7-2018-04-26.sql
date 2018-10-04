ALTER TABLE `#__djc2_users` ADD `gdpr_agreement` TINYINT NOT NULL DEFAULT '0' AFTER `image`; 
ALTER TABLE `#__djc2_users` ADD `modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `gdpr_agreement`;
ALTER TABLE `#__djc2_users` ADD `tos` TINYINT NOT NULL DEFAULT '0' AFTER `modified`; 

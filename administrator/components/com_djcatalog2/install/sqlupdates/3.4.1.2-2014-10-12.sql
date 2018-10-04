ALTER TABLE `#__djc2_quotes` 
ADD `position` varchar(100) DEFAULT NULL AFTER `company`,
ADD `phone` varchar(20) DEFAULT NULL AFTER `country_id`,
ADD `fax` varchar(20) DEFAULT NULL AFTER `phone`,
ADD `www` varchar(255) DEFAULT NULL AFTER `fax`;

ALTER TABLE `#__djc2_orders` 
ADD `position` varchar(100) DEFAULT NULL AFTER `company`,
ADD `phone` varchar(20) DEFAULT NULL AFTER `country_id`,
ADD `fax` varchar(20) DEFAULT NULL AFTER `phone`,
ADD `www` varchar(255) DEFAULT NULL AFTER `fax`;

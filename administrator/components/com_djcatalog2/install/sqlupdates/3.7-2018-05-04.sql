ALTER TABLE `#__djc2_delivery_methods` 
ADD `access` INT NOT NULL DEFAULT '0' AFTER `params`, 
ADD `countries` MEDIUMTEXT NULL AFTER `access`, 
ADD `postcodes` TEXT NULL AFTER `countries`;

ALTER TABLE `#__djc2_payment_methods` 
ADD `access` INT NOT NULL DEFAULT '0' AFTER `params`, 
ADD `countries` MEDIUMTEXT NULL AFTER `access`, 
ADD `postcodes` TEXT NULL AFTER `countries`;


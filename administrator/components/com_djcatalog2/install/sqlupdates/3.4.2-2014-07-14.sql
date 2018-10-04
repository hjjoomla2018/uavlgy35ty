ALTER TABLE `#__djc2_items` ADD `address` VARCHAR( 255 ) NULL AFTER `tax_rate_id` ,
ADD `city` VARCHAR( 100 ) NULL AFTER `address` ,
ADD `postcode` VARCHAR( 20 ) NULL AFTER `city` ,
ADD `country` INT NULL AFTER `postcode` ,
ADD `latitude` DECIMAL( 18, 15 ) NULL AFTER `country` ,
ADD `longitude` DECIMAL( 18, 15 ) NULL AFTER `latitude`;

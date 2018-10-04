ALTER TABLE `#__djc2_items` ADD `onstock` INT NOT NULL DEFAULT '1' AFTER `available`;
ALTER TABLE `#__djc2_items` ADD `stock` DECIMAL (10,4) NOT NULL DEFAULT '0.0' AFTER `onstock`;

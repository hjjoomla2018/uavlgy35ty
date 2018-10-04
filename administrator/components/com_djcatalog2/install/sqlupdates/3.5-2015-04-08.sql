ALTER TABLE `#__djc2_items` ADD `parent_id` INT NOT NULL DEFAULT '0' AFTER `id`;
ALTER TABLE `#__djc2_items` ADD INDEX `idx_parent_id` ( `parent_id` );

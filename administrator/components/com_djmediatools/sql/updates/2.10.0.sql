ALTER TABLE `#__djmt_albums` ADD `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `published`;
ALTER TABLE `#__djmt_albums` ADD `created_by` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`;

ALTER TABLE `#__djmt_items` ADD `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `published`;
ALTER TABLE `#__djmt_items` ADD `created_by` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`;
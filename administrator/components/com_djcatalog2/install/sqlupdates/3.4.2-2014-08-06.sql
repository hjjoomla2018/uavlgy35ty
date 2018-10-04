CREATE TABLE IF NOT EXISTS `#__djc2_items_groups` (
  `item_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`item_id`),
  UNIQUE KEY `item_group` (`item_id`,`group_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__djc2_countries_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_country_id` (`country_id`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djc2_items` ADD `state` INT NULL AFTER `country`,
ADD `phone` VARCHAR( 20 ) NULL AFTER `state`,
ADD `mobile` VARCHAR( 20 ) NULL AFTER `phone`,
ADD `fax` VARCHAR( 20 ) NULL AFTER `phone`,
ADD `website` VARCHAR( 255 ) NULL AFTER `mobile`,
ADD `email` VARCHAR( 255 ) NULL AFTER `website`;

INSERT IGNORE INTO `#__djc2_items_groups` (`item_id`, `group_id`) SELECT id, group_id FROM `#__djc2_items` WHERE group_id > 0;
INSERT IGNORE INTO `#__djc2_items_groups` (`item_id`, `group_id`) SELECT id, 0 FROM `#__djc2_items`;

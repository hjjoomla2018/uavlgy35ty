CREATE TABLE IF NOT EXISTS `#__djrevs_fields_groups` (
  `field_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_field_group` (`field_id`,`group_id`),
  KEY `idx_ordering` (`ordering`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE `#__djrevs_fields_groups`;

INSERT INTO `#__djrevs_fields_groups` (`field_id`, `group_id`, `ordering`) 
SELECT `id`, `group_id`, `ordering` FROM `#__djrevs_rating_fields`;

ALTER TABLE `#__djrevs_rating_fields` ADD `type` varchar(50) NOT NULL AFTER `name`;

UPDATE `#__djrevs_rating_fields` SET `type`="rating";

CREATE TABLE IF NOT EXISTS `#__djrevs_reviews_items_text` (
  `review_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `rating` TEXT NOT NULL,
  KEY `idx_review_field` (`review_id`,`field_id`),
  KEY `idx_field_review` (`field_id`,`review_id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
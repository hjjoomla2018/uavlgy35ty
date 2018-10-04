CREATE TABLE IF NOT EXISTS `#__djrevs_objects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `object_type` varchar(50) NOT NULL,
  `rating_group_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `link` text NOT NULL,
  `avg_rate` decimal(4,2) NOT NULL DEFAULT '0',
  `plugin` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_group_object_entry` (`rating_group_id`,`object_type`,`entry_id`),
  KEY `idx_object` (`object_type`),
  KEY `idx_entry` (`entry_id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djrevs_rating_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text,
  `weight` decimal(4,2) NOT NULL,
  `list_options` text DEFAULT NULL,
  `regular_exp` varchar(255) DEFAULT NULL,
  `published` TINYINT NOT NULL DEFAULT '0',
  `required` TINYINT NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_ordering` (`ordering`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djrevs_rating_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `params` text,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djrevs_fields_groups` (
  `field_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_field_group` (`field_id`,`group_id`),
  KEY `idx_ordering` (`ordering`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djrevs_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `object_type` varchar(50) NOT NULL,
  `object_id` int(11) NOT NULL,
  `rating_group_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_login` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text,
  `published` tinyint(4) NOT NULL DEFAULT '0',
  `avg_rate` decimal(4,2) NOT NULL DEFAULT '0',
  `post_info` text,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `recalculate` TINYINT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_object_type` (`object_type`),
  KEY `idx_object_id` (`object_id`),
  KEY `idx_rating_group` (`rating_group_id`),
  KEY `idx_created_by` (`created_by`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djrevs_reviews_items` (
  `review_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `rating` decimal(4,2) NOT NULL,
  KEY `idx_review_field` (`review_id`,`field_id`),
  KEY `idx_field_review` (`field_id`,`review_id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djrevs_reviews_items_text` (
  `review_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `rating` TEXT NOT NULL,
  KEY `idx_review_field` (`review_id`,`field_id`),
  KEY `idx_field_review` (`field_id`,`review_id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__djrevs_objects_items` (
  `object_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `rating` decimal(4,2) NOT NULL,
  KEY `idx_object_field` (`object_id`,`field_id`),
  KEY `idx_field_object` (`field_id`,`object_id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


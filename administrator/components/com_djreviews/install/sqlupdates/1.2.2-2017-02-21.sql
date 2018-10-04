ALTER TABLE `#__djrevs_rating_fields` ADD `list_options` text DEFAULT NULL AFTER `weight`;

ALTER TABLE `#__djrevs_rating_fields` ADD `regular_exp` varchar(255) DEFAULT NULL AFTER `list_options`;

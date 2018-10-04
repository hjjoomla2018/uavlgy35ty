ALTER TABLE `#__djrevs_rating_fields` ADD `label` varchar(255) DEFAULT NULL AFTER `name`;

UPDATE `#__djrevs_rating_fields` SET `label` = `name`;

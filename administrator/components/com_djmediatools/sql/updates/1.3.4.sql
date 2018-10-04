
RENAME TABLE `#__djmediatools` TO `#__djmt_items`;
RENAME TABLE `#__djmediatools_categories` TO `#__djmt_albums`;

ALTER TABLE `#__djmt_items` ADD `video` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `image`;


ALTER TABLE `#__djmt_albums` ADD `visible` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `published`, ADD INDEX (  `visible` );

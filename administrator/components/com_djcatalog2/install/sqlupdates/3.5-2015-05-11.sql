ALTER TABLE `#__djc2_items` ADD `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `published`;
ALTER TABLE `#__djc2_categories` ADD `access` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `published`;

ALTER TABLE `#__djc2_items` ADD INDEX `idx_access` ( `access` );
ALTER TABLE `#__djc2_categories` ADD INDEX `idx_access` ( `access` );

UPDATE #__djc2_items SET access = (SELECT id FROM #__viewlevels ORDER BY ordering ASC, id ASC LIMIT 1);
UPDATE #__djc2_categories SET access = (SELECT id FROM #__viewlevels ORDER BY ordering ASC, id ASC LIMIT 1);

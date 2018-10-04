ALTER TABLE `#__djc2_files` ADD `access` INT NOT NULL DEFAULT '0' AFTER `type`;

UPDATE `#__djc2_files` SET access = (SELECT id FROM #__viewlevels ORDER BY ordering ASC, id ASC LIMIT 1);
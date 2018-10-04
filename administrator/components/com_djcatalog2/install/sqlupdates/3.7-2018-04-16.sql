ALTER TABLE `#__djc2_items`  ADD `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'  AFTER `publish_down`,  ADD `modified_by` INT(10) NOT NULL DEFAULT '0'  AFTER `modified`;

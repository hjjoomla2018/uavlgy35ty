ALTER TABLE `#__djc2_order_items` ADD `sku` varchar(50) DEFAULT '' AFTER `item_id`;
ALTER TABLE `#__djc2_quote_items` ADD `sku` varchar(50) DEFAULT '' AFTER `item_id`;

ALTER TABLE `#__djc2_items` ADD `sku` varchar(50) DEFAULT '' AFTER `alias`;

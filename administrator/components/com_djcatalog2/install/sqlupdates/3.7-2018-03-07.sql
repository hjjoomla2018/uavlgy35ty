ALTER TABLE `#__djc2_order_items` CHANGE `quantity` `quantity` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
ALTER TABLE `#__djc2_order_items` ADD `unit` VARCHAR(10) NULL AFTER `quantity`;
ALTER TABLE `#__djc2_quote_items` CHANGE `quantity` `quantity` DECIMAL(10,4) NOT NULL DEFAULT '0.0';
ALTER TABLE `#__djc2_quote_items` ADD `unit` VARCHAR(10) NULL AFTER `quantity`;
ALTER TABLE `#__djc2_items_combinations` CHANGE `stock` `stock` DECIMAL(10,4) NOT NULL DEFAULT '0.0000';
ALTER TABLE `#__djc2_items` ADD `unit_id` INT NOT NULL DEFAULT '0' AFTER `stock`;

CREATE TABLE IF NOT EXISTS `#__djc2_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  `is_int` tinyint(4) NOT NULL DEFAULT '1',
  `min_quantity` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `max_quantity` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `step` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `show_box` tinyint(4) NOT NULL DEFAULT '1',
  `show_unit` tinyint(4) NOT NULL DEFAULT '1',
  `show_buttons` tinyint(4) NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `#__djc2_units` (`id`, `name`, `unit`, `is_default`, `is_int`, `min_quantity`, `max_quantity`, `step`, `show_box`, `show_unit`, `show_buttons`, `ordering`) VALUES
(1, 'Piece', 'pc', 1, 1, '1.0000', '0.0000', '1.0000', 1, 0, 1, 1),
(2, 'Kilogram', 'kg', 0, 0, '0.1000', '0.0000', '0.0100', 1, 1, 1, 2),
(3, 'Litre', 'l', 0, 0, '0.1000', '0.0000', '0.1000', 1, 1, 1, 3),
(4, 'Meter', 'm', 0, 0, '0.1000', '0.0000', '0.1000', 1, 1, 1, 4);


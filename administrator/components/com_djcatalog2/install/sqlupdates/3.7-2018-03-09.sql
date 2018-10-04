CREATE TABLE IF NOT EXISTS `#__djc2_inv_counters` (
  `year` int(11) NOT NULL,
  `counter` int(11) NOT NULL DEFAULT '0'
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djc2_orders` ADD `invoice_counter` INT NOT NULL DEFAULT '0' AFTER `order_number`;

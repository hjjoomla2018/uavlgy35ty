ALTER TABLE `#__djc2_orders` 
ADD `service_date` DATETIME DEFAULT '0000-00-00 00:00:00' AFTER `invoice_date` ,
ADD `payment_date` DATETIME DEFAULT '0000-00-00 00:00:00' AFTER `service_date`;

ALTER TABLE `#__djc2_quotes` ADD `grand_total` DECIMAL( 10, 4 ) NOT NULL AFTER `created_date`;

ALTER TABLE `#__djc2_quote_items` 
ADD `price` DECIMAL( 10, 4 ) NOT NULL AFTER `quantity`,
ADD `total` DECIMAL( 10, 4 ) NOT NULL AFTER `price`;
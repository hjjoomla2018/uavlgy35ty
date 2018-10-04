ALTER TABLE `#__djc2_items` CHANGE `special_price` `special_price` DECIMAL(14,4) NULL DEFAULT NULL;
ALTER TABLE `#__djc2_items` CHANGE `price` `price` DECIMAL(14,4) NULL DEFAULT NULL;

ALTER TABLE `#__djc2_orders` 
CHANGE `total` `total` DECIMAL(14,4) NOT NULL, 
CHANGE `grand_total` `grand_total` DECIMAL(14,4) NOT NULL,
CHANGE `tax` `tax` DECIMAL(14,4) NOT NULL, 
CHANGE `payment_price` `payment_price` DECIMAL(14,4) NOT NULL DEFAULT '0.0000', 
CHANGE `payment_tax` `payment_tax` DECIMAL(14,4) NOT NULL DEFAULT '0.0000', 
CHANGE `payment_tax_rate` `payment_tax_rate` DECIMAL(14,4) NOT NULL DEFAULT '0.0000', 
CHANGE `payment_total` `payment_total` DECIMAL(14,4) NOT NULL DEFAULT '0.0000', 
CHANGE `delivery_price` `delivery_price` DECIMAL(14,4) NOT NULL DEFAULT '0.0000', 
CHANGE `delivery_tax` `delivery_tax` DECIMAL(14,4) NOT NULL DEFAULT '0.0000', 
CHANGE `delivery_tax_rate` `delivery_tax_rate` DECIMAL(14,4) NOT NULL DEFAULT '0.0000',
CHANGE `delivery_total` `delivery_total` DECIMAL(14,4) NOT NULL DEFAULT '0.0000';

ALTER TABLE `#__djc2_order_items` 
CHANGE `cost` `cost` DECIMAL(14,4) NOT NULL, 
CHANGE `base_cost` `base_cost` DECIMAL(14,4) NOT NULL, 
CHANGE `tax` `tax` DECIMAL(14,4) NOT NULL, 
CHANGE `tax_rate` `tax_rate` DECIMAL(14,4) NOT NULL, 
CHANGE `total` `total` DECIMAL(14,4) NOT NULL;

ALTER TABLE `#__djc2_quotes` CHANGE `grand_total` `grand_total` DECIMAL(14,4) NOT NULL;

ALTER TABLE `#__djc2_quote_items` CHANGE `price` `price` DECIMAL(14,4) NOT NULL, CHANGE `total` `total` DECIMAL(14,4) NOT NULL;

ALTER TABLE `#__djc2_prices` CHANGE `price` `price` DECIMAL(14,4) NOT NULL DEFAULT '0.0000'; 

ALTER TABLE `#__djc2_customisations` CHANGE `price` `price` DECIMAL(14,4) NOT NULL DEFAULT '0.00';

ALTER TABLE `#__djc2_items_combinations` CHANGE `price` `price` DECIMAL(14,4) NOT NULL;

ALTER TABLE `#__djc2_items_customisations` CHANGE `price` `price` DECIMAL(14,4) NOT NULL DEFAULT '0.00'; 

ALTER TABLE `#__djc2_items_price_tiers` CHANGE `price` `price` DECIMAL(14,4) NOT NULL; 

ALTER TABLE `#__djc2_payment_methods` 
CHANGE `price` `price` DECIMAL(14,4) NULL DEFAULT NULL, 
CHANGE `free_amount` `free_amount` DECIMAL(14,4) NULL DEFAULT NULL; 

ALTER TABLE `#__djc2_delivery_methods` 
CHANGE `price` `price` DECIMAL(14,4) NULL DEFAULT NULL, 
CHANGE `free_amount` `free_amount` DECIMAL(14,4) NULL DEFAULT NULL; 

ALTER TABLE `#__djc2_tax_rates` CHANGE `value` `value` DECIMAL(14,4) NOT NULL; 

ALTER TABLE `#__djc2_vat_rates` CHANGE `value` `value` DECIMAL(14,4) NOT NULL; 



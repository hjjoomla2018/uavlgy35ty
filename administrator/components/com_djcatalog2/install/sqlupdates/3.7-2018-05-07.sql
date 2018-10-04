CREATE TABLE IF NOT EXISTS `#__djc2_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(31) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('percent','amount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `value` decimal(14,4) DEFAULT '0.0000',
  `start_date` datetime DEFAULT NULL,
  `expire_date` datetime DEFAULT NULL,
  `reuse` tinyint(1) NOT NULL DEFAULT '0',
  `reuse_limit` int(11) NOT NULL DEFAULT '1',
  `reuse_count` int(11) NOT NULL DEFAULT '0',
  `user_reuse` tinyint(1) NOT NULL DEFAULT '0',
  `user_reuse_limit` int(11) NOT NULL DEFAULT '1',
  `product_id` text COLLATE utf8mb4_unicode_ci,
  `category_id` text COLLATE utf8mb4_unicode_ci,
  `excluded_product_id` text COLLATE utf8mb4_unicode_ci,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__djc2_coupons_used` (
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `used_date` datetime DEFAULT NULL
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__djc2_orders`  
ADD `coupon_id` INT NULL DEFAULT '0'  AFTER `tax`,  
ADD `coupon_code` VARCHAR(31) NULL  AFTER `coupon_id`,  
ADD `coupon_type` ENUM('percent','amount','other') NULL DEFAULT 'percent' AFTER `coupon_code`,  
ADD `coupon_value` DECIMAL(14,4) NULL DEFAULT '0.0000'  AFTER `coupon_type`;

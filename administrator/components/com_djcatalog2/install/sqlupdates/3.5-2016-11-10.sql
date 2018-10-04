CREATE TABLE IF NOT EXISTS `#__djc2_import_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `csv_name` varchar(255) NOT NULL,
  `target_name` varchar(255) NOT NULL,
  `is_db` tinyint(4) NOT NULL DEFAULT '0',
  `db_name` varchar(255) DEFAULT NULL,
  `db_lookup_column` varchar(255) DEFAULT NULL,
  `db_value_column` varchar(255) DEFAULT NULL,
  `db_operator` tinyint(4) NOT NULL DEFAULT '0',
  `db_where_clause` text,
  `merging` char(1) DEFAULT NULL,
  `html_wrapper` varchar(20) DEFAULT NULL,
  `published` tinyint(4) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__djmt_resmushit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `md5` varchar(50) NOT NULL,
  `path` varchar(255) NOT NULL,
  `original_size` int(8) unsigned NOT NULL DEFAULT '0',
  `size` int(8) unsigned NOT NULL DEFAULT '0',
  `percent` double(4,2) NOT NULL DEFAULT '0.00',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `md5` (`md5`)
) DEFAULT CHARSET=utf8;

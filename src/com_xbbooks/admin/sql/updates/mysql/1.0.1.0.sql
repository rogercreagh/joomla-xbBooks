# v1.0.1.0 add #__xbbookgroup
CREATE TABLE IF NOT EXISTS `#__xbbookgroup` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `role` varchar(255) NOT NULL DEFAULT '',
  `role_note` varchar(255) NOT NULL DEFAULT '',
  `listorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_book_id` (`book_id`),
  KEY `idx_group_id` (`group_id`)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

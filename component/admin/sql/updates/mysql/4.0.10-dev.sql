CREATE TABLE IF NOT EXISTS `#__localise_specialkeys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(6) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `specialcase` varchar(255) NOT NULL,
  `keystatus` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__localise` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_path` (`path`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__localise_specialuntranslatablekeys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client` varchar(255) NOT NULL,
  `tag` varchar(6) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `untranslatablekeycase` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__localise_specialblockedkeys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client` varchar(255) NOT NULL,
  `tag` varchar(6) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `blockedkeycase` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__localise_specialextrakeys` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client` varchar(255) NOT NULL,
  `tag` varchar(6) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `extrakeycase` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


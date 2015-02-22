DROP TABLE IF EXISTS `#__localise`;
DROP TABLE IF EXISTS `#__localise_specialuntranslatablekeys`;
DROP TABLE IF EXISTS `#__localise_specialblockedkeys`;
DROP TABLE IF EXISTS `#__localise_specialextrakeys`;
DELETE FROM `#__assets` WHERE `name` LIKE 'com_localise%';

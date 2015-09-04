-- Adminer 4.0.3 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+01:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `places`;
CREATE TABLE `places` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `category` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `postcode` varchar(8) NOT NULL,
  `entry_rates` varchar(255) NOT NULL,
  `opening_times` varchar(255) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `more_info` text,
  `facilities` text,
  `good_stuff` text,
  `bad_stuff` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `places` (`id`, `name`, `latitude`, `longitude`, `category`, `email`, `telephone`, `address`, `postcode`, `entry_rates`, `opening_times`, `rating`, `more_info`, `facilities`, `good_stuff`, `bad_stuff`) VALUES
(1,	'Playground',	51,	0,	'playground',	'playground@kids.com',	'01234556677',	'',	'',	'',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL),
(2,	'Museum',	51,	0.1,	'',	NULL,	'',	'',	'',	'',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);

-- 2015-08-19 21:30:04

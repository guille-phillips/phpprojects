# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.6.22)
# Database: betometer
# Generation Time: 2015-06-12 14:55:55 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table algorithm
# ------------------------------------------------------------

DROP TABLE IF EXISTS `algorithm`;

CREATE TABLE `algorithm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teaser` varchar(255) DEFAULT NULL,
  `advert` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `single_bet` varchar(255) DEFAULT NULL,
  `multi_bet` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `algorithm` WRITE;
/*!40000 ALTER TABLE `algorithm` DISABLE KEYS */;

INSERT INTO `algorithm` (`id`, `teaser`, `advert`, `description`, `single_bet`, `multi_bet`)
VALUES
	(1,'This is the teaser','This is the advert','This is the description','Single bet text','Multi bet text');

/*!40000 ALTER TABLE `algorithm` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table companies
# ------------------------------------------------------------

DROP TABLE IF EXISTS `companies`;

CREATE TABLE `companies` (
  `company_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `license_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;

INSERT INTO `companies` (`company_id`, `name`, `license_key`)
VALUES
	(1,'Chroma Sports','123456'),
	(2,'Test','654321');

/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table company_feeds
# ------------------------------------------------------------

DROP TABLE IF EXISTS `company_feeds`;

CREATE TABLE `company_feeds` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `feed_identifier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `company_feeds` WRITE;
/*!40000 ALTER TABLE `company_feeds` DISABLE KEYS */;

INSERT INTO `company_feeds` (`id`, `company_id`, `feed_identifier`)
VALUES
	(1,1,'beteasy');

/*!40000 ALTER TABLE `company_feeds` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table company_templates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `company_templates`;

CREATE TABLE `company_templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) unsigned NOT NULL,
  `copy` text NOT NULL,
  `priority` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `company_templates` WRITE;
/*!40000 ALTER TABLE `company_templates` DISABLE KEYS */;

INSERT INTO `company_templates` (`id`, `company_id`, `copy`, `priority`)
VALUES
	(1,1,'This is the text on the betometer and a bit more text',1),
	(2,2,'This is the hot tip text 2',1),
	(3,1,'fgh\r\nasdff',2);

/*!40000 ALTER TABLE `company_templates` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table company_themes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `company_themes`;

CREATE TABLE `company_themes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) unsigned NOT NULL DEFAULT '1',
  `background_colour` varchar(8) NOT NULL DEFAULT 'FFFFFF',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `company_themes` WRITE;
/*!40000 ALTER TABLE `company_themes` DISABLE KEYS */;

INSERT INTO `company_themes` (`id`, `company_id`, `background_colour`)
VALUES
	(1,1,'f00080');

/*!40000 ALTER TABLE `company_themes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `session_id` binary(20) DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `admin` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`user_id`, `username`, `password`, `session_id`, `company_id`, `admin`)
VALUES
	(1,'guillermo','agua',X'356A192B7913B04C54574D18C28D46E6395428AB',1,0),
	(2,'carl','agua',X'0000000000000000000000000000000000000000',1,0),
	(3,'daniele','agua',X'0000000000000000000000000000000000000000',2,0),
	(4,'admin','admin',X'1B6453892473A467D07372D45EB05ABC2031647A',NULL,1);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

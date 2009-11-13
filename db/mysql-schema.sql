-- MySQL dump 10.11
--
-- Host: localhost.localdomain    Database: icms
-- ------------------------------------------------------
-- Server version	5.0.45-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_log`
--

DROP TABLE IF EXISTS `access_log`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `access_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` varchar(255) default '',
  `view` tinyint(4) default '0',
  `ref` varchar(255) default '',
  `ip` int(10) unsigned default '0',
  `user_agent` varchar(255) default '',
  `user_id` int(10) unsigned default '0',
  `sess_id` varchar(32) default '',
  `visitor_id` int(11) default '0',
  `log_on` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `url` (`url`),
  KEY `log_on` (`log_on`),
  KEY `view` (`view`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `article`
--

DROP TABLE IF EXISTS `article`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `article` (
  `id` int(11) NOT NULL default '0',
  `folder_id` smallint(6) default NULL,
  `slug` varchar(255) default NULL,
  `flag` tinyint(4) default NULL,
  `post_by` int(11) default NULL,
  `pub_on` int(11) default '0',
  `pub_by` smallint(5) unsigned default '0',
  `headline` tinyint(4) default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `folder_id_slug` (`folder_id`,`slug`),
  KEY `folder_id` (`folder_id`),
  KEY `flag` (`flag`),
  KEY `headline` (`headline`),
  KEY `pub_on` (`pub_on`),
  CONSTRAINT `article_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folder` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `article_comment`
--

DROP TABLE IF EXISTS `article_comment`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `article_comment` (
  `article_id` int(11) NOT NULL default '0',
  `comment_id` int(10) unsigned NOT NULL default '0',
  `ok` tinyint(4) default NULL,
  PRIMARY KEY  (`article_id`,`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `article_field`
--

DROP TABLE IF EXISTS `article_field`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `article_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `article_id` int(11) default NULL,
  `field` tinyint(4) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `article_id` (`article_id`,`field`),
  CONSTRAINT `article_field_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `article_file`
--

DROP TABLE IF EXISTS `article_file`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `article_file` (
  `file_id` smallint(5) unsigned NOT NULL default '0',
  `article_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`article_id`,`file_id`),
  KEY `file_id` (`file_id`),
  CONSTRAINT `article_file_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `article_file_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `article_hits`
--

DROP TABLE IF EXISTS `article_hits`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `article_hits` (
  `article_id` int(11) NOT NULL default '0',
  `dayofyear` smallint(6) NOT NULL default '0',
  `hits` int(10) unsigned default NULL,
  PRIMARY KEY  (`article_id`,`dayofyear`),
  KEY `dayofyear` (`dayofyear`),
  KEY `hits` (`hits`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `article_rev`
--

DROP TABLE IF EXISTS `article_rev`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `article_rev` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `article_field` int(10) unsigned default NULL,
  `content` text,
  `rev_by` smallint(5) unsigned default NULL,
  `rev_on` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `article_field` (`article_field`),
  KEY `rev_on` (`rev_on`),
  CONSTRAINT `article_rev_ibfk_1` FOREIGN KEY (`article_field`) REFERENCES `article_field` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `article_search`
--

DROP TABLE IF EXISTS `article_search`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `article_search` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `folder_id` tinyint(4) default NULL,
  `article_id` int(11) default NULL,
  `permalink` varchar(255) default NULL,
  `subject` varchar(255) default NULL,
  `kicker` varchar(255) default NULL,
  `body` text,
  `rev_on` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `article_id` (`article_id`),
  FULLTEXT KEY `subject` (`subject`,`kicker`,`body`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `comment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `mail` varchar(32) default NULL,
  `website` varchar(32) default NULL,
  `comment` text,
  `post_on` int(11) default NULL,
  `post_sess_id` varchar(32) default NULL,
  `pub_on` int(11) default '0',
  `pub_by` smallint(6) default NULL,
  PRIMARY KEY  (`id`),
  KEY `post_on` (`post_on`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `file` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `subject` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `note` text,
  `type` varchar(128) default NULL,
  `key` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `folder`
--

DROP TABLE IF EXISTS `folder`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `folder` (
  `id` smallint(6) NOT NULL default '0',
  `parent_id` smallint(6) default NULL,
  `short` varchar(18) default NULL,
  `name` varchar(32) default NULL,
  `ctrl` varchar(24) default NULL,
  `special` tinyint(4) default '0',
  `sort_id` tinyint(4) default '0',
  `active` tinyint(4) default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `short` (`short`,`parent_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `folder_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folder` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `folder_admin`
--

DROP TABLE IF EXISTS `folder_admin`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `folder_admin` (
  `folder_id` tinyint(3) NOT NULL default '0',
  `user_id` smallint(5) unsigned NOT NULL default '0',
  `edit` tinyint(4) default '0',
  `publish` tinyint(4) default '0',
  PRIMARY KEY  (`folder_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `folder_config`
--

DROP TABLE IF EXISTS `folder_config`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `folder_config` (
  `folder_id` tinyint(3) NOT NULL default '0',
  `name` varchar(16) NOT NULL default '',
  `value` varchar(32) default NULL,
  PRIMARY KEY  (`folder_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `mail_log`
--

DROP TABLE IF EXISTS `mail_log`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `mail_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` varchar(255) default NULL,
  `sess_id` varchar(32) default NULL,
  `mailfrom` varchar(64) default NULL,
  `name` varchar(48) default NULL,
  `mailto` varchar(64) default NULL,
  `msg` varchar(255) default NULL,
  `log_on` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `order_buyer`
--

DROP TABLE IF EXISTS `order_buyer`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `order_buyer` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `mail` varchar(64) default NULL,
  `shipping_address` varchar(255) default NULL,
  `order_on` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `mail` (`mail`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `order_item`
--

DROP TABLE IF EXISTS `order_item`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `order_item` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `buyer_id` int(10) unsigned default NULL,
  `article_id` int(11) default NULL,
  `qty` smallint(5) unsigned default '1',
  `price` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `order_tmp`
--

DROP TABLE IF EXISTS `order_tmp`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `order_tmp` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `session_id` char(32) default NULL,
  `article_id` int(11) default NULL,
  `qty` smallint(5) unsigned default '1',
  `price` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `session_id` (`session_id`,`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `poll_opt`
--

DROP TABLE IF EXISTS `poll_opt`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `poll_opt` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `article_id` int(11) default NULL,
  `name` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `article_id` (`article_id`,`name`),
  CONSTRAINT `poll_opt_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `poll_vote`
--

DROP TABLE IF EXISTS `poll_vote`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `poll_vote` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `opt_id` smallint(5) unsigned default NULL,
  `ip` int(10) unsigned default NULL,
  `user_agent` varchar(255) default NULL,
  `poll_id` int(11) default NULL,
  `sess_id` char(32) default NULL,
  `vote_on` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `poll_id` (`poll_id`,`sess_id`),
  KEY `ip` (`ip`),
  KEY `user_agent` (`user_agent`),
  KEY `opt_id` (`opt_id`),
  CONSTRAINT `poll_vote_ibfk_1` FOREIGN KEY (`opt_id`) REFERENCES `poll_opt` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `sessions` (
  `id` char(32) NOT NULL default '',
  `access` int(11) default NULL,
  `data` char(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `access` (`access`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `trash`
--

DROP TABLE IF EXISTS `trash`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `trash` (
  `id` int(11) NOT NULL default '0',
  `tbl` varchar(32) default NULL,
  `object` text,
  `del_on` int(11) default '0',
  `del_by` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `uid` varchar(16) default NULL,
  `pwd` char(32) default NULL,
  `name` varchar(32) default NULL,
  `address` varchar(255) default NULL,
  `city` varchar(32) default NULL,
  `mail` varchar(48) default NULL,
  `phone` char(16) default NULL,
  `level` tinyint(4) default '127',
  `since` int(11) default NULL,
  `newsletter` tinyint(4) default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid` (`uid`),
  UNIQUE KEY `mail` (`mail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_tmp`
--

DROP TABLE IF EXISTS `user_tmp`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_tmp` (
  `id` char(32) NOT NULL default '',
  `uid` varchar(16) default NULL,
  `pwd` char(32) default NULL,
  `name` varchar(32) default NULL,
  `address` varchar(255) default NULL,
  `city` varchar(32) default NULL,
  `mail` varchar(48) default NULL,
  `hp` char(16) default NULL,
  `level` tinyint(4) default '127',
  `since` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid` (`uid`),
  UNIQUE KEY `mail` (`mail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-11-12  7:48:16

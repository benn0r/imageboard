-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 01, 2012 at 07:36 
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `board4`
--

-- --------------------------------------------------------

--
-- Table structure for table `board_blacklist_words`
--

CREATE TABLE IF NOT EXISTS `board_blacklist_words` (
  `wid` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wid`),
  KEY `wid` (`wid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_boards`
--

CREATE TABLE IF NOT EXISTS `board_boards` (
  `bid` int(11) NOT NULL AUTO_INCREMENT,
  `pbid` int(11) NOT NULL,
  `url_alias` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `pos` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `default` int(11) NOT NULL,
  PRIMARY KEY (`bid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_bookmarks`
--

CREATE TABLE IF NOT EXISTS `board_bookmarks` (
  `mid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mid`,`uid`),
  KEY `mid` (`mid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `board_config`
--

CREATE TABLE IF NOT EXISTS `board_config` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `board_guets`
--

CREATE TABLE IF NOT EXISTS `board_guets` (
  `remote_addr` varchar(255) NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`remote_addr`,`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `board_media`
--

CREATE TABLE IF NOT EXISTS `board_media` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `image` varchar(4) NOT NULL,
  `extid` varchar(50) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `published` datetime NOT NULL,
  `author_name` varchar(250) NOT NULL,
  `author_uri` varchar(250) NOT NULL,
  `source_name` varchar(250) NOT NULL,
  `source_uri` varchar(250) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `inserttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `filename` varchar(150) NOT NULL,
  PRIMARY KEY (`mid`),
  KEY `uid` (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=51 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_mediaratings`
--

CREATE TABLE IF NOT EXISTS `board_mediaratings` (
  `mid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rating` int(11) NOT NULL,
  PRIMARY KEY (`mid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `board_news`
--

CREATE TABLE IF NOT EXISTS `board_news` (
  `nid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `inserttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text` text NOT NULL,
  PRIMARY KEY (`nid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_posts`
--

CREATE TABLE IF NOT EXISTS `board_posts` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `ppid` int(11) DEFAULT NULL,
  `replyto` int(11) NOT NULL,
  `bid` int(11) NOT NULL DEFAULT '10',
  `uid` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subject` varchar(200) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `ppid` (`ppid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=63 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_posts2tags`
--

CREATE TABLE IF NOT EXISTS `board_posts2tags` (
  `pid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pid`,`tid`),
  KEY `uid` (`uid`),
  KEY `tid` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `board_postvisits`
--

CREATE TABLE IF NOT EXISTS `board_postvisits` (
  `vid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `visittime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `remote_addr` varchar(255) NOT NULL,
  `http_user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`vid`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_reports`
--

CREATE TABLE IF NOT EXISTS `board_reports` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `comment` int(11) NOT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_tags`
--

CREATE TABLE IF NOT EXISTS `board_tags` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_usercomments`
--

CREATE TABLE IF NOT EXISTS `board_usercomments` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `ownerid` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `uid` int(11) NOT NULL,
  `inserttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `private` tinyint(1) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`cid`),
  KEY `ownerid` (`ownerid`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_usernotifications`
--

CREATE TABLE IF NOT EXISTS `board_usernotifications` (
  `nid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pid` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `inserttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `readtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  PRIMARY KEY (`nid`),
  KEY `uid` (`uid`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_users`
--

CREATE TABLE IF NOT EXISTS `board_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `grade` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `last_activity` datetime NOT NULL,
  `activity_text` varchar(255) NOT NULL,
  `online` int(11) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `avatar` varchar(50) NOT NULL,
  `header` varchar(4) NOT NULL,
  `password` varchar(32) NOT NULL,
  `regtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `board` tinyint(1) NOT NULL,
  `board_perpage` int(11) NOT NULL DEFAULT '100',
  `notification_mail_comment` int(11) NOT NULL DEFAULT '1',
  `notification_mail_comment2` int(11) NOT NULL DEFAULT '0',
  `notification_comment` int(11) NOT NULL DEFAULT '1',
  `notification_comment2` int(11) NOT NULL DEFAULT '1',
  `notification_favorite` int(11) NOT NULL DEFAULT '1',
  `remote_addr` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `sid` (`sid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1121 ;

-- --------------------------------------------------------

--
-- Table structure for table `board_userstyles`
--

CREATE TABLE IF NOT EXISTS `board_userstyles` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `stylename` varchar(255) NOT NULL,
  `namestyle` varchar(255) NOT NULL,
  `textstyle` varchar(255) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `board_media`
--
ALTER TABLE `board_media`
  ADD CONSTRAINT `board_media_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `board_posts` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `board_news`
--
ALTER TABLE `board_news`
  ADD CONSTRAINT `board_news_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `board_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `board_posts`
--
ALTER TABLE `board_posts`
  ADD CONSTRAINT `board_posts_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `board_users` (`uid`),
  ADD CONSTRAINT `board_posts_ibfk_3` FOREIGN KEY (`ppid`) REFERENCES `board_posts` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `board_posts2tags`
--
ALTER TABLE `board_posts2tags`
  ADD CONSTRAINT `board_posts2tags_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `board_posts` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `board_posts2tags_ibfk_2` FOREIGN KEY (`tid`) REFERENCES `board_tags` (`tid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `board_postvisits`
--
ALTER TABLE `board_postvisits`
  ADD CONSTRAINT `board_postvisits_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `board_posts` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `board_postvisits_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `board_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `board_usercomments`
--
ALTER TABLE `board_usercomments`
  ADD CONSTRAINT `board_usercomments_ibfk_1` FOREIGN KEY (`ownerid`) REFERENCES `board_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `board_usercomments_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `board_users` (`uid`);

--
-- Constraints for table `board_usernotifications`
--
ALTER TABLE `board_usernotifications`
  ADD CONSTRAINT `board_usernotifications_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `board_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `board_usernotifications_ibfk_2` FOREIGN KEY (`pid`) REFERENCES `board_posts` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
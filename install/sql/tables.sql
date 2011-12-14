SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_blacklist_words`
--

CREATE TABLE IF NOT EXISTS `board_blacklist_words` (
  `wid` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wid`),
  KEY `wid` (`wid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_boards`
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
-- Tabellenstruktur für Tabelle `board_bookmarks`
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
-- Tabellenstruktur für Tabelle `board_config`
--

CREATE TABLE IF NOT EXISTS `board_config` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_guets`
--

CREATE TABLE IF NOT EXISTS `board_guets` (
  `remote_addr` varchar(255) NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`remote_addr`,`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_media`
--

CREATE TABLE IF NOT EXISTS `board_media` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `media` text NOT NULL,
  `media_1` text NOT NULL,
  `media_2` text NOT NULL,
  `media_3` text NOT NULL,
  `hash` varchar(32) NOT NULL,
  `watermark` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`mid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10837 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_media2posts`
--

CREATE TABLE IF NOT EXISTS `board_media2posts` (
  `pid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  PRIMARY KEY (`pid`,`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_mediaratings`
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
-- Tabellenstruktur für Tabelle `board_posts`
--

CREATE TABLE IF NOT EXISTS `board_posts` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `ppid` int(11) NOT NULL,
  `replyto` int(11) NOT NULL,
  `bid` int(11) NOT NULL DEFAULT '10',
  `uid` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subject` varchar(200) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `ppid` (`ppid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=42348 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_posts2tags`
--

CREATE TABLE IF NOT EXISTS `board_posts2tags` (
  `pid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pid`,`tid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_postvisits`
--

CREATE TABLE IF NOT EXISTS `board_postvisits` (
  `vid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `visittime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `remote_addr` varchar(255) NOT NULL,
  `http_user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`vid`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=901089 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_tags`
--

CREATE TABLE IF NOT EXISTS `board_tags` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=108 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_usernotifications`
--

CREATE TABLE IF NOT EXISTS `board_usernotifications` (
  `nid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `inserttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `readtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  PRIMARY KEY (`nid`),
  KEY `uid` (`uid`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=42859 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_users`
--

CREATE TABLE IF NOT EXISTS `board_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `grade` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `last_activity` datetime NOT NULL,
  `remote_addr` varchar(255) NOT NULL,
  `online` int(11) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `avatar` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `regtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `board_perpage` int(11) NOT NULL DEFAULT '100',
  `notification_mail_comment` int(11) NOT NULL DEFAULT '1',
  `notification_mail_comment2` int(11) NOT NULL DEFAULT '0',
  `notification_comment` int(11) NOT NULL DEFAULT '1',
  `notification_comment2` int(11) NOT NULL DEFAULT '1',
  `notification_favorite` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1118 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board_userstyles`
--

CREATE TABLE IF NOT EXISTS `board_userstyles` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `stylename` varchar(20) NOT NULL,
  `namestyle` varchar(255) NOT NULL,
  `textstyle` varchar(255) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tiny_url`
--

CREATE TABLE IF NOT EXISTS `tiny_url` (
  `source` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `target` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SET FOREIGN_KEY_CHECKS=1;
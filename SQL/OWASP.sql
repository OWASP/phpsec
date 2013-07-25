-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 25, 2013 at 03:34 AM
-- Server version: 5.5.22
-- PHP Version: 5.3.10-1ubuntu3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `OWASP`
--

-- --------------------------------------------------------

--
-- Table structure for table `LOGS`
--

CREATE TABLE IF NOT EXISTS `LOGS` (
  `MESSAGE` text,
  `FILENAME` text,
  `TYPE` text,
  `PRIORITY` text,
  `DATETIME` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `LOGS`
--

INSERT INTO `LOGS` (`MESSAGE`, `FILENAME`, `TYPE`, `PRIORITY`, `DATETIME`) VALUES
('This is the first message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'WARNING', 'LOW', '07-24-2013 01:52:11'),
('This is the second message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'ERROR', 'NORMAL', '07-24-2013 01:52:11'),
('This is the first message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'WARNING', 'LOW', '07-24-2013 01:52:13'),
('This is the second message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'ERROR', 'NORMAL', '07-24-2013 01:52:13'),
('This is the first messageeee', '', 'ERROR', 'NORMAL', '07-24-2013 01:52:31'),
('This is the first message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'WARNING', 'LOW', '07-24-2013 02:20:05'),
('This is the second message', '', 'ERROR', 'NORMAL', '07-24-2013 02:20:05'),
('This is the first message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'WARNING', 'LOW', '07-24-2013 03:57:40'),
('This is the second message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'ERROR', 'NORMAL', '07-24-2013 03:57:40'),
('This is the first message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'WARNING', 'LOW', '07-25-2013 03:03:07'),
('This is the second message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'ERROR', 'NORMAL', '07-25-2013 03:03:07'),
('This is the first message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'WARNING', 'LOW', '07-25-2013 03:04:26'),
('This is the second message', '/var/www/phpsec/test/libs/logs/Logs.db.test.php', 'ERROR', 'NORMAL', '07-25-2013 03:04:26');

-- --------------------------------------------------------

--
-- Table structure for table `PASSWORD`
--

CREATE TABLE IF NOT EXISTS `PASSWORD` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TEMP_PASS` varchar(128) NOT NULL,
  `USE_FLAG` tinyint(1) NOT NULL,
  `TEMP_TIME` int(10) NOT NULL,
  `TOTAL_LOGIN_ATTEMPTS` int(2) DEFAULT NULL,
  `LAST_LOGIN_ATTEMPT` int(10) DEFAULT NULL,
  `USERID` varchar(32) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `USERID` (`USERID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `SESSION`
--

CREATE TABLE IF NOT EXISTS `SESSION` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SESSION_ID` varchar(32) NOT NULL,
  `DATE_CREATED` int(10) NOT NULL,
  `LAST_ACTIVITY` int(10) NOT NULL,
  `USERID` varchar(32) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SESSION_ID` (`SESSION_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `SESSION_DATA`
--

CREATE TABLE IF NOT EXISTS `SESSION_DATA` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SESSION_ID` varchar(32) NOT NULL,
  `KEY` varchar(32) NOT NULL,
  `VALUE` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `USER`
--

CREATE TABLE IF NOT EXISTS `USER` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `USERID` varchar(32) NOT NULL,
  `ACCOUNT_CREATED` int(10) NOT NULL,
  `HASH` varchar(128) NOT NULL,
  `DATE_CREATED` int(10) NOT NULL,
  `TOTAL_SESSIONS` int(2) NOT NULL DEFAULT '0',
  `ALGO` varchar(15) NOT NULL,
  `DYNAMIC_SALT` varchar(128) NOT NULL,
  `STATIC_SALT` varchar(128) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `USERID` (`USERID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

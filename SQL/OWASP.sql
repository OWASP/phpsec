-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 13, 2013 at 11:37 PM
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
-- Table structure for table `AUTH_TOKENS`
--

CREATE TABLE IF NOT EXISTS `AUTH_TOKENS` (
  `AUTH_ID` varchar(32) DEFAULT NULL,
  `USERID` varchar(32) NOT NULL,
  `DATE_CREATED` int(10) NOT NULL,
  UNIQUE KEY `AUTH_ID` (`AUTH_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `LOGS`
--

CREATE TABLE IF NOT EXISTS `LOGS` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `MESSAGE` text,
  `FILENAME` text,
  `TYPE` text,
  `PRIORITY` text,
  `DATETIME` text,
  `LINE` int(10) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `USER`
--

CREATE TABLE IF NOT EXISTS `USER` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `USERID` varchar(32) NOT NULL,
  `ACCOUNT_CREATED` int(10) NOT NULL,
  `LOCKED` tinyint(1) NOT NULL DEFAULT '0',
  `INACTIVE` tinyint(1) NOT NULL DEFAULT '0',
  `HASH` varchar(128) NOT NULL,
  `DATE_CREATED` int(10) NOT NULL,
  `ALGO` varchar(15) NOT NULL,
  `DYNAMIC_SALT` varchar(128) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `USERID` (`USERID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=182 ;

-- --------------------------------------------------------

--
-- Table structure for table `XUSER`
--

CREATE TABLE IF NOT EXISTS `XUSER` (
  `USERID` varchar(32) NOT NULL,
  `P_EMAIL` varchar(128) NOT NULL,
  `S_EMAIL` varchar(128) DEFAULT NULL,
  `FIRST_NAME` varchar(40) DEFAULT NULL,
  `LAST_NAME` varchar(40) DEFAULT NULL,
  `DOB` int(10) DEFAULT NULL,
  `SECURITY1` varchar(128) DEFAULT NULL,
  `SECURITY2` varchar(128) DEFAULT NULL,
  `DYNAMIC_SALT` varchar(128) DEFAULT NULL,
  `ALGO` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`USERID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 04, 2013 at 02:04 PM
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
-- Table structure for table `AUTH_STORAGE`
--

CREATE TABLE IF NOT EXISTS `AUTH_STORAGE` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AUTH_ID` varchar(128) DEFAULT NULL,
  `DATE_CREATED` int(10) NOT NULL,
  `USERID` varchar(32) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `USERID` (`USERID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

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
  UNIQUE KEY `USERID_2` (`USERID`),
  KEY `USERID` (`USERID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=855 ;

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
  UNIQUE KEY `SESSION_ID` (`SESSION_ID`),
  KEY `USERID` (`USERID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=776 ;

-- --------------------------------------------------------

--
-- Table structure for table `SESSION_DATA`
--

CREATE TABLE IF NOT EXISTS `SESSION_DATA` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SESSION_ID` varchar(32) NOT NULL,
  `KEY` varchar(32) NOT NULL,
  `VALUE` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SESSION_ID` (`SESSION_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=201 ;

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
  `FIRST_NAME` varchar(20) DEFAULT NULL,
  `LAST_NAME` varchar(20) DEFAULT NULL,
  `EMAIL` varchar(60) DEFAULT NULL,
  `ALGO` varchar(15) NOT NULL,
  `DYNAMIC_SALT` varchar(128) NOT NULL,
  `STATIC_SALT` varchar(128) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `USERID` (`USERID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2245 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `AUTH_STORAGE`
--
ALTER TABLE `AUTH_STORAGE`
  ADD CONSTRAINT `AUTH_STORAGE_ibfk_1` FOREIGN KEY (`USERID`) REFERENCES `USER` (`USERID`) ON DELETE NO ACTION;

--
-- Constraints for table `PASSWORD`
--
ALTER TABLE `PASSWORD`
  ADD CONSTRAINT `PASSWORD_ibfk_3` FOREIGN KEY (`USERID`) REFERENCES `USER` (`USERID`) ON DELETE NO ACTION;

--
-- Constraints for table `SESSION`
--
ALTER TABLE `SESSION`
  ADD CONSTRAINT `SESSION_ibfk_2` FOREIGN KEY (`USERID`) REFERENCES `USER` (`USERID`) ON DELETE NO ACTION;

--
-- Constraints for table `SESSION_DATA`
--
ALTER TABLE `SESSION_DATA`
  ADD CONSTRAINT `SESSION_DATA_ibfk_5` FOREIGN KEY (`SESSION_ID`) REFERENCES `SESSION` (`SESSION_ID`) ON DELETE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

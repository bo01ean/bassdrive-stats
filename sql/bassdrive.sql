-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 27, 2012 at 12:18 AM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

create database bassdrive;

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `bassdrive`
--

-- --------------------------------------------------------

--
-- Table structure for table `pulls`
--

CREATE TABLE IF NOT EXISTS `pulls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creationTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2305 ;

-- --------------------------------------------------------

--
-- Table structure for table `shows`
--

CREATE TABLE IF NOT EXISTS `shows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `creationTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=230 ;

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numListeners` int(11) NOT NULL,
  `streamStatus` int(11) NOT NULL,
  `peakListeners` int(11) NOT NULL,
  `maxListeners` int(11) NOT NULL,
  `uniqueListeners` int(11) NOT NULL,
  `bitrate` int(11) NOT NULL,
  `showId` int(11) NOT NULL,
  `pullId` int(11) NOT NULL,
  `urlId` int(11) NOT NULL,
  `creationTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `showId` (`showId`),
  KEY `urlId` (`urlId`),
  KEY `pullId` (`pullId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=52521 ;

-- --------------------------------------------------------

--
-- Table structure for table `urls`
--

CREATE TABLE IF NOT EXISTS `urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(128) NOT NULL,
  `creationTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

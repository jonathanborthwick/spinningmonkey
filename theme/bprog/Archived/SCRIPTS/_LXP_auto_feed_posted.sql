-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 05, 2020 at 09:18 PM
-- Server version: 5.6.41-84.1
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spinnis0_WPLXP`
--

-- --------------------------------------------------------

--
-- Table structure for table `_LXP_auto_feed_posted`
--

CREATE TABLE `_LXP_auto_feed_posted` (
  `AUTO_FEED_ID` bigint(20) UNSIGNED NOT NULL,
  `POST_ID` bigint(20) UNSIGNED NOT NULL,
  `INDEX_IN_MARKUP` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `ORIGINAL_POST_HYPERLINK` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `DATE_CREATED` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IS_EDITED` tinyint(1) NOT NULL DEFAULT '0',
  `NOTES` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 12, 2013 at 12:06 PM
-- Server version: 5.5.28
-- PHP Version: 5.3.10-1ubuntu3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bhawani`
--

-- --------------------------------------------------------

--
-- Table structure for table `jos_xbalance_sheet`
--

CREATE TABLE IF NOT EXISTS `jos_xbalance_sheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Head` varchar(45) DEFAULT NULL,
  `positive_side` varchar(2) NOT NULL,
  `is_pandl` tinyint(4) NOT NULL,
  `show_sub` varchar(20) NOT NULL,
  `subtract_from` varchar(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `jos_xbalance_sheet`
--

INSERT INTO `jos_xbalance_sheet` (`id`, `Head`, `positive_side`, `is_pandl`, `show_sub`, `subtract_from`) VALUES
(1, 'Liabilities', 'LT', 0, 'SchemeGroup', 'Cr'),
(2, 'Assets', 'RT', 0, 'SchemeGroup', 'Dr'),
(3, 'Capital Account', 'LT', 0, 'SchemeName', 'Cr'),
(4, 'Expenses', 'RT', 1, 'SchemeName', 'Cr'),
(5, 'Income', 'LT', 1, 'SchemeName', 'Dr'),
(6, 'Suspence Account', 'LT', 0, 'SchemeGroup', 'Cr'),
(7, 'Fixed Assets', 'RT', 0, 'SchemeGroup', 'Dr'),
(8, 'Branch/Divisions', 'RT', 0, 'Accounts', 'Dr'),
(9, 'Current Liabilities', 'LT', 0, 'SchemeName', 'Dr');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

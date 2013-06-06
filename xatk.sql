-- phpMyAdmin SQL Dump
-- version 3.5.7deb1.precise~ppa.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 06, 2013 at 05:31 PM
-- Server version: 5.5.28-0ubuntu0.12.04.2
-- PHP Version: 5.3.10-1ubuntu3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bhawani_softdemo`
--

-- --------------------------------------------------------

--
-- Table structure for table `jos_xatk_attendance`
--

CREATE TABLE IF NOT EXISTS `jos_xatk_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `Created_at` date NOT NULL,
  `TimeHour` int(11) NOT NULL,
  `TimeMinute` int(11) NOT NULL,
  `Mode` varchar(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `jos_xatk_attendance`
--

INSERT INTO `jos_xatk_attendance` (`id`, `emp_id`, `Created_at`, `TimeHour`, `TimeMinute`, `Mode`) VALUES
(5, 1, '2013-05-17', 3, 27, 'PL'),
(6, 1, '2013-05-18', 10, 27, 'L'),
(7, 1, '2013-05-16', 18, 52, 'A'),
(8, 1, '2013-05-15', 18, 53, 'P'),
(9, 1, '2013-05-14', 18, 54, 'P'),
(10, 1, '2013-05-19', 10, 56, 'P');

-- --------------------------------------------------------

--
-- Table structure for table `jos_xatk_emphistrory`
--

CREATE TABLE IF NOT EXISTS `jos_xatk_emphistrory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Created_At` date NOT NULL,
  `Post` varchar(255) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `jos_xatk_employee`
--

CREATE TABLE IF NOT EXISTS `jos_xatk_employee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `FatherName` varchar(255) NOT NULL,
  `PresentAddress` text NOT NULL,
  `PermanentAddress` text NOT NULL,
  `MobileNo` int(11) NOT NULL,
  `LandlineNo` int(11) NOT NULL,
  `DOB` date NOT NULL,
  `OtherDetails` text NOT NULL,
  `Salary` int(11) NOT NULL,
  `Allownces` int(11) NOT NULL,
  `PFSalary` int(11) NOT NULL,
  `isPFApplicable` tinyint(4) NOT NULL DEFAULT '0',
  `PFAmount` int(11) NOT NULL,
  `TDSAmount` int(11) NOT NULL,
  `Account_Number` int(11) NOT NULL,
  `Bank_Name` varchar(255) NOT NULL,
  `SalaryMode` int(11) NOT NULL,
  `is_Active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `jos_xatk_employee`
--

INSERT INTO `jos_xatk_employee` (`id`, `branch_id`, `name`, `FatherName`, `PresentAddress`, `PermanentAddress`, `MobileNo`, `LandlineNo`, `DOB`, `OtherDetails`, `Salary`, `Allownces`, `PFSalary`, `isPFApplicable`, `PFAmount`, `TDSAmount`, `Account_Number`, `Bank_Name`, `SalaryMode`, `is_Active`) VALUES
(1, 2, 'ramlal', 'fname', 'aaa', 'aa', 0, 0, '0000-00-00', '', 5000, 0, 5000, 1, 200, 100, 45, '', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `jos_xatk_holidays`
--

CREATE TABLE IF NOT EXISTS `jos_xatk_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) NOT NULL,
  `HolidayDate` datetime NOT NULL,
  `Remark` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `jos_xatk_holidays`
--

INSERT INTO `jos_xatk_holidays` (`id`, `branch_id`, `HolidayDate`, `Remark`) VALUES
(2, 2, '2013-05-13 00:00:00', 'holiday'),
(3, 3, '2013-05-14 00:00:00', 'holiday'),
(4, 5, '2013-05-13 00:00:00', 'holiday'),
(5, 7, '2013-05-13 00:00:00', 'holiday'),
(6, 8, '2013-05-13 00:00:00', 'holiday'),
(7, 2, '2013-04-07 00:00:00', 'Universal holiday'),
(8, 3, '2013-04-07 00:00:00', 'Universal holiday'),
(9, 5, '2013-04-07 00:00:00', 'Universal holiday'),
(10, 7, '2013-04-07 00:00:00', 'Universal holiday'),
(11, 8, '2013-04-07 00:00:00', 'Universal holiday');

-- --------------------------------------------------------

--
-- Table structure for table `jos_xatk_leaves_alloted`
--

CREATE TABLE IF NOT EXISTS `jos_xatk_leaves_alloted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `Created_At` datetime NOT NULL,
  `Leaves` int(11) NOT NULL,
  `Narretion` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `jos_xatk_leaves_alloted`
--

INSERT INTO `jos_xatk_leaves_alloted` (`id`, `emp_id`, `Created_At`, `Leaves`, `Narretion`) VALUES
(1, 1, '2013-04-01 00:00:00', 12, 'Total leavs');

-- --------------------------------------------------------

--
-- Table structure for table `jos_xatk_leaves_used`
--

CREATE TABLE IF NOT EXISTS `jos_xatk_leaves_used` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `Created_At` datetime NOT NULL,
  `leaves` int(11) NOT NULL,
  `Narretion` text NOT NULL,
  `isPaid` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `jos_xatk_leaves_used`
--

INSERT INTO `jos_xatk_leaves_used` (`id`, `emp_id`, `Created_At`, `leaves`, `Narretion`, `isPaid`) VALUES
(12, 1, '2013-05-17 00:00:00', 1, 'Leave on 2013-05-17', 1),
(14, 1, '2013-05-18 00:00:00', 1, 'Leave on 2013-05-18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `jos_xatk_payment`
--

CREATE TABLE IF NOT EXISTS `jos_xatk_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `TotalWorkingDays` int(11) NOT NULL,
  `PresentDays` int(11) NOT NULL,
  `HoliDays` int(11) NOT NULL,
  `Sundays` int(11) NOT NULL,
  `Leaves` int(11) NOT NULL,
  `LeavesPaid` int(11) NOT NULL,
  `Absent` int(11) NOT NULL,
  `Salary` float(11,2) NOT NULL,
  `PFAmount` int(11) NOT NULL,
  `Deduction` int(11) NOT NULL,
  `MonthYear` varchar(6) NOT NULL,
  `Narration` text NOT NULL,
  `Created_At` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `jos_xatk_payment`
--

INSERT INTO `jos_xatk_payment` (`id`, `emp_id`, `TotalWorkingDays`, `PresentDays`, `HoliDays`, `Sundays`, `Leaves`, `LeavesPaid`, `Absent`, `Salary`, `PFAmount`, `Deduction`, `MonthYear`, `Narration`, `Created_At`) VALUES
(12, 1, 26, 2, 1, 4, 1, 1, 1, 569.92, 0, 7, '201305', '0', '2013-05-19');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

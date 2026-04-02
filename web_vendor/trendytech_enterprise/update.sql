-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2016-01-04 08:48:22
-- 服务器版本： 5.5.44-MariaDB
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `vinchin_db`
--
USE `vinchin_db`;
-- --------------------------------------------------------

TRUNCATE TABLE `bd_email_notice`;
--
-- 转存表中的数据 `bd_email_notice`
--

INSERT INTO `bd_email_notice` (`email_notice_flag`, `email_start_time`, `email_end_time`, `system_notice_flag`, `system_notice_level`, `task_notice_flag`, `task_notice_level`, `smtp_config`) VALUES
(2, '00:00:00', '23:59:59', 2, '', 2, '', '{"host":"","port":"25","email":"","pass":"","encryption":0}');


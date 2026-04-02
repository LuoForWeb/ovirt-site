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

--
-- 转存表中的数据 `bd_user`
--

UPDATE `bd_user` SET `password` = "a1d91ce62e3e35944cd98df7764afaed" WHERE `user_name` = "admin";
UPDATE `bd_system_settings` SET `settings_content` = '{\"config\":{\"title\":\"Vinchin Backup & Recovery\"}}' WHERE `bd_system_settings`.`settings_id` = 2;
UPDATE `bd_organization` SET `parent_name` = 'organization' WHERE `bd_organization`.`id` = 2;
UPDATE `bd_agent_group` SET `group_name` = 'Default Group' WHERE `group_uuid` = "0e8a97e7-b170-90c2-8b7f-86bbd5a28a4c";

--
-- 更改用户组和角色名称为英文
--
UPDATE bd_role SET role_name = "Master" WHERE role_uuid = "a30f7728-2ef7-bca0-2224-07deba8ce3e5";
UPDATE bd_role SET role_name = "Admin" WHERE role_uuid = "32c4bd54-2448-a4ac-50d8-279edda0fb89";
UPDATE bd_role SET role_name = "Operator" WHERE role_uuid = "a633143d-f5b3-b348-998d-a209d62e27f0";
UPDATE bd_role SET role_name = "Auditor" WHERE role_uuid = "7e891486-9d2c-603b-6fe1-8bc0d332f602";
UPDATE bd_role SET role_name = "Tenant Admin" WHERE role_uuid = "eee859d5-341a-b95a-33d0-6ed583d0f7a1";
UPDATE bd_role SET role_name = "Tenant Operator" WHERE role_uuid = "2b214439-8f0b-ec23-a3be-2014ad9baecc";
UPDATE bd_role SET role_name = "Tenant Auditor" WHERE role_uuid = "03e12c93-9dc1-371a-6a64-b74965d36723";

UPDATE bd_user_group SET user_group_name = "Master" WHERE user_group_uuid = "e99ae858-d549-4754-9310-457477f4c2e3";
UPDATE bd_user_group SET user_group_name = "Admin" WHERE user_group_uuid = "372fa0bf-d1b0-46ec-864c-cd5592813669";
UPDATE bd_user_group SET user_group_name = "Operator" WHERE user_group_uuid = "09d1c727-d87b-494a-81bf-fb9014ed313c";
UPDATE bd_user_group SET user_group_name = "Auditor" WHERE user_group_uuid = "d57a9f76-845d-43a8-bb79-bdafbf405c85";

TRUNCATE TABLE `bd_email_notice`;
--
-- 转存表中的数据 `bd_email_notice`
--

INSERT INTO `bd_email_notice` (`email_notice_flag`, `email_start_time`, `email_end_time`, `system_notice_flag`, `system_notice_level`, `task_notice_flag`, `task_notice_level`, `smtp_config`, `report_config`, `receive_email`) VALUES
(2, '00:00:00', '23:59:59', 2, '', 2, '', '{"host":"","port":"25","email":"","pass":"","encryption":0}', '{}', '[]');
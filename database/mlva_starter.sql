SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS `databases` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `user_id` int(10) NOT NULL,
  `group_id` int(10) NOT NULL,
  `marker_num` int(11) NOT NULL,
  `metadata` text NOT NULL,
  `data` longtext NOT NULL,
  `state` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `description` text,
  `website` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `genotypenumbers` (
  `value` int(11) NOT NULL,
  `strain_id` int(10) unsigned NOT NULL,
  `panel_id` int(10) unsigned NOT NULL,
  `state` tinyint(4) NOT NULL COMMENT 'fixed ?'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `genotype_number` (
  `panel_id` int(11) NOT NULL,
  `value` int(11) DEFAULT NULL,
  `state` int(3) NOT NULL,
  `data` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT 'info',
  `permissions` text,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `migrations` (
  `version` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `panels` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `database_id` int(10) NOT NULL,
  `data` longtext NOT NULL,
  `state` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `strains` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `database_id` int(10) NOT NULL,
  `metadata` text NOT NULL,
  `data` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `token` varchar(255) DEFAULT 'user_default_token'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users_infos` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `bio` text,
  `birthdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `website` varchar(255) DEFAULT NULL,
  `notifications` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_has_group` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `databases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `group_id` (`group_id`);

ALTER TABLE `genotypenumbers`
  ADD KEY `strain_id` (`strain_id`,`panel_id`),
  ADD KEY `strain_id_2` (`strain_id`);

ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `panels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `database_id` (`database_id`);

ALTER TABLE `strains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `database_id` (`database_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `users_infos`
  ADD PRIMARY KEY (`user_id`);


ALTER TABLE `databases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `panels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `strains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


INSERT INTO `groups` (`id`, `name`, `label`, `permissions`, `description`) VALUES
(1, 'Administrator', 'primary', '{"admin":1}', NULL);
INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `last_login`, `token`) VALUES
(1, 'admin', 'raffin@ensta.fr', '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'user_default_token');
INSERT INTO `users_infos` (`user_id`, `first_name`, `last_name`, `bio`, `birthdate`, `website`, `notifications`) VALUES
(1, '', '', '', '0000-00-00 00:00:00', NULL, 1);
INSERT INTO `user_has_group` (`user_id`, `group_id`) VALUES
(1, 1);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

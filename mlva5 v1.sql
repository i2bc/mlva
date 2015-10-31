-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Sam 31 Octobre 2015 à 13:41
-- Version du serveur: 5.6.12-log
-- Version de PHP: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `mlva5`
--
CREATE DATABASE IF NOT EXISTS `mlva5` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `mlva5`;

-- --------------------------------------------------------

--
-- Structure de la table `databases`
--

CREATE TABLE IF NOT EXISTS `databases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `marker_num` int(11) NOT NULL,
  `metadatas` text NOT NULL,
  `datas` text NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'public',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_udpate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `databases`
--

INSERT INTO `databases` (`id`, `name`, `user_id`, `group_id`, `marker_num`, `metadatas`, `datas`, `state`, `created_at`, `last_udpate`) VALUES
(1, 'Bactérie lambda', 3, 4, 2, '["location", "species"]', '["Bruce00-1322", "Bruce12-73", "Bruce55-2066"]', 1, '2015-10-28 08:00:34', '2015-10-28 11:04:29');

-- --------------------------------------------------------

--
-- Structure de la table `genotypenumbers`
--

CREATE TABLE IF NOT EXISTS `genotypenumbers` (
  `value` int(11) NOT NULL,
  `strain_id` int(10) unsigned NOT NULL,
  `panel_id` int(10) unsigned NOT NULL,
  `state` tinyint(4) NOT NULL COMMENT 'fixed ?',
  KEY `strain_id` (`strain_id`,`panel_id`),
  KEY `strain_id_2` (`strain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT 'info',
  `permissions` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Contenu de la table `groups`
--

INSERT INTO `groups` (`id`, `name`, `label`, `permissions`) VALUES
(1, 'Admin', 'primary', '{"admin":1}'),
(2, 'Modérateur', 'info', '{"moderator":1, "comments.edit":1, "comments.delete":1}'),
(3, 'Rédacteur', 'info', '{"news.create":1, "news.edit":1}'),
(4, 'User', 'info', '{"videos.create":1, "comments.create":1}'),
(6, 'Validateur', 'info', '{"videos.edit":1, "videos.moderate":1}');

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE IF NOT EXISTS `migrations` (
  `version` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `migrations`
--

INSERT INTO `migrations` (`version`) VALUES
(20151024164426);

-- --------------------------------------------------------

--
-- Structure de la table `panels`
--

CREATE TABLE IF NOT EXISTS `panels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `database_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `database_id` (`database_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `strains`
--

CREATE TABLE IF NOT EXISTS `strains` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `database_id` int(11) NOT NULL,
  `metadatas` text NOT NULL,
  `datas` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `database_id` (`database_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `strains`
--

INSERT INTO `strains` (`id`, `name`, `database_id`, `metadatas`, `datas`) VALUES
(1, 'Poulet', 1, '{"location":"Turkey"}', '{"Bruce00-1322":2, "Bruce12-73":5, "Bruce55-2066":1}'),
(2, 'Poule', 1, '{"location":"France","species":"cow"}', '{"Bruce00-1322":1, "Bruce12-73":3, "Bruce55-2066":4}'),
(3, 'Cocotte', 1, '{"location":"England","species":"cow"}', '{"Bruce00-1322":1, "Bruce12-73":4, "Bruce55-2066":1}');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `password`, `created_at`, `last_login`) VALUES
(1, 'Jon', 'Snow', 'jon', 'jon@ensta.fr', '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi', '2015-10-27 10:56:00', '0000-00-00 00:00:00'),
(2, 'John', 'Doe', 'john', 'jhon@ensta.fr', '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi', '2015-10-27 10:56:00', '0000-00-00 00:00:00'),
(3, 'Brendan', 'Daoud', 'brendan', 'daoud@ensta.fr', '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi', '2015-10-27 10:56:01', '2015-10-31 08:06:24'),
(4, 'Antonin', 'Raffin', 'antonin', 'antonin.raffin@ensta-paristech.fr', '$2y$10$6/q.yfu9QHcStu5JPLb8teT6lIF0F8z50gts8yvO7Hcz23QAhFGpi', '2015-10-27 10:56:01', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `user_has_group`
--

CREATE TABLE IF NOT EXISTS `user_has_group` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `user_has_group`
--

INSERT INTO `user_has_group` (`user_id`, `group_id`) VALUES
(0, 5),
(3, 1),
(4, 1),
(1, 2),
(1, 6),
(2, 3),
(3, 4),
(4, 4);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

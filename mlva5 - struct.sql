-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Sam 24 Octobre 2015 à 13:00
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
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

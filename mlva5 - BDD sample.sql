-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Jeu 29 Octobre 2015 à 07:59
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

--
-- Contenu de la table `databases`
--

INSERT INTO `databases` (`id`, `name`, `user_id`, `group_id`, `marker_num`, `metadatas`, `datas`, `state`, `created_at`, `last_udpate`) VALUES
(1, 'Bactérie lambda', 3, 4, 2, '["location", "species"]', '["Bruce00-1322", "Bruce12-73", "Bruce55-2066"]', 1, '2015-10-28 08:00:34', '2015-10-28 11:04:29');

--
-- Contenu de la table `strains`
--

INSERT INTO `strains` (`id`, `name`, `database_id`, `metadatas`, `datas`) VALUES
(1, 'Poulet', 1, '{"location":"Turkey"}', '{"Bruce00-1322":2, "Bruce12-73":5, "Bruce55-2066":1}'),
(2, 'Poule', 1, '{"location":"France","species":"cow"}', '{"Bruce00-1322":1, "Bruce12-73":3, "Bruce55-2066":4}'),
(3, 'Cocotte', 1, '{"location":"England","species":"cow"}', '{"Bruce00-1322":1, "Bruce12-73":4, "Bruce55-2066":1}');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

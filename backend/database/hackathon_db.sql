-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : dim. 06 avr. 2025 à 12:02
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `hackathon_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `data` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `level` varchar(20) DEFAULT 'info',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `data`, `ip_address`, `user_agent`, `level`, `created_at`) VALUES
(1, NULL, 'create_error', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:44:06'),
(2, NULL, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:44:07'),
(3, NULL, 'create_error', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:46:20'),
(4, NULL, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:46:20'),
(5, NULL, 'login_attempt', 'Tentative de connexion', '{\"email\":\"admin@test.com\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'info', '2025-03-26 18:46:49'),
(6, NULL, 'login_failed', 'Échec de connexion', '{\"email\":\"admin@test.com\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'warning', '2025-03-26 18:46:49'),
(7, NULL, 'login_error', 'Email ou mot de passe incorrect', '{\"email\":\"admin@test.com\",\"error\":\"Email ou mot de passe incorrect\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:46:49'),
(8, NULL, 'create_error', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:56:23'),
(9, NULL, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:56:23'),
(10, NULL, 'create_error', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'', '{\"email\":\"blacknazard@gmail.com\",\"error\":\"SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:57:09'),
(11, NULL, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'', '{\"email\":\"blacknazard@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[42S22]: Column not found: 1054 Unknown column \'status\' in \'field list\'\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:57:09'),
(12, NULL, 'login_attempt', 'Tentative de connexion', '{\"email\":\"blacknazard@gmail.com\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'info', '2025-03-26 18:58:52'),
(13, NULL, 'login_failed', 'Échec de connexion', '{\"email\":\"blacknazard@gmail.com\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'warning', '2025-03-26 18:58:52'),
(14, NULL, 'login_error', 'Email ou mot de passe incorrect', '{\"email\":\"blacknazard@gmail.com\",\"error\":\"Email ou mot de passe incorrect\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-03-26 18:58:52'),
(15, NULL, 'login_attempt', 'Tentative de connexion', '{\"email\":\"loyovat896@opposir.com\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'info', '2025-03-26 18:59:25'),
(16, 1, 'login_success', 'Connexion réussie', '{\"user_id\":1,\"role\":\"participant\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'info', '2025-03-26 18:59:25'),
(17, NULL, 'login_attempt', 'Tentative de connexion', '{\"email\":\"galigom995@opposir.com\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'info', '2025-03-30 12:56:34'),
(18, 2, 'login_success', 'Connexion réussie', '{\"user_id\":2,\"role\":\"participant\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'info', '2025-03-30 12:56:34'),
(19, 2, 'login_error', 'Email ou mot de passe incorrect', '{\"email\":\"loyovat896@opposir.com\",\"error\":\"Email ou mot de passe incorrect\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:11:50'),
(20, 2, 'create_error', 'SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:12:58'),
(21, 2, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:12:58'),
(22, 2, 'create_error', 'SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:14:19'),
(23, 2, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:14:19'),
(24, 2, 'create_error', 'SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:14:52'),
(25, 4, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:14:52'),
(26, 2, 'create_error', 'SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:15:00'),
(27, 2, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:15:00'),
(28, 2, 'create_error', 'SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:19:21'),
(29, 2, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value', '{\"email\":\"azerty@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[HY000]: General error: 1364 Field \'special_comp\' doesn\'t have a default value\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-01 19:19:21'),
(30, 2, 'create_error', 'SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-03 13:25:37'),
(31, 2, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-03 13:25:38'),
(32, 2, 'create_error', 'SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-03 13:25:44'),
(33, 2, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-03 13:25:44'),
(34, 2, 'create_error', 'SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-03 13:25:51'),
(35, 2, 'register_error', 'Erreur lors de la création de l\'utilisateur: SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1', '{\"email\":\"babaokechi@gmail.com\",\"error\":\"Erreur lors de la création de l\'utilisateur: SQLSTATE[22003]: Numeric value out of range: 1264 Out of range value for column \'number\' at row 1\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-03 13:25:51'),
(36, NULL, 'login_error', 'Email ou mot de passe incorrect.', '{\"email\":\"loyovat896@opposir.com\",\"error\":\"Email ou mot de passe incorrect.\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-03 20:16:31'),
(37, NULL, 'login_error', 'Email ou mot de passe incorrect.', '{\"email\":\"loyovat896@opposir.com\",\"error\":\"Email ou mot de passe incorrect.\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'error', '2025-04-03 20:16:40'),
(38, 4, 'zerthgfddvbvd', 'zerttrezefgfxcv', 'zertredfggfddf', '124.155.14.1', 'zertyfgdsvfgd', 'info', '2025-04-06 00:23:59');

-- --------------------------------------------------------

--
-- Structure de la table `challenges`
--

CREATE TABLE `challenges` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `points` varchar(155) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `description` text NOT NULL,
  `difficulty` enum('easy','medium','hard') NOT NULL,
  `hackathon_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `type`, `points`, `description`, `difficulty`, `hackathon_id`, `created_at`, `updated_at`) VALUES
(1, 'azertyghfdazertyghfdazertyghfdazertyghfdazertyghfd', 'hacking', '150', 'azertyghfdazertyghfdazertyghfdazertyghfdazertyghfd', 'medium', 2, '2025-04-03 21:52:00', '2025-04-06 08:47:53');

-- --------------------------------------------------------

--
-- Structure de la table `challenge_submissions`
--

CREATE TABLE `challenge_submissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `submission_value` varchar(255) NOT NULL,
  `status` enum('active','rejected','pending') NOT NULL DEFAULT 'pending',
  `points` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `challenge_submissions`
--

INSERT INTO `challenge_submissions` (`id`, `user_id`, `challenge_id`, `submission_value`, `status`, `points`, `created_at`, `updated_at`) VALUES
(1, 4, 1, '1', 'active', 150, '2025-04-06 00:18:16', '2025-04-06 00:18:16'),
(2, 4, 1, '', 'pending', 30, '2025-04-06 00:20:58', '2025-04-06 00:20:58'),
(3, 4, 1, '', 'pending', 30, '2025-04-06 00:21:16', '2025-04-06 00:21:16');

-- --------------------------------------------------------

--
-- Structure de la table `challenge_technologies`
--

CREATE TABLE `challenge_technologies` (
  `challenge_id` int(11) NOT NULL,
  `technology_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `challenge_technologies`
--

INSERT INTO `challenge_technologies` (`challenge_id`, `technology_id`) VALUES
(2, 5),
(2, 8),
(3, 3);

-- --------------------------------------------------------

--
-- Structure de la table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `judge_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `criteria` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `flags`
--

CREATE TABLE `flags` (
  `id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `points` int(11) DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `hackathons`
--

CREATE TABLE `hackathons` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `max_teams` int(11) NOT NULL DEFAULT 10,
  `max_team_members` int(11) NOT NULL DEFAULT 4,
  `rules` text NOT NULL,
  `prizes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `hackathons`
--

INSERT INTO `hackathons` (`id`, `name`, `description`, `start_date`, `end_date`, `location`, `max_teams`, `max_team_members`, `rules`, `prizes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'azertyghfdazertyghfdazertyghfdazertyghfdazertyghfd', 'azertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfd', '2025-04-03 23:49:43', '2025-04-03 23:49:43', 'azertyghfdazertyghfdazertyghfdazertyghfdazertyghfd', 10, 4, 'azertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfdazertyghfd', '1254', 3, '2025-04-03 21:51:24', '2025-04-03 21:51:24'),
(2, '', '', '2025-04-03 23:49:43', '2025-04-03 23:49:43', NULL, 10, 4, '', NULL, 2, '2025-04-03 21:51:24', '2025-04-03 21:51:24');

-- --------------------------------------------------------

--
-- Structure de la table `hackathon_participants`
--

CREATE TABLE `hackathon_participants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_id` int(255) NOT NULL,
  `hackathon_id` int(11) NOT NULL,
  `participation_status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `hackathon_participants`
--

INSERT INTO `hackathon_participants` (`id`, `user_id`, `team_id`, `hackathon_id`, `participation_status`, `joined_at`) VALUES
(1, 4, 0, 2, 'accepted', '2025-04-03 21:52:25');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `auteur_id` int(11) NOT NULL,
  `hackathon_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `read_status`, `created_at`) VALUES
(1, 6, 'azertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdf', 1, '2025-04-04 11:50:53'),
(2, 4, 'azertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdfazertresdf', 0, '2025-04-04 11:50:53');

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('ongoing','completed','validated','rejected') NOT NULL DEFAULT 'ongoing',
  `repository_url` varchar(255) DEFAULT NULL,
  `demo_url` varchar(255) DEFAULT NULL,
  `documentation_url` varchar(255) DEFAULT NULL,
  `team_id` int(11) NOT NULL,
  `hackathon_id` int(11) NOT NULL,
  `technologies` text DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `judges_comments` text DEFAULT NULL,
  `evaluation_criteria` text DEFAULT NULL,
  `version` varchar(50) DEFAULT '1.0',
  `rule_compliance` tinyint(1) DEFAULT 1,
  `security_issues` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `hackathon_id` int(11) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `teams`
--

INSERT INTO `teams` (`id`, `name`, `hackathon_id`, `leader_id`, `created_at`, `updated_at`) VALUES
(1, 'destroy', 1, 5, '2025-04-06 08:44:45', '2025-04-06 08:44:45');

-- --------------------------------------------------------

--
-- Structure de la table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `team_members`
--

INSERT INTO `team_members` (`id`, `user_id`, `team_id`, `joined_at`) VALUES
(1, 4, 1, '2025-04-06 08:45:21'),
(2, 5, 1, '2025-04-06 08:45:30');

-- --------------------------------------------------------

--
-- Structure de la table `technologies`
--

CREATE TABLE `technologies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `technologies`
--

INSERT INTO `technologies` (`id`, `name`) VALUES
(6, 'Angular'),
(4, 'Java'),
(2, 'JavaScript'),
(10, 'MongoDB'),
(9, 'MySQL'),
(8, 'Node.js'),
(1, 'PHP'),
(3, 'Python'),
(5, 'React'),
(7, 'Vue.js');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `school` varchar(35) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `special_comp` varchar(85) NOT NULL,
  `idea_project` text DEFAULT NULL,
  `study_level` varchar(50) NOT NULL,
  `number` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `role` enum('admin','organizer','participant','judge') NOT NULL DEFAULT 'participant',
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `school`, `email`, `password`, `special_comp`, `idea_project`, `study_level`, `number`, `role`, `profile_picture`, `bio`, `github_url`, `linkedin_url`, `created_at`, `updated_at`, `status`) VALUES
(1, 'lolo andoche', 'jean messiah', '', 'loyovat896@opposir.com', '$2y$10$HNVlAKZZk9unXeT1QwWHdOSYvh3jvjpgRx58k7U6ry6OHwZy7B8wK', '0', NULL, '', '0', 'participant', NULL, NULL, NULL, NULL, '2025-03-26 18:58:43', '2025-03-26 18:58:43', 'active'),
(2, 'Test01', 'jean messiah', 'ESGIS', 'galigom995@opposir.com', '$2y$10$Mj3KoO9C7XLg9MQzMyUwsOhsaSnswRb1KAV9pz8hrNeGB9rf1suRq', '0', NULL, '', '0', 'participant', NULL, NULL, NULL, NULL, '2025-03-30 12:56:11', '2025-03-30 12:56:11', 'active'),
(3, 'ggggg', 'ggggg', 'ESGIS', 'babaokechi@gmail.com', '$2y$10$AVLQBs9j5PPQ.O8rowaSQ.ZpUdbzKDkahfd1BiXPruLjEshJWpgWG', 'frontend', NULL, 'master2', '22925252', 'participant', NULL, NULL, NULL, NULL, '2025-04-03 13:26:25', '2025-04-03 13:26:25', 'active'),
(4, 'azerty', 'jean messiah', 'ESGIS', 'azerty@gmail.com', '$2y$10$LNF6H69p3HGREmXi8p9FJOc7.AOq/E4jy4N57yoz.VN.e7ak.xvr6', 'backend', NULL, 'master1', '228747474', 'participant', NULL, NULL, NULL, NULL, '2025-04-03 21:30:51', '2025-04-03 21:30:51', 'active'),
(5, 'azertyu', 'jean messiah', 'ESGIS', 'admin@fruits-legumes.com', '$2y$10$92FKnmscX6NOMqscDpFdK.LPUkmV9rlzOH3fuhm4P5IHssDD8PeIC', 'backend', NULL, 'licence1', '22825252525', 'participant', NULL, NULL, NULL, NULL, '2025-04-03 23:16:47', '2025-04-03 23:16:47', 'active'),
(6, 'azertyui', 'azertyuiop', 'CPPA', 'azertyui@gmail.com', '$2y$10$UCC5gfrNPVrzhEWgG2PHv.kW11I7ggGDdLMdnaDBQ96RAR7cJRgIW', 'backend', NULL, 'licence2', '+22960804972', 'participant', NULL, NULL, NULL, NULL, '2025-04-04 11:01:33', '2025-04-04 11:01:33', 'active');

-- --------------------------------------------------------

--
-- Structure de la table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(50) DEFAULT 'ongoing',
  `progress_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`progress_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `refresh_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `type` enum('authentication','password_reset','email_verification','remember_me','api') NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_tokens`
--

INSERT INTO `user_tokens` (`id`, `user_id`, `token`, `refresh_token`, `type`, `expires_at`, `created_at`, `updated_at`, `used`, `revoked`, `user_agent`, `ip_address`) VALUES
(1, 4, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ5b3VyLWRvbWFpbi5jb20iLCJpYXQiOjE3NDM3NjQzNDgsImV4cCI6MTc0NjM1NjM0OCwic3ViIjo0LCJqdGkiOiJkNTM2N2FmYzZjZTQ4ZTA4MWJmM2RiYjc3OWY5Nzc3ZiIsIm5iZiI6MTc0Mzc2NDM0N30.B7t7npdryphpkFq6WeN2fBJKlkxMMTMr9tYccDVNDtE', '29f40f38f21a6311a11f988ed84d9a00203541149fa80e9dbe1dc97c016c8166', 'authentication', '2025-05-04 11:59:08', '2025-04-04 10:59:09', '2025-04-05 15:14:32', 0, 1, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '127.0.0.1'),
(2, 5, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ5b3VyLWRvbWFpbi5jb20iLCJpYXQiOjE3NDM3NjQ0MTIsImV4cCI6MTc0NjM1NjQxMiwic3ViIjo1LCJqdGkiOiJhNDcwZTRkNmI1YzdkODQ1YjVhZjU2ZTcwMGRlM2U0MiIsIm5iZiI6MTc0Mzc2NDQxMX0.nxk_hSULTreXu0QCdenmhqN8xoC7qnsx33bBQ9XpTHc', 'e99c97c181b498e76cd9f0418a83ee7ee6537ea4b3346a7e7699e0cfec94f925', 'authentication', '2025-05-04 12:00:12', '2025-04-04 11:00:12', '2025-04-04 11:00:12', 0, 0, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '127.0.0.1'),
(3, 6, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ5b3VyLWRvbWFpbi5jb20iLCJpYXQiOjE3NDM4NjYwMDgsImV4cCI6MTc0NjQ1ODAwOCwic3ViIjo2LCJqdGkiOiJhNDgyZjQwZTExM2E1YjNiZTQ2ZjdlYzQ4NTRlNDU1ZSIsIm5iZiI6MTc0Mzg2NjAwN30.udilZz7hgkJ3Fh7LvyKi7jIkznFERSLPhYcfaAYBu3M', 'd41efca4480a5a1d0c087bcc8c813307f873982a13ceb0d1af34c5144e779209', 'authentication', '2025-05-05 16:13:28', '2025-04-05 15:13:28', '2025-04-05 15:13:28', 0, 0, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '127.0.0.1'),
(4, 4, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ5b3VyLWRvbWFpbi5jb20iLCJpYXQiOjE3NDM4NjYwNzIsImV4cCI6MTc0NjQ1ODA3Miwic3ViIjo0LCJqdGkiOiJjNDdmOWUyODcwMzZhNTM1M2VjNGNhNGY0OTJlZGYzNCIsIm5iZiI6MTc0Mzg2NjA3MX0.IfkOyNLLkt78LibnSDzGx3mHWa5mM90PIuJBFA0i_B4', '02e221927593b0e097f760a541f41910e72c86ca7633aa53f8747acc42c2db3a', 'authentication', '2025-05-05 16:14:32', '2025-04-05 15:14:32', '2025-04-05 15:14:32', 0, 0, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', '127.0.0.1');

-- --------------------------------------------------------

--
-- Structure de la table `validated_flags`
--

CREATE TABLE `validated_flags` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `flag_id` int(11) NOT NULL,
  `validated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_action_level` (`action`,`level`);

--
-- Index pour la table `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hackathon_id` (`hackathon_id`);

--
-- Index pour la table `challenge_submissions`
--
ALTER TABLE `challenge_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_challenge_user` (`challenge_id`,`user_id`),
  ADD KEY `idx_ranking` (`status`,`user_id`,`points`);

--
-- Index pour la table `challenge_technologies`
--
ALTER TABLE `challenge_technologies`
  ADD PRIMARY KEY (`challenge_id`,`technology_id`),
  ADD KEY `technology_id` (`technology_id`);

--
-- Index pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `judge_id` (`judge_id`);

--
-- Index pour la table `flags`
--
ALTER TABLE `flags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_challenge_flag_name` (`challenge_id`,`name`),
  ADD UNIQUE KEY `unique_challenge_flag_value` (`challenge_id`,`value`);

--
-- Index pour la table `hackathons`
--
ALTER TABLE `hackathons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_date_range` (`start_date`,`end_date`),
  ADD KEY `idx_location` (`location`);

--
-- Index pour la table `hackathon_participants`
--
ALTER TABLE `hackathon_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`hackathon_id`),
  ADD KEY `fk_hackpart_hackathon` (`hackathon_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur_id` (`auteur_id`),
  ADD KEY `hackathon_id` (`hackathon_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`);

--
-- Index pour la table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hackathon_id` (`hackathon_id`),
  ADD KEY `idx_status_score` (`status`,`score`),
  ADD KEY `idx_team_hackathon` (`team_id`,`hackathon_id`);

--
-- Index pour la table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_ip_created` (`ip_address`,`created_at`);

--
-- Index pour la table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hackathon_id` (`hackathon_id`),
  ADD KEY `leader_id` (`leader_id`);

--
-- Index pour la table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`team_id`),
  ADD KEY `fk_teammember_team` (`team_id`);

--
-- Index pour la table `technologies`
--
ALTER TABLE `technologies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role_status` (`role`,`status`),
  ADD KEY `idx_email_password` (`email`,`password`),
  ADD KEY `idx_school_study` (`school`,`study_level`);

--
-- Index pour la table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_challenge` (`user_id`,`challenge_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Index pour la table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type_expires` (`type`,`expires_at`),
  ADD KEY `user_id_2` (`user_id`),
  ADD KEY `idx_token_type` (`token`,`type`),
  ADD KEY `idx_created_expires` (`created_at`,`expires_at`);

--
-- Index pour la table `validated_flags`
--
ALTER TABLE `validated_flags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_flag` (`user_id`,`flag_id`),
  ADD KEY `flag_id` (`flag_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `challenge_submissions`
--
ALTER TABLE `challenge_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `flags`
--
ALTER TABLE `flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `hackathons`
--
ALTER TABLE `hackathons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `hackathon_participants`
--
ALTER TABLE `hackathon_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `technologies`
--
ALTER TABLE `technologies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `validated_flags`
--
ALTER TABLE `validated_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `challenges`
--
ALTER TABLE `challenges`
  ADD CONSTRAINT `challenges_ibfk_1` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `challenge_submissions`
--
ALTER TABLE `challenge_submissions`
  ADD CONSTRAINT `challenge_submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `challenge_submissions_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `challenge_technologies`
--
ALTER TABLE `challenge_technologies`
  ADD CONSTRAINT `challenge_technologies_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `old_challenges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `challenge_technologies_ibfk_2` FOREIGN KEY (`technology_id`) REFERENCES `technologies` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`judge_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `flags`
--
ALTER TABLE `flags`
  ADD CONSTRAINT `flags_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `hackathons`
--
ALTER TABLE `hackathons`
  ADD CONSTRAINT `hackathons_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `hackathon_participants`
--
ALTER TABLE `hackathon_participants`
  ADD CONSTRAINT `fk_hackpart_hackathon` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hackpart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `old_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`hackathon_id`) REFERENCES `old_hackathons` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `fk_security_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `security_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`leader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `fk_teammember_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_teammember_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `validated_flags`
--
ALTER TABLE `validated_flags`
  ADD CONSTRAINT `validated_flags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `validated_flags_ibfk_2` FOREIGN KEY (`flag_id`) REFERENCES `flags` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

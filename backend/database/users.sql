-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : sam. 15 mars 2025 à 22:39
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
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('participant','organisateur','jury') NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verifie` tinyint(1) DEFAULT 0,
  `deux_fa_enabled` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `email`, `mot_de_passe`, `role`, `date_inscription`, `email_verifie`, `deux_fa_enabled`, `reset_token`, `reset_token_expiry`) VALUES
(1, 'Admin Test', 'admin@test.com', '$2y$12$vex/rsTWMaIUHOZt489e5uR6ZfWwfqIyUUINJQ0PF7cD9bIkCu4QC', 'organisateur', '2025-02-23 05:09:06', 0, 0, NULL, NULL),
(2, 'Test User', 'testuser@example.com', '$2y$10$7NcCUnEnS5os9hZs5maaFekZLyyikWLVIXLfQLJwvKPUcDMtwepxG', 'participant', '2025-02-23 09:50:14', 0, 0, NULL, NULL),
(3, 'Nom de l\'utilisateur', 'email@example.com', 'votre_mot_de_passe', 'participant', '2025-02-23 13:59:59', 0, 0, NULL, NULL),
(28, 'Alice Dupont', 'alice@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL),
(29, 'Bob Martin', 'bob@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL),
(30, 'Charlie Durand', 'charlie@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL),
(31, 'David Lefevre', 'david@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL),
(32, 'Emma Morel', 'emma@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL),
(33, 'Franck Simon', 'franck@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL),
(38, 'TestUser', 'test@example.com', '$2y$12$u8w269/qwS43diXe5O0HCOdRszW/V6AgTXWyj0eCN9IdYKU23tq1u', 'participant', '2025-03-15 17:07:26', 0, 0, NULL, NULL),
(39, 'lolo', 'galigom995@opposir.com', '$2y$12$2QRU0hqRKlXwofxYHq56MuimqyWcEFii/bGvaIwD4d4c1U.4a2hN6', 'participant', '2025-03-15 17:37:51', 0, 0, NULL, NULL),
(40, 'lolo', 'floflo@gmioal.com', '$2y$12$lUD7det3EqnbGqHedXMA6uMrX4HkDDmpiZ21sXbAUYKE42TNU2/QC', 'participant', '2025-03-15 17:39:58', 0, 0, NULL, NULL),
(41, 'baba OKECHI', 'babaokechi@gmail.com', '$2y$12$TD75jYwSyEloaEE1I.UIS.XuWfQgMkvMST/yGRY3/vzzFyD3XlJHq', 'participant', '2025-03-15 17:43:07', 0, 0, NULL, NULL),
(42, 'yayano', 'darkvader807@gmail.com', '$2y$12$bLTQdKQlNEsWz1B4Gj43MuiRTw/8i7.CObNBxJHt.aSB784fLGR8q', 'participant', '2025-03-15 18:54:39', 0, 0, NULL, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_reset_token` (`reset_token`),
  ADD KEY `idx_reset_token_expiry` (`reset_token_expiry`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

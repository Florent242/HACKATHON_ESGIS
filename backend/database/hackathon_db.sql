-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 20 mars 2025 à 16:51
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
-- Structure de la table `challenges`
--

CREATE TABLE `challenges` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `deadline` date NOT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `description`, `deadline`, `max_participants`, `featured`, `created_by`, `created_at`, `status`, `updated_at`) VALUES
(2, 'Challenge React/Node.js', 'Développez une application full-stack moderne', '2024-03-15', 15, 1, 1, '2025-02-23 05:09:07', 'active', '2025-02-23 14:06:10'),
(3, 'Challenge Python/AI', 'Créez un modèle d\'IA pour résoudre un problème réel', '2024-04-01', 10, 1, 1, '2025-02-23 05:09:07', 'active', '2025-02-23 14:06:10'),
(4, 'Test Challenge', 'Description de test', '2025-03-10', 10, 1, 1, '2025-02-23 10:32:28', 'active', '2025-02-23 14:06:10'),
(5, 'Test Challenge', 'Description de test', '2025-03-10', 10, 1, 1, '2025-02-23 10:34:06', 'active', '2025-02-23 14:06:10');

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
-- Structure de la table `equipes`
--

CREATE TABLE `equipes` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `hackathon_id` int(11) NOT NULL,
  `chef_equipe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `equipes`
--

INSERT INTO `equipes` (`id`, `nom`, `hackathon_id`, `chef_equipe_id`) VALUES
(2, 'Equipe Alpha', 2, 2),
(3, 'Team Alpha', 2, 1),
(4, 'Team Alpha', 2, 1),
(5, 'Nouvelle Equipe', 2, 2),
(14, 'Equipe Alpha', 7, 33),
(21, 'Equipe Alpha', 8, 1),
(22, 'Team Beta', 8, 2),
(29, 'Equipe Test', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `projet_id` int(11) NOT NULL,
  `jury_id` int(11) NOT NULL,
  `note` int(11) DEFAULT NULL CHECK (`note` between 0 and 10),
  `commentaire` text DEFAULT NULL,
  `date_evaluation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `hackathons`
--

CREATE TABLE `hackathons` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `organisateur_id` int(11) NOT NULL,
  `statut` enum('inscription','en cours','terminé') DEFAULT 'inscription'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `hackathons`
--

INSERT INTO `hackathons` (`id`, `nom`, `description`, `date_debut`, `date_fin`, `organisateur_id`, `statut`) VALUES
(2, 'Hackathon ESGIS', 'Un hackathon dédié aux étudiants passionnés par la tech.', '2025-03-10', '2025-03-12', 1, 'inscription'),
(3, 'Hackathon 2025', 'Un hackathon de 48h sur l\'IA', '2025-04-10', '2025-04-12', 1, 'inscription'),
(4, 'Hackathon 2025', 'Un hackathon de 48h sur l\'IA', '2025-04-10', '2025-04-12', 1, 'inscription'),
(5, 'Hackathon 2025', 'Un hackathon de 48h sur l\'IA', '2025-04-10', '2025-04-12', 1, 'inscription'),
(6, 'Hackathon 2025', 'Un hackathon de 48h sur l\'IA', '2025-04-10', '2025-04-12', 1, 'inscription'),
(7, 'Hackathon 2025', 'Un hackathon de 48h sur l\'IA', '2025-04-10', '2025-04-12', 1, 'inscription'),
(8, 'Hackathon Alpha', 'Un hackathon sur la technologie.', '2025-03-10', '2025-03-12', 1, 'inscription'),
(9, 'Hackathon Test', 'Un hackathon de test.', '2025-03-10', '2025-03-12', 1, 'inscription'),
(10, 'Hackathon Test', 'Un hackathon de test.', '2025-03-10', '2025-03-12', 1, 'inscription');

-- --------------------------------------------------------

--
-- Structure de la table `membres_equipes`
--

CREATE TABLE `membres_equipes` (
  `id` int(11) NOT NULL,
  `equipe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `membres_equipes`
--

INSERT INTO `membres_equipes` (`id`, `equipe_id`, `user_id`) VALUES
(1, 2, 2);

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
  `lu` tinyint(1) DEFAULT 0,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participants_hackathon`
--

CREATE TABLE `participants_hackathon` (
  `id` int(11) NOT NULL,
  `hackathon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `equipe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `participants_hackathon`
--

INSERT INTO `participants_hackathon` (`id`, `hackathon_id`, `user_id`, `equipe_id`) VALUES
(15, 2, 3, 2),
(16, 2, 3, 2);

-- --------------------------------------------------------

--
-- Structure de la table `participations`
--

CREATE TABLE `participations` (
  `id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('active','completed','abandoned') DEFAULT 'active',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `projets`
--

CREATE TABLE `projets` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `fichier` varchar(255) DEFAULT '',
  `hackathon_id` int(11) NOT NULL,
  `equipe_id` int(11) NOT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en cours','validé','refusé') DEFAULT 'en cours'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `nom_complet` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('participant','organisateur','jury') NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verifie` tinyint(1) DEFAULT 0,
  `deux_fa_enabled` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom_complet`, `email`, `mot_de_passe`, `role`, `date_inscription`, `email_verifie`, `deux_fa_enabled`, `reset_token`, `reset_token_expiry`, `profile_picture`, `username`) VALUES
(1, 'Admin Test', 'admin@test.com', '$2y$12$vex/rsTWMaIUHOZt489e5uR6ZfWwfqIyUUINJQ0PF7cD9bIkCu4QC', 'organisateur', '2025-02-23 05:09:06', 0, 0, NULL, NULL, '', 'Administrateur'),
(2, 'Test User', 'testuser@example.com', '$2y$10$7NcCUnEnS5os9hZs5maaFekZLyyikWLVIXLfQLJwvKPUcDMtwepxG', 'participant', '2025-02-23 09:50:14', 0, 0, NULL, NULL, '', 'test'),
(3, 'Nom de l\'utilisateur', 'email@example.com', 'votre_mot_de_passe', 'participant', '2025-02-23 13:59:59', 0, 0, NULL, NULL, '', 'fer'),
(28, 'Alice Dupont', 'alice@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL, '', 'errfferfref'),
(29, 'Bob Martin', 'bob@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL, '', 'errfferfref'),
(30, 'Charlie Durand', 'charlie@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL, '', 'errfferfref'),
(31, 'David Lefevre', 'david@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL, '', 'azerrty'),
(32, 'Emma Morel', 'emma@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL, '', 'EmmaMo'),
(33, 'Franck Simon', 'franck@example.com', 'hashed_password', 'participant', '2025-02-23 12:41:03', 0, 0, NULL, NULL, '', 'vbferfef'),
(38, 'TestUser', 'test@example.com', '$2y$12$u8w269/qwS43diXe5O0HCOdRszW/V6AgTXWyj0eCN9IdYKU23tq1u', 'participant', '2025-03-15 17:07:26', 0, 0, NULL, NULL, '', 'kjferfbhjhfer'),
(39, 'lolo', 'galigom995@opposir.com', '$2y$12$2QRU0hqRKlXwofxYHq56MuimqyWcEFii/bGvaIwD4d4c1U.4a2hN6', 'participant', '2025-03-15 17:37:51', 0, 0, NULL, NULL, '', 'errfferfref'),
(40, 'lolo', 'floflo@gmioal.com', '$2y$12$lUD7det3EqnbGqHedXMA6uMrX4HkDDmpiZ21sXbAUYKE42TNU2/QC', 'participant', '2025-03-15 17:39:58', 0, 0, NULL, NULL, '', 'errfferfref'),
(41, 'baba OKECHI', 'babaokechi@gmail.com', '$2y$12$TD75jYwSyEloaEE1I.UIS.XuWfQgMkvMST/yGRY3/vzzFyD3XlJHq', 'participant', '2025-03-15 17:43:07', 0, 0, NULL, NULL, '', 'errfferfref'),
(42, 'yayano', 'darkvader807@gmail.com', '$2y$12$bLTQdKQlNEsWz1B4Gj43MuiRTw/8i7.CObNBxJHt.aSB784fLGR8q', 'participant', '2025-03-15 18:54:39', 0, 0, NULL, NULL, '', 'errfferfrefnjnd'),
(43, 'gogeta', 'gogeta@gmail.com', '$2y$12$iLbXr4BN5qWk.PDEUQLXD.IrxEF8SCrlfcMnJnA/84HdiKWGXieO.', 'participant', '2025-03-15 21:54:48', 0, 0, NULL, NULL, '', 'hzbujberf,r'),
(44, 'hy', 'hy@gmail.com', '$2y$12$31z7shxA8sTgAF9kS2q7HeY57wjCk6zCeOgRTFynUKK/UYwFA1f8G', 'participant', '2025-03-15 21:56:20', 0, 0, NULL, NULL, '', 'hbjfzifjze'),
(45, 'fd', 'jhbhbhrv@gmail.com', '$2y$12$FtXYTevKe8/CmgXJiw8WVevpcMuOY7FSgtBr/zM86Ow81sWujyDNK', 'participant', '2025-03-16 00:20:48', 0, 0, NULL, NULL, '', 'hjsncsjvfv'),
(46, 'fd', 'aaaa@gmail.com', '$2y$12$l6kuH5KSEnUaj1ghk3JreevkZK2D.DTChNb45OaTJ45PkCIXvMOAi', 'participant', '2025-03-16 02:04:34', 0, 0, NULL, NULL, '', 'hjk,sffsdx'),
(47, 'mhd', 'babaokechia@gmail.com', '$2y$12$0zlS8eALTEN/J88FLfuJxO6p3ceQwcGbEPTVgOWKRD2PDw6Cq.BVe', 'participant', '2025-03-18 16:21:15', 0, 0, NULL, NULL, '', 'rtyuio'),
(48, 'dfghj', 'ghjfrh@bhr.com', '$2y$12$we4cac3RFqHsHDoPBPpTteJWhHVFvfZA8T1xPlHsYQzG8rAkZ2a8W', 'participant', '2025-03-20 07:08:31', 0, 0, NULL, NULL, '', 'fgjghvcchjk'),
(49, 'florent test 01', 'florenttest@test.com', '$2y$12$.etbp5LQ1zmSLyMCwXM4k.QVF2oL.yXdeoVWdZ2QGNlv7uMCivma2', 'participant', '2025-03-20 09:26:16', 0, 0, NULL, NULL, '', 'ytfdfgh');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `challenge_technologies`
--
ALTER TABLE `challenge_technologies`
  ADD PRIMARY KEY (`challenge_id`,`technology_id`),
  ADD KEY `technology_id` (`technology_id`);

--
-- Index pour la table `equipes`
--
ALTER TABLE `equipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hackathon_id` (`hackathon_id`),
  ADD KEY `chef_equipe_id` (`chef_equipe_id`);

--
-- Index pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projet_id` (`projet_id`),
  ADD KEY `jury_id` (`jury_id`);

--
-- Index pour la table `hackathons`
--
ALTER TABLE `hackathons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hackathons_ibfk_1` (`organisateur_id`);

--
-- Index pour la table `membres_equipes`
--
ALTER TABLE `membres_equipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipe_id` (`equipe_id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `participants_hackathon`
--
ALTER TABLE `participants_hackathon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hackathon_id` (`hackathon_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Index pour la table `participations`
--
ALTER TABLE `participations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_participation` (`challenge_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `projets`
--
ALTER TABLE `projets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hackathon_id` (`hackathon_id`),
  ADD KEY `equipe_id` (`equipe_id`);

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
  ADD KEY `idx_reset_token` (`reset_token`),
  ADD KEY `idx_reset_token_expiry` (`reset_token_expiry`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `equipes`
--
ALTER TABLE `equipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `hackathons`
--
ALTER TABLE `hackathons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `membres_equipes`
--
ALTER TABLE `membres_equipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `participants_hackathon`
--
ALTER TABLE `participants_hackathon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `participations`
--
ALTER TABLE `participations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `projets`
--
ALTER TABLE `projets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `technologies`
--
ALTER TABLE `technologies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `challenges`
--
ALTER TABLE `challenges`
  ADD CONSTRAINT `challenges_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `challenge_technologies`
--
ALTER TABLE `challenge_technologies`
  ADD CONSTRAINT `challenge_technologies_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `challenge_technologies_ibfk_2` FOREIGN KEY (`technology_id`) REFERENCES `technologies` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `equipes`
--
ALTER TABLE `equipes`
  ADD CONSTRAINT `equipes_ibfk_1` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipes_ibfk_2` FOREIGN KEY (`chef_equipe_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`jury_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `hackathons`
--
ALTER TABLE `hackathons`
  ADD CONSTRAINT `hackathons_ibfk_1` FOREIGN KEY (`organisateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `membres_equipes`
--
ALTER TABLE `membres_equipes`
  ADD CONSTRAINT `membres_equipes_ibfk_1` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `membres_equipes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `participants_hackathon`
--
ALTER TABLE `participants_hackathon`
  ADD CONSTRAINT `participants_hackathon_ibfk_1` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_hackathon_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `participations`
--
ALTER TABLE `participations`
  ADD CONSTRAINT `participations_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `projets`
--
ALTER TABLE `projets`
  ADD CONSTRAINT `projets_ibfk_1` FOREIGN KEY (`hackathon_id`) REFERENCES `hackathons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projets_ibfk_2` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Migration pour le système de permissions granulaires
-- Création des tables: permissions, roles_permissions, user_permissions

-- Table des permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des associations rôles-permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role`, `permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des permissions spécifiques à l'utilisateur (exceptions)
CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`, `permission_id`),
  CONSTRAINT `fk_user_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insérer les permissions de base
INSERT INTO `permissions` (`name`, `slug`, `description`, `category`) VALUES
-- Permissions utilisateur
('Voir tous les utilisateurs', 'users.view.all', 'Peut voir tous les utilisateurs du système', 'Users'),
('Voir utilisateur', 'users.view', 'Peut voir un utilisateur spécifique', 'Users'),
('Ajouter utilisateur', 'users.create', 'Peut créer un nouvel utilisateur', 'Users'),
('Modifier utilisateur', 'users.update', 'Peut modifier un utilisateur existant', 'Users'),
('Supprimer utilisateur', 'users.delete', 'Peut supprimer un utilisateur', 'Users'),
('Changer rôle utilisateur', 'users.change_role', 'Peut changer le rôle d\'un utilisateur', 'Users'),

-- Permissions hackathon
('Voir tous les hackathons', 'hackathons.view.all', 'Peut voir tous les hackathons', 'Hackathons'),
('Voir hackathon', 'hackathons.view', 'Peut voir un hackathon spécifique', 'Hackathons'),
('Créer hackathon', 'hackathons.create', 'Peut créer un nouveau hackathon', 'Hackathons'),
('Modifier hackathon', 'hackathons.update', 'Peut modifier un hackathon existant', 'Hackathons'),
('Supprimer hackathon', 'hackathons.delete', 'Peut supprimer un hackathon', 'Hackathons'),
('Publier hackathon', 'hackathons.publish', 'Peut publier/dépublier un hackathon', 'Hackathons'),

-- Permissions équipe
('Voir toutes les équipes', 'teams.view.all', 'Peut voir toutes les équipes', 'Teams'),
('Voir équipe', 'teams.view', 'Peut voir une équipe spécifique', 'Teams'),
('Créer équipe', 'teams.create', 'Peut créer une nouvelle équipe', 'Teams'),
('Modifier équipe', 'teams.update', 'Peut modifier une équipe existante', 'Teams'),
('Modifier sa propre équipe', 'teams.update.own', 'Peut modifier sa propre équipe', 'Teams'),
('Supprimer équipe', 'teams.delete', 'Peut supprimer une équipe', 'Teams'),
('Rejoindre équipe', 'teams.join', 'Peut rejoindre une équipe', 'Teams'),
('Quitter équipe', 'teams.leave', 'Peut quitter une équipe', 'Teams'),

-- Permissions challenge
('Voir tous les challenges', 'challenges.view.all', 'Peut voir tous les challenges', 'Challenges'),
('Voir challenge', 'challenges.view', 'Peut voir un challenge spécifique', 'Challenges'),
('Créer challenge', 'challenges.create', 'Peut créer un nouveau challenge', 'Challenges'),
('Modifier challenge', 'challenges.update', 'Peut modifier un challenge existant', 'Challenges'),
('Supprimer challenge', 'challenges.delete', 'Peut supprimer un challenge', 'Challenges'),

-- Permissions ressources
('Voir toutes les ressources', 'resources.view.all', 'Peut voir toutes les ressources', 'Resources'),
('Voir ressource', 'resources.view', 'Peut voir une ressource spécifique', 'Resources'),
('Ajouter ressource', 'resources.create', 'Peut ajouter une nouvelle ressource', 'Resources'),
('Modifier ressource', 'resources.update', 'Peut modifier une ressource existante', 'Resources'),
('Supprimer ressource', 'resources.delete', 'Peut supprimer une ressource', 'Resources'),

-- Permissions projet
('Voir tous les projets', 'projects.view.all', 'Peut voir tous les projets soumis', 'Projects'),
('Voir projet', 'projects.view', 'Peut voir un projet spécifique', 'Projects'),
('Soumettre projet', 'projects.submit', 'Peut soumettre un projet', 'Projects'),
('Modifier projet', 'projects.update', 'Peut modifier un projet existant', 'Projects'),
('Modifier son propre projet', 'projects.update.own', 'Peut modifier son propre projet', 'Projects'),
('Supprimer projet', 'projects.delete', 'Peut supprimer un projet', 'Projects'),

-- Permissions évaluation
('Évaluer projet', 'evaluations.create', 'Peut évaluer un projet', 'Evaluations'),
('Voir toutes les évaluations', 'evaluations.view.all', 'Peut voir toutes les évaluations', 'Evaluations'),
('Voir évaluation', 'evaluations.view', 'Peut voir une évaluation spécifique', 'Evaluations'),
('Modifier évaluation', 'evaluations.update', 'Peut modifier une évaluation existante', 'Evaluations'),
('Modifier sa propre évaluation', 'evaluations.update.own', 'Peut modifier sa propre évaluation', 'Evaluations'),
('Supprimer évaluation', 'evaluations.delete', 'Peut supprimer une évaluation', 'Evaluations'),

-- Permissions administration
('Accéder au tableau de bord admin', 'admin.dashboard', 'Peut accéder au tableau de bord administrateur', 'Administration'),
('Voir logs du système', 'admin.logs', 'Peut voir les logs du système', 'Administration'),
('Exporter données', 'admin.export', 'Peut exporter des données du système', 'Administration'),
('Configuration système', 'admin.settings', 'Peut modifier les paramètres du système', 'Administration');

-- Insérer les permissions pour les rôles par défaut
-- Admin (toutes les permissions)
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'admin', id FROM `permissions`;

-- Organisateur
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'organizer', id FROM `permissions` WHERE
slug IN ('users.view.all', 'users.view', 'users.create',
'hackathons.view.all', 'hackathons.view', 'hackathons.create', 'hackathons.update', 'hackathons.publish',
'teams.view.all', 'teams.view',
'challenges.view.all', 'challenges.view', 'challenges.create', 'challenges.update',
'resources.view.all', 'resources.view', 'resources.create', 'resources.update',
'projects.view.all', 'projects.view',
'evaluations.view.all', 'evaluations.view',
'admin.dashboard');

-- Juge
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'judge', id FROM `permissions` WHERE
slug IN ('users.view',
'hackathons.view.all', 'hackathons.view',
'teams.view.all', 'teams.view',
'challenges.view.all', 'challenges.view',
'resources.view.all', 'resources.view',
'projects.view.all', 'projects.view',
'evaluations.create', 'evaluations.view.all', 'evaluations.view', 'evaluations.update.own');

-- Participant
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'participant', id FROM `permissions` WHERE
slug IN ('users.view',
'hackathons.view.all', 'hackathons.view',
'teams.view', 'teams.create', 'teams.update.own', 'teams.join', 'teams.leave',
'challenges.view.all', 'challenges.view',
'resources.view.all', 'resources.view',
'projects.view', 'projects.submit', 'projects.update.own');

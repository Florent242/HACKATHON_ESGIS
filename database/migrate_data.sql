-- Migration des données de l'ancien schéma vers le nouveau schéma
USE hackathon_db;

-- Sauvegarde des anciennes tables
RENAME TABLE
    users TO old_users,
    hackathons TO old_hackathons,
    challenges TO old_challenges,
    equipes TO old_teams,
    membres_equipe TO old_team_members,
    projets TO old_projects,
    evaluations TO old_evaluations,
    participants TO old_participants,
    notifications TO old_notifications;

-- Création des nouvelles tables
SOURCE new_schema.sql;

-- Migration des utilisateurs
INSERT INTO users (first_name, last_name, email, password, role, profile_picture, bio, created_at, updated_at)
SELECT
    IF(LOCATE(' ', full_name) > 0, SUBSTRING_INDEX(full_name, ' ', 1), full_name) AS first_name,
    IF(LOCATE(' ', full_name) > 0, SUBSTRING(full_name, LOCATE(' ', full_name) + 1), '') AS last_name,
    email,
    password,
    CASE
        WHEN role = 'organisateur' THEN 'organizer'
        WHEN role = 'juge' THEN 'judge'
        ELSE 'participant'
    END AS role,
    profile_picture,
    bio,
    created_at,
    updated_at
FROM old_users;

-- Migration des hackathons
INSERT INTO hackathons (id, name, description, start_date, end_date, max_teams, rules, created_by, created_at, updated_at)
SELECT
    id,
    title AS name,
    description,
    start_date AS start_date,
    end_date AS end_date,
    max_participants AS max_teams,
    'Les règles seront définies ultérieurement.' AS rules,
    created_by,
    created_at,
    updated_at
FROM old_hackathons;

-- Migration des challenges
INSERT INTO challenges (id, title, description, difficulty, hackathon_id, created_at, updated_at)
SELECT
    id,
    title,
    description,
    'medium' AS difficulty, -- Valeur par défaut pour la difficulté
    hackathon_id,
    created_at,
    updated_at
FROM old_challenges;

-- Migration des équipes
INSERT INTO teams (id, name, hackathon_id, leader_id, created_at, updated_at)
SELECT
    e.id,
    e.name,
    e.hackathon_id,
    e.created_by AS leader_id,
    e.created_at,
    e.updated_at
FROM old_teams e;

-- Migration des membres d'équipe
INSERT INTO team_members (user_id, team_id, joined_at)
SELECT
    user_id,
    equipe_id AS team_id,
    joined_at
FROM old_team_members;

-- Migration des projets
INSERT INTO projects (id, name, description, status, repository_url, demo_url, team_id, hackathon_id, created_at, updated_at)
SELECT
    p.id,
    p.title AS name,
    p.description,
    CASE
        WHEN p.status = 'draft' THEN 'ongoing'
        WHEN p.status = 'submitted' THEN 'completed'
        ELSE p.status
    END AS status,
    p.repository_url,
    p.demo_url,
    p.equipe_id AS team_id,
    t.hackathon_id,
    p.created_at,
    p.updated_at
FROM old_projects p
JOIN old_teams t ON p.equipe_id = t.id;

-- Migration des évaluations
INSERT INTO evaluations (project_id, judge_id, score, created_at)
SELECT
    projet_id AS project_id,
    juge_id AS judge_id,
    score,
    created_at
FROM old_evaluations;

-- Migration des notifications
INSERT INTO notifications (user_id, message, read_status, created_at)
SELECT
    user_id,
    message,
    is_read AS read_status,
    created_at
FROM old_notifications;

-- Migration des participants
INSERT INTO hackathon_participants (user_id, hackathon_id, participation_status, joined_at)
SELECT
    user_id,
    hackathon_id,
    CASE
        WHEN status = 'registered' THEN 'pending'
        WHEN status = 'confirmed' THEN 'accepted'
        WHEN status = 'cancelled' THEN 'rejected'
        ELSE 'pending'
    END AS participation_status,
    created_at AS joined_at
FROM old_participants;

-- Mise à jour des auto-incréments
-- Cette partie doit être adaptée en fonction de vos données réelles
SELECT @max_user_id := MAX(id) FROM users;
SELECT @max_hackathon_id := MAX(id) FROM hackathons;
SELECT @max_challenge_id := MAX(id) FROM challenges;
SELECT @max_team_id := MAX(id) FROM teams;
SELECT @max_project_id := MAX(id) FROM projects;
SELECT @max_evaluation_id := MAX(id) FROM evaluations;
SELECT @max_notification_id := MAX(id) FROM notifications;
SELECT @max_participant_id := MAX(id) FROM hackathon_participants;

-- Réinitialiser les séquences d'auto-incrémentation
ALTER TABLE users AUTO_INCREMENT = @max_user_id + 1;
ALTER TABLE hackathons AUTO_INCREMENT = @max_hackathon_id + 1;
ALTER TABLE challenges AUTO_INCREMENT = @max_challenge_id + 1;
ALTER TABLE teams AUTO_INCREMENT = @max_team_id + 1;
ALTER TABLE projects AUTO_INCREMENT = @max_project_id + 1;
ALTER TABLE evaluations AUTO_INCREMENT = @max_evaluation_id + 1;
ALTER TABLE notifications AUTO_INCREMENT = @max_notification_id + 1;
ALTER TABLE hackathon_participants AUTO_INCREMENT = @max_participant_id + 1;

-- Optionnel : Suppression des anciennes tables (à faire seulement après vérification que la migration s'est bien déroulée)
-- DROP TABLE old_users, old_hackathons, old_challenges, old_teams, old_team_members, old_projects, old_evaluations, old_participants, old_notifications;

<?php
/**
 * Fichier de fonctions utilitaires pour les pages du frontend/admin
 */

/**
 * Vérifie si l'utilisateur est authentifié en tant qu'administrateur
 *
 * @return bool True si l'utilisateur est administrateur, false sinon
 */
function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT role FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $role = $stmt->fetchColumn();

    return $role === 'admin' || $role === 'organisateur';
}

/**
 * Redirection si non admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /HACKATHON_ESGIS/public/auth_admin');
        exit;
    }
}

/**
 * Récupère des statistiques pour le tableau de bord
 *
 * @return array Statistiques générales
 */
function getDashboardStats() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    // Nombre total d'utilisateurs
    $usersQuery = "SELECT COUNT(*) FROM users";
    $stmt = $db->prepare($usersQuery);
    $stmt->execute();
    $usersCount = $stmt->fetchColumn();

    // Nombre d'administrateurs
    $adminsQuery = "SELECT COUNT(*) FROM users WHERE role IN ('admin', 'organisateur')";
    $stmt = $db->prepare($adminsQuery);
    $stmt->execute();
    $adminsCount = $stmt->fetchColumn();

    // Nombre de hackathons
    $hackathonsQuery = "SELECT COUNT(*) FROM hackathons";
    $stmt = $db->prepare($hackathonsQuery);
    $stmt->execute();
    $hackathonsCount = $stmt->fetchColumn();

    // Nombre de challenges
    $challengesQuery = "SELECT COUNT(*) FROM challenges";
    $stmt = $db->prepare($challengesQuery);
    $stmt->execute();
    $challengesCount = $stmt->fetchColumn();

    // Nombre d'équipes
    $teamsQuery = "SELECT COUNT(*) FROM equipes";
    $stmt = $db->prepare($teamsQuery);
    $stmt->execute();
    $teamsCount = $stmt->fetchColumn();

    // Nombre de participants
    $participantsQuery = "SELECT COUNT(*) FROM participants";
    $stmt = $db->prepare($participantsQuery);
    $stmt->execute();
    $participantsCount = $stmt->fetchColumn();

    // Nombre de soumissions
    $submissionsQuery = "SELECT COUNT(*) FROM challenge_submissions";
    $stmt = $db->prepare($submissionsQuery);
    $stmt->execute();
    $submissionsCount = $stmt->fetchColumn();

    return [
        'users' => $usersCount,
        'admins' => $adminsCount,
        'participants' => $participantsCount,
        'hackathons' => $hackathonsCount,
        'challenges' => $challengesCount,
        'teams' => $teamsCount,
        'submissions' => $submissionsCount
    ];
}

/**
 * Récupère tous les utilisateurs
 *
 * @param string $filter Filtre par rôle ou statut
 * @return array Liste des utilisateurs
 */
function getAllUsers($filter = null) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT u.*,
            (SELECT COUNT(*) FROM participants p WHERE p.user_id = u.id) as participations,
            (SELECT COUNT(*) FROM equipe_membres em WHERE em.user_id = u.id) as teams
            FROM users u";

    if ($filter) {
        if ($filter === 'admin') {
            $query .= " WHERE u.role IN ('admin', 'organisateur')";
        } elseif ($filter === 'participant') {
            $query .= " WHERE u.role = 'participant'";
        } elseif ($filter === 'active') {
            $query .= " WHERE u.status = 'active'";
        } elseif ($filter === 'inactive') {
            $query .= " WHERE u.status = 'inactive'";
        }
    }

    $query .= " ORDER BY u.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère tous les hackathons
 *
 * @param string $status Filtre par statut
 * @return array Liste des hackathons
 */
function getAllHackathons($status = null) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT h.*,
            u.username as organizer,
            (SELECT COUNT(*) FROM participants p WHERE p.hackathon_id = h.id) as participants_count,
            (SELECT COUNT(*) FROM challenges c WHERE c.hackathon_id = h.id) as challenges_count
            FROM hackathons h
            JOIN users u ON h.created_by = u.id";

    if ($status) {
        $query .= " WHERE h.status = :status";
    }

    $query .= " ORDER BY h.start_date DESC";

    $stmt = $db->prepare($query);

    if ($status) {
        $stmt->bindValue(':status', $status);
    }

    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère tous les challenges
 *
 * @param int $hackathonId Filtre par hackathon
 * @return array Liste des challenges
 */
function getAllChallenges($hackathonId = null) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT c.*,
            h.title as hackathon_title,
            (SELECT COUNT(*) FROM challenge_submissions cs WHERE cs.challenge_id = c.id) as submissions_count,
            (SELECT COUNT(*) FROM challenge_submissions cs WHERE cs.challenge_id = c.id AND cs.status = 'accepted') as solved_count
            FROM challenges c
            JOIN hackathons h ON c.hackathon_id = h.id";

    if ($hackathonId) {
        $query .= " WHERE c.hackathon_id = :hackathon_id";
    }

    $query .= " ORDER BY c.created_at DESC";

    $stmt = $db->prepare($query);

    if ($hackathonId) {
        $stmt->bindValue(':hackathon_id', $hackathonId);
    }

    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les équipes
 *
 * @param int $hackathonId Filtre par hackathon
 * @return array Liste des équipes
 */
function getAllTeams($hackathonId = null) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT e.*,
            h.title as hackathon_title,
            (SELECT COUNT(*) FROM equipe_membres em WHERE em.equipe_id = e.id) as members_count,
            (SELECT SUM(points) FROM challenge_submissions cs
             JOIN equipe_membres em ON cs.user_id = em.user_id
             WHERE em.equipe_id = e.id AND cs.status = 'accepted') as total_points
            FROM equipes e
            JOIN hackathons h ON e.hackathon_id = h.id";

    if ($hackathonId) {
        $query .= " WHERE e.hackathon_id = :hackathon_id";
    }

    $query .= " ORDER BY e.created_at DESC";

    $stmt = $db->prepare($query);

    if ($hackathonId) {
        $stmt->bindValue(':hackathon_id', $hackathonId);
    }

    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère les membres d'une équipe
 *
 * @param int $teamId ID de l'équipe
 * @return array Liste des membres
 */
function getTeamMembers($teamId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT u.*, em.role as team_role
            FROM users u
            JOIN equipe_membres em ON u.id = em.user_id
            WHERE em.equipe_id = :team_id";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':team_id', $teamId);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les soumissions
 *
 * @param int $challengeId Filtre par challenge
 * @param int $userId Filtre par utilisateur
 * @return array Liste des soumissions
 */
function getAllSubmissions($challengeId = null, $userId = null) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT cs.*,
            u.username,
            c.title as challenge_title,
            h.title as hackathon_title
            FROM challenge_submissions cs
            JOIN users u ON cs.user_id = u.id
            JOIN challenges c ON cs.challenge_id = c.id
            JOIN hackathons h ON c.hackathon_id = h.id";

    $params = [];

    if ($challengeId || $userId) {
        $query .= " WHERE";

        if ($challengeId) {
            $query .= " cs.challenge_id = :challenge_id";
            $params[':challenge_id'] = $challengeId;

            if ($userId) {
                $query .= " AND";
            }
        }

        if ($userId) {
            $query .= " cs.user_id = :user_id";
            $params[':user_id'] = $userId;
        }
    }

    $query .= " ORDER BY cs.created_at DESC";

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les ressources
 *
 * @param int $hackathonId Filtre par hackathon
 * @return array Liste des ressources
 */
function getAllResources($hackathonId = null) {
    require_once __DIR__ . '/../backend/models/Database.php';
    require_once __DIR__ . '/../backend/models/Ressource.php';

    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();
    $ressource = new \Auth\Model\Ressource($db);

    if ($hackathonId) {
        return $ressource->getByHackathon($hackathonId);
    }

    $query = "SELECT r.*,
            u.username as created_by_name,
            h.title as hackathon_title
            FROM ressources r
            JOIN users u ON r.created_by = u.id
            JOIN hackathons h ON r.hackathon_id = h.id
            ORDER BY r.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère les logs d'activité
 *
 * @param int $limit Nombre de logs à récupérer
 * @param int $userId Filtre par utilisateur
 * @return array Liste des logs
 */
function getActivityLogs($limit = 50, $userId = null) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT al.*, u.username
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id";

    if ($userId) {
        $query .= " WHERE al.user_id = :user_id";
    }

    $query .= " ORDER BY al.created_at DESC LIMIT :limit";

    $stmt = $db->prepare($query);

    if ($userId) {
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
    }

    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère les notifications pour les administrateurs
 *
 * @param int $limit Nombre de notifications à récupérer
 * @return array Liste des notifications
 */
function getAdminNotifications($limit = 5) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    // Récupération des notifications système
    $query = "SELECT 'system' as type, al.id, al.action as title, al.description as message,
            al.level as notification_type, al.created_at, al.user_id, u.username
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.level IN ('warning', 'error')

            UNION

            SELECT 'user' as type, n.id, n.title, n.message, n.type as notification_type,
            n.created_at, n.user_id, u.username
            FROM notifications n
            JOIN users u ON n.user_id = u.id
            WHERE n.admin_notification = 1

            ORDER BY created_at DESC
            LIMIT :limit";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère les statistiques d'un hackathon spécifique
 *
 * @param int $hackathonId ID du hackathon
 * @return array Statistiques du hackathon
 */
function getHackathonStats($hackathonId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    // Récupérer le hackathon
    $hackathonQuery = "SELECT * FROM hackathons WHERE id = :id";
    $stmt = $db->prepare($hackathonQuery);
    $stmt->bindValue(':id', $hackathonId);
    $stmt->execute();
    $hackathon = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$hackathon) {
        return null;
    }

    // Nombre de participants
    $participantsQuery = "SELECT COUNT(*) FROM participants WHERE hackathon_id = :id";
    $stmt = $db->prepare($participantsQuery);
    $stmt->bindValue(':id', $hackathonId);
    $stmt->execute();
    $participantsCount = $stmt->fetchColumn();

    // Nombre d'équipes
    $teamsQuery = "SELECT COUNT(*) FROM equipes WHERE hackathon_id = :id";
    $stmt = $db->prepare($teamsQuery);
    $stmt->bindValue(':id', $hackathonId);
    $stmt->execute();
    $teamsCount = $stmt->fetchColumn();

    // Nombre de challenges
    $challengesQuery = "SELECT COUNT(*) FROM challenges WHERE hackathon_id = :id";
    $stmt = $db->prepare($challengesQuery);
    $stmt->bindValue(':id', $hackathonId);
    $stmt->execute();
    $challengesCount = $stmt->fetchColumn();

    // Nombre de soumissions
    $submissionsQuery = "SELECT COUNT(*) FROM challenge_submissions cs
                      JOIN challenges c ON cs.challenge_id = c.id
                      WHERE c.hackathon_id = :id";
    $stmt = $db->prepare($submissionsQuery);
    $stmt->bindValue(':id', $hackathonId);
    $stmt->execute();
    $submissionsCount = $stmt->fetchColumn();

    // Taux de résolution des challenges
    $solvedQuery = "SELECT
                    (SELECT COUNT(*) FROM challenge_submissions cs
                     JOIN challenges c ON cs.challenge_id = c.id
                     WHERE c.hackathon_id = :id AND cs.status = 'accepted') /
                    (SELECT COUNT(*) FROM challenges WHERE hackathon_id = :id) * 100 as rate";
    $stmt = $db->prepare($solvedQuery);
    $stmt->bindValue(':id', $hackathonId);
    $stmt->execute();
    $solvedRate = $stmt->fetchColumn();

    return [
        'hackathon' => $hackathon,
        'participants_count' => $participantsCount,
        'teams_count' => $teamsCount,
        'challenges_count' => $challengesCount,
        'submissions_count' => $submissionsCount,
        'solved_rate' => $solvedRate ?? 0
    ];
}

/**
 * Récupère les statistiques d'un challenge spécifique
 *
 * @param int $challengeId ID du challenge
 * @return array Statistiques du challenge
 */
function getChallengeStats($challengeId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    // Récupérer le challenge
    $challengeQuery = "SELECT c.*, h.title as hackathon_title
                      FROM challenges c
                      JOIN hackathons h ON c.hackathon_id = h.id
                      WHERE c.id = :id";
    $stmt = $db->prepare($challengeQuery);
    $stmt->bindValue(':id', $challengeId);
    $stmt->execute();
    $challenge = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$challenge) {
        return null;
    }

    // Nombre de soumissions
    $submissionsQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE challenge_id = :id";
    $stmt = $db->prepare($submissionsQuery);
    $stmt->bindValue(':id', $challengeId);
    $stmt->execute();
    $submissionsCount = $stmt->fetchColumn();

    // Nombre de résolutions
    $solvedQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE challenge_id = :id AND status = 'accepted'";
    $stmt = $db->prepare($solvedQuery);
    $stmt->bindValue(':id', $challengeId);
    $stmt->execute();
    $solvedCount = $stmt->fetchColumn();

    // Taux de réussite
    $successRate = ($submissionsCount > 0) ? ($solvedCount / $submissionsCount) * 100 : 0;

    // Temps moyen de résolution
    $avgTimeQuery = "SELECT AVG(TIMESTAMPDIFF(MINUTE, c.created_at, cs.created_at)) as avg_time
                    FROM challenge_submissions cs
                    JOIN challenges c ON cs.challenge_id = c.id
                    WHERE cs.challenge_id = :id AND cs.status = 'accepted'";
    $stmt = $db->prepare($avgTimeQuery);
    $stmt->bindValue(':id', $challengeId);
    $stmt->execute();
    $avgTime = $stmt->fetchColumn();

    return [
        'challenge' => $challenge,
        'submissions_count' => $submissionsCount,
        'solved_count' => $solvedCount,
        'success_rate' => $successRate,
        'avg_solve_time' => $avgTime
    ];
}

/**
 * Met à jour le statut d'un utilisateur
 *
 * @param int $userId ID de l'utilisateur
 * @param string $status Nouveau statut
 * @return bool Succès ou échec
 */
function updateUserStatus($userId, $status) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "UPDATE users SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $userId);
    $stmt->bindValue(':status', $status);

    return $stmt->execute();
}

/**
 * Met à jour le statut d'un hackathon
 *
 * @param int $hackathonId ID du hackathon
 * @param string $status Nouveau statut
 * @return bool Succès ou échec
 */
function updateHackathonStatus($hackathonId, $status) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "UPDATE hackathons SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $hackathonId);
    $stmt->bindValue(':status', $status);

    return $stmt->execute();
}

/**
 * Format une date pour l'affichage
 *
 * @param string $date Date au format SQL
 * @param string $format Format de sortie
 * @return string Date formatée
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (!$date) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Protection CSRF
 *
 * @param string $token Token à vérifier
 * @return bool Validité du token
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obtenir un jeton CSRF
 *
 * @return string Jeton CSRF
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

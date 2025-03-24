<?php
/**
 * Fichier de fonctions utilitaires pour les pages du frontend/admin_test
 */

/**
 * Vérifie si l'utilisateur est authentifié en tant qu'administrateur de test
 *
 * @return bool True si l'utilisateur est administrateur de test, false sinon
 */
function isTestAdmin() {
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

    return $role === 'admin' || $role === 'test_admin';
}

/**
 * Redirection si non admin de test
 */
function requireTestAdmin() {
    if (!isTestAdmin()) {
        header('Location: /HACKATHON_ESGIS/public/auth_admin');
        exit;
    }
}

/**
 * Récupère des statistiques pour le tableau de bord test
 *
 * @return array Statistiques générales
 */
function getTestDashboardStats() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    // Statistiques simplifiées pour le tableau de bord de test
    $stats = [
        'users' => [
            'total' => 0,
            'admins' => 0,
            'active' => 0,
            'inactive' => 0
        ],
        'hackathons' => [
            'total' => 0,
            'active' => 0,
            'upcoming' => 0,
            'completed' => 0
        ],
        'challenges' => [
            'total' => 0,
            'security' => 0,
            'development' => 0
        ],
        'teams' => [
            'total' => 0
        ],
        'submissions' => [
            'total' => 0,
            'pending' => 0,
            'accepted' => 0,
            'rejected' => 0
        ]
    ];

    // Utilisateurs
    $usersQuery = "SELECT COUNT(*) FROM users";
    $stmt = $db->prepare($usersQuery);
    $stmt->execute();
    $stats['users']['total'] = $stmt->fetchColumn();

    $adminsQuery = "SELECT COUNT(*) FROM users WHERE role IN ('admin', 'organisateur')";
    $stmt = $db->prepare($adminsQuery);
    $stmt->execute();
    $stats['users']['admins'] = $stmt->fetchColumn();

    $activeUsersQuery = "SELECT COUNT(*) FROM users WHERE status = 'active'";
    $stmt = $db->prepare($activeUsersQuery);
    $stmt->execute();
    $stats['users']['active'] = $stmt->fetchColumn();

    $inactiveUsersQuery = "SELECT COUNT(*) FROM users WHERE status = 'inactive'";
    $stmt = $db->prepare($inactiveUsersQuery);
    $stmt->execute();
    $stats['users']['inactive'] = $stmt->fetchColumn();

    // Hackathons
    $hackathonsQuery = "SELECT COUNT(*) FROM hackathons";
    $stmt = $db->prepare($hackathonsQuery);
    $stmt->execute();
    $stats['hackathons']['total'] = $stmt->fetchColumn();

    $activeHackathonsQuery = "SELECT COUNT(*) FROM hackathons WHERE status = 'active'";
    $stmt = $db->prepare($activeHackathonsQuery);
    $stmt->execute();
    $stats['hackathons']['active'] = $stmt->fetchColumn();

    $upcomingHackathonsQuery = "SELECT COUNT(*) FROM hackathons WHERE start_date > NOW()";
    $stmt = $db->prepare($upcomingHackathonsQuery);
    $stmt->execute();
    $stats['hackathons']['upcoming'] = $stmt->fetchColumn();

    $completedHackathonsQuery = "SELECT COUNT(*) FROM hackathons WHERE end_date < NOW()";
    $stmt = $db->prepare($completedHackathonsQuery);
    $stmt->execute();
    $stats['hackathons']['completed'] = $stmt->fetchColumn();

    // Challenges
    $challengesQuery = "SELECT COUNT(*) FROM challenges";
    $stmt = $db->prepare($challengesQuery);
    $stmt->execute();
    $stats['challenges']['total'] = $stmt->fetchColumn();

    $securityChallengesQuery = "SELECT COUNT(*) FROM challenges WHERE type = 'security'";
    $stmt = $db->prepare($securityChallengesQuery);
    $stmt->execute();
    $stats['challenges']['security'] = $stmt->fetchColumn();

    $devChallengesQuery = "SELECT COUNT(*) FROM challenges WHERE type = 'development'";
    $stmt = $db->prepare($devChallengesQuery);
    $stmt->execute();
    $stats['challenges']['development'] = $stmt->fetchColumn();

    // Équipes
    $teamsQuery = "SELECT COUNT(*) FROM equipes";
    $stmt = $db->prepare($teamsQuery);
    $stmt->execute();
    $stats['teams']['total'] = $stmt->fetchColumn();

    // Soumissions
    $submissionsQuery = "SELECT COUNT(*) FROM challenge_submissions";
    $stmt = $db->prepare($submissionsQuery);
    $stmt->execute();
    $stats['submissions']['total'] = $stmt->fetchColumn();

    $pendingSubmissionsQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE status = 'pending'";
    $stmt = $db->prepare($pendingSubmissionsQuery);
    $stmt->execute();
    $stats['submissions']['pending'] = $stmt->fetchColumn();

    $acceptedSubmissionsQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE status = 'accepted'";
    $stmt = $db->prepare($acceptedSubmissionsQuery);
    $stmt->execute();
    $stats['submissions']['accepted'] = $stmt->fetchColumn();

    $rejectedSubmissionsQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE status = 'rejected'";
    $stmt = $db->prepare($rejectedSubmissionsQuery);
    $stmt->execute();
    $stats['submissions']['rejected'] = $stmt->fetchColumn();

    return $stats;
}

/**
 * Récupère tous les utilisateurs pour admin_test
 *
 * @return array Liste simplifiée des utilisateurs
 */
function getTestUsers() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère tous les hackathons pour admin_test
 *
 * @return array Liste simplifiée des hackathons
 */
function getTestHackathons() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT h.id, h.title, h.description, h.start_date, h.end_date, h.status,
              u.username as organizer,
              (SELECT COUNT(*) FROM participants WHERE hackathon_id = h.id) as participants_count
              FROM hackathons h
              JOIN users u ON h.created_by = u.id
              ORDER BY h.start_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère tous les challenges pour admin_test
 *
 * @return array Liste simplifiée des challenges
 */
function getTestChallenges() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT c.id, c.title, c.type, c.difficulty, c.points,
              h.title as hackathon_title,
              (SELECT COUNT(*) FROM challenge_submissions WHERE challenge_id = c.id) as attempts_count
              FROM challenges c
              JOIN hackathons h ON c.hackathon_id = h.id
              ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les équipes pour admin_test
 *
 * @return array Liste simplifiée des équipes
 */
function getTestTeams() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT e.id, e.name, e.description, e.created_at,
              h.title as hackathon_title,
              (SELECT COUNT(*) FROM equipe_membres WHERE equipe_id = e.id) as members_count
              FROM equipes e
              JOIN hackathons h ON e.hackathon_id = h.id
              ORDER BY e.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les soumissions pour admin_test
 *
 * @return array Liste simplifiée des soumissions
 */
function getTestSubmissions() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT cs.id, cs.submission_value, cs.status, cs.points, cs.created_at,
              u.username,
              c.title as challenge_title
              FROM challenge_submissions cs
              JOIN users u ON cs.user_id = u.id
              JOIN challenges c ON cs.challenge_id = c.id
              ORDER BY cs.created_at DESC LIMIT 50";
    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les ressources pour admin_test
 *
 * @return array Liste simplifiée des ressources
 */
function getTestResources() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT r.id, r.titre, r.type, r.created_at,
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
 * Récupère les logs d'activité pour admin_test
 *
 * @param int $limit Nombre de logs à récupérer
 * @return array Liste simplifiée des logs
 */
function getTestActivityLogs($limit = 30) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT al.id, al.action, al.description, al.level, al.created_at,
              u.username
              FROM activity_logs al
              LEFT JOIN users u ON al.user_id = u.id
              ORDER BY al.created_at DESC LIMIT :limit";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère les membres d'une équipe pour admin_test
 *
 * @param int $teamId ID de l'équipe
 * @return array Liste simplifiée des membres
 */
function getTestTeamMembers($teamId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT u.id, u.username, u.email, em.role as team_role
              FROM users u
              JOIN equipe_membres em ON u.id = em.user_id
              WHERE em.equipe_id = :team_id";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':team_id', $teamId, \PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Format une date pour l'affichage
 *
 * @param string $date Date au format SQL
 * @param string $format Format de sortie
 * @return string Date formatée
 */
function formatTestDate($date, $format = 'd/m/Y H:i') {
    if (!$date) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Génère un badge HTML selon un statut
 *
 * @param string $status Statut
 * @return string Code HTML du badge
 */
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge badge-green">Actif</span>',
        'inactive' => '<span class="badge badge-red">Inactif</span>',
        'pending' => '<span class="badge badge-yellow">En attente</span>',
        'accepted' => '<span class="badge badge-green">Accepté</span>',
        'rejected' => '<span class="badge badge-red">Rejeté</span>',
        'admin' => '<span class="badge badge-purple">Admin</span>',
        'organisateur' => '<span class="badge badge-blue">Organisateur</span>',
        'participant' => '<span class="badge badge-gray">Participant</span>',
        'completed' => '<span class="badge badge-blue">Terminé</span>',
        'upcoming' => '<span class="badge badge-yellow">À venir</span>',
        'in_progress' => '<span class="badge badge-green">En cours</span>',
        'canceled' => '<span class="badge badge-red">Annulé</span>',
        'easy' => '<span class="badge badge-green">Facile</span>',
        'medium' => '<span class="badge badge-yellow">Moyen</span>',
        'hard' => '<span class="badge badge-red">Difficile</span>',
        'security' => '<span class="badge badge-purple">Sécurité</span>',
        'development' => '<span class="badge badge-blue">Développement</span>',
        'document' => '<span class="badge badge-blue">Document</span>',
        'video' => '<span class="badge badge-purple">Vidéo</span>',
        'lien' => '<span class="badge badge-green">Lien</span>',
        'info' => '<span class="badge badge-blue">Info</span>',
        'warning' => '<span class="badge badge-yellow">Attention</span>',
        'error' => '<span class="badge badge-red">Erreur</span>',
        'leader' => '<span class="badge badge-purple">Leader</span>',
        'member' => '<span class="badge badge-gray">Membre</span>'
    ];

    return $badges[$status] ?? '<span class="badge badge-gray">' . ucfirst($status) . '</span>';
}

/**
 * Obtenir un jeton CSRF pour admin_test
 *
 * @return string Jeton CSRF
 */
function getTestCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un jeton CSRF pour admin_test
 *
 * @param string $token Jeton à vérifier
 * @return bool Validité du jeton
 */
function validateTestCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

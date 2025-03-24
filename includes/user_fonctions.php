<?php
/**
 * Fichier de fonctions utilitaires pour les pages du frontend/user
 */

/**
 * Récupère les informations complètes de l'utilisateur connecté
 *
 * @return array|null Les données de l'utilisateur ou null si non connecté
 */
function getUserData() {
    if (!isAuthenticated()) {
        return null;
    }

    require_once __DIR__ . '/../backend/models/Database.php';
    require_once __DIR__ . '/../backend/models/User.php';

    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();
    $user = new \Auth\Model\User($db);

    return $user->find($_SESSION['user_id']);
}

/**
 * Récupère les statistiques de l'utilisateur (flags, équipes, classement)
 *
 * @param int $userId ID de l'utilisateur
 * @return array Les statistiques de l'utilisateur
 */
function getUserStats($userId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    // Récupérer le nombre de flags obtenus
    $flagsQuery = "SELECT COUNT(*) FROM challenge_submissions
                  WHERE user_id = :user_id AND status = 'accepted'";
    $stmt = $db->prepare($flagsQuery);
    $stmt->execute([':user_id' => $userId]);
    $flagsCount = $stmt->fetchColumn();

    // Récupérer le nombre d'équipes
    $teamsQuery = "SELECT COUNT(*) FROM equipe_membres WHERE user_id = :user_id";
    $stmt = $db->prepare($teamsQuery);
    $stmt->execute([':user_id' => $userId]);
    $teamsCount = $stmt->fetchColumn();

    // Récupérer le classement
    $rankQuery = "SELECT
                    COUNT(*) + 1 as rank
                  FROM
                    (SELECT
                        user_id,
                        SUM(points) as total_points
                     FROM
                        challenge_submissions
                     WHERE
                        status = 'accepted'
                     GROUP BY
                        user_id
                     HAVING
                        SUM(points) > (SELECT COALESCE(SUM(points), 0) FROM challenge_submissions WHERE user_id = :user_id AND status = 'accepted')
                    ) as better_users";
    $stmt = $db->prepare($rankQuery);
    $stmt->execute([':user_id' => $userId]);
    $rank = $stmt->fetchColumn();

    // Vérifier si l'utilisateur est dans le top 50
    $isTop50 = ($rank <= 50);

    return [
        'flags_count' => $flagsCount,
        'teams_count' => $teamsCount,
        'rank' => $rank,
        'is_top50' => $isTop50
    ];
}

/**
 * Récupère les hackathons auxquels l'utilisateur participe
 *
 * @param int $userId ID de l'utilisateur
 * @return array Liste des hackathons
 */
function getUserHackathons($userId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    require_once __DIR__ . '/../backend/models/Participant.php';

    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();
    $participant = new \Auth\Model\Participant($db);

    return $participant->getByUser($userId);
}

/**
 * Récupère les équipes auxquelles l'utilisateur appartient
 *
 * @param int $userId ID de l'utilisateur
 * @return array Liste des équipes
 */
function getUserTeams($userId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT e.*
              FROM equipes e
              JOIN equipe_membres em ON e.id = em.equipe_id
              WHERE em.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère les défis (challenges) disponibles pour l'utilisateur
 *
 * @param int $userId ID de l'utilisateur
 * @param string $type Type de défi (security, development, etc.)
 * @return array Liste des défis
 */
function getUserChallenges($userId, $type = null) {
    require_once __DIR__ . '/../backend/models/Database.php';
    require_once __DIR__ . '/../backend/models/Challenge.php';

    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT c.*,
              (SELECT COUNT(*) FROM challenge_submissions
               WHERE challenge_id = c.id AND user_id = :user_id AND status = 'accepted') as is_solved
              FROM challenges c
              JOIN hackathons h ON c.hackathon_id = h.id
              JOIN participants p ON h.id = p.hackathon_id AND p.user_id = :user_id
              WHERE p.status = 'approved'";

    if ($type) {
        $query .= " AND c.type = :type";
    }

    $query .= " ORDER BY c.difficulty, c.created_at";

    $stmt = $db->prepare($query);
    $params = [':user_id' => $userId];

    if ($type) {
        $params[':type'] = $type;
    }

    $stmt->execute($params);

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère le tableau de classement général
 *
 * @param int $limit Nombre d'entrées à récupérer
 * @return array Classement
 */
function getLeaderboard($limit = 50) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT
                u.id, u.username,
                SUM(cs.points) as total_points,
                COUNT(DISTINCT cs.challenge_id) as solved_challenges,
                (SELECT COUNT(DISTINCT cs2.challenge_id) FROM challenge_submissions cs2
                WHERE cs2.user_id = u.id AND cs2.status = 'accepted') as solved_count
              FROM
                users u
              LEFT JOIN
                challenge_submissions cs ON u.id = cs.user_id AND cs.status = 'accepted'
              GROUP BY
                u.id, u.username
              ORDER BY
                total_points DESC, solved_count DESC
              LIMIT :limit";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Récupère les ressources disponibles pour un hackathon
 *
 * @param int $hackathonId ID du hackathon
 * @return array Liste des ressources
 */
function getHackathonResources($hackathonId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    require_once __DIR__ . '/../backend/models/Ressource.php';

    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();
    $ressource = new \Auth\Model\Ressource($db);

    return $ressource->getByHackathon($hackathonId);
}

/**
 * Récupère les informations d'un hackathon
 *
 * @param int $hackathonId ID du hackathon
 * @return array|null Informations du hackathon ou null si non trouvé
 */
function getHackathonInfo($hackathonId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT h.*,
                u.username as organizer_name,
                (SELECT COUNT(*) FROM participants WHERE hackathon_id = h.id) as participants_count
              FROM hackathons h
              JOIN users u ON h.created_by = u.id
              WHERE h.id = :id";

    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $hackathonId]);

    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

/**
 * Vérifie si un utilisateur est inscrit à un hackathon
 *
 * @param int $userId ID de l'utilisateur
 * @param int $hackathonId ID du hackathon
 * @return array|false Inscription ou false si non inscrit
 */
function isUserRegisteredToHackathon($userId, $hackathonId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT * FROM participants
              WHERE user_id = :user_id AND hackathon_id = :hackathon_id";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':user_id' => $userId,
        ':hackathon_id' => $hackathonId
    ]);

    return $stmt->fetch(\PDO::FETCH_ASSOC);
}

/**
 * Récupère les notifications d'un utilisateur
 *
 * @param int $userId ID de l'utilisateur
 * @param int $limit Nombre de notifications à récupérer
 * @return array Liste des notifications
 */
function getUserNotifications($userId, $limit = 5) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT * FROM notifications
              WHERE user_id = :user_id
              ORDER BY created_at DESC
              LIMIT :limit";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Marque une notification comme lue
 *
 * @param int $notificationId ID de la notification
 * @return bool Succès ou échec
 */
function markNotificationAsRead($notificationId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "UPDATE notifications
              SET is_read = 1
              WHERE id = :id";

    $stmt = $db->prepare($query);
    return $stmt->execute([':id' => $notificationId]);
}

/**
 * Récupère les FAQ
 *
 * @return array Liste des FAQ
 */
function getFAQs() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT * FROM faqs ORDER BY position";
    $stmt = $db->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Vérifie si l'utilisateur est authentifié
 *
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 *
 * @param string $role Rôle à vérifier
 * @return bool True si l'utilisateur a le rôle, false sinon
 */
function hasRole($role) {
    if (!isAuthenticated()) {
        return false;
    }

    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $query = "SELECT role FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $userRole = $stmt->fetchColumn();

    return $userRole === $role;
}

/**
 * Soumet un flag pour un challenge
 *
 * @param int $userId ID de l'utilisateur
 * @param int $challengeId ID du challenge
 * @param string $flag Réponse soumise
 * @return array Résultat de la soumission
 */
function submitChallengeFlag($userId, $challengeId, $flag) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    // Récupérer le challenge
    $query = "SELECT * FROM challenges WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $challengeId]);
    $challenge = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$challenge) {
        return [
            'success' => false,
            'message' => 'Challenge non trouvé'
        ];
    }

    // Vérifier si le flag est correct
    $isCorrect = ($flag === $challenge['flag']);
    $status = $isCorrect ? 'accepted' : 'rejected';
    $points = $isCorrect ? $challenge['points'] : 0;

    // Enregistrer la soumission
    $insertQuery = "INSERT INTO challenge_submissions
                    (user_id, challenge_id, submission_value, status, points, created_at)
                    VALUES (:user_id, :challenge_id, :submission, :status, :points, NOW())";

    $insertStmt = $db->prepare($insertQuery);
    $insertStmt->execute([
        ':user_id' => $userId,
        ':challenge_id' => $challengeId,
        ':submission' => $flag,
        ':status' => $status,
        ':points' => $points
    ]);

    return [
        'success' => true,
        'is_correct' => $isCorrect,
        'points' => $points,
        'message' => $isCorrect ? 'Félicitations ! Flag correct.' : 'Flag incorrect. Essayez encore.'
    ];
}

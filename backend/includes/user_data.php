<?php
header('Content-Type: application/json');

/**
 * Renvoie une réponse JSON et termine l'exécution du script.
 *
 * @param mixed $data Données à envoyer en JSON
 */
function sendJsonResponse($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Vérifie si l'utilisateur est authentifié
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Récupère les informations de l'utilisateur connecté et renvoie un JSON
 */
function getUserData() {
    if (!isAuthenticated()) {
        sendJsonResponse(['error' => 'Utilisateur non authentifié']);
    }

    require_once __DIR__ . '/../backend/models/Database.php';
    require_once __DIR__ . '/../backend/models/User.php';

    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();
    $user = new \Auth\Model\User($db);

    $userData = $user->find($_SESSION['user_id']);
    sendJsonResponse($userData);
}

/**
 * Récupère les statistiques de l'utilisateur et renvoie un JSON
 */
function getUserStats($userId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT COUNT(*) FROM challenge_submissions WHERE user_id = :user_id AND status = 'active'");
    $stmt->execute([':user_id' => $userId]);
    $flagsCount = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM equipe_membres WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $teamsCount = $stmt->fetchColumn();

    $rankQuery = "SELECT COUNT(*) + 1 as rank FROM (SELECT user_id, SUM(points) as total_points FROM challenge_submissions WHERE status = 'active' GROUP BY user_id HAVING SUM(points) > (SELECT COALESCE(SUM(points), 0) FROM challenge_submissions WHERE user_id = :user_id AND status = 'active')) as better_users";
    $stmt = $db->prepare($rankQuery);
    $stmt->execute([':user_id' => $userId]);
    $rank = $stmt->fetchColumn();

    sendJsonResponse([
        'flags_count' => $flagsCount,
        'teams_count' => $teamsCount,
        'rank' => $rank,
        'is_top50' => ($rank <= 50)
    ]);
}

/**
 * Récupère les hackathons de l'utilisateur et renvoie un JSON
 */
function getUserHackathons($userId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    require_once __DIR__ . '/../backend/models/Participant.php';

    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();
    $participant = new \Auth\Model\Participant($db);

    sendJsonResponse($participant->getByUser($userId));
}

/**
 * Récupère les équipes de l'utilisateur et renvoie un JSON
 */
function getUserTeams($userId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT e.* FROM equipes e JOIN equipe_membres em ON e.id = em.equipe_id WHERE em.user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);

    sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

/**
 * Récupère le classement général et renvoie un JSON
 */
function getLeaderboard($limit = 50) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT u.id, u.username, SUM(cs.points) as total_points FROM users u LEFT JOIN challenge_submissions cs ON u.id = cs.user_id AND cs.status = 'active' GROUP BY u.id, u.username ORDER BY total_points DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();

    sendJsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

/**
 * Soumet un flag pour un challenge et renvoie un JSON
 */
function submitChallengeFlag($userId, $challengeId, $flag) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT * FROM challenges WHERE id = :id");
    $stmt->execute([':id' => $challengeId]);
    $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$challenge) {
        sendJsonResponse(['success' => false, 'message' => 'Challenge non trouvé']);
    }

    $isCorrect = ($flag === $challenge['flag']);
    $status = $isCorrect ? 'active' : 'rejected';
    $points = $isCorrect ? $challenge['points'] : 0;

    $stmt = $db->prepare("INSERT INTO challenge_submissions (user_id, challenge_id, submission_value, status, points, created_at) VALUES (:user_id, :challenge_id, :submission, :status, :points, NOW())");
    $stmt->execute([
        ':user_id' => $userId,
        ':challenge_id' => $challengeId,
        ':submission' => $flag,
        ':status' => $status,
        ':points' => $points
    ]);

    sendJsonResponse([
        'success' => true,
        'is_correct' => $isCorrect,
        'points' => $points,
        'message' => $isCorrect ? 'Félicitations ! Flag correct.' : 'Flag incorrect. Essayez encore.'
    ]);
}

<?php

namespace Auth\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Auth\Model\Participant; // Ajoutez cette ligne
use Auth\Model\User; // Assurez-vous d'importer le modèle User
use Auth\Model\Database; // Assurez-vous d'importer le modèle Database
use Exception;
use PDO;
use PDOException;
use Auth\Model\TokenManager;

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if (!class_exists('User')) {
    require_once __DIR__ . '/../models/User.php';
}
if (!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}
if (!class_exists('Auth\Model\TokenManager')) {
    require_once __DIR__ . '/../models/TokenManager.php';
}

// Vérifier si la classe Database existe, sinon l'inclure
if (!class_exists('Auth\Model\Database')) {
    require_once __DIR__ . '/../models/Database.php';
}

class UserController extends Controller
{
    private $user;
    private $db;
    private $key = 'your-secret-key';

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->user = new User($this->db);
    }

    public function get($id)
    {
        try {
            $this->validateMethod('GET');

            $user = $this->user->find($id);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }

            unset($user['password']); // Ne pas renvoyer le mot de passe

            $this->jsonResponse([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'UserController.php ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Récupère le token JWT depuis les headers
     */
    public function getBearerToken(): ?string
    {
        // D'abord essayer le header Authorization
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        
        // Si pas dans les headers, chercher dans les cookies
        if (isset($_COOKIE['long_term_token'])) {
            return $_COOKIE['long_term_token'];
        }
        
        if (isset($_COOKIE['jwt_token'])) {
            return $_COOKIE['jwt_token'];
        }

        return null;
    }

    /**
     * Récupère le header Authorization
     */
    public function getAuthorizationHeader(): ?string
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * Valide un token JWT
     */
    public function validateToken(string $token): array
    {
        $tokenManager = new TokenManager($this->key, $this->db);
        return $tokenManager->validateToken($token);
    }

    public function update($id, $jwt)
    {
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->getUserIdFromJWT($jwt);

            // Vérifier si l'utilisateur modifie son propre profil ou est admin
            if ($currentUserId != $id && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Non autorisé'], 403);
                return;
            }

            $updatableFields = ['username', 'fullname', 'school', 'email', 'special_comp', 'idea_project', 'study_level', 'number', 'bio', 'github_url', 'linkedin_url'];
            $data = $this->filterData($_POST, $updatableFields);

            if (empty($data)) {
                $this->jsonResponse(['success' => false, 'error' => 'Aucune donnée à mettre à jour'], 400);
                return;
            }

            // Si l'email est modifié, vérifier qu'il n'existe pas déjà pour un autre utilisateur
            if (isset($data['email'])) {
                $existingUser = $this->user->findByEmail($data['email']);
                if ($existingUser && (int) $existingUser['id'] !== (int) $id) {
                    $this->jsonResponse(['success' => false, 'error' => 'Cette adresse email est déjà utilisée. Mise à jour annulée !'], 400);
                    return;
                }
            }

            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->user->update($id, $data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Profil mis à jour avec succès'
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function updatePassword($id, $jwt)
    {
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->getUserIdFromJWT($jwt);

            // Vérifier si l'utilisateur modifie son propre mot de passe
            if ($currentUserId != $id) {
                $this->jsonResponse(['success' => false, 'error' => 'Non autorisé'], 403);
                return;
            }

            $requiredFields = ['old_password', 'new_password'];
            $this->validateRequiredFields($_POST, $requiredFields);

            // Vérifier l'ancien mot de passe
            $user = $this->user->find($id);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'error' => 'Utilisateur non trouvé'], 404);
                return;
            }
            if (!password_verify($_POST['old_password'], $user['password'])) {
                $this->jsonResponse(['success' => false, 'error' => 'Ancien mot de passe incorrect'], 400);
                return;
            }

            // Hasher le nouveau mot de passe
            $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

            $this->user->update($id, [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function delete($id)
    {
        try {
            $this->validateMethod('POST');

            if (!hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            if (!$this->user->delete($id)) {
                throw new Exception('Erreur lors de la suppression de l\'utilisateur');
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Met à jour le rôle d'un utilisateur
     * @param int $id ID de l'utilisateur
     */
    public function updateRole($id)
    {
        try {
            $this->validateMethod('POST');

            // Vérifier si l'utilisateur a les droits d'administrateur
            if (!hasRole('admin')) {
                throw new Exception('Non autorisé - Réservé aux administrateurs');
            }

            if (empty($_POST['role'])) {
                throw new Exception('Le rôle est requis');
            }

            $role = $_POST['role'];
            $allowedRoles = ['participant', 'organisateur', 'jury', 'admin'];

            if (!in_array($role, $allowedRoles)) {
                throw new Exception('Rôle non valide');
            }

            $this->user->update($id, [
                'role' => $role,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Rôle mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getJuryList()
    {
        try {
            $this->validateMethod('GET');

            $jurys = $this->user->getByRole('jury');

            $this->jsonResponse([
                'success' => true,
                'data' => $jurys
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getProfile()
    {
        try {
            $this->validateMethod('GET');

            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            $user = $this->user->find($_SESSION['user_id']);
            unset($user['password']);

            $this->jsonResponse([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }


    //toutes les fonctions user_data et user...



    /**
     * Renvoie une réponse JSON et termine l'exécution du script.
     *
     * @param mixed $data Données à envoyer en JSON
     */
    public function sendJsonResponse($data)
    {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Récupère les statistiques de l'utilisateur et renvoie un JSON
     */
    public function getUserDashboardData($userId, $jwt)
    {
        header('Content-Type: application/json');

        try {
            $currentUserId = $this->getUserIdFromJWT($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }

            $data = [
                'validated_flags' => $this->countUserValidatedFlags($userId),
                'dev_challenges' => $this->countUserChallengesByType($userId, 'development'), // Adaptez le type
                'ongoing_dev_challenges' => $this->countUserOngoingChallengesByType($userId, 'development'), // Adaptez le type
                'hacking_challenges' => $this->countUserChallengesByType($userId, 'hacking'), // Adaptez le type
                'ongoing_hacking_challenges' => $this->countUserOngoingChallengesByType($userId, 'hacking'), // Adaptez le type
                'submitted_projects' => $this->countUserSubmittedProjects($userId),
                'total_points' => $this->getUserTotalPoints($userId),
                'points_change' => $this->calculatePointsChange($userId),
                'recent_activity' => $this->getUserRecentActivity($userId),
            ];

            $this->jsonResponse(['success' => true, 'data' => $data]);

        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    public function getUserStats($userId)
{
    header('Content-Type: application/json');
    
    try {
        $database = Database::getInstance();
        $db = $database->getConnection();

        // Vérifier que l'utilisateur existe
        $userCheck = $db->prepare("SELECT id FROM users WHERE id = ?");
        $userCheck->execute([$userId]);
        if ($userCheck->rowCount() === 0) {
            throw new Exception("Utilisateur non trouvé");
        }

        // Structure de réponse complète
        $response = [
            'success' => true,
            'data' => [
                'stats' => [
                    'number-dev-challenges' => 0,
                    'number-dev-challenges-on' => 0,
                    'number-hacking-challenges' => 0,
                    'number-hacking-challenges-validate' => 0,
                    'number-submitted-projects' => 0,
                    'total-points' => 0,
                    'total-points-stat' => 0,
                    'hacking-stat' => 0
                ],
                'notifications' => [
                    'list' => [],
                    'unread_count' => 0
                ]
            ]
        ];

        // 1. Récupérer les statistiques
        // Défis de développement
        $devQuery = $db->prepare("
            SELECT 
                COUNT(*) as total,
                COALESCE(SUM(status = 'ongoing'), 0) as in_progress
            FROM projects 
            WHERE team_id IN (
                SELECT team_id FROM team_members WHERE user_id = ?
            )
        ");
        $devQuery->execute([$userId]);
        $devData = $devQuery->fetch(PDO::FETCH_ASSOC);
        
        $response['data']['stats']['number-dev-challenges'] = (int)$devData['total'];
        $response['data']['stats']['number-dev-challenges-on'] = (int)$devData['in_progress'];

        // Défis de hacking
        $hackingTotalQuery = $db->prepare("
            SELECT COUNT(*) as total 
            FROM challenges
            WHERE hackathon_id IN (
                SELECT hackathon_id FROM hackathon_participants WHERE user_id = ?
            )
        ");
        $hackingTotalQuery->execute([$userId]);
        $hackingTotal = $hackingTotalQuery->fetch(PDO::FETCH_ASSOC);
        $response['data']['stats']['number-hacking-challenges'] = (int)$hackingTotal['total'];

        // Défis validés
        $hackingValidQuery = $db->prepare("
            SELECT COALESCE(COUNT(*), 0) as validated
            FROM challenge_submissions 
            WHERE user_id = ? AND status = 'validated'
        ");
        $hackingValidQuery->execute([$userId]);
        $hackingValid = $hackingValidQuery->fetch(PDO::FETCH_ASSOC);
        $response['data']['stats']['number-hacking-challenges-validate'] = (int)$hackingValid['validated'];

        // Pourcentage de réussite hacking
        if ($response['data']['stats']['number-hacking-challenges'] > 0) {
            $response['data']['stats']['hacking-stat'] = round(
                ($response['data']['stats']['number-hacking-challenges-validate'] / 
                 $response['data']['stats']['number-hacking-challenges']) * 100
            );
        }

        // Projets soumis
        $projectsQuery = $db->prepare("
            SELECT COUNT(*) as submitted
            FROM projects 
            WHERE status = 'completed' 
            AND team_id IN (
                SELECT team_id FROM team_members WHERE user_id = ?
            )
        ");
        $projectsQuery->execute([$userId]);
        $projectsData = $projectsQuery->fetch(PDO::FETCH_ASSOC);
        $response['data']['stats']['number-submitted-projects'] = (int)$projectsData['submitted'];

        // Points totaux
        $pointsQuery = $db->prepare("
            SELECT COALESCE(SUM(points), 0) as total
            FROM challenge_submissions 
            WHERE user_id = ? AND status = 'validated'
        ");
        $pointsQuery->execute([$userId]);
        $pointsData = $pointsQuery->fetch(PDO::FETCH_ASSOC);
        $response['data']['stats']['total-points'] = (int)$pointsData['total'];
        
        // Pourcentage de progression
        $response['data']['stats']['total-points-stat'] = min(100, 
            round(($response['data']['stats']['total-points'] / 1000) * 100));

        // 2. Récupérer les notifications
        $notificationsQuery = $db->prepare("
                SELECT
                    id,
                    message,
                    read_status as isRead,
                    created_at as createdAt
                FROM notifications
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 5
            ");
            $notificationsQuery->execute([$userId]);
            $notifications = $notificationsQuery->fetchAll(PDO::FETCH_ASSOC);

            $response['data']['notifications']['list'] = $notifications;

            // 3. Compter les notifications non lues
            $unreadQuery = $db->prepare("
                SELECT COUNT(*) 
                FROM notifications
                WHERE user_id = ? AND read_status = 0
            ");
            $unreadQuery->execute([$userId]);
            $unreadCount = $unreadQuery->fetchColumn();

            $response['data']['notifications']['unread_count'] = (int)$unreadCount;

            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
}
    /**
     * Récupère les hackathons de l'utilisateur et renvoie un JSON
     */
    public function getUserHackathons($userId, $jwt)
    {
        $currentUserId = $this->getUserIdFromJWT($jwt);
        if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
            $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
            return;
        }
        
        $database = Database::getInstance();
        $db = $database->getConnection();
        $participant = new Participant($db);

        $hackathons = $participant->getByUser($userId);
        
        $this->jsonResponse([
            'success' => true, 
            'data' => $hackathons
        ]);
    }
    public function getOngoingChallenges($userId, $jwt)
    {
        header('Content-Type: application/json');

        try {        
            $currentUserId = $this->getUserIdFromJWT($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }
            $database = Database::getInstance();
            $db = $database->getConnection();
    
            $stmt = $db->prepare("
                SELECT c.* 
                FROM challenges c
                JOIN challenge_submissions cs ON c.id = cs.challenge_id
                WHERE cs.user_id = :userId AND cs.status = 'pending'
            ");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $challenges
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Récupère l'activité récente de l'utilisateur
 * @param int $userId ID de l'utilisateur
 */


    /**
     * Récupère les défis en cours d'un utilisateur
     * @param int $userId ID de l'utilisateur
     */
    public function getCurrentChallenges($userId, $jwt)
    {
        $currentUserId = $this->getUserIdFromJWT($jwt);
        if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
            $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
            return;
        }
        $challenges = $this->user->getCurrentChallenges($userId);
        $this->jsonResponse(['success' => true, 'data' => $challenges]);
    }
    
    public function getRecentActivities($userId, $jwt)
    {
        $currentUserId = $this->getUserIdFromJWT($jwt);
        if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
            $this->jsonResponse(['success' => false, 'error' => 'Non autorisé'], 403);
            return;
        }
        $activities = $this->user->getRecentActivities($userId);
        $this->jsonResponse(['success' => true, 'data' => $activities]);
    }
    /**
     * Récupère les équipes de l'utilisateur et renvoie un JSON
     */
    public function getUserTeams($userId)
    {
        $db = $this->db;

        $stmt = $db->prepare("SELECT e.* FROM equipes e JOIN equipe_membres em ON e.id = em.equipe_id WHERE em.user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    /**
     * Récupère le classement général et renvoie un JSON
     */
    public function getLeaderboard($limit = 50)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $stmt = $db->prepare("SELECT u.id, u.username, SUM(cs.points) as total_points FROM users u LEFT JOIN challenge_submissions cs ON u.id = cs.user_id AND cs.status = 'active' GROUP BY u.id, u.username ORDER BY total_points DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    /**
     * Soumet un flag pour un challenge et renvoie un JSON
     */
    public function submitChallengeFlag($userId, $challengeId, $flag)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $stmt = $db->prepare("SELECT * FROM challenges WHERE id = :id");
        $stmt->execute([':id' => $challengeId]);
        $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$challenge) {
            return json_encode(['success' => false, 'message' => 'Challenge non trouvé']);
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

        echo json_encode([
            'success' => true,
            'is_correct' => $isCorrect,
            'points' => $points,
            'message' => $isCorrect ? 'Félicitations ! Flag correct.' : 'Flag incorrect. Essayez encore.'
        ]);
        exit;
    }
/****
 * 
 * 
 * 
 * 
 */
private function countUserValidatedFlags($userId) {
    $query = "SELECT COUNT(*) FROM validated_flags WHERE user_id = :user_id";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

private function countUserChallengesByType($userId, $type) {
    $query = "SELECT COUNT(*) FROM challenges c
              INNER JOIN challenge_submissions uc ON c.id = uc.challenge_id
              WHERE uc.user_id = :user_id AND c.type = :type";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':type', $type);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

private function countUserOngoingChallengesByType($userId, $type)
{
    // Adapter la requête en fonction de la façon dont vous suivez les défis en cours
    $query = "SELECT COUNT(*) FROM challenges c
              INNER JOIN user_progress up ON c.id = up.challenge_id
              WHERE up.user_id = :user_id AND c.type = :type AND up.status = 'ongoing'";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

private function countUserSubmittedProjects($userId) {
    $query = "SELECT COUNT(*) FROM projects WHERE user_id = :user_id";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}


private function getUserTotalPoints($userId)
{
    $query = "SELECT COALESCE(SUM(points), 0) FROM submissions WHERE user_id = :user_id AND status = 'validated'";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

private function calculatePointsChange($userId)
    {
        $query = "SELECT COALESCE(SUM(s.points), 0)
                  FROM submissions s
                  INNER JOIN users u ON s.user_id = u.id
                  WHERE s.user_id = :user_id AND s.created_at > u.last_login";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    private function getUserRecentActivity($userId, $limit = 5)
    {
        $query = "SELECT cs.submission_value, c.title AS challenge_title, cs.created_at 
        FROM challenge_submissions cs
        INNER JOIN challenges c ON cs.challenge_id = c.id
        WHERE cs.user_id = :user_id
        ORDER BY cs.created_at DESC
        LIMIT 5"; 
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /** */

    public function getUserIdFromJWT($jwt)
    {
        if (!$jwt) {
            return null;
        }
        $algorithm = 'HS256';
        // Vérifiez si le JWT est valide
        $decoded = JWT::decode($jwt, new Key($this->key, $algorithm));
        return $decoded->sub; // Supposons que l'ID de l'utilisateur est stocké dans le champ 'sub'
    }
    /**
     * Récupère les données du leaderboard et les renvoie au format JSON
     * @return string Données du leaderboard au format JSON
     */
    public function getLeaderboardJSON()
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        try {
            // Requête pour récupérer les utilisateurs classés par points
            $stmt = $db->prepare("
                SELECT 
                    u.id, 
                    u.username, 
                    u.fullname, 
                    u.school, 
                    u.profile_picture,
                    u.bio,
                    u.email,
                    (
                        SELECT COUNT(*) 
                        FROM team_members tm 
                        WHERE tm.user_id = u.id
                    ) as teams_count,
                    (
                        SELECT COUNT(*) 
                        FROM activity_logs al 
                        WHERE al.user_id = u.id AND al.action = 'login_success'
                    ) as login_count
                FROM users u
                ORDER BY login_count DESC, teams_count DESC
                LIMIT 50
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ajouter le rang à chaque utilisateur
            $rank = 1;
            foreach ($users as &$user) {
                $user['rank'] = $rank++;

                // Récupérer les activités récentes de l'utilisateur
                $activityStmt = $db->prepare("
                    SELECT action, description, level, created_at
                    FROM activity_logs
                    WHERE user_id = :userId
                    ORDER BY created_at DESC
                    LIMIT 5
                ");
                $activityStmt->bindParam(':userId', $user['id'], PDO::PARAM_INT);
                $activityStmt->execute();
                $user['recent_activities'] = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode([
                "status" => "success",
                "data" => $users
            ]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Récupère les données du profil d'un utilisateur et les renvoie au format JSON
     * @return string Données du profil au format JSON
     */
    public function getProfileJSON()
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $jwt = $_COOKIE['jwt'];
        $userId = $this->getUserIdFromJWT($jwt);
        try {
            // Récupérer les informations de base de l'utilisateur
            $stmt = $db->prepare("
                SELECT 
                    id, 
                    username, 
                    fullname, 
                    email, 
                    school, 
                    bio, 
                    profile_picture, 
                    github_url, 
                    linkedin_url, 
                    role,
                    created_at,
                    updated_at,
                    status
                FROM users 
                WHERE id = :userId
            ");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return json_encode(["status" => "error", "message" => "User not found"]);
            }

            // Récupérer les équipes de l'utilisateur
            $teamsStmt = $db->prepare("
                SELECT 
                    t.id, 
                    t.name, 
                    t.created_at,
                    h.name as hackathon_name,
                    h.id as hackathon_id,
                    (t.leader_id = :userId) as is_leader
                FROM teams t
                JOIN team_members tm ON t.id = tm.team_id
                JOIN hackathons h ON t.hackathon_id = h.id
                WHERE tm.user_id = :userId
            ");
            $teamsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $teamsStmt->execute();
            $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupérer les hackathons auxquels l'utilisateur participe
            $hackathonsStmt = $db->prepare("
                SELECT 
                    h.id, 
                    h.name, 
                    h.description, 
                    h.start_date, 
                    h.end_date, 
                    h.location,
                    hp.participation_status
                FROM hackathons h
                JOIN hackathon_participants hp ON h.id = hp.hackathon_id
                WHERE hp.user_id = :userId
            ");
            $hackathonsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $hackathonsStmt->execute();
            $hackathons = $hackathonsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupérer les activités récentes de l'utilisateur
            $activityStmt = $db->prepare("
                SELECT 
                    id,
                    action, 
                    description, 
                    data, 
                    level, 
                    created_at
                FROM activity_logs
                WHERE user_id = :userId
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $activityStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $activityStmt->execute();
            $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

            // Traiter les données JSON dans la colonne 'data'
            foreach ($activities as &$activity) {
                if (!empty($activity['data'])) {
                    $activity['data'] = json_decode($activity['data'], true);
                }
            }

            // Récupérer les notifications de l'utilisateur
            $notificationsStmt = $db->prepare("
                SELECT 
                    id, 
                    message, 
                    read_status, 
                    created_at
                FROM notifications
                WHERE user_id = :userId
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $notificationsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $notificationsStmt->execute();
            $notifications = $notificationsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculer les statistiques de l'utilisateur
            $statsStmt = $db->prepare("
                SELECT 
                    COUNT(DISTINCT hp.hackathon_id) as hackathons_count,
                    COUNT(DISTINCT tm.team_id) as teams_count,
                    COUNT(DISTINCT al.id) as activities_count
                FROM users u
                LEFT JOIN hackathon_participants hp ON u.id = hp.user_id
                LEFT JOIN team_members tm ON u.id = tm.user_id
                LEFT JOIN activity_logs al ON u.id = al.user_id
                WHERE u.id = :userId
            ");
            $statsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $statsStmt->execute();
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

            // Ajouter des statistiques supplémentaires
            $stats['login_count'] = $db->query("SELECT COUNT(*) FROM activity_logs WHERE user_id = $userId AND action = 'login_success'")->fetchColumn();
            $stats['last_login'] = $db->query("SELECT created_at FROM activity_logs WHERE user_id = $userId AND action = 'login_success' ORDER BY created_at DESC LIMIT 1")->fetchColumn();

            return json_encode([
                "status" => "success",
                "data" => [
                    "user" => $user,
                    "teams" => $teams,
                    "hackathons" => $hackathons,
                    "activities" => $activities,
                    "notifications" => $notifications,
                    "stats" => $stats
                ]
            ]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
            exit;
        }
    }
    /**
     * Récupère les données des défis et les renvoie au format JSON
     * @return string Données des défis au format JSON
     */
    public function getChallengesJSON()
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $jwt = $_COOKIE['jwt'];
        $userId = $this->getUserIdFromJWT($jwt);
        try {
            // Récupérer tous les défis
            $stmt = $db->prepare("
                SELECT 
                    c.id, 
                    c.title, 
                    c.description, 
                    c.difficulty, 
                    c.hackathon_id,
                    c.created_at,
                    c.updated_at,
                    h.name as hackathon_name
                FROM challenges c
                JOIN hackathons h ON c.hackathon_id = h.id
                ORDER BY c.created_at DESC
            ");
            $stmt->execute();
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Pour chaque défi, récupérer les technologies associées
            foreach ($challenges as &$challenge) {
                $techStmt = $db->prepare("
                    SELECT 
                        t.id, 
                        t.name
                    FROM technologies t
                    JOIN challenge_technologies ct ON t.id = ct.technology_id
                    WHERE ct.challenge_id = :challengeId
                ");
                $techStmt->bindParam(':challengeId', $challenge['id'], PDO::PARAM_INT);
                $techStmt->execute();
                $challenge['technologies'] = $techStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Récupérer les meilleurs utilisateurs
            $topUsersStmt = $db->prepare("
                SELECT 
                    u.id, 
                    u.username, 
                    u.fullname,
                    u.profile_picture,
                    COUNT(DISTINCT al.id) as activity_count
                FROM users u
                LEFT JOIN activity_logs al ON u.id = al.user_id
                GROUP BY u.id
                ORDER BY activity_count DESC
                LIMIT 10
            ");
            $topUsersStmt->execute();
            $topUsers = $topUsersStmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupérer toutes les technologies disponibles
            $technologiesStmt = $db->prepare("
                SELECT id, name
                FROM technologies
                ORDER BY name
            ");
            $technologiesStmt->execute();
            $technologies = $technologiesStmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                "status" => "success",
                "data" => [
                    "challenges" => $challenges,
                    "top_users" => $topUsers,
                    "technologies" => $technologies,
                    "filters" => [
                        "difficulties" => ["easy", "medium", "hard"],
                        "technologies" => array_column($technologies, 'name')
                    ]
                ]
            ]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Fonction utilitaire pour convertir un objet JSON en tableau associatif
     * Utile pour traiter les données JSON stockées dans la base de données
     * @param string $json Chaîne JSON à convertir
     * @return array Tableau associatif
     */
    public function jsonToRecord($json)
    {
        if (empty($json)) {
            return [];
        }

        try {
            $data = json_decode($json, true);
            return is_array($data) ? $data : [];
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }
    }

    /**
     * Fonction utilitaire pour renvoyer des données au format JSON avec les en-têtes appropriés
     * @param string $jsonData Données au format JSON
     */
    public function outputJSON($jsonData)
    {
        // Définir les en-têtes pour le JSON et CORS
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        // Afficher les données JSON
        echo $jsonData;
        exit;
    }

    /**
     * Fonction pour récupérer toutes les données nécessaires pour les trois pages
     * et les renvoyer dans un seul objet JSON
     * @return string Toutes les données au format JSON
     */
    public function getAllDataJSON()
    {
        $jwt = $_COOKIE['jwt'];
        $userId = $this->getUserIdFromJWT($jwt);
        try {
            $data = [
                "status" => "success",
                "timestamp" => date('Y-m-d H:i:s'),
                "data" => []
            ];

            // Récupérer les données du leaderboard
            $leaderboardData = json_decode($this->getLeaderboardJSON(), true);
            if (isset($leaderboardData['status']) && $leaderboardData['status'] === 'success') {
                $data['data']['leaderboard'] = $leaderboardData['data'];
            } else {
                $data['data']['leaderboard'] = ["error" => "Failed to retrieve leaderboard data"];
            }

            // Récupérer les données des défis
            $challengesData = json_decode($this->getChallengesJSON(), true);
            if (isset($challengesData['status']) && $challengesData['status'] === 'success') {
                $data['data']['challenges'] = $challengesData['data'];
            } else {
                $data['data']['challenges'] = ["error" => "Failed to retrieve challenges data"];
            }

            // Récupérer les données du profil si un ID utilisateur est fourni
            if ($userId) {
                $profileData = json_decode($this->getProfileJSON(), true);
                if (isset($profileData['status']) && $profileData['status'] === 'success') {
                    $data['data']['profile'] = $profileData['data'];
                } else {
                    $data['data']['profile'] = ["error" => "Failed to retrieve profile data"];
                }
            }

            echo json_encode($data);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Error retrieving data: " . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Exemple d'utilisation:
     * 
     * // Pour récupérer et afficher le leaderboard
     * $leaderboardData = getLeaderboardJSON();
     * outputJSON($leaderboardData);
     * 
     * // Pour récupérer et afficher le profil d'un utilisateur
     * $profileData = getProfileJSON(1);
     * outputJSON($profileData);
     * 
     * // Pour récupérer et afficher les défis
     * $challengesData = getChallengesJSON();
     * outputJSON($challengesData);
     * 
     * // Pour récupérer toutes les données en une seule fois
     * $allData = getAllDataJSON(1); // Avec ID utilisateur
     * outputJSON($allData);
     */




    // admin 
    public function getJwt()
    {
        return $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    }

    /**
     * Vérifie si l'utilisateur est authentifié en tant qu'administrateur
     *
     * @return bool True si l'utilisateur est administrateur, false sinon
     */
    public function isAdmin($userId)
    {
        if (!isset($userId)) {
            return false;
        }

        $database = Database::getInstance();
        $db = $database->getConnection();

        $query = "SELECT role FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $userId]);
        $role = $stmt->fetchColumn();

        return $role === 'admin' || $role === 'organisateur';
    }

    /**
     * Redirection si non admin
     */
    public function requireAdmin()
    {
        if (!$this->isAdmin($_SESSION['user_id'])) {
            header('Location: /HACKATHON_ESGIS/public/auth_admin');
            exit;
        }
    }

    /**
     * Récupère des statistiques pour le tableau de bord
     *
     * @return array Statistiques générales
     */
    public function getDashboardStats()
    {
        $database = Database::getInstance();
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

        $data = [
            'users' => $usersCount,
            'admins' => $adminsCount,
            'participants' => $participantsCount,
            'hackathons' => $hackathonsCount,
            'challenges' => $challengesCount,
            'teams' => $teamsCount,
            'submissions' => $submissionsCount
        ];

        echo json_encode($data);
        exit;
    }

    /**
     * Récupère tous les utilisateurs
     *
     * @param string $filter Filtre par rôle ou statut
     * @return array Liste des utilisateurs
     */
    public function getAllUsers($filter = null)
    {
        $database = Database::getInstance();
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

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result) !== false ? $result : [];
        exit;
    }

    /**
     * Récupère tous les hackathons
     *
     * @param string $status Filtre par statut
     * @return array Liste des hackathons
     */
    public function getAllHackathons($status = null)
    {
        $database = Database::getInstance();
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

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result) !== false ? $result : [];
        exit;
    }
    public function getNotifications($userId, $jwt)
    {
        header('Content-Type: application/json');

        try {
            $currentUserId = $this->getUserIdFromJWT($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }

            $database = Database::getInstance();
            $db = $database->getConnection();

            // Récupérer les notifications de l'utilisateur
            $stmt = $db->prepare("
                SELECT
                    id,
                    message,
                    read_status,
                    created_at
                FROM notifications
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT 10 -- Limiter le nombre de notifications récupérées
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $notificationsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compter le nombre de notifications non lues
            $unreadStmt = $db->prepare("
                SELECT COUNT(*)
                FROM notifications
                WHERE user_id = :user_id AND read_status = 0
            ");
            $unreadStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $unreadStmt->execute();
            $unreadCount = (int) $unreadStmt->fetchColumn();

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'list' => $notificationsList,
                    'unread_count' => $unreadCount
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère tous les challenges
     *
     * @param int $hackathonId Filtre par hackathon
     * @return array Liste des challenges
     */
    public function getAllChallenges($hackathonId = null)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $query = "SELECT c.*,
                h.name as hackathon_title,
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

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result) !== false ? $result : [];
        exit;
    }

    /**
     * Récupère toutes les équipes
     *
     * @param int $hackathonId Filtre par hackathon
     * @return array Liste des équipes
     */
    public function getAllTeams($hackathonId = null)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $query = "SELECT e.*,
                h.name as hackathon_title,
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

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result) !== false ? $result : [];
        exit;
    }

    /**
     * Récupère les membres d'une équipe
     *
     * @param int $teamId ID de l'équipe
     * @return array Liste des membres
     */
    public function getTeamMembers($teamId)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $query = "SELECT u.*, em.role as team_role
                FROM users u
                JOIN equipe_membres em ON u.id = em.user_id
                WHERE em.equipe_id = :team_id";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':team_id', $teamId);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result) !== false ? $result : [];
        exit;
    }

    /**
     * Récupère toutes les soumissions
     *
     * @param int $challengeId Filtre par challenge
     * @param int $userId Filtre par utilisateur
     * @return array Liste des soumissions
     */
    public function getAllSubmissions($challengeId = null, $userId = null)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $query = "SELECT cs.*,
                u.username,
                c.title as challenge_title,
                h.name as hackathon_title
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

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result) !== false ? $result : [];
        exit;
    }

    /**
     * Récupère toutes les ressources
     *
     * @param int $hackathonId Filtre par hackathon
     * @return array Liste des ressources
     */
    public function getAllResources($hackathonId = null)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();
        $ressource = new \Auth\Model\Ressource($db);

        if ($hackathonId) {
            return $ressource->getByHackathon($hackathonId);
        }

        $query = "SELECT r.*,
                u.username as created_by_name,
                h.name as hackathon_title
                FROM ressources r
                JOIN users u ON r.created_by = u.id
                JOIN hackathons h ON r.hackathon_id = h.id
                ORDER BY r.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result) !== false ? $result : [];
        exit;
    }
    public function getTopHackers()
    {
        try {
            $query = "SELECT ranking, username, points FROM top_hackers ORDER BY points DESC LIMIT 10";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $hackers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $hackers]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la récupération du classement des hackers: ' . $e->getMessage()]);
        }
    }

    /**
     * Récupère les logs d'activité
     *
     * @param int $limit Nombre de logs à récupérer
     * @param int $userId Filtre par utilisateur
     * @return array Liste des logs
     */
    public function getActivityLogs($limit = 50, $userId = null)
    {
        $database = Database::getInstance();
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
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result) !== false ? $result : [];
        exit;
    }

    /**
     * Récupère les notifications pour les administrateurs
     *
     * @param int $limit Nombre de notifications à récupérer
     * @return array Liste des notifications
     */
    public function getAdminNotifications($limit = 5)
    {
        $database = Database::getInstance();
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
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result) !== false ? $result : [];
        exit;
    }
// Dans votre contrôleur
public function getHackers()
{
    header('Content-Type: application/json');
    
    try {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $stmt = $db->prepare("
            SELECT id, username, email, role, created_at 
            FROM users 
            WHERE role = 'hacker' OR role = 'participant'
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        
        $hackers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $hackers,
            'count' => count($hackers),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}
    /**
     * Récupère les statistiques d'un hackathon spécifique
     *
     * @param int $hackathonId ID du hackathon
     * @return array Statistiques du hackathon
     */
    public function getHackathonStats($hackathonId)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        // Récupérer le hackathon
        $hackathonQuery = "SELECT * FROM hackathons WHERE id = :id";
        $stmt = $db->prepare($hackathonQuery);
        $stmt->bindValue(':id', $hackathonId);
        $stmt->execute();
        $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hackathon) {
            return [];
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

        $data = [
            'hackathon' => $hackathon,
            'participants_count' => $participantsCount,
            'teams_count' => $teamsCount,
            'challenges_count' => $challengesCount,
            'submissions_count' => $submissionsCount,
            'solved_rate' => $solvedRate ?? 0
        ];
        echo json_encode($data);
        exit;
    }

    /**
     * Récupère les statistiques d'un challenge spécifique
     *
     * @param int $challengeId ID du challenge
     * @return array Statistiques du challenge
     */
    public function getChallengeStats($challengeId)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        // Récupérer le challenge
        $challengeQuery = "SELECT c.*, h.name as hackathon_title
                          FROM challenges c
                          JOIN hackathons h ON c.hackathon_id = h.id
                          WHERE c.id = :id";
        $stmt = $db->prepare($challengeQuery);
        $stmt->bindValue(':id', $challengeId);
        $stmt->execute();
        $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$challenge) {
            return [];
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

        $data = [
            'challenge' => $challenge,
            'submissions_count' => $submissionsCount,
            'solved_count' => $solvedCount,
            'success_rate' => $successRate,
            'avg_solve_time' => $avgTime
        ];
        echo json_encode($data);
        exit;
    }

    /**
     * Met à jour le statut d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param string $status Nouveau statut
     * @return bool Succès ou échec
     */
    public function updateUserStatus($userId, $status)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $query = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $userId);
        $stmt->bindValue(':status', $status);

        echo json_encode($stmt->execute()) !== false ? $stmt->execute() : [];
        exit;
    }

    /**
     * Met à jour le statut d'un hackathon
     *
     * @param int $hackathonId ID du hackathon
     * @param string $status Nouveau statut
     * @return bool Succès ou échec
     */
    public function updateHackathonStatus($hackathonId, $status)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $query = "UPDATE hackathons SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $hackathonId);
        $stmt->bindValue(':status', $status);

        echo json_encode($stmt->execute()) !== false ? $stmt->execute() : [];
        exit;
    }

    /**
     * Format une date pour l'affichage
     *
     * @param string $date Date au format SQL
     * @param string $format Format de sortie
     * @return string Date formatée
     */
    public function formatDate($date, $format = 'd/m/Y H:i')
    {
        if (!$date) return 'N/A';
        return date($format, strtotime($date));
    }

    /**
     * Protection CSRF
     *
     * @param string $token Token à vérifier
     * @return bool Validité du token
     */
    // public function validateCsrfToken($token)
    // {
    //     return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    // }

    /**
     * Obtenir un jeton CSRF
     *
     * @return string Jeton CSRF
     */
    /**
     * Récupère l'ID de l'utilisateur actuellement connecté
     * @return int|null ID de l'utilisateur ou null si non authentifié
     */

    // In UserController.php
public function getUserChallengeIds($userId) {
    try {
        // Authorization check (if needed)
        // ...
        $challengeIds = $this->user->getChallengeIdsForUser($userId);
        $this->jsonResponse([
            'success' => true,
            'data' => $challengeIds
        ]);
    } catch (Exception $e) {
        $this->jsonResponse([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}   public function getNextEvent($userId, $jwt)
    {
        header('Content-Type: application/json');

        try {
            $currentUserId = $this->getUserIdFromJWT($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }

            $database = Database::getInstance();
            $db = $database->getConnection();

            // Récupérer les hackathons auxquels l'utilisateur participe et qui sont futurs
            $stmt = $db->prepare("
                SELECT
                    h.id,
                    h.name,
                    h.description,
                    h.start_date,
                    h.end_date,
                    h.location
                FROM hackathons h
                INNER JOIN hackathon_participants hp ON h.id = hp.hackathon_id
                WHERE hp.user_id = :user_id
                  AND h.start_date > NOW()
                ORDER BY h.start_date ASC
                LIMIT 1
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $nextHackathon = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($nextHackathon) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => $nextHackathon
                ]);
            } else {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Aucun événement futur trouvé pour cet utilisateur.'
                ]);
            }

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
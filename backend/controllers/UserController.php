<?php

namespace Auth\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Auth\Model\User; // Assurez-vous d'importer le modèle User
use Auth\Model\Database; // Assurez-vous d'importer le modèle Database
use Exception;
use PDO;
use PDOException;
use Auth\Model\TokenManager;
use Auth\Service\InputInspectionService;

if (!class_exists('InputInspectionService')) {
    require_once __DIR__ . '/../services/InputInspectionService.php';
}
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
    private $key;
    protected $tokenManager;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        $this->db = $db;
        $this->user = new User($this->db);
        $this->tokenManager = $tokenManager;
    }

    /**
     * Récupère un utilisateur par son ID
     * @param mixed $id
     * @throws \Exception
     * @return void
     */
    public function get($id)
    {
        try {
            $this->validateMethod('GET');

            $user = $this->user->find($id);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé :' . $id);
            }

            unset($user['password']); // Ne pas renvoyer le mot de passe
            unset($user['role']);

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

    public function getAll()
    {
        try {
            $this->validateMethod('GET');

            // Verifier si l'utilisateur est admin
            if (!$this->isAdmin($this->tokenManager->getCurrentUserId())) {
                throw new Exception('Non autorisé', 403);
            }

            $users = $this->user->getAll();
            if (!$users) {
                throw new Exception('Aucun utilisateur trouvé');
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $users
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
     * Régénère le token CSRF
     */
    public function refreshCsrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $_SESSION['csrf_token_created'] = time();

        $this->jsonResponse([
            'success' => true,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
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
        $tokenManager = new TokenManager( $this->key, $this->db);
        return $tokenManager->validateToken($token);
    }

    public function update($id, $jwt)
    {
        try {
            $this->validateMethod('PUT');

            $currentUserId = $this->getUserIdFromJWT($jwt);

            // Vérifier si l'utilisateur modifie son propre profil ou est admin
            if ($currentUserId != $id && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Non autorisé'], 403);
                return;
            }

            // Récupérer les données selon le type de requête
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);

            // Vérifier que les données existent
            if (empty($data)) {
                $this->jsonResponse(['success' => false, 'error' => 'Aucune donnée reçue'], 400);
                return;
            }

            // Inspection et sanitation des entrées utilisateur (après fallback éventuel vers $_POST)
            try {
                $inputInspectionService = new InputInspectionService();
                $method = $_SERVER['REQUEST_METHOD'];
                $headers = function_exists('getallheaders') ? getallheaders() : [];
                $data = $inputInspectionService->inspectInput($data, [
                    'method' => $method,
                    'headers' => $headers,
                    'raw' => $rawData,
                    'max_body_bytes' => 1024 * 1024,
                ]);
            } catch (Exception $e) {
                if (isAjaxRequest()) {
                    header('Content-Type: application/json');
                    http_response_code($e->getCode() ?: 400);
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                } else {
                    setFlashMessage('error', 'Entrée invalide', $e->getMessage());
                    header('Location: ' . '/');
                }
                exit();
            }
            $updatableFields = ['username', 'fullname', 'school', 'email', 'special_comp', 'idea_project', 'study_level', 'number', 'bio', 'github_url', 'linkedin_url'];
            $filteredData  = $this->filterData($data, $updatableFields);

            if (empty($filteredData)) {
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
            $this->validateMethod('POST', 'PUT');

            $currentUserId = $this->getUserIdFromJWT($jwt);

            // Vérifier si l'utilisateur modifie son propre mot de passe
            if ($currentUserId != $id) {
                $this->jsonResponse(['success' => false, 'error' => 'Non autorisé'], 403);
                return;
            }

            // Récupérer les données du corps de la requête
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);

            $requiredFields = ['currentPassword', 'newPassword'];
            $this->validateRequiredFields($data, $requiredFields);

            // Inspection et sanitation des entrées utilisateur (après fallback éventuel vers $_POST)
            try {
                $inputInspectionService = new InputInspectionService();
                $method = $_SERVER['REQUEST_METHOD'];
                $headers = function_exists('getallheaders') ? getallheaders() : [];
                $data = $inputInspectionService->inspectInput($data, [
                    'method' => $method,
                    'headers' => $headers,
                    'raw' => $rawData,
                    'max_body_bytes' => 1024 * 1024,
                ]);
            } catch (Exception $e) {
                if (isAjaxRequest()) {
                    header('Content-Type: application/json');
                    http_response_code($e->getCode() ?: 400);
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                } else {
                    setFlashMessage('error', 'Entrée invalide', $e->getMessage());
                    header('Location: ' . '/');
                }
                exit();
            }
            // Vérifier l'ancien mot de passe
            $user = $this->user->find($id, true);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'error' => 'Utilisateur non trouvé'], 404);
                return;
            }
            if (!password_verify($data['currentPassword'], $user['password'])) {
                $this->jsonResponse(['success' => false, 'error' => 'Ancien mot de passe incorrect'], 400);
                return;
            }

            // Maintenir le nouveau mot de passe puisqu'il est deja hasher dans user->update
            $password = $data['newPassword'];

            $this->user->update($id, [
                'password' => $password,
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
            $currentUserId = $this->tokenManager->getCurrentUserId($jwt);
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
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getUserStats($userId)
    {
        header('Content-Type: application/json');

        try {
            $database = Database::getInstance();
            $db = $database->getConnection();

            // Vérifier l'existence de l'utilisateur
            $userCheck = $db->prepare("SELECT id FROM users WHERE id = ?");
            $userCheck->execute([$userId]);
            $user = $userCheck->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                throw new Exception("Utilisateur non trouvé");
            }

            // Initialiser la structure de réponse
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
                        'hacking-stat' => 0,
                        'points-change' => 0,
                        'points-change-percent' => 0
                    ]
                ]
            ];

            // Défis de développement (basé sur user_progress et snippets)
            try {
                $devQuery = $db->prepare("
                    SELECT 
                        COUNT(DISTINCT up.challenge_id) as total,
                        COALESCE(SUM(CASE WHEN up.status = 'ongoing' THEN 1 ELSE 0 END), 0) as in_progress
                    FROM user_progress up
                    LEFT JOIN snippets s ON up.challenge_id = s.challenge_id
                    WHERE up.user_id = ?
                    AND (s.language IS NULL OR s.language IN ('java', 'python', 'js', 'bash'))
                ");
                $devQuery->execute([$userId]);
                $devData = $devQuery->fetch(PDO::FETCH_ASSOC);

                if ($devData) {
                    $response['data']['stats']['number-dev-challenges'] = (int)$devData['total'];
                    $response['data']['stats']['number-dev-challenges-on'] = (int)$devData['in_progress'];
                }
            } catch (Exception $e) {
                error_log("Erreur dans la requête des défis de développement: " . $e->getMessage());
            }

            // Défis de hacking (basé sur validated_flags)
            try {
                $hackingQuery = $db->prepare("
                    SELECT 
                        COUNT(*) as total,
                        COALESCE(SUM(CASE WHEN vf.is_valid = 1 THEN 1 ELSE 0 END), 0) as validated
                    FROM validated_flags vf
                    WHERE vf.user_id = ?
                ");
                $hackingQuery->execute([$userId]);
                $hackingData = $hackingQuery->fetch(PDO::FETCH_ASSOC);

                if ($hackingData) {
                    $response['data']['stats']['number-hacking-challenges'] = (int)$hackingData['total'];
                    $response['data']['stats']['number-hacking-challenges-validate'] = (int)$hackingData['validated'];

                    if ($response['data']['stats']['number-hacking-challenges'] > 0) {
                        $response['data']['stats']['hacking-stat'] = round(
                            ($response['data']['stats']['number-hacking-challenges-validate'] /
                                $response['data']['stats']['number-hacking-challenges']) * 100
                        );
                    }
                }
            } catch (Exception $e) {
                error_log("Erreur dans la requête des défis de hacking: " . $e->getMessage());
            }

            // Projets soumis (basé sur team_members et teams)
            try {
                $projectsQuery = $db->prepare("
                    SELECT * 
                    FROM projects p
                    WHERE status = 'submitted' AND team_id IN (
                        SELECT team_id 
                        FROM team_members 
                        WHERE user_id = ?
                    )
                ");
                $projectsQuery->execute([$userId]);
                $projectsData = $projectsQuery->fetch(PDO::FETCH_ASSOC);

                if ($projectsData) {
                    $response['data']['stats']['number-submitted-projects'] = (int)$projectsData['submitted'];
                }
            } catch (Exception $e) {
                error_log("Erreur dans la requête des projets soumis: " . $e->getMessage());
            }

            // Points totaux (basé sur validated_flags)
            try {
                $pointsQuery = $db->prepare("
                    SELECT COALESCE(SUM(points_gained), 0) as total
                    FROM validated_flags
                    WHERE user_id = ? AND is_valid = 1
                ");
                $pointsQuery->execute([$userId]);
                $pointsData = $pointsQuery->fetch(PDO::FETCH_ASSOC);

                if ($pointsData) {
                    $response['data']['stats']['total-points'] = (int)$pointsData['total'];
                }

                // Points gagnés depuis la dernière connexion
                $pointsChangeQuery = $db->prepare("
                    SELECT COALESCE(SUM(points_gained), 0) as total
                    FROM validated_flags vf
                    JOIN users u ON vf.user_id = u.id
                    WHERE vf.user_id = ? AND vf.created_at > u.last_login
                ");
                $pointsChangeQuery->execute([$userId]);
                $pointsChangeData = $pointsChangeQuery->fetch(PDO::FETCH_ASSOC);

                if ($pointsChangeData) {
                    $response['data']['stats']['points-change'] = (int)$pointsChangeData['total'];
                    $response['data']['stats']['points-change-percent'] = $response['data']['stats']['total-points'] > 0
                        ? round(($response['data']['stats']['points-change'] / $response['data']['stats']['total-points']) * 100)
                        : 0;
                }
            } catch (Exception $e) {
                error_log("Erreur dans la requête des points: " . $e->getMessage());
            }

            // Pourcentage de progression
            $response['data']['stats']['total-points-stat'] = min(
                100,
                round(($response['data']['stats']['total-points'] / 1000) * 100)
            );

            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        } catch (PDOException $e) {
            error_log("Erreur de base de données: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques',
                'code' => 500,
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            error_log("Erreur générale: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques',
                'code' => 500,
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    public function getUserHackathons($userId, $jwt)
    {
        header('Content-Type: application/json');

        try {
            $currentUserId = $this->tokenManager->getCurrentUserId($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }
            $database = Database::getInstance();
            $db = $database->getConnection();

            $stmt = $db->prepare("SELECT * FROM hackathons WHERE id IN (SELECT hackathon_id FROM hackathon_participants WHERE user_id = :userId)");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse([
                'success' => true,
                'data' => $hackathons
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Récupère les hackathons de l'utilisateur et renvoie un JSON
     */
    public function getOngoingChallenges($userId, $jwt)
    {
        header('Content-Type: application/json');

        try {
            $currentUserId = $this->tokenManager->getCurrentUserId($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }
            $database = Database::getInstance();
            $db = $database->getConnection();

            // Ongoing = non-CTF with at least one non-completed submission OR
            // CTF with at least one valid flag but total points still below challenge points
            $stmt = $db->prepare("
            WITH non_ctf_ongoing AS (
                SELECT 
                    cs.challenge_id,
                    MIN(cs.submitted_at) AS started_at,
                    MAX(cs.submitted_at) AS last_activity
                FROM challenge_submissions cs
                JOIN challenges c ON c.id = cs.challenge_id AND c.type <> 'ctf'
                WHERE cs.user_id = :user_id
                  AND cs.status <> 'completed'
                GROUP BY cs.challenge_id
            ),
            ctf_ongoing AS (
                SELECT 
                    vf.challenge_id,
                    MIN(vf.validated_at) AS started_at,
                    MAX(vf.validated_at) AS last_activity,
                    SUM(vf.points_gained) AS total_ctf_points
                FROM validated_flags vf
                JOIN challenges ctf ON ctf.id = vf.challenge_id AND ctf.type = 'ctf'
                WHERE vf.user_id = :user_idp
                  AND vf.is_valid = 1
                GROUP BY vf.challenge_id
                HAVING SUM(vf.points_gained) < MAX(ctf.points)
            ),
            ongoing AS (
                SELECT challenge_id, started_at, last_activity FROM non_ctf_ongoing
                UNION
                SELECT challenge_id, started_at, last_activity FROM ctf_ongoing
            )
            SELECT 
                c.id,
                c.title,
                c.description,
                c.type,
                c.category,
                c.difficulty,
                c.points,
                'ongoing' AS status,
                og.last_activity AS progress_last_updated,
                og.started_at AS progress_start_date
            FROM challenges c
            JOIN ongoing og ON og.challenge_id = c.id
            WHERE c.is_active = 1
            ORDER BY og.last_activity DESC
            LIMIT 10
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':user_idp', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Harmonize shape with frontend expectations
            $data = array_map(function ($row) {
                return [
                    'challenge_id' => $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'type' => $row['type'],
                    'category' => $row['category'],
                    'difficulty' => $row['difficulty'],
                    'points' => $row['points'],
                    'status' => $row['status'],
                    'updated_at' => $row['progress_last_updated'],
                ];
            }, $rows);

            $this->jsonResponse([
                'success' => true,
                'data' => $data
            ]);
        } catch (PDOException $e) {
            error_log("Erreur de base de données dans getCurrentChallenges: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la récupération des défis en cours',
                // 'details' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            error_log("Erreur générale dans getCurrentChallenges: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les défis en cours d'un utilisateur
     * @param int $userId ID de l'utilisateur
     */
    public function getCurrentChallenges($userId, $jwt)
    {
        header('Content-Type: application/json');

        try {
            $currentUserId = $this->tokenManager->getCurrentUserId($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }

            $database = Database::getInstance();
            $db = $database->getConnection();

            // Récupérer les défis en cours sans dépendre de user_progress
            // 1) Non-CTF: au moins une soumission non terminée
            // 2) CTF: au moins un flag validé mais total points < challenge points
            $query = "
                WITH non_ctf_ongoing AS (
                    SELECT 
                        cs.challenge_id,
                        MIN(cs.submitted_at) AS started_at,
                        MAX(cs.submitted_at) AS last_activity
                    FROM challenge_submissions cs
                    JOIN challenges c ON c.id = cs.challenge_id AND c.type <> 'ctf'
                    WHERE cs.user_id = :user_id
                      AND cs.status <> 'completed'
                    GROUP BY cs.challenge_id
                ),
                ctf_ongoing AS (
                    SELECT 
                        vf.challenge_id,
                        MIN(vf.validated_at) AS started_at,
                        MAX(vf.validated_at) AS last_activity,
                        SUM(vf.points_gained) AS total_ctf_points
                    FROM validated_flags vf
                    JOIN challenges ctf ON ctf.id = vf.challenge_id AND ctf.type = 'ctf'
                    WHERE vf.user_id = :user_idp
                      AND vf.is_valid = 1
                    GROUP BY vf.challenge_id
                    HAVING SUM(vf.points_gained) < MAX(ctf.points)
                ),
                ongoing AS (
                    SELECT challenge_id, started_at, last_activity FROM non_ctf_ongoing
                    UNION
                    SELECT challenge_id, started_at, last_activity FROM ctf_ongoing
                )
                SELECT 
                    c.id,
                    c.title,
                    c.description,
                    c.type,
                    c.category,
                    c.difficulty,
                    c.points,
                    'ongoing' AS status,
                    og.last_activity AS progress_last_updated,
                    og.started_at AS progress_start_date
                FROM challenges c
                JOIN ongoing og ON og.challenge_id = c.id
                WHERE c.is_active = 1
                ORDER BY og.last_activity DESC
                LIMIT 10
            ";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':user_idp', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formater les données pour correspondre au frontend
            $formattedChallenges = array_map(function ($challenge) {
                return [
                    'challenge_id' => $challenge['id'],
                    'title' => $challenge['title'],
                    'description' => $challenge['description'],
                    'type' => $challenge['type'],
                    'category' => $challenge['category'],
                    'difficulty' => $challenge['difficulty'],
                    'points' => $challenge['points'],
                    'status' => $challenge['status'],
                    'updated_at' => $challenge['progress_last_updated']
                ];
            }, $challenges);

            $this->jsonResponse([
                'success' => true,
                'data' => $formattedChallenges
            ]);
        } catch (PDOException $e) {
            error_log("Erreur de base de données dans getCurrentChallenges: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la récupération des défis en cours',
                // 'details' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            error_log("Erreur générale dans getCurrentChallenges: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCompletedChallenges($userId, $jwt)
    {
        try {
            $currentUserId = $this->tokenManager->getCurrentUserId($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }
            $database = Database::getInstance();
            $db = $database->getConnection();

            try {
                // Récupérer les défis complétés de l'utilisateur
                $stmt = $db->prepare("
                SELECT 
                    c.id,
                    c.title,
                    c.description,
                    c.difficulty,
                    solved.points as points,
                    c.type,
                    solved.completed_date as completed_date
                FROM challenges c
                LEFT JOIN (
                    -- 1. Algo / Projet / Finale : meilleures soumissions validées
                    SELECT cs.challenge_id, MAX(cs.total_score) AS points, MAX(cs.submitted_at) AS completed_date
                    FROM challenge_submissions cs
                    WHERE cs.user_id = :userId
                    AND cs.status = 'completed'
                    GROUP BY cs.challenge_id

                    UNION

                    -- 2. CTF : cumul des points des flags validés
                    SELECT vf.challenge_id, SUM(vf.points_gained) AS points, MAX(vf.validated_at) AS completed_date
                    FROM validated_flags vf
                    JOIN challenges ctf ON vf.challenge_id = ctf.id
                    WHERE vf.user_id = :userIdp
                    AND vf.is_valid = 1
                    AND ctf.type = 'ctf'
                    GROUP BY vf.challenge_id
                ) AS solved ON solved.challenge_id = c.id
                WHERE solved.points IS NOT NULL
                ORDER BY solved.completed_date DESC
            ");
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':userIdp', $userId, PDO::PARAM_INT);
                $stmt->execute();

                $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => "Une erreur est survenue lors de la récupération des défis en cours !"
                    // pour debug
                    // . $e->getMessage()
                ], 500);
            }
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

    public function getRecentActivities($userId, $jwt)
    {
        try {
            $currentUserId = $this->tokenManager->getCurrentUserId($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }

            $database = Database::getInstance();
            $db = $database->getConnection();

            try {
                // Récupérer les activités récentes de l'utilisateur
                $stmt = $db->prepare("
                SELECT 
                    action as type,
                    description,
                    level,
                    created_at as timestamp
                FROM activity_logs
                WHERE user_id = :userId
                ORDER BY created_at DESC
                LIMIT 10
            ");
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->execute();

                $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => "Une erreur est survenue lors de la récupération des activités récentes !"
                    // pour debug
                    // . $e->getMessage()
                ], 500);
            }
            $this->jsonResponse([
                'success' => true,
                'data' => $activities
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getAllActivities($userId, $jwt = null)
    {
        try {
            $currentUserId = $this->tokenManager->getCurrentUserId();
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }

            $database = Database::getInstance();
            $db = $database->getConnection();

            try {
                // Récupérer les activités récentes de l'utilisateur
                $stmt = $db->prepare("
                SELECT 
                    user_id,
                    action,
                    description,
                    data,
                    level,
                    ip_address,
                    user_agent,
                    created_at as timestamp
                FROM activity_logs
                WHERE user_id = :userId
                ORDER BY created_at DESC
            ");
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->execute();

                $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => "Une erreur est survenue lors de la récupération des activités récentes !"
                    // pour debug
                    // . $e->getMessage()
                ], 500);
            }
            $this->jsonResponse([
                'success' => true,
                'data' => $activities
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getCurrentHackathons($userId, $jwt)
    {
        $currentUserId = $this->tokenManager->getCurrentUserId($jwt);
        if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
            $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
            return;
        }
        $hackathons = $this->user->getCurrentHackathons($userId, $jwt);
        $this->jsonResponse(['success' => true, 'data' => $hackathons]);
    }


    /**
     * Récupère les équipes de l'utilisateur et renvoie un JSON
     */
    public function getUserTeams($userId)
    {
        $db = $this->db;

        $stmt = $db->prepare("SELECT e.* FROM team e JOIN team_members em ON e.id = em.team_id WHERE em.user_id = :user_id");
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

        $stmt = $db->prepare("SELECT u.id, u.username, SUM(cs.total_score) as total_points FROM users u LEFT JOIN challenge_submissions cs ON u.id = cs.user_id AND cs.status = 'completed' GROUP BY u.id, u.username ORDER BY total_points DESC LIMIT :limit");
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

        $stmt = $db->prepare("INSERT INTO challenge_submissions (user_id, challenge_id, status, points, submitted_at) VALUES (:user_id, :challenge_id, :status, :points, NOW())");
        $stmt->execute([
            ':user_id' => $userId,
            ':challenge_id' => $challengeId,
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
    private function countUserValidatedFlags($userId)
    {
        $query = "SELECT COUNT(*) FROM validated_flags WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    private function countUserChallengesByType($userId, $type)
    {
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

    private function countUserSubmittedProjects($userId)
    {
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
        $query = "SELECT cs.submission_value, c.title AS challenge_title, cs.submitted_at 
        FROM challenge_submissions cs
        INNER JOIN challenges c ON cs.challenge_id = c.id
        WHERE cs.user_id = :user_id
        ORDER BY cs.submitted_at DESC
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
     * Vérifie si l'utilisateur est authentifié en tant qu'administrateur
     *
     * @return bool True si l'utilisateur est administrateur, false sinon
     */
    function isAdmin ($userId)
    {
        if (!isset($userId)) {
            return false;
        }
    
        // Vérifier si la table existe
        global $db;
    
        // Si aucune connexion à la base de données n'est disponible, essayer d'en créer une
        if (!isset($db)) {
            try {
                require_once __DIR__ . '/../models/Database.php';
                $database = Database::getInstance();
                $db = $database->getConnection();
            } catch (Exception $e) {
                error_log("Erreur de connexion à la base de données. check-up : " . $e->getMessage());
                return false;
            }
        }
    
        // Vérification du rôle global
        $query = "SELECT role FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $userId]);
        $role = $stmt->fetchColumn();
    
        if (!in_array($role, ['admin', 'organisateur'])) {
            return false;
        }
    
        // Vérification dans la whitelist
        $query = "SELECT 1 FROM admin_whitelist WHERE user_id = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $userId]);
    
        return (bool) $stmt->fetchColumn();
    }
    
    public function getNotifications($userId, $jwt = null)
    {
        header('Content-Type: application/json');

        try {
            $currentUserId = $this->tokenManager->getCurrentUserId();
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
                LIMIT 10
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
        } catch (PDOException $e) {
            error_log("Erreur de base de données dans getNotifications: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la récupération des notifications',
                'code' => 500,
                'details' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            error_log("Erreur générale dans getNotifications: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère toutes les ressources
     *
     * @param int $hackathonId Filtre par hackathon
     * @return array Liste des ressources
     */
    public function getTopHackers()
    {
        try {
            $query = "SELECT ranking, username, points FROM top_hackers ORDER BY points DESC LIMIT 10";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $hackers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse(['success' => true, 'data' => $hackers]);
        } catch (Exception $e) {
            http_response_code(500);
            $this->jsonResponse(['success' => false, 'error' => 'Erreur lors de la récupération du classement des hackers: ' . $e->getMessage()], 500);
        }
        exit;
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
        $teamsQuery = "SELECT COUNT(*) FROM team WHERE hackathon_id = :id";
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
                         WHERE c.hackathon_id = :id AND cs.status = 'completed') /
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
        $solvedQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE challenge_id = :id AND status = 'completed'";
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
                        WHERE cs.challenge_id = :id AND cs.status = 'completed'";
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

    public function getNextEvent($userId, $jwt)
    {
        header('Content-Type: application/json');

        try {
            $currentUserId = $this->tokenManager->getCurrentUserId($jwt);
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }

            $database = Database::getInstance();
            $db = $database->getConnection();

            // Récupérer les hackathons futurs
            $stmt = $db->prepare("
                SELECT
                    h.id,
                    h.name,
                    h.start_date
                FROM hackathons h
                WHERE h.start_date > NOW()  
                ORDER BY h.start_date ASC
            ");

            // Exécution de la requête sans bindParam
            $stmt->execute();
            $nextHackathon = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($nextHackathon) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => $nextHackathon
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Aucun événement futur trouvé.'
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

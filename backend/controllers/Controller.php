<?php

namespace Auth\Controller;

use Exception;
use Auth\Service\InputInspectionService;
use Auth\Model\Database;
use PDO;

if (!class_exists('InputInspectionService')) {
    require_once __DIR__ . '/../services/InputInspectionService.php';
}

if (!class_exists('Database')) {
    require_once __DIR__ . '/../models/Database.php';
}

class Controller
{
    protected $tokenManager;
    protected $publicRoutes = [
        'auth/login',
        'auth/register',
        'auth/forgot-password',
        'auth/reset-password',
        'auth/verify-email',
        'auth/check-auth'
    ];
    private $db;

    public function __construct($tokenManager)
    {
        $this->tokenManager = $tokenManager;
        $this->db = Database::getInstance()->getConnection();

        // Vérification CSRF pour les méthodes non-GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !$this->validateCsrfToken()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Session expirée, veuillez recharger la page - controller - Si ce message d\'erreur persiste, veuillez contacter le support'
            ], 403);
        }

        $isAuthenticated = $this->isAuthenticated();
        // Vérification d'authentification sauf pour les routes publiques
        if (!$this->isPublicRoute() && !$isAuthenticated) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Authentification requise'
            ], 401);
        }

        // Pour les utilisateurs connectés
        if ($isAuthenticated) {
            $userId = $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? null;
            try {
                $this->checkUserStatus($userId);
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], $e->getCode() ?: 403);
                exit;
            }
        }
    }


    public function logSecurityEvent(int $userId, string $eventType, array $details = [])
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO security_logs 
                (user_id, event_type, ip_address, user_agent, details, created_at) 
                VALUES (:user_id, :event_type, :ip, :ua, :details, NOW())"
            );

            $stmt->execute([
                ':user_id' => $userId,
                ':event_type' => $eventType,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                ':details' => json_encode($details)
            ]);
        } catch (Exception $e) {
            error_log('Failed to log security event: ' . $e->getMessage());
        }
    }

    protected function logActivity($action, $description, $data, $level, $ip_address, $user_agent)
    {
        // Si aucune connexion à la base de données n'est disponible, essayer d'en créer une
        if (!isset($this->db)) {
            try {
                $database = Database::getInstance();
                $this->db = $database->getConnection();
            } catch (Exception $e) {
                error_log("Erreur de connexion à la base de données pour logActivity: " . $e->getMessage());
                return false;
            }
        }

        // Données utilisateur
        $userId = isset($_SESSION['user']) && isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : $userId ?? $data['identifier'] ?? null;
        $ipAddress = $ip_address ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $user_agent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Données sérialisées
        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        try {
            // Insérer le log
            $query = "INSERT INTO activity_logs (user_id, action, description, data, ip_address, user_agent, level)
                  VALUES (:user_id, :action, :description, :data, :ip_address, :user_agent, :level)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':data', $dataJson);
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->bindParam(':user_agent', $userAgent);
            $stmt->bindParam(':level', $level);

            $result = $stmt->execute();

            // Également, enregistrer dans le fichier de log
            $logMessage = date('Y-m-d H:i:s') . " [$level] - $action - $description - " .
                "User: $userId - IP: $ipAddress - Data: $dataJson";
            error_log($logMessage);

            return $result;
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement de l'activité: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie le statut de l'utilisateur
     * 
     * @param int $userId ID de l'utilisateur à vérifier
     * @throws Exception Si l'utilisateur est inactif, suspendu ou supprimé
     */
    protected function checkUserStatus($userId)
    {
        if (empty($userId)) {
            throw new Exception('ID utilisateur manquant', 400);
        }

        $user = $this->getUserStatus($userId);

        if (!$user) {
            throw new Exception('Utilisateur non trouvé', 404);
        }

        // Vérifier si le compte est supprimé
        if ($user['deleted_at'] !== null) {
            $this->logSecurityEvent(
                $userId,
                'account_access_attempt_deleted',
                ['message' => 'Tentative d\'accès à un compte supprimé']
            );
            throw new Exception('Ce compte a été supprimé', 403);
        }

        // Vérifier si le compte est inactif
        if ($user['status'] === 'inactive') {
            $this->logSecurityEvent(
                $userId,
                'account_access_attempt_inactive',
                ['message' => 'Tentative d\'accès à un compte inactif']
            );
            throw new Exception('Ce compte est inactif', 403);
        }

        // Vérifier si le compte est suspendu
        if ($user['suspended_until'] !== null && strtotime($user['suspended_until']) > time()) {
            $suspensionTime = date('d/m/Y H:i', strtotime($user['suspended_until']));
            $this->logSecurityEvent(
                $userId,
                'account_access_attempt_suspended',
                ['suspended_until' => $user['suspended_until']]
            );
            throw new Exception("Ce compte est suspendu jusqu'au $suspensionTime", 403);
        }

        // Vérifier si le compte est verrouillé
        if ($this->isAccountLocked($user)) {
            $this->logSecurityEvent(
                $userId,
                'account_access_attempt_locked',
                ['locked_until' => $user['locked_until']]
            );
            throw new Exception('Trop de tentatives de connexion. Veuillez réessayer plus tard.', 403);
        }
    }

    protected function getUserStatus($userId)
{
    $stmt = $this->db->prepare("
        SELECT 
            status, 
            suspended_until, 
            deleted_at,
            locked_until,
            two_factor_enabled,
            two_factor_secret
        FROM users 
        WHERE id = :id
    ");
    
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

protected function isAccountLocked($user)
{
    return $user['locked_until'] !== null && strtotime($user['locked_until']) > time();
}
    /**
     * Vérifie si la route actuelle est publique
     */
    protected function isPublicRoute(): bool
    {
        $requestPath = $this->getRequestPath();

        foreach ($this->publicRoutes as $route) {
            if (strpos($requestPath, $route) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie l'authentification via JWT
     */
    protected function isAuthenticated(): bool
    {
        try {
            $token = $this->getBearerToken();

            if (empty($token)) {
                return false;
            }

            $validation = $this->tokenManager->validateToken($token);
            return $validation['valid'];
        } catch (Exception $e) {
            error_log('Authentication error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère le token JWT depuis les headers
     */
    protected function getBearerToken(): ?string
    {
        $headers = $this->getAuthorizationHeader();

        if (isset($_COOKIE['long_term_token'])) {
            return $_COOKIE['long_term_token'];
        }

        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Récupère le header Authorization
     */
    private function getAuthorizationHeader(): ?string
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $headers = trim($requestHeaders['Authorization'] ?? '');
        }

        return $headers;
    }

    /**
     * Vérifie le token CSRF
     */
    protected function validateCsrfToken(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return true;
        }

        // 1. Récupération du token depuis différentes sources
        $token = $_POST['_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? $this->getTokenFromJsonInput();

        // 2. Vérification de l'existence
        if (!$token) {
            return false;
        }

        // 3. Vérification de la validité
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }

        return true;
    }

    private function getTokenFromJsonInput(): ?string
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);

            // Inspection et sanitation des entrées utilisateur (après fallback éventuel vers $_POST)
            try {
                $inputInspectionService = new InputInspectionService();
                $rawInput = $rawData;
                $method = $_SERVER['REQUEST_METHOD'];
                $headers = function_exists('getallheaders') ? getallheaders() : [];
                $data = $inputInspectionService->inspectInput($data, [
                    'method' => $method,
                    'headers' => $headers,
                    'raw' => $rawInput,
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
            return $data['csrf_token'] ?? null;
        }
        return null;
    }


    /**
     * Retourne le chemin de la requête
     */
    protected function getRequestPath(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        return ltrim($path, '/');
    }

    /**
     * Envoie une réponse JSON
     */ protected function jsonResponse($data, $statusCode = 200)
    {
        try {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code((int)$statusCode);
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
            error_log("jsonResponse: " . $json);
            echo $json;
            exit;
        } catch (Exception $e) {
            error_log("Erreur dans jsonResponse: " . $e->getMessage());
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur de sérialisation JSON: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Valide les champs requis
     */
    protected function validateRequiredFields(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Le champ '$field' est requis");
            }
        }
    }

    /**
     * Valide les méthodes HTTP autorisées
     */
    protected function validateMethod(string $method, string $method2 = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method) && $_SERVER['REQUEST_METHOD'] !== strtoupper($method2)) {
            throw new Exception("Méthode {$_SERVER['REQUEST_METHOD']} non autorisée");
        }
    }

    /**
     * Récupère les données de la requête
     */
    protected function getRequestData(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                return json_decode(file_get_contents('php://input'), true) ?? [];
            }
            return $_POST;
        }

        return $_GET;
    }

    /**
     * Filtre les données selon les champs autorisés
     */
    protected function filterData(array $data, array $allowedFields): array
    {
        return array_intersect_key($data, array_flip($allowedFields));
    }
}

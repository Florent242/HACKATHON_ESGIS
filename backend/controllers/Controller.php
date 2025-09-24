<?php

namespace Auth\Controller;

use Exception;

class Controller
{
    private $tokenManager;
    protected $publicRoutes = [
        'auth/login',
        'auth/register',
        'auth/forgot-password',
        'auth/reset-password',
        'auth/verify-email',
        'auth/check-auth'
    ];

    public function __construct($tokenManager)
    {
        $this->tokenManager = $tokenManager;

        // Vérification CSRF pour les méthodes non-GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !$this->validateCsrfToken()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Token CSRF invalide - controller'
            ], 403);
        }

        // Vérification d'authentification sauf pour les routes publiques
        if (!$this->isPublicRoute() && !$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Authentification requise'
            ], 401);
        }
    }

    protected function logActivity($action, $description, $data, $level, $ip_address, $user_agent)
    {
        // Vérifier si la table existe
        global $db;

        // Si aucune connexion à la base de données n'est disponible, essayer d'en créer une
        if (!isset($db)) {
            try {
                require_once __DIR__ . '/../models/Database.php';
                $database = \Auth\Model\Database::getInstance();
                $db = $database->getConnection();
            } catch (Exception $e) {
                error_log("Erreur de connexion à la base de données pour logActivity: " . $e->getMessage());
                return false;
            }
        }

        // Vérifier si l'utilisateur est connecté

        // Données utilisateur
        $userId = isset($_SESSION['user']) && isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : $userId ?? $data['identifier'] ?? null;
        $ipAddress = $ip_address ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $user_agent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Données sérialisées
        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        try {
            // Vérifier si la table activity_logs existe
            $stmt = $db->prepare("SHOW TABLES LIKE 'activity_logs'");
            $stmt->execute();
            $tableExists = $stmt->rowCount() > 0;

            // Si la table n'existe pas, on la crée
            if (!$tableExists) {
                $createTable = "CREATE TABLE activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                action VARCHAR(255) NOT NULL,
                description TEXT,
                data TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                level VARCHAR(20) DEFAULT 'info',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
                $db->exec($createTable);
            }

            // Insérer le log
            $query = "INSERT INTO activity_logs (user_id, action, description, data, ip_address, user_agent, level)
                  VALUES (:user_id, :action, :description, :data, :ip_address, :user_agent, :level)";

            $stmt = $db->prepare($query);
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

        if (isset($_COOKIE['jwt_token'])) {
            return $_COOKIE['jwt_token'];
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

        // Pour les requêtes PUT, récupérer le token du corps de la requête
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        $token = $data['csrf_token'] ?? '';
        if (!$token) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }

        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
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
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($statusCode);
        }

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (php_sapi_name() !== 'cli') {
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
                throw new Exception("Le champ '$field' est requis" . print_r($data, true));
            }
        }
    }

    /**
     * Valide les méthodes HTTP autorisées
     */
    protected function validateMethod(string $method, string $method2 = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method) && $_SERVER['REQUEST_METHOD'] !== strtoupper($method2)) {
            throw new Exception("Méthode {$_SERVER['REQUEST_METHOD']} non autorisée. method_required :" . $method . " " . $method2);
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

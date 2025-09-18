<?php

namespace Auth\Controller;

use Exception;
use Auth\Service\InputInspectionService;

if (!class_exists('InputInspectionService')) {
    require_once __DIR__ . '/../services/InputInspectionService.php';
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

    public function __construct($tokenManager)
    {
        $this->tokenManager = $tokenManager;

        // Vérification CSRF pour les méthodes non-GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !$this->validateCsrfToken()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Session expirée, veuillez recharger la page - controller - Si ce message d\'erreur persiste, veuillez contacter le support'
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
                $rawInput = $rawData;
                $method = $_SERVER['REQUEST_METHOD'];
                $headers = function_exists('getallheaders') ? getallheaders() : [];
                $data = InputInspectionService::inspectInput($data, [
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

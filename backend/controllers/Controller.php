<?php
namespace Auth\Controller;

use Exception;

class Controller {
    public function __construct() {
        // Vérification de l'authentification si nécessaire
        $publicRoutes = ['/auth/login', '/auth/signup', '/auth/forgot-password'];
        if (!in_array($_SERVER['REQUEST_URI'], $publicRoutes) && !isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Non authentifié'
            ], 401);
        }
    }

    protected function jsonResponse($data, $statusCode = 200) {
        if (headers_sent()) {
            echo json_encode($data, JSON_PRETTY_PRINT);
        } else {
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        
        if (php_sapi_name() !== 'cli') {
            exit;
        }
    }

    protected function validateRequiredFields($data, $fields) {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Le champ '$field' est requis");
            }
        }
    }

    protected function validateMethod($method) {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            throw new Exception('Méthode non autorisée');
        }
    }

    protected function getRequestData() {
        return $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
    }

    protected function filterData($data, $allowedFields) {
        return array_intersect_key($data, array_flip($allowedFields));
    }
}

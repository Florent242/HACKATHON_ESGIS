<?php

// Configuration CORS
function configureCors() {
    // Autoriser l'origine spécifique de votre frontend
    // En développement, vous pouvez utiliser '*' mais en production, spécifiez l'origine exacte
    header('Access-Control-Allow-Origin: *');
    
    // Autoriser les méthodes HTTP
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    
    // Autoriser les en-têtes personnalisés
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    // Autoriser l'envoi des credentials (cookies, en-têtes d'autorisation)
    header('Access-Control-Allow-Credentials: true');
    
    // Durée de mise en cache des résultats du pre-flight
    header('Access-Control-Max-Age: 86400'); // 24 heures

    // Pour les requêtes OPTIONS (pre-flight)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}

// Fonction pour envoyer une réponse JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Fonction pour vérifier si c'est une requête API
function isApiRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Fonction pour vérifier le token JWT
function verifyJwtToken() {
    $headers = getallheaders();
    
    // Vérifier si le token est présent
    if (!isset($headers['Authorization'])) {
        return false;
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    try {
        // Vérifier et décoder le token
        // Remplacez cette partie avec votre logique de vérification JWT
        $decoded = jwt_decode($token);
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}

// Fonction pour créer un token JWT
function createJwtToken($userId) {
    // Configurer les claims du token
    $payload = [
        'sub' => $userId,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // 24 heures
    ];

    // Générer et retourner le token
    // Remplacez cette partie avec votre logique de création JWT
    return jwt_encode($payload);
}

// Fonction pour gérer les erreurs API
function handleApiError($error, $statusCode = 400) {
    jsonResponse([
        'error' => true,
        'message' => $error instanceof Exception ? $error->getMessage() : $error
    ], $statusCode);
}

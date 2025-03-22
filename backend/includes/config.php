<?php

// Configuration de la base de données SQLite
define('DB_FILE', __DIR__ . '/../database/hackathon.db');

// Configuration de l'application
define('APP_NAME', 'Hackathon Platform');
define('APP_URL', 'http://localhost');
define('APP_VERSION', '1.0.0');

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en production avec HTTPS

// Configuration du fuseau horaire
date_default_timezone_set('Africa/Porto-Novo');

// Configuration des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1); // Mettre à 0 en production

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour générer un token CSRF
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier un token CSRF
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Fonction pour vérifier si l'utilisateur est connecté
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier le rôle de l'utilisateur
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Fonction pour la réponse JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
// Elements de cors.php
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

// Fonction pour gérer les erreurs API
function handleApiError($error, $statusCode = 400) {
    jsonResponse([
        'error' => true,
        'message' => $error instanceof Exception ? $error->getMessage() : $error
    ], $statusCode);
}



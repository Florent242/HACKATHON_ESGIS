<?php

// Configuration de la base de données SQLite
define('DB_FILE', __DIR__ . '/../database/hackathon.db');

// Configuration de l'application
define('APP_NAME', 'Hackathon Platform');
define('APP_URL', 'http://localhost:8000');
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

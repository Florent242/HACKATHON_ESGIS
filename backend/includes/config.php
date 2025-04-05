<?php
if (!defined('CONFIG_INCLUDED')) {
    define('CONFIG_INCLUDED', true);
}
// Configuration des routes par rôle
define('ROLE_REDIRECTIONS', [
    'admin' => '/HACKATHON_ESGIS/public/admin',
    'organizer' => '/HACKATHON_ESGIS/public/admin/hackathon',
    'participant' => '/HACKATHON_ESGIS/public/user',
    'guest' => '/HACKATHON_ESGIS/public/auth'
]);

// Routes publiques qui ne nécessitent pas d'authentification
define('PUBLIC_ROUTES', [
    '/HACKATHON_ESGIS/public/auth', // Page d'authentification
    '/HACKATHON_ESGIS/public/auth/login', // Connexion
    '/HACKATHON_ESGIS/public/auth/register', // Inscription
    '/HACKATHON_ESGIS/public/auth/forgot-password', // Mot de passe oublié
    '/HACKATHON_ESGIS/public/auth/reset-password', // Réinitialisation du mot de passe
    '/HACKATHON_ESGIS/public/auth/verify-email', // Vérification de l'email
    '/HACKATHON_ESGIS/public/auth/confirm-email', // Confirmation de l'email
    '/HACKATHON_ESGIS/public/auth/verify-otp', // Vérification du code OTP
    '/HACKATHON_ESGIS/public/auth/send-otp', // Envoi du code OTP
    '/HACKATHON_ESGIS/public/auth/logout', // Déconnexion
    '/HACKATHON_ESGIS/public/api/auth', // API d'authentification
    '/HACKATHON_ESGIS/public/api/auth/register', // API d'inscription
    '/HACKATHON_ESGIS/public/api/auth/login', // API de connexion
    '/HACKATHON_ESGIS/public/api/auth/forgot-password', // API de récupération de mot de passe
    '/HACKATHON_ESGIS/public/api/auth/reset-password', // API de réinitialisation de mot de passe
    '/HACKATHON_ESGIS/public/api/auth/verify-email', // API de vérification d'email
    '/HACKATHON_ESGIS/public/api/auth/confirm-email', // API de confirmation d'email
    '/HACKATHON_ESGIS/public/api/auth/verify-otp', // API de vérification OTP
    '/HACKATHON_ESGIS/public/api/auth/send-otp', // API d'envoi OTP
    '/HACKATHON_ESGIS/public/api/auth/logout', // API de déconnexion
    '/HACKATHON_ESGIS/public/api/auth/refresh-token', // API de rafraîchissement du token
    '/HACKATHON_ESGIS/public/api/auth/validate-token', // API de validation du token
    '/HACKATHON_ESGIS/public/api/public', // API publique
    '/HACKATHON_ESGIS/public/api/public/*', // Toutes les routes API publiques
    '/HACKATHON_ESGIS/public/assets/*', // Accès aux assets (images, css, js)
    '/HACKATHON_ESGIS/public/docs', // Documentation
    '/HACKATHON_ESGIS/public/docs/*', // Documentation
    '/HACKATHON_ESGIS/public/about', // À propos
    '/HACKATHON_ESGIS/public/contact', // Contact
    '/HACKATHON_ESGIS/public/privacy', // Politique de confidentialité
    '/HACKATHON_ESGIS/public/terms', // Conditions d'utilisation
    '/HACKATHON_ESGIS/public/faq', // FAQ
    '/HACKATHON_ESGIS/public/health-check', // Vérification de santé
    '/HACKATHON_ESGIS/public/error', // Page d'erreur
    '/HACKATHON_ESGIS/public/error/*', // Toutes les pages d'erreur
    '/HACKATHON_ESGIS/public/robots.txt', // Fichier robots.txt
    '/HACKATHON_ESGIS/public/sitemap.xml', // Sitemap
    '/HACKATHON_ESGIS/public/favicon.ico', // Favicon
    '/HACKATHON_ESGIS/public/opensearch.xml', // OpenSearch
    '/HACKATHON_ESGIS/public/apple-touch-icon.png', // Icône Apple Touch
    '/HACKATHON_ESGIS/public/.well-known/*' // Routes .well-known
]);

// Durée de vie des sessions (en secondes)
define('SESSION_LIFETIME', 3600); // 1 heure
// Configuration de la base de données SQLite
define('DB_FILE', __DIR__ . '/../database/hackathon.db');

// Configuration de l'application
// define('APP_NAME', 'Hackathon Platform');
// define('APP_URL', 'http://localhost');
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
    exit;
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



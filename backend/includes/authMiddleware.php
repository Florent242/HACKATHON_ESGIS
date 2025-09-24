<?php
// authMiddleware.php
use Auth\Model\Database;
use Auth\Model\TokenManager;

if (!class_exists('Auth\Model\TokenManager')) {
    require_once __DIR__ . '/../models/TokenManager.php';
}
if (!class_exists('Database')) {
    require_once __DIR__ . '/../models/Database.php';
}

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}

if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}

if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 86400); // 24 heures
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

class AuthMiddleware
{
    private $key;
    private $db;
    public function __construct($key)
    {
        $this->key = $key;
        $this->db = Database::getInstance()->getConnection();
    }
    public static function checkAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si c'est une route publique, on ne fait rien
        if (self::isPublicAdminRoute()) {
            return;
        }

        // Vérifier si l'utilisateur est connecté
        if (empty($_SESSION['admin']) || !$_SESSION['admin']['logged_in']) {
            // Si pas de session, vérifier le token JWT
            if (!self::isAuthenticated()) {
                self::redirectToLogin();
                return;
            }
        }

        // Vérifier la session admin
        if (!empty($_SESSION['user']) && $_SESSION['user']['logged_in']) {
            // Vérifier l'expiration de la session
            if ($_SESSION['user']['last_activity'] + SESSION_LIFETIME > time()) {
                $_SESSION['user']['last_activity'] = time(); // Mettre à jour le timestamp
            } else {
                setFlashMessage('error', 'Session expirée');
                self::logout(); // Session expirée
            }
        }

        // Vérifier le token JWT (fallback)
        if ((isset($_COOKIE['jwt_token']) && !empty($_COOKIE['jwt_token'])) || (isset($_COOKIE['long_term_token']) && !empty($_COOKIE['long_term_token']))) {
            try {
                $token = $_COOKIE['long_term_token'] ?? $_COOKIE['jwt_token'];
                if (empty($token)) {
                    throw new Exception('Token manquant');
                }
                $database = Database::getInstance();
                $db = $database->getConnection();
                $tokenManager = new TokenManager($_ENV['JWT_SECRET'] ?? 'your-secret-key', $db, [
                    'shortTermExpiry' => 3600, // 1 heure
                    'longTermExpiry' => 2592000 // 30 jours
                ]);
                $user = $tokenManager->validateToken($token);
                if (!$user['valid']) {
                    self::logout();
                }
                // Recréer la session à partir du token
                $_SESSION['user'] = [
                    'id' => $user['user_id'],
                    'logged_in' => true,
                    'last_activity' => time()
                ];

                return $_SESSION['user'];
            } catch (Exception $e) {
                setFlashMessage('error', 'Token invalide', $e->getMessage());
                self::logout();
            }
        }

        // Redirection si non authentifié
        // setFlashMessage('error', "Non authentifié");
        // self::redirectToLogin();
    }
    public static function isAdminRoute(): bool
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        foreach (ADMIN_ROUTES as $route) {
            if (preg_match($route, $currentUri)) {
                return true;
            }
        }
        return false;
    }

    public static function isPublicAdminRoute(): bool
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        foreach (PUBLIC_ADMIN_ROUTES as $route) {
            if (preg_match($route, $currentUri)) {
                return true;
            }
        }
        return false;
    }

    public static function isAuthenticated(): bool
    {
        if ((isset($_COOKIE['jwt_token']) && !empty($_COOKIE['jwt_token'])) || (isset($_COOKIE['long_term_token']) && !empty($_COOKIE['long_term_token']))) {
            try {
                $token = $_COOKIE['long_term_token'] ?? $_COOKIE['jwt_token'];
                $database = Database::getInstance();
                $db = $database->getConnection();
                $tokenManager = new TokenManager($_ENV['JWT_SECRET'] ?? 'your-secret-key', $db, [
                    'shortTermExpiry' => 3600, // 1 heure
                    'longTermExpiry' => 2592000 // 30 jours
                ]);
                $user = $tokenManager->validateToken($token);
                if (!$user['valid']) {
                    self::logout();
                    return false;
                }
                // Recréer la session à partir du token
                $_SESSION['user']['id'] = $user['user_id'];
                $_SESSION['user']['logged_in'] = true;
                $_SESSION['user']['last_activity'] = time();

                return $user['valid'];
            } catch (Exception $e) {
                setFlashMessage('error', 'Token invalide', $e->getMessage());
                self::logout();
                return false;
            }
        }
        return false;
    }

    public static function logout()
    {
        session_unset();
        session_destroy();
        setcookie('jwt_token', '', time() - 3600, '/');
        setcookie('long_term_token', '', time() - 2592000, '/');
    }
    public static function refreshToken($token)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();
        $tokenManager = new TokenManager($_ENV['JWT_SECRET'] ?? 'your-secret-key', $db, [
            'shortTermExpiry' => 3600, // 1 heure
            'longTermExpiry' => 2592000 // 30 jours
        ]);
        $user = $tokenManager->validateToken($token);
        if (!$user['valid']) {
            self::logout();
        }
        // Recréer la session à partir du token
        $_SESSION['user']['id'] = $user['user_id'];
        $_SESSION['user']['logged_in'] = true;
        $_SESSION['user']['last_activity'] = time();

        return $user['valid'];
    }
    private static function redirectToLogin()
    {
        header('Location: /admin/login');
        exit;
    }
}

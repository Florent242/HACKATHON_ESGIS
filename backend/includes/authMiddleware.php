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

        // Vérifier si la route est publique
        if (!self::isPublicRoute() && !self::isAuthenticated()) {
            header('Location: /HACKATHON_ESGIS/public/auth');
            return;
        }

        if (self::isPublicRoute() && self::isAuthenticated()) {
            header('Location: /HACKATHON_ESGIS/public/user');
            return;
        }

        // Vérifier la session utilisateur
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
                $database = Database::getInstance();
                $db = $database->getConnection();
                $tokenManager = new TokenManager($_ENV['JWT_SECRET'] ?? 'your-secret-key', $db, [
                    'shortTermExpiry' => 3600, // 1 heure
                    'longTermExpiry' => 2592000 // 30 jours
                ]);
                $user = $tokenManager->validateToken($token);
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
    public static function isPublicRoute(): bool
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        foreach (PUBLIC_ROUTES as $route) {
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
                // Recréer la session à partir du token
                $_SESSION['user'] = [
                    'id' => $user['user_id'],
                    'logged_in' => true,
                    'last_activity' => time()
                ];

                return $user['valid'];
            } catch (Exception $e) {
                setFlashMessage('error', 'Token invalide', $e->getMessage());
                self::logout();
            }
        }
        return false;
    }

    public static function logout()
    {
        session_unset();
        session_destroy();
        setcookie('jwt_token', '', time() - 3600, '/');
    }

    private static function redirectToLogin()
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';

        if (strpos($currentUri, '/admin') !== false) {
            header('Location: /HACKATHON_ESGIS/public/auth_admin');
        } else {
            header('Location: /HACKATHON_ESGIS/public/auth');
        }
        exit;
    }

    public static function redirectBasedOnRole($role)
    {
        $redirectUrl = ROLE_REDIRECTIONS[$role] ?? ROLE_REDIRECTIONS['guest'];
        header("Location: $redirectUrl");
        exit;
    }
}
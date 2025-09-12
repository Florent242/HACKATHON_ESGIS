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

// Inclure le fichier autoload de Composer pour charger les variables d'environnement
require_once __DIR__ . '/../../vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();


class AuthMiddleware
{
    private $key;
    private $db;
    public function __construct()
    {
        $this->key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        $this->db = Database::getInstance()->getConnection();
    }
    public static function checkAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vérifier si la route est publique
        if (!self::isPublicRoute() && !self::isAuthenticated()) {
            header('Location: /auth');
            return;
        }

        if (self::isPublicRoute() && self::isAuthenticated()) {
            header('Location: /user');
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
        if ((isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) || (isset($_COOKIE['long_term_token']) && !empty($_COOKIE['long_term_token']))) {
            try {
                $token = $_COOKIE['long_term_token'] ?? $_COOKIE['auth_token'];
                if (empty($token)) {
                    throw new Exception('Token manquant');
                }
                $database = Database::getInstance();
                $db = $database->getConnection();
                $tokenManager = new TokenManager($db, [
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

        // TODO: Verifier si l'utilisateur est suspendu OU BANNI
        


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
        if ((isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) || (isset($_COOKIE['long_term_token']) && !empty($_COOKIE['long_term_token']))) {
            try {
                $token = $_COOKIE['long_term_token'] ?? $_COOKIE['auth_token'];
                $database = Database::getInstance();
                $db = $database->getConnection();
                $tokenManager = new TokenManager($db, [
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
        setcookie('auth_token', '', time() - 3600, '/');
        setcookie('long_term_token', '', time() - 2592000, '/');
    }
    public static function refreshToken($token)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();
        $tokenManager = new TokenManager($db, [
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

        return $user['valid'];
    }
    private static function redirectToLogin()
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';

        if (strpos($currentUri, '/admin') !== false) {
            header('Location: /auth_admin');
        } else {
            header('Location: /auth');
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

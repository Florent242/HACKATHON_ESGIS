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

// Gestion des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 24h
        'gc_maxlifetime' => 86400   // 24h
    ]);
}

// Gestion des tokens CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_created'] = time();
}

// Récupérer l'ID utilisateur pour les notifications
$user_id = $_SESSION['user_id'] ?? null;

class AuthMiddleware
{
    private static $key;
    private static $db;
    public function __construct()
    {
        self::initDb();
    }
    public static function checkAuth()
    {
        self::initDb(); // S'assurer que la base de données est initialisée

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isAuthenticated = self::isAuthenticated();
        $isPublicRoute = self::isPublicRoute();

        // Vérifier si la route est publique
        if (!$isPublicRoute && !$isAuthenticated) {
            header('Location: /auth');
            return;
        }

        if ($isPublicRoute && $isAuthenticated) {
            header('Location: /user');
            return;
        }

        // Pour les utilisateurs connectés, vérifier le statut de l'utilisateur
        if ($isAuthenticated) {
            $userId = $_SESSION['user']['id'] ?? null;
            try {
                self::checkUserStatus($userId);
            } catch (Exception $e) {
                self::logout();
                setFlashMessage('error', $e->getMessage());
                self::redirectToLogin();
            }
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
    }

    // Initialisation statique
    private static function initDb()
    {
        if (self::$db === null) {
            try {
                $database = Database::getInstance();
                self::$db = $database->getConnection();
                self::$key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
            } catch (Exception $e) {
                error_log("Erreur de connexion à la base de données : " . $e->getMessage());
                throw new RuntimeException("Impossible de se connecter à la base de données");
            }
        }
    }

    /**
     * Vérifie le statut de l'utilisateur
     * 
     * @param int $userId ID de l'utilisateur à vérifier
     * @throws Exception Si l'utilisateur est inactif, suspendu ou supprimé
     */
    public static function checkUserStatus($userId)
    {
        if (empty($userId) || !is_numeric($userId)) {
            throw new Exception('ID utilisateur manquant', 400);
        }

        $user = self::getUserStatus($userId);

        if (!$user) {
            throw new Exception('Utilisateur non trouvé', 404);
        }

        // Vérifier si le compte est supprimé
        if ($user['deleted_at'] !== null) {
            self::logSecurity(
                'account_access_attempt_deleted',
                'Tentative d\'accès à un compte supprimé',
                [
                    'message' => 'Tentative d\'accès à un compte supprimé',
                    'user_id' => $userId,
                    'user_status' => $user['status'],
                    'request_uri' => $_SERVER['REQUEST_URI']
                ],
                $userId,
                'warning'
            );
            throw new Exception('Ce compte a été supprimé', 403);
        }

        // Vérifier si le compte est inactif
        if ($user['status'] === 'inactive') {
            self::logSecurity(
                'account_access_attempt_inactive',
                'Tentative d\'accès à un compte inactif',
                [
                    'message' => 'Tentative d\'accès à un compte inactif',
                    'user_id' => $userId,
                    'user_status' => $user['status'],
                    'request_uri' => $_SERVER['REQUEST_URI']
                ],
                $userId,
                'warning'
            );
            throw new Exception('Ce compte est désactivé', 403);
        }

        // Vérifier si le compte est suspendu
        if ($user['suspended_until'] !== null && strtotime($user['suspended_until']) > time()) {
            $suspensionTime = date('d/m/Y H:i', strtotime($user['suspended_until']));
            self::logSecurity(
                'account_access_attempt_suspended',
                'Tentative d\'accès à un compte suspendu',
                [
                    'suspended_until' => $user['suspended_until'],
                    'user_id' => $userId,
                    'user_status' => $user['status'],
                    'request_uri' => $_SERVER['REQUEST_URI']
                ],
                $userId,
                'warning'
            );
            throw new Exception("Ce compte est suspendu jusqu'au $suspensionTime", 403);
        }

        // Vérifier si le compte est verrouillé
        if (self::isAccountLocked($user)) {
            self::logSecurity(
                'account_access_attempt_locked',
                'Tentative d\'accès à un compte verrouillé',
                [
                    'locked_until' => $user['locked_until'],
                    'user_id' => $userId,
                    'user_status' => $user['status'],
                    'request_uri' => $_SERVER['REQUEST_URI']
                ],
                $userId,
                'warning'
            );
            throw new Exception('Ce compte est verrouillé', 403);
        }
    }
    public static function getUserStatus($userId)
    {
        if (empty($userId) || !is_numeric($userId)) {
            throw new Exception('ID utilisateur manquant', 400);
        }
        $stmt = self::$db->prepare("
        SELECT 
            status, 
            suspended_until, 
            deleted_at,
            locked_until,
            two_factor_enabled,
            two_factor_secret
        FROM users 
        WHERE id = :id
    ");

        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function isAccountLocked($user)
    {
        return $user['locked_until'] !== null && strtotime($user['locked_until']) > time();
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

    public static function logSecurity($action, $description, $data = [], $userId = null, $level = 'info')
    {
        try {
            $db = Database::getInstance()->getConnection();
            // Récupérer l'adresse IP du client
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            // Récupérer le user-agent du navigateur
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            // Convertir les données en JSON pour le stockage
            $dataJson = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

            // Préparer et exécuter la requête d'insertion
            $query = "INSERT INTO security_logs 
                 (user_id, event_type, ip_address, user_agent, details) 
                 VALUES (:user_id, :event_type, :ip_address, :user_agent, :details)";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':event_type', $action);
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->bindParam(':user_agent', $userAgent);
            $stmt->bindParam(':details', $description);

            $result = $stmt->execute();

            // Également, enregistrer dans le fichier de log système
            $logMessage = date('Y-m-d H:i:s') . " [SECURITY][$level] - $action - $description - " .
                "User: " . ($userId ?? 'guest') . " - IP: $ipAddress";
            if (!empty($data)) {
                $logMessage .= " - Data: " . json_encode($data);
            }
            error_log($logMessage);

            return $result;
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du log de sécurité: " . $e->getMessage());
            return false;
        }
    }
    public static function redirectBasedOnRole($role)
    {
        $redirectUrl = ROLE_REDIRECTIONS[$role] ?? ROLE_REDIRECTIONS['guest'];
        header("Location: $redirectUrl");
        exit;
    }
}

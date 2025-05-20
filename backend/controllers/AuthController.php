<?php

namespace Auth\Controller;

use Exception;
use Auth\Model\Database;
use Auth\Model\User;
use Auth\Model\TokenManager;
use PDO;
use PDOException;

if (!class_exists('Auth\Model\TokenManager')) {
    require_once __DIR__ . '/../models/TokenManager.php';
}
if (!class_exists('Database')) {
    require_once __DIR__ . '/../models/Database.php';
}
if (!class_exists('User')) {
    require_once __DIR__ . '/../models/User.php';
}
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/includes/functions.php';
}

class AuthController
{
    private const BASE_URL = '/HACKATHON_ESGIS/public';
    private $user;
    private $db;
    private $tokenManager;

    public function __construct($db = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($db) {
            $this->db = $db;
        } else {
            $database = Database::getInstance();
            $this->db = $database->getConnection();
        }

        $this->user = new User($this->db);
        $this->tokenManager = new TokenManager($_ENV['JWT_SECRET'] ?? 'your-secret-key', $this->db, [
            'shortTermExpiry' => 3600, // 1 heure
            'longTermExpiry' => 2592000 // 30 jours
        ]);

        // Générer un token CSRF s'il n'existe pas
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Récupère le token JWT depuis les headers
     */
    public function getBearerToken(): ?string
    {
        // D'abord essayer le header Authorization
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }

        // Si pas dans les headers, chercher dans les cookies
        if (isset($_COOKIE['long_term_token'])) {
            return $_COOKIE['long_term_token'];
        }

        if (isset($_COOKIE['jwt_token'])) {
            return $_COOKIE['jwt_token'];
        }

        return null;
    }

    /**
     * Récupère le header Authorization
     */
    public function getAuthorizationHeader(): ?string
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    public function checkAuth()
    {
        header('Content-Type: application/json');

        try {
            // Vérifier d'abord la session
            if (isset($_SESSION['user']) && $_SESSION['user']['logged_in']) {
                echo json_encode([
                    'authenticated' => true,
                    'id' => $_SESSION['user']['id'],
                    'role' => $_SESSION['user']['role']
                ]);
                return;
            }

            // Puis vérifier les tokens
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Token manquant', 401);
            }

            $tokenManager = $this->tokenManager;
            $user = $tokenManager->validateToken($token);

            if (!$user || !$user['valid']) {
                throw new Exception('Token invalide', 401);
            }

            // Mettre à jour la session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $userInfo = $this->user->find($user['user_id']);
            $_SESSION['user'] = [
                'id' => $user['user_id'],
                'email' => $userInfo['email'],
                'role' => $userInfo['role'],
                'logged_in' => true,
                'last_activity' => time()
            ];

            echo json_encode([
                'authenticated' => true,
                'id' => $user['user_id'],
                'role' => $userInfo['role']
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'authenticated' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }

    private function validateCsrfToken()
    {
        // Récupérer le token CSRF de la requête
        $requestToken = filter_input(INPUT_POST, 'csrf_token', FILTER_DEFAULT) ?: null;

        // Vérifier si le token est présent dans la requête
        if (empty($requestToken)) {
            throw new Exception('Token CSRF manquant', 400);
        }

        // Récupérer le token CSRF de la session
        $sessionToken = $_SESSION['csrf_token'] ?? null;

        // Vérifier si le token est présent dans la session
        if (empty($sessionToken)) {
            throw new Exception('Session CSRF invalide', 400);
        }

        // Comparer les tokens avec hash_equals pour éviter les attaques de timing
        if (!hash_equals($sessionToken, $requestToken)) {
            throw new Exception('Token CSRF invalide', 403);
        }

        // Optionnel : régénérer le token CSRF après utilisation
        if (empty($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === $requestToken) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function setAuthCookies($token, $longTermToken = null)
    {
        // Cookie court terme (1 heure)
        setcookie("jwt_token", $token, [
            "expires" => time() + (60 * 60 ),
            "path" => "/",
            "httponly" => true,
            "secure" => true,
            "samesite" => "Strict"
        ]);

        // Cookie long terme (30 jours) si demandé
        if ($longTermToken) {
            setcookie("long_term_token", $longTermToken, [
                "expires" => time() + (60 * 60 * 24 * 30),
                "path" => "/",
                "httponly" => true,
                "secure" => true,
                "samesite" => "Strict"
            ]);
        }
    }

    // Traiter l'inscription
    public function register()
    {
        try {
            // Vérifier le token CSRF
            $this->validateCsrfToken();

            // Récupération et nettoyage des données
            $data = [
                'username'    => trim(filter_input(INPUT_POST, 'username', FILTER_DEFAULT) ?: ''),
                'fullname'    => trim(filter_input(INPUT_POST, 'fullname', FILTER_DEFAULT) ?: ''),
                'email'       => trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: ''),
                'school'      => trim(filter_input(INPUT_POST, 'school', FILTER_DEFAULT) ?: ''),
                'password'    => trim(filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW) ?: ''), // Ne pas filtrer le mot de passe
                'number'      => trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT) ?: ''),
                'special_comp' => trim(filter_input(INPUT_POST, 'main_skill', FILTER_DEFAULT) ?: ''),
                'study_level' => trim(filter_input(INPUT_POST, 'education_level', FILTER_DEFAULT) ?: ''),
                'role'        => 'participant'
            ];

            // Validation des données
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new Exception("Tous les champs sont obligatoires");
            }

            // Validation de l'email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Format d'email invalide");
            }

            // Validation du mot de passe
            if (strlen($data['password']) < 8) {
                throw new Exception("Le mot de passe doit contenir au moins 8 caractères");
            }

            // Création de l'utilisateur
            $userId = $this->user->create($data);

            if ($userId) {
                // Générer un token court terme
                $token = $this->tokenManager->generateJwt($userId);

                // Définir le cookie
                $this->setAuthCookies($token);

                setFlashMessage('success', 'Inscription réussie');

                echo json_encode([
                    'success' => true,
                    'redirect' => self::BASE_URL . "/user"
                ]);
                exit();
            } else {
                throw new Exception("Erreur lors de la création de l'utilisateur");
            }
        } catch (Exception $e) {
            logActivity('register_error', $e->getMessage(), [
                'email' => $data['email'] ?? 'non fourni',
                'error' => $e->getMessage()
            ], $userId, 'error');

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }

    // Traiter la connexion
    public function login()
    {
        try {
            // Récupérer les données
            $data = json_decode(file_get_contents("php://input"), true);
            if ($data === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = $_POST;
            }

            if (!isset($data['identifier']) || !isset($data['password'])) {
                throw new Exception('Email et mot de passe requis');
            }

            $attemptId = getUserAttemptId($data['identifier']);
            // Récupérer l'identifiant brut depuis POST ou $data
            $identifier = isset($_POST['identifier']) ? $_POST['identifier'] : $data['identifier'];
            $identifier = trim(htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8'));
            
            if (empty($identifier)) {
                throw new Exception('Identifiant invalide');
            }
            
            $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
            $rememberMe = isset($data['remember_me']) && $data['remember_me'] === 'on';

            // Authentifier l'utilisateur en verifiant son statut et son mot de passe
            $user = $this->user->authenticate($identifier, $password);

            if (isset($user) && $user) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                // Créer la session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'logged_in' => true,
                    'last_activity' => time()
                ];

                // Générer les tokens
                $token = $this->tokenManager->generateJwt($user['id']);
                $longTermToken = null;

                if ($rememberMe) {
                    /**
                     * TODO: Gérer le refresh token
                     */
                    $longTermTokenData = $this->tokenManager->generateLongTermToken($user['id']);
                    $longTermToken = $longTermTokenData['token'];
                }

                // Définir les cookies
                $this->setAuthCookies($token, $longTermToken);

                // Réponse JSON
                echo json_encode([
                    'success' => true,
                    'token' => $token,
                    'refresh_token' => $longTermToken,
                    'user' => $user,
                    'message' => 'Connexion reussie',
                    'redirect' => self::BASE_URL . "/user"
                ]);
                exit();
            } else {
                throw new Exception("Email ou mot de passe incorrect.");
            }
        } catch (Exception $e) {
            logActivity('login_error', $e->getMessage(), [
                'identifier' => $identifier ?? 'non fourni',
                'error' => $e->getMessage()
            ], $attemptId ?? null, 'error');

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }

    // Traiter la déconnexion
    public function logout()
    {
        try {
            // Révocation des tokens
            if (isset($_COOKIE['long_term_token'])) {
                $this->tokenManager->revokeToken($_COOKIE['long_term_token']);
            }
            $userId = isset($_SESSION['user']) && isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

            // Suppression des cookies
            setcookie("jwt_token", "", time() - 3600, "/");
            setcookie("long_term_token", "", time() - 3600, "/");
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            unset($_SESSION['user']);
            session_destroy();

            // Réponse JSON
            echo json_encode([
                'success' => true,
                'redirect' => self::BASE_URL
            ]);
            exit();
        } catch (Exception $e) {
            logActivity('logout_error', 'Erreur lors de la déconnexion', ['error' => $e->getMessage()], $userId ?? null, 'error');

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }

    // Afficher le profil
    public function profile()
    {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                redirect('/login');
            }

            $user = $this->user->find($_SESSION['user_id']);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }

            // Inclure la vue du profil
            require_once VIEWS_PATH . '/profile.php';
        } catch (Exception $e) {
            $_SESSION['notification'] = [
                'message' => 'Erreur de profil',
                'details' => $e->getMessage(),
                'type' => 'error'
            ];
            header("Location: " . self::BASE_URL . "/profile");
            throw new Exception($e->getMessage());
        }
    }

    // Mettre à jour le profil
    public function updateProfile()
    {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            // Vérifier si la requête est en POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            $this->validateCsrfToken();

            // Récupérer et valider les données
            $data = [
                'username' => filter_input(INPUT_POST, 'username', FILTER_DEFAULT) ?? '',
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '',
                'fullname' => filter_input(INPUT_POST, 'fullname', FILTER_DEFAULT) ?? null
            ];

            // Ajouter le mot de passe s'il est fourni
            if (!empty(filter_input(INPUT_POST, 'password', FILTER_DEFAULT))) {
                $data['password'] = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
            }

            // Décode les entités HTML si présentes
            $data['fullnname'] = html_entity_decode($data['fullnname'], ENT_QUOTES, 'UTF-8');
            $data['username'] = html_entity_decode($data['username'], ENT_QUOTES, 'UTF-8');

            // Mettre à jour l'utilisateur
            $this->user->update($_SESSION['user_id'], $data);

            $_SESSION['notification'] = [
                'message' => 'Profil mis à jour avec succès !',
                'details' => 'Profil mis à jour avec succès !',
                'type' => 'success'
            ];
            header("Location: " . self::BASE_URL . "/profile");
            exit();
        } catch (Exception $e) {
            $_SESSION['notification'] = [
                'message' => 'Erreur de profil',
                'details' => $e->getMessage(),
                'type' => 'error'
            ];
            header("Location: " . self::BASE_URL . "/profile");
            throw new Exception($e->getMessage());
        }
    }

    // Afficher le formulaire de réinitialisation du mot de passe
    public function forgotPassword()
    {
        try {
            // =======
            //     public function forgotPassword() {
            //         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //             try {
            //                 $email = htmlspecialchars($_POST['email'] ?? '');
            //                 $user = $this->user->findByEmail($email);

            //                 if ($user) {
            //                     // Générer un token de réinitialisation
            //                     $token = bin2hex(random_bytes(32));
            //                     $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            // >>>>>>> frontend
            // Si c'est une requête API avec des données JSON
            $jsonData = json_decode(file_get_contents('php://input'), true);
            if ($jsonData && isset($jsonData['email'])) {
                try {
                    $email = filter_var($jsonData['email'], FILTER_VALIDATE_EMAIL);
                    if (!$email) {
                        throw new Exception('Adresse email invalide');
                    }

                    $user = $this->user->findByEmail($email);
                    if ($user) {
                        // Générer un token de réinitialisation
                        $token = bin2hex(random_bytes(32));
                        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                        // Enregistrer le token dans la base de données
                        $query = "INSERT INTO password_resets (user_id, token, expiry) VALUES (:user_id, :token, :expiry)";
                        $stmt = $this->db->prepare($query);
                        $stmt->bindParam(':user_id', $user['id'], \PDO::PARAM_INT);
                        $stmt->bindParam(':token', $token);
                        $stmt->bindParam(':expiry', $expiry);
                        $stmt->execute();

                        // En production, envoi d'email
                        // sendResetPasswordEmail($email, $token);

                        // Pour des fins de test, on affiche le token
                        $resetLink = self::BASE_URL . '/reset-password?token=' . $token;

                        $this->sendResponse([
                            'success' => true,
                            'message' => 'Si votre email existe dans notre base de données, vous recevrez les instructions de réinitialisation.',
                            // En production, ne pas envoyer le token dans la réponse
                            'debug' => [
                                'token' => $token,
                                'reset_link' => $resetLink
                            ]
                        ]);
                    } else {
                        // Ne pas indiquer si l'email existe ou non pour des raisons de sécurité
                        $this->sendResponse([
                            'success' => true,
                            'message' => 'Si votre email existe dans notre base de données, vous recevrez les instructions de réinitialisation.'
                        ]);
                    }
                } catch (Exception $e) {
                    $this->sendResponse([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 400);
                    throw new Exception($e->getMessage());
                }
                return;
            }

            // Si c'est une requête POST formulaire
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $email = filter_var(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
                    if (!$email) {
                        throw new Exception('Adresse email invalide');
                    }

                    $user = $this->user->findByEmail($email);
                    if ($user) {
                        // Générer un token de réinitialisation
                        $token = bin2hex(random_bytes(32));
                        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                        // Enregistrer le token dans la base de données
                        $query = "INSERT INTO password_resets (user_id, token, expiry) VALUES (:user_id, :token, :expiry)";
                        $stmt = $this->db->prepare($query);
                        $stmt->bindParam(':user_id', $user['id'], \PDO::PARAM_INT);
                        $stmt->bindParam(':token', $token);
                        $stmt->bindParam(':expiry', $expiry);
                        $stmt->execute();

                        // En production, envoi d'email
                        // sendResetPasswordEmail($email, $token);

                        // Pour des fins de test/développement, stocker le lien dans la session
                        $_SESSION['reset_link'] = self::BASE_URL . '/reset-password?token=' . $token;
                    }

                    // Ne pas indiquer si l'email existe ou non pour des raisons de sécurité
                    $_SESSION['notification'] = [
                        'message' => 'Si votre email existe dans notre base de données, vous recevrez les instructions de réinitialisation.',
                        'type' => 'info'
                    ];
                    header("Location: " . self::BASE_URL . "/login");
                    exit();
                } catch (Exception $e) {
                    $_SESSION['notification'] = [
                        'message' => 'Erreur de réinitialisation du mot de passe',
                        'details' => $e->getMessage(),
                        'type' => 'error'
                    ];
                    header("Location: " . self::BASE_URL . "/forgot-password");
                    exit();
                }
            }

            // Afficher le formulaire de demande de réinitialisation de mot de passe
            require_once VIEWS_PATH . '/auth/forgot-password.php';
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
            throw new Exception($e->getMessage());
        }
    }

    // Réinitialiser le mot de passe
    public function resetPassword()
    {
        try {
            // Récupérer les données du corps de la requête
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                $data = $_POST;
            }

            // Valider les données
            if (!isset($data['token']) || !isset($data['password']) || empty($data['password'])) {
                throw new Exception('Token et nouveau mot de passe requis');
            }

            $token = $data['token'];
            $password = $data['password'];

            // Validation du mot de passe
            if (strlen($password) < 8) {
                throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
            }

            // Récupérer l'utilisateur avec ce token
            $query = "SELECT user_id FROM password_resets WHERE token = :token AND expiry > NOW() LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new Exception('Token invalide ou expiré');
            }

            $userId = $result['user_id'];

            // Mettre à jour le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password = :password WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $success = $updateStmt->execute();

            if ($success) {
                // Supprimer le token
                $deleteQuery = "DELETE FROM password_resets WHERE token = :token";
                $deleteStmt = $this->db->prepare($deleteQuery);
                $deleteStmt->bindParam(':token', $token);
                $deleteStmt->execute();

                // Log activity
                logActivity('password_reset', 'Mot de passe réinitialisé avec succès', [
                    'user_id' => $userId
                ], $userId, 'info');

                $this->sendResponse([
                    'success' => true,
                    'message' => 'Mot de passe réinitialisé avec succès'
                ]);
            } else {
                throw new Exception('Erreur lors de la réinitialisation du mot de passe');
            }
        } catch (Exception $e) {
            // Log error
            logActivity('password_reset_error', $e->getMessage(), [
                'error' => $e->getMessage()
            ], $userId, 'error');

            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
            throw new Exception($e->getMessage());
        }
    }

    public function checkEmail(string $email)
    {
        try {
            // Vérifier si l'email est vide
            if (!isset($email) || empty($email)) {
                sendResponse(400, ['error' => 'Email requis']);
                return;
            }

            // Vérifier si l'email existe déjà
            $user = $this->user->findByEmail($email);

            sendResponse(200, [
                'exists' => $user !== false
            ]);
        } catch (Exception $e) {
            error_log("Erreur de vérification de l'email : " . $e->getMessage());
            sendResponse(500, ['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }

    public function checkUsername(string $username)
    {
        try {
            // Vérifier si le nom d'utilisateur est vide
            if (empty($username)) {
                sendResponse(400, ['error' => 'Nom d\'utilisateur requis']);
                return;
            }

            // Vérifier si le nom d'utilisateur existe déjà
            $user = $this->user->findByUsername($username);

            sendResponse(200, [
                'exists' => $user !== false
            ]);
        } catch (Exception $e) {
            error_log("Erreur de vérification du nom d'utilisateur : " . $e->getMessage());
            sendResponse(500, ['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }

    private function generateToken($userId)
    {
        // En production, utilisez une bibliothèque JWT sécurisée
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode([
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // 24 heures
        ]));
        $signature = hash_hmac('sha256', "$header.$payload", $_ENV['JWT_SECRET'] ?? 'your-256-bit-secret');

        return "$header.$payload.$signature";
    }

    private function sendResponse($data, $statusCode = 200)
    {
        try {
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

function getUserAttemptId($email)
{
    global $db;
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['id'] ?? null;
}

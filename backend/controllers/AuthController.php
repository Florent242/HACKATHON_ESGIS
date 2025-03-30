<?php

namespace Auth\Controller;

use Exception;
use Auth\Model\Database;
use Auth\Model\User;
use PDO;
use PDOException;

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

        // Générer un token CSRF s'il n'existe pas
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function validateCsrfToken()
    {
        if (
            !isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            throw new Exception('Token CSRF invalide');
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
                'role'        => 'participant'
            ];

            error_log("Tentative d'inscription avec les données : " . json_encode($data));
            if (!$data && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = $_POST;
            }

            error_log("Tentative d'inscription avec les données : " . json_encode($data));

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

            // Hash du mot de passe avant création
            $userId = $this->user->create($data);

            if ($userId) {
                error_log("Utilisateur créé avec succès. ID: " . $userId);
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $data['username'];
                $_SESSION['role'] = $data['role'];
                setFlashMessage('success', 'Inscription réussie');

                // Redirection selon le rôle

                header("Location: " . self::BASE_URL . "/auth");

                exit();
            } else {
                throw new Exception("Erreur lors de la création de l'utilisateur");
            }
        } catch (Exception $e) {
            error_log("Erreur d'inscription : " . $e->getMessage());

            logActivity('register_error', $e->getMessage(), [
                'email' => $data['email'] ?? 'non fourni',
                'error' => $e->getMessage()
            ], 'error');
            // un echo pour les requetes frontend
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            throw new Exception($e->getMessage());
        }
    }

    // Traiter la connexion
    public function login()
    {
        try {
            // Get JSON data
            $data = json_decode(file_get_contents("php://input"), true);
            if ($data === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = $_POST;
            }

            if (!isset($data['email']) || !isset($data['password'])) {
                throw new Exception('Email et mot de passe requis' . print_r($data, true) . 'auth');
            }

            $email = $data['email'];
            $password = $data['password'];
            // =======
            //             $user = $this->user->findByEmail($email);

            //             if ($user && password_verify($password, $user['mot_de_passe'])) {
            //                 // Créer un token JWT
            //                 $jwt = $this->generateToken($user['id']);

            //                 // Stocker les informations de session
            //                 $_SESSION['user_id'] = htmlspecialchars($user['id']);
            //                 $_SESSION['username'] = htmlspecialchars($user['username']);
            //                 $_SESSION['role'] = htmlspecialchars($user['role']);
            //                 setFlashMessage('success', 'Connexion réussie');
            // >>>>>>> frontend

            // Log the login attempt (without password)
            logActivity('login_attempt', 'Tentative de connexion', ['email' => $email], 'info');

            $user = $this->user->authenticate($email, $password);

            if ($user) {
                // Login successful
                $_SESSION['user'] = $user;
                $_SESSION['is_logged_in'] = true;

                // Log successful login
                logActivity('login_success', 'Connexion réussie', [
                    'user_id' => $user['id'],
                    'role' => $user['role']
                ], 'info');

                // un echo pour les requetes frontend
                echo json_encode([
                    'success' => true,
                    'user' => $user
                ]);
                setFlashMessage('success', 'Connexion réussie');
                header("Location: " . self::BASE_URL . "/user");
                exit();
            } else {
                // Log failed login
                logActivity('login_failed', 'Échec de connexion', ['email' => $email], 'warning');

                throw new Exception('Email ou mot de passe incorrect');
            }
        } catch (Exception $e) {
            // Log exception
            logActivity('login_error', $e->getMessage(), [
                'email' => $email ?? 'non fourni',
                'error' => $e->getMessage()
            ], 'error');

            // un echo pour les requetes frontend
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            throw new Exception($e->getMessage());
        }
    }

    // Traiter la déconnexion
    public function logout()
    {
        try {
            // Log user logout before clearing session
            if (isset($_SESSION['user'])) {
                logActivity('logout', 'Déconnexion réussie', [
                    'user_id' => $_SESSION['user']['id'],
                    'role' => $_SESSION['user']['role']
                ], 'info');
            }

            // Détruire la session
            session_unset();
            session_destroy();

            // Rediriger vers la page d'accueil
            header('Location: ' . self::BASE_URL);
            exit();
        } catch (Exception $e) {
            logActivity('logout_error', 'Erreur lors de la déconnexion', ['error' => $e->getMessage()], 'error');

            // Gérer l'erreur
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            throw new Exception($e->getMessage());
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
                ], 'info');

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
            ], 'error');

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

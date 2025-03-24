<?php
namespace Auth\Controller;

use Exception;
use Auth\Model\Database;
use Auth\Model\User;

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../includes/functions.php';

class AuthController {
    private const BASE_URL = '/HACKATHON_ESGIS/public';
    private $user;
    private $db;

    public function __construct($db = null) {
        session_start();
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

    private function validateCsrfToken() {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Token CSRF invalide');
        }
    }

    // Traiter l'inscription
    public function register()
    {
        try {
            // Vérifier le token CSRF
            $this->validateCsrfToken();

            $data = [
                'username' => $_POST['username'] ?? '',
                'nom_complet' => $_POST['fullName'] ?? '',
                'email' => $_POST['email'] ?? '',
                'mot_de_passe' => $_POST['password'] ?? '',
                'role' => 'participant'
            ];

            error_log("Tentative d'inscription avec les données : " . json_encode($data));

            // Validation des données
            if (empty($data['username']) || empty($data['email']) || empty($data['mot_de_passe'])) {
                throw new Exception("Tous les champs sont obligatoires");
            }

            // Validation de l'email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Format d'email invalide");
            }

            // Validation du mot de passe
            if (strlen($data['mot_de_passe']) < 8) {
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
                if ($data['role'] === 'organisateur') {
                    header("Location: " . self::BASE_URL . "/auth_admin");
                } else {
                    header("Location: " . self::BASE_URL . "/auth");
                }
                exit();
            } else {
                throw new Exception("Erreur lors de la création de l'utilisateur");
            }
        } catch (Exception $e) {
            error_log("Erreur d'inscription : " . $e->getMessage());
            $_SESSION['notification'] = [
                'message' => 'Erreur d\'inscription',
                'details' => $e->getMessage(),
                'type' => 'error'
            ];
            header("Location: " . self::BASE_URL . "/auth");
            exit();
        }
    }

    // Traiter la connexion
    public function login() {
        try {
            // Get JSON data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['email']) || !isset($data['password'])) {
                throw new Exception('Email et mot de passe requis');
            }

            $email = $data['email'];
            $password = $data['password'];

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

                echo json_encode([
                    'success' => true,
                    'user' => $user
                ]);
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

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // Traiter la déconnexion
    public function logout() {
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
        }
    }

    // Afficher le profil
    public function profile() {
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
            exit();
        }
    }

    // Mettre à jour le profil
    public function updateProfile() {
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
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'full_name' => $_POST['full_name'] ?? null
            ];

            // Ajouter le mot de passe s'il est fourni
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }

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
            exit();
        }
    }

    // Afficher le formulaire de réinitialisation du mot de passe
    public function forgotPassword() {
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

                    $this->jsonResponse([
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
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Si votre email existe dans notre base de données, vous recevrez les instructions de réinitialisation.'
                    ]);
                }
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            return;
        }

        // Si c'est une requête POST formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
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
    }

    // Réinitialiser le mot de passe
    public function resetPassword() {
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

                $this->jsonResponse([
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

            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    private function generateToken($userId) {
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

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

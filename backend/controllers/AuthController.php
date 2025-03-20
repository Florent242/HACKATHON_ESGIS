<?php
namespace Auth\Controller;

use Exception;
use Auth\Model\Database;
use Auth\Model\User;

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private const BASE_URL = 'http://localhost:222/HACKATHON_ESGIS/public';
    private $user;
    private $db;

    public function __construct() {
        session_start();
        $database = Database::getInstance();
        $this->db = $database->getConnection();
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
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => 'participant'
            ];
            
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
                
                // Redirection selon le rôle
                if ($data['role'] === 'organisateur') {
                    header("Location: " . self::BASE_URL . "/admin");
                } else {
                    header("Location: " . self::BASE_URL . "/user");
                }
                exit();
            } else {
                throw new Exception("Erreur lors de la création de l'utilisateur");
            }
        } catch (Exception $e) {
            error_log("Erreur d'inscription : " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header("Location: " . self::BASE_URL . "/auth");
            exit();
        }
    }

    // Traiter la connexion
    public function login() {
        try {
            // Vérifier le token CSRF
            $this->validateCsrfToken();

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                throw new Exception('Email et mot de passe requis');
            }

            $user = $this->user->findByEmail($email);
            
            if ($user && password_verify($password, $user['hashed_password'])) {
                // Créer un token JWT
                $jwt = $this->generateToken($user['id']);
                
                // Stocker les informations de session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nom'];
                $_SESSION['role'] = $user['role'];

                // Redirection selon le rôle
                if ($user['role'] === 'organisateur') {
                    header("Location: " . self::BASE_URL . "/admin");
                } else {
                    header("Location: " . self::BASE_URL . "/user");
                }
                
                // Envoyer le token dans un cookie sécurisé
                setcookie(
                    'jwt',
                    $jwt,
                    time() + (60 * 60 * 24), // 24 heures
                    '/',
                    '',
                    true, // Secure (HTTPS)
                    true // HttpOnly
                );
                
                exit();
            } else {
                throw new Exception('Email ou mot de passe incorrect');
            }
        } catch (Exception $e) {
            error_log("Erreur de connexion : " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header("Location: " . self::BASE_URL . "/auth");
            exit();
        }
    }

    // Traiter la déconnexion
    public function logout() {
        try {
            // Vérifier si la requête est en POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Supprimer le token (côté client)
            // Détruire la session
            session_destroy();

            // Rediriger vers la page d'accueil
            setFlashMessage('success', 'Vous avez été déconnecté avec succès.');
            redirect('/');

        } catch (Exception $e) {
            setFlashMessage('error', $e->getMessage());
            redirect('/');
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
            setFlashMessage('error', $e->getMessage());
            redirect('/');
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

            setFlashMessage('success', 'Profil mis à jour avec succès !');
            redirect('/profile');

        } catch (Exception $e) {
            setFlashMessage('error', $e->getMessage());
            redirect('/profile');
        }
    }

    // Afficher le formulaire de réinitialisation du mot de passe
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = cleanInput($_POST['email']);
                $user = $this->user->findByEmail($email);

                if ($user) {
                    // Générer un token de réinitialisation
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // Stocker le token dans la base de données
                    // Envoyer l'email de réinitialisation
                    // Note : À implémenter selon vos besoins

                    setFlashMessage('success', 'Si votre email existe dans notre base de données, vous recevrez les instructions de réinitialisation.');
                    redirect('/login');
                }

            } catch (Exception $e) {
                setFlashMessage('error', $e->getMessage());
            }
        }

        // Afficher le formulaire
        require_once VIEWS_PATH . '/auth/forgot-password.php';
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

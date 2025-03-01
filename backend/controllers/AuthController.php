<?php

class AuthController {
    private $user;
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // Traiter l'inscription
    public function register() {
        try {
            // Vérifier si la requête est en POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Récupérer et valider les données
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => 'participant', // Par défaut
                'full_name' => $_POST['full_name'] ?? null
            ];

            // Créer l'utilisateur
            $userId = $this->user->create($data);

            // Générer le token JWT
            $token = $this->generateToken($userId);

            // Démarrer la session
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_role'] = $data['role'];

            // Rediriger avec un message de succès
            setFlashMessage('success', 'Inscription réussie ! Bienvenue sur la plateforme.');
            redirect('/profile');

        } catch (Exception $e) {
            setFlashMessage('error', $e->getMessage());
            redirect('/register');
        }
    }

    // Traiter la connexion
    public function login() {
        try {
            // Vérifier si la requête est en POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Récupérer les identifiants
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Authentifier l'utilisateur
            $user = $this->user->authenticate($email, $password);

            if (!$user) {
                throw new Exception('Email ou mot de passe incorrect');
            }

            // Générer le token JWT
            $token = $this->generateToken($user['id']);

            // Démarrer la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];

            // Rediriger selon le rôle
            $redirect = '/profile';
            if ($user['role'] === 'organisateur') {
                $redirect = '/admin/dashboard';
            } elseif ($user['role'] === 'juge') {
                $redirect = '/jury/dashboard';
            }

            setFlashMessage('success', 'Connexion réussie !');
            redirect($redirect);

        } catch (Exception $e) {
            setFlashMessage('error', $e->getMessage());
            redirect('/login');
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
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

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

<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/Controller.php';

class AuthController extends Controller {
    private $user;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->user = new User($this->db);
    }

    public function login() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['email', 'password'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $user = $this->user->findByEmail($_POST['email']);
            if (!$user || !password_verify($_POST['password'], $user['password'])) {
                throw new Exception('Email ou mot de passe incorrect');
            }

            // Créer la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];

            // Ne pas renvoyer le mot de passe
            unset($user['password']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function register() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['nom', 'prenom', 'email', 'password'];
            $this->validateRequiredFields($_POST, $requiredFields);

            // Vérifier si l'email existe déjà
            if ($this->user->findByEmail($_POST['email'])) {
                throw new Exception('Cette adresse email est déjà utilisée');
            }

            // Validation du mot de passe
            if (strlen($_POST['password']) < 8) {
                throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
            }

            $data = [
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'role' => 'participant',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->user->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Inscription réussie',
                'data' => ['id' => $userId]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function logout() {
        try {
            $this->validateMethod('POST');
            
            // Détruire la session
            session_destroy();
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function resetPassword() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['email'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $user = $this->user->findByEmail($_POST['email']);
            if (!$user) {
                throw new Exception('Adresse email non trouvée');
            }

            // Générer un token unique
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Sauvegarder le token dans la base de données
            $this->user->update($user['id'], [
                'reset_token' => $token,
                'reset_token_expiry' => $expiry
            ]);

            // TODO: Envoyer l'email avec le lien de réinitialisation
            // Pour l'instant, on renvoie juste le token
            $this->jsonResponse([
                'success' => true,
                'message' => 'Instructions envoyées par email',
                'data' => ['token' => $token] // À supprimer en production
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function confirmResetPassword() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['token', 'new_password'];
            $this->validateRequiredFields($_POST, $requiredFields);

            if (strlen($_POST['new_password']) < 8) {
                throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
            }

            // Vérifier le token
            $user = $this->user->findByResetToken($_POST['token']);
            if (!$user) {
                throw new Exception('Token invalide');
            }

            // Vérifier si le token n'a pas expiré
            if (strtotime($user['reset_token_expiry']) < time()) {
                throw new Exception('Le lien de réinitialisation a expiré');
            }

            // Mettre à jour le mot de passe
            $this->user->update($user['id'], [
                'password' => password_hash($_POST['new_password'], PASSWORD_DEFAULT),
                'reset_token' => null,
                'reset_token_expiry' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function checkAuth() {
        try {
            $this->validateMethod('GET');
            
            if (!isAuthenticated()) {
                throw new Exception('Non authentifié');
            }

            $user = $this->user->find($_SESSION['user_id']);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }

            unset($user['password']);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 401);
        }
    }
}

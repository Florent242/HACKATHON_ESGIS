<?php
namespace Auth\Controller;

use Exception;

if(!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if(!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if(!class_exists('User')) {
    require_once __DIR__ . '/../models/User.php';
}
if(!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}

class UserController extends Controller {
    private $user;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->user = new \Auth\Model\User($this->db);
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

            // Hasher le mot de passe
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $data = [
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'email' => $_POST['email'],
                'password' => $hashedPassword,
                'role' => $_POST['role'] ?? 'participant',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->user->create($data);
            $userId = $result;

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

    public function login() {
        try {
            $this->validateMethod('POST');

            $requiredFields = ['email', 'password'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $result = $this->user->authenticate($_POST['email'], $_POST['password']);
            if (!$result) {
                throw new Exception('Email ou mot de passe incorrect');
            }

            // Créer la session
            $user = $this->user->find($result['id']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];

            unset($user['password']); // Ne pas renvoyer le mot de passe

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

    public function logout() {
        try {
            $this->validateMethod('POST');

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

    public function get($id) {
        try {
            $this->validateMethod('GET');

            $user = $this->user->find($id);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }

            unset($user['password']); // Ne pas renvoyer le mot de passe

            $this->jsonResponse([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update($id) {
        try {
            $this->validateMethod('POST');

            // Vérifier si l'utilisateur modifie son propre profil ou est admin
            if ($_SESSION['user_id'] != $id && !hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            $updatableFields = ['nom', 'prenom', 'email', 'role'];
            $data = $this->filterData($_POST, $updatableFields);

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            // Si l'email est modifié, vérifier qu'il n'existe pas déjà
            if (isset($data['email'])) {
                $existingUser = $this->user->findByEmail($data['email']);
                if ($existingUser && $existingUser['id'] != $id) {
                    throw new Exception('Cette adresse email est déjà utilisée');
                }
            }

            // Seul l'admin peut changer le rôle
            if (isset($data['role']) && !hasRole('admin')) {
                unset($data['role']);
            }

            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->user->update($id, $data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Profil mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function updatePassword($id) {
        try {
            $this->validateMethod('POST');

            // Vérifier si l'utilisateur modifie son propre mot de passe
            if ($_SESSION['user_id'] != $id) {
                throw new Exception('Non autorisé');
            }

            $requiredFields = ['old_password', 'new_password'];
            $this->validateRequiredFields($_POST, $requiredFields);

            // Vérifier l'ancien mot de passe
            $user = $this->user->find($id);
            if (!password_verify($_POST['old_password'], $user['password'])) {
                throw new Exception('Ancien mot de passe incorrect');
            }

            // Hasher le nouveau mot de passe
            $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

            $this->user->update($id, [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function delete($id) {
        try {
            $this->validateMethod('POST');

            if (!hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            if (!$this->user->delete($id)) {
                throw new Exception('Erreur lors de la suppression de l\'utilisateur');
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Met à jour le rôle d'un utilisateur
     * @param int $id ID de l'utilisateur
     */
    public function updateRole($id) {
        try {
            $this->validateMethod('POST');

            // Vérifier si l'utilisateur a les droits d'administrateur
            if (!hasRole('admin')) {
                throw new Exception('Non autorisé - Réservé aux administrateurs');
            }

            if (empty($_POST['role'])) {
                throw new Exception('Le rôle est requis');
            }

            $role = $_POST['role'];
            $allowedRoles = ['participant', 'organisateur', 'jury', 'admin'];

            if (!in_array($role, $allowedRoles)) {
                throw new Exception('Rôle non valide');
            }

            $this->user->update($id, [
                'role' => $role,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Rôle mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getJuryList() {
        try {
            $this->validateMethod('GET');

            $jurys = $this->user->getByRole('jury');

            $this->jsonResponse([
                'success' => true,
                'data' => $jurys
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getProfile() {
        try {
            $this->validateMethod('GET');

            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            $user = $this->user->find($_SESSION['user_id']);
            unset($user['password']);

            $this->jsonResponse([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}

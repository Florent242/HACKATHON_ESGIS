<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/EquipeMembre.php';
require_once __DIR__ . '/../models/Notification.php';

class EquipeMembreController {
    private $equipemembreModel;
    private $notificationModel;

    public function __construct() {
        $this->equipemembreModel = new EquipeMembre();
        $this->notificationModel = new Notification();
    }

    // Afficher les membres d'une équipe
    public function index($equipeId) {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            // Récupérer les membres
            $membres = $this->equipemembreModel->getByEquipe($equipeId);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['membres' => $membres]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/equipe-membre/index.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/equipes/{$equipeId}");
        }
    }

    // Ajouter un membre à l'équipe
    public function add($equipeId) {
        try {
            // Vérifier si l'utilisateur est connecté et leader de l'équipe
            if (!isAuthenticated() || !$this->equipemembreModel->isLeader($_SESSION['user_id'], $equipeId)) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Récupérer et nettoyer les données
            $userId = cleanInput($_POST['user_id']);

            // Créer le membre
            $data = [
                'equipe_id' => $equipeId,
                'user_id' => $userId,
                'role' => 'member'
            ];

            $membreId = $this->equipemembreModel->create($data);

            // Notifier le nouveau membre
            $this->notificationModel->create([
                'user_id' => $userId,
                'type' => 'info',
                'message' => "Vous avez été ajouté à l'équipe",
                'link' => "/equipes/{$equipeId}"
            ]);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['message' => 'Membre ajouté avec succès']);
            }

            setFlashMessage('success', 'Membre ajouté avec succès');
            redirect("/equipes/{$equipeId}/membres");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/equipes/{$equipeId}/membres");
        }
    }

    // Promouvoir un membre comme leader
    public function promote($id) {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Récupérer le membre
            $membre = $this->equipemembreModel->find($id);
            if (!$membre) {
                throw new Exception('Membre non trouvé');
            }

            // Vérifier si l'utilisateur est le leader actuel
            if (!$this->equipemembreModel->isLeader($_SESSION['user_id'], $membre['equipe_id'])) {
                throw new Exception('Non autorisé à promouvoir des membres');
            }

            // Mettre à jour le rôle
            $this->equipemembreModel->updateRole($id, 'leader');

            // Notifier le membre promu
            $this->notificationModel->create([
                'user_id' => $membre['user_id'],
                'type' => 'success',
                'message' => "Vous avez été promu leader de l'équipe",
                'link' => "/equipes/{$membre['equipe_id']}"
            ]);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['message' => 'Membre promu avec succès']);
            }

            setFlashMessage('success', 'Membre promu avec succès');
            redirect("/equipes/{$membre['equipe_id']}/membres");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/equipes/{$membre['equipe_id']}/membres");
        }
    }

    // Rétrograder un leader en membre
    public function demote($id) {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Récupérer le membre
            $membre = $this->equipemembreModel->find($id);
            if (!$membre) {
                throw new Exception('Membre non trouvé');
            }

            // Vérifier si l'utilisateur est un autre leader de l'équipe
            if (!$this->equipemembreModel->isLeader($_SESSION['user_id'], $membre['equipe_id']) 
                || $_SESSION['user_id'] === $membre['user_id']) {
                throw new Exception('Non autorisé à rétrograder ce membre');
            }

            // Mettre à jour le rôle
            $this->equipemembreModel->updateRole($id, 'member');

            // Notifier le membre rétrogradé
            $this->notificationModel->create([
                'user_id' => $membre['user_id'],
                'type' => 'warning',
                'message' => "Vous avez été rétrogradé au rôle de membre dans l'équipe",
                'link' => "/equipes/{$membre['equipe_id']}"
            ]);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['message' => 'Membre rétrogradé avec succès']);
            }

            setFlashMessage('success', 'Membre rétrogradé avec succès');
            redirect("/equipes/{$membre['equipe_id']}/membres");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/equipes/{$membre['equipe_id']}/membres");
        }
    }

    // Retirer un membre de l'équipe
    public function remove($id) {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Récupérer le membre
            $membre = $this->equipemembreModel->find($id);
            if (!$membre) {
                throw new Exception('Membre non trouvé');
            }

            // Vérifier si l'utilisateur est le leader ou le membre lui-même
            if (!$this->equipemembreModel->isLeader($_SESSION['user_id'], $membre['equipe_id']) 
                && $_SESSION['user_id'] !== $membre['user_id']) {
                throw new Exception('Non autorisé à retirer ce membre');
            }

            // Retirer le membre
            $this->equipemembreModel->remove($id);

            // Notifier le membre retiré si ce n'est pas lui qui quitte
            if ($_SESSION['user_id'] !== $membre['user_id']) {
                $this->notificationModel->create([
                    'user_id' => $membre['user_id'],
                    'type' => 'warning',
                    'message' => "Vous avez été retiré de l'équipe",
                    'link' => "/hackathons/{$membre['hackathon_id']}"
                ]);
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['message' => 'Membre retiré avec succès']);
            }

            setFlashMessage('success', 'Membre retiré avec succès');
            redirect("/equipes/{$membre['equipe_id']}/membres");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/equipes/{$membre['equipe_id']}/membres");
        }
    }

    // Afficher mes équipes
    public function myTeams() {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            // Récupérer les équipes
            $teams = $this->equipemembreModel->getByUser($_SESSION['user_id']);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['teams' => $teams]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/equipe-membre/my-teams.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/dashboard');
        }
    }
}

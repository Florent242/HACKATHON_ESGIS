<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Participant.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/Controller.php';

class ParticipantController extends Controller {
    private $participant;
    private $notification;
    private $db;

    public function __construct() {
        parent::__construct();
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->participant = new Participant($this->db);
        $this->notification = new Notification($this->db);
    }

    // Afficher les participants d'un hackathon
    public function index($hackathonId) {
        try {
            // Vérifier si l'utilisateur est un organisateur
            if (!isAuthenticated() || !hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            // Récupérer le statut filtré si présent
            $status = isset($_GET['status']) ? cleanInput($_GET['status']) : null;

            // Récupérer les participants
            $participants = $this->participant->getByHackathon($hackathonId, $status);
            $counts = $this->participant->countByStatus($hackathonId);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => [
                        'participants' => $participants,
                        'counts' => $counts
                    ]
                ]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/participant/index.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/hackathons/{$hackathonId}");
        }
    }

    // S'inscrire à un hackathon
    public function register($hackathonId) {
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

            // Enregistrer le participant
            $participantId = $this->participant->create([
                'user_id' => $_SESSION['user_id'],
                'hackathon_id' => $hackathonId
            ]);

            // Créer une notification
            $this->notification->create([
                'user_id' => $_SESSION['user_id'],
                'title' => 'Inscription au hackathon',
                'message' => 'Votre inscription a été enregistrée avec succès',
                'type' => 'success'
            ]);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Inscription réussie',
                    'data' => [
                        'participant_id' => $participantId
                    ]
                ]);
            }

            setFlashMessage('success', 'Inscription réussie');
            redirect("/hackathons/{$hackathonId}");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/hackathons/{$hackathonId}");
        }
    }

    // Approuver une inscription
    public function approve($id) {
        try {
            // Vérifier si l'utilisateur est un organisateur
            if (!isAuthenticated() || !hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Récupérer l'inscription
            $participant = $this->participant->find($id);
            if (!$participant) {
                throw new Exception('Inscription non trouvée');
            }

            // Mettre à jour le statut
            $this->participant->updateStatus($id, 'approved');

            // Créer une notification
            $this->notification->create([
                'user_id' => $participant['user_id'],
                'title' => 'Statut de participation mis à jour',
                'message' => "Votre statut de participation a été mis à jour : approuvé",
                'type' => 'success'
            ]);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Inscription approuvée'
                ]);
            }

            setFlashMessage('success', 'Inscription approuvée');
            redirect("/hackathons/{$participant['hackathon_id']}/participants");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/hackathons/{$participant['hackathon_id']}/participants");
        }
    }

    // Rejeter une inscription
    public function reject($id) {
        try {
            // Vérifier si l'utilisateur est un organisateur
            if (!isAuthenticated() || !hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Récupérer l'inscription
            $participant = $this->participant->find($id);
            if (!$participant) {
                throw new Exception('Inscription non trouvée');
            }

            // Mettre à jour le statut
            $this->participant->updateStatus($id, 'rejected');

            // Créer une notification
            $this->notification->create([
                'user_id' => $participant['user_id'],
                'title' => 'Statut de participation mis à jour',
                'message' => "Votre statut de participation a été mis à jour : rejeté",
                'type' => 'error'
            ]);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Inscription rejetée'
                ]);
            }

            setFlashMessage('success', 'Inscription rejetée');
            redirect("/hackathons/{$participant['hackathon_id']}/participants");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/hackathons/{$participant['hackathon_id']}/participants");
        }
    }

    // Annuler son inscription
    public function cancel($id) {
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

            // Récupérer l'inscription
            $participant = $this->participant->find($id);
            if (!$participant) {
                throw new Exception('Inscription non trouvée');
            }

            // Vérifier si l'utilisateur est le propriétaire de l'inscription
            if ($participant['user_id'] !== $_SESSION['user_id']) {
                throw new Exception('Non autorisé à annuler cette inscription');
            }

            // Annuler l'inscription
            $this->participant->cancel($id);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Inscription annulée'
                ]);
            }

            setFlashMessage('success', 'Inscription annulée');
            redirect("/hackathons/{$participant['hackathon_id']}");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/hackathons/{$participant['hackathon_id']}");
        }
    }

    // Afficher mes participations
    public function myParticipations() {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            // Récupérer les participations
            $participations = $this->participant->getByUser($_SESSION['user_id']);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => [
                        'participations' => $participations
                    ]
                ]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/participant/my-participations.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/dashboard');
        }
    }
}

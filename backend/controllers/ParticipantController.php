<?php
namespace Auth\Controller;

use Exception;

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Participant.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/Controller.php';

class ParticipantController extends Controller {
    private $participant;
    private $notification;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
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

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'participants' => $participants,
                    'counts' => $counts
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
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

            $this->jsonResponse([
                'success' => true,
                'message' => 'Inscription réussie',
                'data' => [
                    'participant_id' => $participantId
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
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

            $this->jsonResponse([
                'success' => true,
                'message' => 'Inscription approuvée'
            ]);
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

            $this->jsonResponse([
                'success' => true,
                'message' => 'Inscription annulée'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
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

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'participations' => $participations
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function create() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['hackathon_id', 'user_id'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $data = [
                'hackathon_id' => (int)$_POST['hackathon_id'],
                'user_id' => (int)$_POST['user_id'],
                'statut' => 'en_attente',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $participantId = $this->participant->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Participation créée avec succès',
                'data' => ['id' => $participantId]
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
            
            $participant = $this->participant->find($id);
            if (!$participant) {
                throw new Exception('Participant non trouvé');
            }
            
            $this->jsonResponse([
                'success' => true,
                'data' => $participant
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function getByHackathon($hackathonId) {
        try {
            $this->validateMethod('GET');
            
            $participants = $this->participant->getByHackathon($hackathonId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $participants
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function updateStatus($id) {
        try {
            $this->validateMethod('POST');
            
            if (!hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            $requiredFields = ['statut'];
            $this->validateRequiredFields($_POST, $requiredFields);

            if (!in_array($_POST['statut'], ['en_attente', 'accepte', 'refuse'])) {
                throw new Exception('Statut invalide');
            }

            $this->participant->update($id, [
                'statut' => $_POST['statut'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Statut mis à jour avec succès'
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

            $this->participant->delete($id);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Participant supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getStats($hackathonId) {
        try {
            $this->validateMethod('GET');
            
            $stats = [
                'total' => $this->participant->countByStatus($hackathonId, null),
                'en_attente' => $this->participant->countByStatus($hackathonId, 'en_attente'),
                'accepte' => $this->participant->countByStatus($hackathonId, 'accepte'),
                'refuse' => $this->participant->countByStatus($hackathonId, 'refuse')
            ];
            
            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}

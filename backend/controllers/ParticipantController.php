<?php

namespace Auth\Controller;

use Exception;
use Auth\Model\Participant;
use Auth\Model\Notification;

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if (!class_exists('Participant')) {
    require_once __DIR__ . '/../models/Participant.php';
}
if (!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}
if (!class_exists('Notification')) {
    require_once __DIR__ . '/../models/Notification.php';
}

class ParticipantController extends Controller
{
    private $participant;
    private $notification;
    private $db;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->participant = new Participant($this->db);
        $this->notification = new Notification($this->db);
    }

    // Afficher les participants d'un hackathon
    public function index($hackathonId)
    {
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
    public function register($hackathonId, $input = null)
    {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Enregistrer le participant
            $participantId = $this->participant->register([
                'user_id' => $_SESSION['user_id'],
                'hackathon_id' => $hackathonId
            ]);

            // Créer une notification
            $this->notification->create([
                'user_id' => $_SESSION['user']['id'] ?? 00,
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

    public function registerTeam($hackathonId, $input)
    {
        try {
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $teamId = $input['team_id'] ?? null;
            if (!$teamId) {
                throw new Exception("ID de l'équipe requis");
            }

            $captainId = $input['leader_id'];

            $success = $this->participant->registerTeam($hackathonId, $teamId, $captainId);

            if (!$success) {
                throw new Exception("Erreur lors de l'inscription de l'équipe");
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Équipe inscrite avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function updateTeamStatus($hackathonId, $input)
    {
        try {
            $userId = $this->tokenManager->getCurrentUserId();
            if (!$this->isAdmin($userId)) {
                throw new Exception('Action non autorisé');
            }

            $teamId = $input['team_id'] ?? throw new Exception ('ID de la team manquante');
            $status = $input['status'] ?? throw new Exception ('Statut manquant');

            if (!in_array($status, ['pending', 'active', 'disqualified', 'archived', 'rejected'])) {
                throw new Exception('Statut invalide');
            }

            // Mise à jour du statut de l'équipe
            $this->participant->updateTeamStatus($hackathonId, $teamId, $status);

            // Si le statut est rejeté ou désactivé, on peut aussi mettre à jour les participants
            if (in_array($status, ['disqualified', 'rejected','active'])) {
                $this->participant->updateTeamMembersStatus($hackathonId, $teamId, $status);
            }

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

    public function unregisterTeam($hackathonId, $teamId)
    {
        try {
            $this->validateMethod('POST');

            if (!hasRole('admin')) {
                throw new Exception("Action non autorisée");
            }

            $this->participant->unregisterTeam((int)$hackathonId, (int)$teamId);

            $this->jsonResponse([
                'success' => true,
                'message' => "L’équipe a été désinscrite du hackathon"
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Approuver une inscription
    public function approve($id)
    {
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
                throw new Exception('Token de session invalide');
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
    public function reject($id)
    {
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
                throw new Exception('Token de session invalide');
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
    public function cancel($id)
    {
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
                throw new Exception('Token de session invalide');
            }

            // Récupérer l'inscription
            $participant = $this->participant->find($id);
            if (!$participant) {
                throw new Exception('Inscription non trouvée');
            }

            // Vérifier si l'utilisateur est le propriétaire de l'inscription
            if ($participant['user_id'] !== $_SESSION['user']['id']) {
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
    public function myParticipations($jwt)
    {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            // Récupérer les participations
            $participations = $this->participant->getByUser($_SESSION['user']['id'], $jwt);

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

    // Récupérer un participant par son id
    public function get($id)
    {
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

    // Récupérer les participants d'un hackathon
    public function getByHackathon($hackathonId)
    {
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

    // Mettre à jour le statut d'un participant
    public function updateStatus($id, $input)
    {
        try {
            $this->validateMethod('POST');

            $currentUser = $this->tokenManager->getCurrentUserId();
            if (!$this->isAdmin($currentUser)) {
                throw new Exception('Non autorisé');
            }

            $requiredFields = ['status'];
            $this->validateRequiredFields($input, $requiredFields);

            if (!in_array($input['status'], ['pending', 'accepted', 'rejected'])) {
                throw new Exception('Statut invalide');
            }

            $this->participant->updateStatus($id, $input['status']);

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

    // Supprimer un participant
    public function delete($id)
    {
        try {
            $this->validateMethod('POST');

            $currentUser = $this->tokenManager->getCurrentUserId();
            if (!$this->isAdmin($currentUser)) {
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

    // Récupérer les statistiques d'un hackathon
    public function getStats($hackathonId)
    {
        try {
            $this->validateMethod('GET');

            $stats = [
                'total' => $this->participant->countByStatus($hackathonId, null),
                'pending' => $this->participant->countByStatus($hackathonId, 'pending'),
                'accepted' => $this->participant->countByStatus($hackathonId, 'accepted'),
                'refused' => $this->participant->countByStatus($hackathonId, 'refused')
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

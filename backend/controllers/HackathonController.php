<?php

namespace Auth\Controller;

use Exception;
use Auth\Model\Hackathon;
use Auth\Model\Phase;

if (!class_exists('Hackathon')) {
    require_once __DIR__ . '/../models/Hackathon.php';
}
if (!class_exists('Phase')) {
    require_once __DIR__ . '/../models/Phase.php';
}
if (!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}

class HackathonController extends Controller
{
    private $hackathon;
    private $phase;
    private $db;
    public $tokenManager;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->hackathon = new Hackathon($this->db);
        $this->phase = new Phase($this->db);
        $this->tokenManager = $tokenManager;
    }

    public function getActivePhase($hackathonId) {
        $userId = $this->tokenManager->getCurrentUserId(); // récupère l’ID user depuis session/jwt
        $phase = $this->phase->getActiveForUser($hackathonId, $userId);
    
        if (!$phase) {
            echo json_encode([
                'success' => false,
                'message' => "Aucune phase active disponible pour vous."
            ]);
            return;
        }
    
        echo json_encode([
            'success' => true,
            'phase_id' => $phase['id'],
            'title' => $phase['title'],
            'start_at' => $phase['start_at'],
            'end_at' => $phase['end_at']
        ]);
    }
    

    /**
     * Récupère tous les hackathons
     */
    public function getAll()
    {
        try {
            $this->validateMethod('GET');
            $hackathons = $this->hackathon->getAll();

            $this->jsonResponse([
                'success' => true,
                'data' => $hackathons
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function checkParticipation($userId, $hackathonId)
    {
        try {
            // Vérifier si l'utilisateur est participant au hackathon
            $query = "SELECT COUNT(*) FROM hackathon_participants 
                     WHERE user_id = :user_id 
                     AND hackathon_id = :hackathon_id 
                     AND participation_status = 'accepted'";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':user_id' => (int)$userId,
                ':hackathon_id' => (int)$hackathonId
            ]);

            if (!$stmt->fetchColumn() > 0) {
                return [
                    'success' => false,
                    'message' => 'Accès non autorisé ! Vous devez être participant au hackathon pour accéder à cette ressource.'
                ];
            }

            // Vérifier si l'utilisateur est membre d'une equipe participant au hackathon
            $query = "SELECT COUNT(*) FROM hackathon_teams ht
                     INNER JOIN hackathon_participants hp ON ht.team_id = hp.team_id
                     WHERE ht.hackathon_id = :hackathon_id 
                     AND hp.user_id = :user_id";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':hackathon_id' => (int)$hackathonId,
                ':user_id' => (int)$userId
            ]);

            if (!$stmt->fetchColumn() > 0) {
                return [
                    'success' => false,
                    'message' => 'Accès non autorisé ! Vous devez être membre d\'une equipe participant au hackathon pour accéder à cette ressource.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Accès autorisé !'
            ];
        } catch (Exception $e) {
            throw new Exception(
                'Erreur lors de la vérification de participation: '
                    // pour debuger
                    . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Récupère les hackathons actifs
     */
    public function getActive()
    {
        try {
            $this->validateMethod('GET');
            $hackathons = $this->hackathon->getActive();

            $this->jsonResponse([
                'success' => true,
                'data' => $hackathons
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère les hackathons passés
     */
    public function getPast()
    {
        try {
            $this->validateMethod('GET');
            $hackathons = $this->hackathon->getPast();

            $this->jsonResponse([
                'success' => true,
                'data' => $hackathons
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère les hackathons futurs
     */
    public function getFuture()
    {
        try {
            $this->validateMethod('GET');
            $hackathons = $this->hackathon->getFuture();

            $this->jsonResponse([
                'success' => true,
                'data' => $hackathons
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère un hackathon par son ID
     * @param int $id ID du hackathon
     */
    public function get($id)
    {
        try {
            $this->validateMethod('GET');

            $hackathon = $this->hackathon->find($id);
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé');
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $hackathon
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Récupère les équipes d'un hackathon
     * @param int $id ID du hackathon
     */
    public function getTeams($id)
    {
        try {
            $this->validateMethod('GET');

            $teams = $this->hackathon->getTeams($id);

            $this->jsonResponse([
                'success' => true,
                'data' => $teams
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère les projets d'un hackathon
     * @param int $id ID du hackathon
     */
    public function getProjects($id)
    {
        try {
            $this->validateMethod('GET');

            $projects = $this->hackathon->getProjects($id);

            $this->jsonResponse([
                'success' => true,
                'data' => $projects
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère les statistiques d'un hackathon
     * @param int $id ID du hackathon
     */
    public function getStats($id)
    {
        try {
            $this->validateMethod('GET');

            $stats = $this->hackathon->getStats($id);

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

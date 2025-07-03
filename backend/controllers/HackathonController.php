<?php

namespace Auth\Controller;

use Exception;
use Auth\Model\Hackathon;

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if (!class_exists('Hackathon')) {
    require_once __DIR__ . '/../models/Hackathon.php';
}
if (!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}

class HackathonController extends Controller
{
    private $hackathon;
    private $db;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->hackathon = new Hackathon($this->db);
    }

    /**
     * Crée un nouveau hackathon
     */
    public function create()
    {
        try {
            $this->validateMethod('POST');

            $requiredFields = ['name', 'description', 'start_date', 'end_date', 'rules', 'created_by'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'location' => $_POST['location'] ?? null,
                'max_teams' => (int)($_POST['max_teams'] ?? 10),
                'max_team_members' => (int)($_POST['max_team_members'] ?? 4),
                'rules' => $_POST['rules'],
                'prizes' => $_POST['prizes'] ?? null,
                'created_by' => (int)$_POST['created_by']
            ];

            // Validation des dates
            if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
                throw new Exception('La date de fin doit être postérieure à la date de début');
            }

            $hackathonId = $this->hackathon->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Hackathon créé avec succès',
                'data' => ['id' => $hackathonId, 'name' => $data['name']]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
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
     * Met à jour un hackathon
     * @param int $id ID du hackathon
     */
    public function update($id)
    {
        try {
            $this->validateMethod('POST');

            $updatableFields = ['name', 'description', 'start_date', 'end_date', 'location', 'max_teams', 'max_team_members', 'rules', 'prizes'];
            $data = $this->filterData($_POST, $updatableFields);

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            if (isset($data['start_date']) && isset($data['end_date'])) {
                if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
                    throw new Exception('La date de fin doit être postérieure à la date de début');
                }
            }

            $this->hackathon->update($id, $data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Hackathon mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Supprime un hackathon
     * @param int $id ID du hackathon
     */
    public function delete($id)
    {
        try {
            $this->validateMethod('POST');

            // Vérifier si l'utilisateur a les droits
            if (!hasRole('admin') && !hasRole('organizer')) {
                throw new Exception('Non autorisé');
            }

            $this->hackathon->delete($id);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Hackathon supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
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

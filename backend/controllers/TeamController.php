<?php
namespace Auth\Controller;

use Exception;
use Auth\Model\Team;

if(!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if(!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if(!class_exists('Team')) {
    require_once __DIR__ . '/../models/Team.php';
}
if(!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}

class TeamController extends Controller {
    private $team;
    private $db;

    public function __construct($db, $tokenManager) {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->team = new Team($this->db);
    }

    /**
     * Récupère toutes les équipes
     */
    public function getAll() {
        try {
            $this->validateMethod('GET');
            $teams = $this->team->getAll();

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
     * Récupère les équipes d'un hackathon
     * @param int $hackathonId ID du hackathon
     */
    public function getByHackathon($hackathonId) {
        try {
            $this->validateMethod('GET');

            $teams = $this->team->getByHackathon($hackathonId);

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
     * Récupère les équipes d'un utilisateur
     * @param int $userId ID de l'utilisateur
     */
    public function getByUser($userId) {
        try {
            $this->validateMethod('GET');

            $teams = $this->team->getByUser($userId);

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
     * Récupère une équipe par son ID
     * @param int $id ID de l'équipe
     */
    public function get($id) {
        try {
            $this->validateMethod('GET');

            $team = $this->team->find($id);
            if (!$team) {
                throw new Exception('Équipe non trouvée');
            }

            // Récupérer les membres de l'équipe
            $members = $this->team->getMembers($id);
            $team['members'] = $members;

            $this->jsonResponse([
                'success' => true,
                'data' => $team
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Crée une nouvelle équipe
     */
    public function create() {
        try {
            $this->validateMethod('POST');

            $requiredFields = ['name', 'hackathon_id', 'leader_id'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $data = [
                'name' => $_POST['name'],
                'hackathon_id' => (int)$_POST['hackathon_id'],
                'leader_id' => (int)$_POST['leader_id']
            ];

            $teamId = $this->team->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Équipe créée avec succès',
                'data' => ['id' => $teamId, 'name' => $data['name']]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Met à jour une équipe
     * @param int $id ID de l'équipe
     */
    public function update($id) {
        try {
            $this->validateMethod('POST');

            $updatableFields = ['name'];
            $data = $this->filterData($_POST, $updatableFields);

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            $this->team->update($id, $data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Équipe mise à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Supprime une équipe
     * @param int $id ID de l'équipe
     */
    public function delete($id) {
        try {
            $this->validateMethod('POST');

            // Vérifier si l'utilisateur a les droits
            if (!hasRole('admin') && !hasRole('organizer')) {
                throw new Exception('Non autorisé');
            }

            $this->team->delete($id);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Équipe supprimée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère les membres d'une équipe
     * @param int $id ID de l'équipe
     */
    public function getMembers($id) {
        try {
            $this->validateMethod('GET');

            $members = $this->team->getMembers($id);

            $this->jsonResponse([
                'success' => true,
                'data' => $members
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ajoute un membre à une équipe
     * @param int $id ID de l'équipe
     */
    public function addMember($id) {
        try {
            $this->validateMethod('POST');

            if (empty($_POST['user_id'])) {
                throw new Exception('ID utilisateur requis');
            }

            $userId = (int)$_POST['user_id'];
            $this->team->addMember($id, $userId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Membre ajouté avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Retire un membre d'une équipe
     * @param int $id ID de l'équipe
     */
    public function removeMember($id) {
        try {
            $this->validateMethod('POST');

            if (empty($_POST['user_id'])) {
                throw new Exception('ID utilisateur requis');
            }

            $userId = (int)$_POST['user_id'];
            $this->team->removeMember($id, $userId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Membre retiré avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Change le leader d'une équipe
     * @param int $id ID de l'équipe
     */
    public function changeLeader($id) {
        try {
            $this->validateMethod('POST');

            if (empty($_POST['new_leader_id'])) {
                throw new Exception('ID du nouveau leader requis');
            }

            $newLeaderId = (int)$_POST['new_leader_id'];
            $this->team->changeLeader($id, $newLeaderId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Leader changé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function acceptRequest($teamId, $userId) {
        try {
            $this->validateMethod('POST');

            $this->team->acceptRequest($teamId, $userId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Demande d\'adhésion acceptée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function rejectRequest($teamId, $userId) {
        try {
            $this->validateMethod('POST');

            $this->team->rejectRequest($teamId, $userId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Demande d\'adhésion rejetée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function teamRequest($teamId, $userId) {
        try {
            $this->validateMethod('POST');

            $this->team->teamRequest($teamId, $userId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Demande d\'adhésion envoyée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    //verifier que seul le leader accepte les demandes d'adhésion
    public function verificateTeamRequest($teamId, $userId) {
        try {
            $this->validateMethod('POST');

            $this->team->verificateTeamRequest($teamId, $userId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Demande d\'adhésion vérifiée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function isLeader($teamId, $userId) {
        try {
            $this->validateMethod('POST');

            $this->team->isLeader($teamId, $userId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Demande d\'adhésion vérifiée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}

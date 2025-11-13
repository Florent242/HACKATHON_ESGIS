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
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}

class HackathonController extends Controller
{
    private $hackathon;
    private $phase;
    private $db;
    public $tokenManager;
    public $isPublicRoute;
    private $phase2QualificationRoute;
    private $phase3QualificationRoute;
    private $phase4QualificationRoute;

    public function __construct($db, $tokenManager)
    {
        $this->publicRoutes = [
            'api/hackathons/public',
            'api/hackathons/[^/]+/stats',
            'api/hackathons/[^/]+/active-phase',
        ];

        $requestPath = ltrim($this->getRequestPath(), '/'); // Enlève le slash initial s'il existe
        $isPublicRoute = false;

        foreach ($this->publicRoutes as $route) {
            $pattern = '#^' . $route . '$#';
            if (preg_match($pattern, $requestPath)) {
                $isPublicRoute = true;
                break;
            }
        }

        if (!$isPublicRoute) {
            parent::__construct($tokenManager);
        }
        $this->isPublicRoute = $isPublicRoute;
        $this->db = $db;
        $this->hackathon = new Hackathon($this->db);
        $this->phase = new Phase($this->db);
        $this->tokenManager = $tokenManager;
        $this->phase2QualificationRoute = $_ENV['DEV_PHASE_2_QUALIFICATION_ROUTE'] ?? null;
        $this->phase3QualificationRoute = $_ENV['DEV_PHASE_3_QUALIFICATION_ROUTE'] ?? null;
    }

    /**
     * Crée un nouveau hackathon
     */
    public function create($input)
    {
        try {
            $this->validateMethod('POST');
            try {
                $current_user = $this->tokenManager->getCurrentUserId();
            } catch (Exception $e) {
                $current_user = $input['created_by'] ?? null;
            }

            if (empty($input)) {
                throw new Exception('Aucune donnée reçue');
            }
            // Validation des champs obligatoires
            $requiredFields = [
                'name',
                'description',
                'start_date',
                'end_date',
                'type',
                'status',
                'visibility',
                'created_by'
            ];
            $this->validateRequiredFields($input, $requiredFields);

            // Préparation des données
            $data = [
                'name' => $input['name'],
                'description' => $input['description'],
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
                'type' => $input['type'],
                'status' => $input['status'],
                'visibility' => $input['visibility'],
                'created_by' => (int)$input['created_by'],
                'slug' => $input['slug'] ?? null,
                'theme' => $input['theme'] ?? null,
                'location' => $input['location'] ?? null,
                'registration_deadline' => $input['registration_deadline'] ?? null,
                'max_teams' => isset($input['max_teams']) ? (int)$input['max_teams'] : 10,
                'min_team_members' => isset($input['min_team_members']) ? (int)$input['min_team_members'] : 1,
                'max_team_members' => isset($input['max_team_members']) ? (int)$input['max_team_members'] : 4,
                'rules' => $input['rules'] ?? [],
                'eligibility_criteria' => $input['eligibility_criteria'] ?? [],
                'prizes' => $input['prizes'] ?? []
            ];

            // Validation des dates
            if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
                throw new Exception('La date de fin doit être postérieure à la date de début');
            }

            if (
                !empty($data['registration_deadline']) &&
                strtotime($data['registration_deadline']) > strtotime($data['start_date'])
            ) {
                throw new Exception('La date limite d\'inscription doit être antérieure à la date de début');
            }

            // Validation du type
            $validTypes = ['ctf', 'dev', 'mixte'];
            if (!in_array($data['type'], $validTypes)) {
                throw new Exception('Type de hackathon invalide');
            }

            // Validation du statut
            $validStatuses = ['draft', 'upcoming', 'active', 'ended', 'cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new Exception('Statut invalide');
            }

            // Validation de la visibilité
            $validVisibilities = ['public', 'private', 'unlisted'];
            if (!in_array($data['visibility'], $validVisibilities)) {
                throw new Exception('Visibilité invalide');
            }

            // Création du hackathon
            $hackathonId = $this->hackathon->create($data);

            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $this->logActivity('hackathon_create', 'Création d\'un hackathon par un admin : ' . $current_user, $hackathonId, 'info', $ip_address, $user_agent);

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
     * Met à jour un hackathon
     * @param int $id ID du hackathon
     */
    public function update($id, $input)
    {
        try {
            $this->validateMethod('PUT');

            try {
                $current_user = $this->tokenManager->getCurrentUserId();
            } catch (Exception $e) {
                $current_user = $input['created_by'] ?? null;
            }

            // Verifier input
            if (empty($input)) {
                throw new Exception('Aucune donnée reçue');
            }

            // Récupération du hackathon existant
            $existingHackathon = $this->hackathon->find($id);
            if (!$existingHackathon) {
                throw new Exception('Hackathon non trouvé');
            }

            // Liste des champs autorisés à être mis à jour
            $updatableFields = [
                'name',
                'slug',
                'theme',
                'description',
                'type',
                'status',
                'visibility',
                'start_date',
                'end_date',
                'registration_deadline',
                'location',
                'max_teams',
                'min_team_members',
                'max_team_members',
                'rules',
                'eligibility_criteria',
                'prizes'
            ];

            // Filtrage des données
            $data = $this->filterData($input, $updatableFields);

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            // Validation des dates
            if (isset($data['start_date']) || isset($data['end_date'])) {
                $startDate = isset($data['start_date']) ? $data['start_date'] : $existingHackathon['start_date'];
                $endDate = isset($data['end_date']) ? $data['end_date'] : $existingHackathon['end_date'];

                if (strtotime($endDate) <= strtotime($startDate)) {
                    throw new Exception('La date de fin doit être postérieure à la date de début');
                }

                // Validation de la date limite d'inscription
                if (isset($data['registration_deadline'])) {
                    if (strtotime($data['registration_deadline']) > strtotime($startDate)) {
                        throw new Exception('La date limite d\'inscription doit être antérieure à la date de début');
                    }
                }
            }

            // Validation des types énumérés
            if (isset($data['type'])) {
                $validTypes = ['ctf', 'dev', 'mixte'];
                if (!in_array($data['type'], $validTypes)) {
                    throw new Exception('Type de hackathon invalide');
                }
            }

            // Validation des statuts
            if (isset($data['status'])) {
                $validStatuses = ['draft', 'upcoming', 'inactive', 'active', 'ended', 'cancelled'];
                if (!in_array($data['status'], $validStatuses)) {
                    throw new Exception('Statut invalide');
                }
            }

            // Validation des visibilités
            if (isset($data['visibility'])) {
                $validVisibilities = ['public', 'private', 'unlisted'];
                if (!in_array($data['visibility'], $validVisibilities)) {
                    throw new Exception('Visibilité invalide');
                }
            }

            // Mise à jour du hackathon
            $this->hackathon->update($id, $data);

            // Récupération des données mises à jour
            $updatedHackathon = $this->hackathon->find($id);

            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $this->logActivity('hackathon_update', 'Mise a jour d\'un hackathon par un admin : ' . $current_user, $updatedHackathon, 'info', $ip_address, $user_agent);
            $this->jsonResponse([
                'success' => true,
                'message' => 'Hackathon mis à jour avec succès',
                'data' => $updatedHackathon
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
            $this->validateMethod('DELETE');

            $userId = $this->tokenManager->getCurrentUserId();
            $isAdmin = $this->isAdmin($userId);
            // Vérifier si l'utilisateur a les droits
            if (!$isAdmin) {
                throw new Exception('Non autorisé');
            }

            $this->hackathon->delete($id);

            $current_user = $this->tokenManager->getCurrentUserId();
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $this->logActivity('hackathon_delete', 'Suppression d\'un hackathon par un admin : ' . $current_user, $id, 'info', $ip_address, $user_agent);
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


    public function getActivePhase($hackathonId)
    {
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

    public function getPublicAll()
    {
        try {
            $this->validateMethod('GET');
            $hackathons = $this->hackathon->getPublicAll();

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

            if (!$stmt->fetchColumn() > 0 && !$this->isAdmin($userId)) {
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

            if (!$stmt->fetchColumn() > 0 && !$this->isAdmin($userId)) {
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

    public function getHackathonParticipants($id)
    {
        try {
            $this->validateMethod('GET');

            $participants = $this->hackathon->getHackathonParticipants($id);

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

    public function getLeaderboard($hackathonId, $phaseId = null)
    {
        try {
            $this->validateMethod('GET');

            $leaderboard = $this->hackathon->getLeaderboard(
                (int)$hackathonId,
                $phaseId ? (int)$phaseId : null
            );

            $this->jsonResponse([
                'success' => true,
                'data' => $leaderboard
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getChallenges($id)
    {
        try {
            $this->validateMethod('GET');

            $challenges = $this->hackathon->getChallenges($id);

            $this->jsonResponse([
                'success' => true,
                'data' => $challenges
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getRegistrations($id)
    {
        try {
            $this->validateMethod('GET');

            $registrations = $this->hackathon->getRegistrations($id);

            $this->jsonResponse([
                'success' => true,
                'data' => $registrations
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

    /**
     * Vérifie si l'utilisateur est qualifié pour une phase spécifique
     * @param int $hackathonId ID du hackathon
     * @param int $phaseId ID de la phase
     * @return void
     */
    public function checkQualification($hackathonId, $phaseId)
    {
        try {
            $this->validateMethod('POST');

            // Récupérer l'ID de l'utilisateur connecté
            $userId = $this->tokenManager->getCurrentUserId();

            if (!$userId) {
                throw new Exception("Utilisateur non connecté");
            }

            // Vérifier si l'utilisateur est qualifié pour la phase
            $isQualified = $this->phase->checkQualification($userId, $phaseId, $hackathonId);

            $this->jsonResponse([
                'success' => true,
                'is_qualified' => $isQualified,
                'message' => $isQualified
                    ? "L'utilisateur est qualifié pour cette phase"
                    : "L'utilisateur n'est pas qualifié pour cette phase",
                'action' => $isQualified && $phaseId == 3
                    ? $this->phase2QualificationRoute
                    : ($isQualified && $phaseId == 4
                        ? $this->phase3QualificationRoute
                        : null
                    )
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}

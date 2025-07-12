<?php

namespace Auth\Controller;

use Exception;
use Auth\Model\Team;
use Throwable;

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if (!class_exists('Team')) {
    require_once __DIR__ . '/../models/Team.php';
}
if (!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}
if (!class_exists('AuthController')) {
    require_once __DIR__ . '/AuthController.php';
}
if (!class_exists('TokenManager')) {
    require_once __DIR__ . '/../models/TokenManager.php';
}
class TeamController extends Controller
{
    private $team;
    private $db;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->team = new Team($this->db);
    }

    /**
     * Récupère toutes les équipes
     */
    public function getAll()
    {
        try {
            $this->validateMethod('GET');
            $teams = $this->team->getAll($this->tokenManager->getCurrentUserId());

            $this->jsonResponse([
                'success' => true,
                'data' => $teams
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Récupère les équipes d'un hackathon
     * @param int $hackathonId ID du hackathon
     */
    public function getByHackathon($hackathonId)
    {
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
            ]);
        }
    }

    /**
     * Récupère le nombre d'équipes d'un hackathon
     * @param int $hackathonId ID du hackathon
     */
    public function countByHackathon($hackathonId)
    {
        try {
            $this->validateMethod('GET');

            $teams = $this->team->countByHackathon($hackathonId);

            $this->jsonResponse([
                'success' => true,
                'data' => $teams
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Récupère les équipes d'un utilisateur
     */
    public function getByUser($userId)
    {
        try {
            $this->validateMethod('GET');
            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            $teams = $this->team->getByUser($userId);

            $this->jsonResponse([
                'success' => true,
                'data' => $teams
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Récupère une équipe par son ID
     * @param int $id ID de l'équipe
     */
    public function get($id)
    {
        try {
            $this->validateMethod('GET');

            $team = $this->team->find($id, $this->tokenManager->getCurrentUserId());
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
            ]);
        }
    }

    /**
     * Crée une nouvelle équipe
     */
    public function create()
    {
        try {
            $this->validateMethod('POST');

            $requiredFields = ['nom', 'type'];
            $this->validateRequiredFields($_POST, $requiredFields);
            // Utiliser getCurrentUserId() de AuthController
            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            $data = [
                'name' => $_POST['nom'],
                'hackathon_id' => $_POST['hackathon_id'] ?? null,
                'leader_id' => $currentUserId,
                'type' => $_POST['type'],
                'description' => $_POST['description'] ?? null,
                'invitation_code' => $this->generateInvitationCode()
            ];

            $teamId = $this->team->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Équipe créée avec succès',
                'data' => [
                    'id' => $teamId,
                    'name' => $data['name'],
                    'hackathon_id' => $data['hackathon_id'],
                    'leader_id' => $data['leader_id'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                    'invitation_code' => $data['invitation_code']
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update($teamId)
    {
        error_log("Début de TeamController::update pour teamId: $teamId");
        try {
            $this->validateMethod('POST');
            error_log("Méthode POST validée");

            $token = $this->getBearerToken();
            if (!$token) {
                error_log("Token manquant");
                throw new Exception('Token manquant');
            }
            error_log("Token récupéré: $token");

            $tokenValidation = $this->tokenManager->validateToken($token);
            if (!$tokenValidation['valid']) {
                error_log("Token invalide: " . ($tokenValidation['error'] ?? 'Aucun détail'));
                throw new Exception('Token invalide: ' . ($tokenValidation['error'] ?? 'Aucun détail'));
            }
            $userId = $tokenValidation['user_id'];
            error_log("Utilisateur validé: userId = $userId");

            // Vérifier si l'utilisateur est le leader
            $isLeader = $this->team->isLeader($teamId, $userId);
            error_log("Vérification leader: isLeader = " . ($isLeader ? 'true' : 'false'));
            if (!$isLeader) {
                error_log("Utilisateur $userId n'est pas leader de l'équipe $teamId");
                throw new Exception('Seul le leader peut modifier l\'équipe');
            }

            // Récupérer les données envoyées
            $data = $_POST;
            error_log("Données reçues: " . print_r($data, true));
            $name = $data['name'] ?? null;
            $description = $data['description'] ?? null;

            if (!$name) {
                error_log("Nom de l'équipe manquant");
                throw new Exception('Le nom de l\'équipe est requis');
            }

            // Vérifier si l'équipe existe
            $query = "SELECT id FROM teams WHERE id = :teamId";
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                error_log("Échec de la préparation de la requête SELECT: " . print_r($this->db->errorInfo(), true));
                throw new Exception('Erreur de préparation de la requête');
            }
            $stmt->execute([':teamId' => $teamId]);
            if (!$stmt->fetch()) {
                error_log("Équipe non trouvée pour teamId: $teamId");
                throw new Exception('Équipe non trouvée');
            }

            // Mettre à jour l'équipe
            $query = "UPDATE teams SET name = :name, description = :description WHERE id = :teamId";
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                error_log("Échec de la préparation de la requête UPDATE: " . print_r($this->db->errorInfo(), true));
                throw new Exception('Erreur de préparation de la requête');
            }
            $success = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':teamId' => $teamId
            ]);
            $rowCount = $stmt->rowCount();
            error_log("Requête SQL exécutée, succès: " . ($success ? 'true' : 'false') . ", lignes affectées: $rowCount");

            if (!$success) {
                error_log("Échec de l'exécution de la requête UPDATE: " . print_r($this->db->errorInfo(), true));
                throw new Exception('Échec de la mise à jour de l\'équipe');
            }

            if ($rowCount === 0) {
                error_log("Aucune ligne affectée pour teamId: $teamId");
                throw new Exception('Aucune modification effectuée sur l\'équipe');
            }

            $response = [
                'success' => true,
                'message' => 'Équipe mise à jour avec succès'
            ];
            error_log("Réponse générée: " . json_encode($response));
            $this->jsonResponse($response);
        } catch (Exception $e) {
            error_log("Erreur dans TeamController::update: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        } catch (Throwable $t) {
            error_log("Erreur fatale dans TeamController::update: " . $t->getMessage() . " (Code: " . $t->getCode() . ")");
            $this->jsonResponse([
                'success' => false,
                'error' => 'Erreur serveur interne: ' . $t->getMessage()
            ]);
        }
    }

    /**
     * Supprime une équipe
     * @param int $teamId ID de l'équipe
     */
    public function delete($teamId)
    {
        error_log("Tentative de suppression de l'équipe ID: $teamId");
        try {
            $this->validateMethod('DELETE');
            $currentUserId = $this->tokenManager->getCurrentUserId();

            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            // Vérifier si l'utilisateur est le leader
            if (!$this->team->isLeader($teamId, $currentUserId)) {
                throw new Exception('Seul le leader peut supprimer l\'équipe');
            }

            // Vérifier si l'équipe existe
            $team = $this->team->find($teamId);
            if (!$team) {
                throw new Exception('Équipe non trouvée');
            }

            // Démarrer une transaction
            $this->db->beginTransaction();

            try {
                // 0. Supprimer les entrées dans hackathon_teams
                $query = "DELETE FROM hackathon_teams WHERE team_id = :teamId";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':teamId' => $teamId]);

                // 1. Supprimer toutes les entrées dans teams_adhesions liées à l'équipe ou au leader
                $query = "DELETE FROM teams_adhesions WHERE teams_id = :teamId OR leader_id = :leaderId";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':teamId' => $teamId, ':leaderId' => $team['leader_id']]);

                // 2. Supprimer les membres de l'équipe
                $query = "DELETE FROM team_members WHERE team_id = :teamId";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':teamId' => $teamId]);

                // 3. Supprimer l'équipe elle-même
                $query = "DELETE FROM teams WHERE id = :teamId";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':teamId' => $teamId]);

                if ($stmt->rowCount() === 0) {
                    throw new Exception('Aucune équipe supprimée');
                }

                $this->db->commit();

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Équipe supprimée avec succès'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Erreur PDO: " . $e->getMessage());
                throw new Exception('Erreur lors de la suppression de l\'équipe: ' . $e->getMessage());
            }
        } catch (Exception $e) {
            error_log("Erreur: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * Récupère les membres d'une équipe
     * @param int $teamId ID de l'équipe
     */
    public function getMembers($teamId)
    {
        try {
            $this->validateMethod('GET');

            $members = $this->team->getMembers($teamId);

            $this->jsonResponse([
                'success' => true,
                'data' => $members
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ajoute un membre à une équipe
     * @param int $teamId ID de l'équipe
     */
    public function addMember($teamId)
    {
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            if (!$this->team->isLeader($teamId, $currentUserId)) {
                throw new Exception('Non autorisé à ajouter un membre');
            }

            if (empty($_POST['user_id'])) {
                throw new Exception('ID utilisateur requis');
            }

            $targetUserId = (int)$_POST['user_id'];
            $this->team->addMember($teamId, $targetUserId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Membre ajouté avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Retire un membre d'une équipe
     * @param int $teamId ID de l'équipe
     */
    public function removeMember($teamId)
    {
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            if (!$this->team->isLeader($teamId, $currentUserId)) {
                throw new Exception('Non autorisé à retirer un membre');
            }

            if (empty($_POST['user_id'])) {
                throw new Exception('ID utilisateur requis');
            }

            $targetUserId = (int)$_POST['user_id'];
            $this->team->removeMember($teamId, $targetUserId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Membre retiré avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Change le leader d'une équipe
     * @param int $id ID de l'équipe
     */
    public function changeLeader($teamId)
    {
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            if (!$this->team->isLeader($teamId, $currentUserId)) {
                throw new Exception('Non autorisé à changer le leader');
            }

            if (empty($_POST['new_leader_id'])) {
                throw new Exception('ID du nouveau leader requis');
            }

            $newLeaderId = (int)$_POST['new_leader_id'];
            $this->team->changeLeader($teamId, $newLeaderId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Leader changé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Accepte une demande d'adhésion
     * @param int $teamId ID de l'équipe
     * @param int $userId ID de l'utilisateur
     */
    public function acceptRequest($teamId, $userId)
    {
        error_log("Appel de acceptRequest pour teamId: $teamId, userId: $userId");
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            if (!$this->team->isLeader($teamId, $currentUserId)) {
                throw new Exception('Non autorisé à accepter une demande');
            }

            if (!$userId) {
                throw new Exception('ID utilisateur manquant');
            }

            $isValid = $this->team->verificateTeamRequest($teamId, $userId);
            if (!$isValid) {
                error_log("Demande d'adhésion non trouvée pour teamId: $teamId, userId: $userId");
                throw new Exception('Demande d\'adhésion non trouvée');
            }

            $this->team->acceptRequest($teamId, $userId);
            error_log("Demande d'adhésion acceptée pour teamId: $teamId, userId: $userId");

            $this->jsonResponse([
                'success' => true,
                'message' => 'Demande d\'adhésion acceptée avec succès'
            ]);
        } catch (Exception $e) {
            error_log("Erreur dans acceptRequest: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Rejette une demande d'adhésion
     * @param int $teamId ID de l'équipe
     * @param int $userId ID de l'utilisateur
     */
    public function rejectRequest($teamId, $userId)
    {
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            if (!$this->team->isLeader($teamId, $currentUserId)) {
                throw new Exception('Non autorisé à rejeter une demande');
            }

            if (!$userId) {
                throw new Exception('ID utilisateur manquant');
            }

            $this->team->rejectRequest($teamId, $userId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Demande d\'adhésion rejetée avec succès'
            ]);
        } catch (Exception $e) {
            error_log("Erreur dans rejectRequest: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Soumet une demande d'adhésion
     * @param int $teamId ID de l'équipe
     */
    public function teamRequest($teamId)
    {
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            $this->team->teamRequest($teamId, $currentUserId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Demande d\'adhésion envoyée avec succès'
            ]);
        } catch (Exception $e) {
            error_log("Erreur dans teamRequest: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Vérifie une demande d'adhésion
     * @param int $teamId ID de l'équipe
     * @param int $userId ID de l'utilisateur
     */
    public function verificateTeamRequest($teamId, $userId)
    {
        error_log("Appel de verificateTeamRequest pour teamId: $teamId, userId: $userId");
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            if (!$this->team->isLeader($teamId, $currentUserId) && !hasRole('admin') && !hasRole('organizer')) {
                throw new Exception('Non autorisé à vérifier une demande');
            }

            if (!$userId) {
                throw new Exception('ID utilisateur requis');
            }

            $isValid = $this->team->verificateTeamRequest($teamId, $userId);
            if (!$isValid) {
                error_log("Demande d'adhésion non trouvée pour teamId: $teamId, userId: $userId");
                throw new Exception('Demande d\'adhésion non trouvée');
            }

            error_log("Demande d'adhésion vérifiée avec succès pour teamId: $teamId, userId: $userId");
            $this->jsonResponse([
                'success' => true,
                'message' => 'Demande d\'adhésion vérifiée avec succès'
            ]);
        } catch (Exception $e) {
            error_log("Erreur dans verificateTeamRequest: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Met à jour le code d'invitation d'une équipe
     * @param int $teamId ID de l'équipe
     */

    public function updateTeamCode($teamId)
    {
        error_log("Appel de updateTeamCode pour teamId: $teamId");
        try {
            $this->validateMethod('POST');
            error_log("Méthode POST validée pour teamId: $teamId");
            $userId = $this->tokenManager->getCurrentUserId();
            error_log("UserId récupéré: $userId");

            if (!$this->team->isLeader($teamId, $userId)) {
                error_log("Utilisateur $userId n'est pas leader de l'équipe $teamId");
                throw new Exception('Seul le leader peut mettre à jour le code d\'invitation');
            }

            $newCode = $this->generateInvitationCode();
            error_log("Nouveau code généré: $newCode");
            $this->team->updateTeamCode($teamId, $newCode);
            error_log("Code mis à jour dans la base de données pour teamId: $teamId");

            // Récupérer l'équipe mise à jour pour confirmer le code
            $team = $this->team->find($teamId);
            if (!$team || $team['invitation_code'] !== $newCode) {
                error_log("Échec de la vérification du nouveau code pour teamId: $teamId");
                throw new Exception('Échec de la mise à jour du code d\'invitation');
            }

            $response = [
                'success' => true,
                'message' => 'Code d\'invitation mis à jour avec succès',
                'data' => [
                    'invitation_code' => $newCode
                ]
            ];
            error_log("Réponse générée: " . json_encode($response));
            $this->jsonResponse($response);
        } catch (Exception $e) {
            error_log("Erreur dans updateTeamCode: " . $e->getMessage() . " (Code: " . ($e->getCode() ?: 400) . ")");
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Génère un code d'invitation unique
     * @return string
     */
    private function generateInvitationCode()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 13; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
            if ($i % 4 === 3 && $i < 11) {
                $code .= '-';
            }
        }
        $sql = "SELECT id FROM teams WHERE invitation_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            return $this->generateInvitationCode();
        }
        return $code;
    }

    /**
     * Récupère toutes les demandes d'adhésion
     */
    public function getAllTeamRequests($teamId)
    {
        try {
            $this->validateMethod('GET');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            //verifier si l'utilisateur est leader de l'équipe
            $isLeader = $this->team->isLeader($teamId, $currentUserId);
            if (!$isLeader) {
                throw new Exception('Non autorisé à récupérer les demandes d\'adhésion');
            }

            $teamRequests = $this->team->getAllTeamRequests($teamId);

            $this->jsonResponse([
                'success' => true,
                'data' => $teamRequests
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Vérifie si l'utilisateur connecté est leader
     * @param int $teamId ID de l'équipe
     */
    public function isLeader($teamId, $userId)
    {
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            $isLeader = $this->team->isLeader($teamId, $userId);

            $this->jsonResponse([
                'success' => true,
                'data' => ['is_leader' => $isLeader]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * Allows a user to join a team using an invitation code
     * @param string $code The invitation code
     */
    public function joinTeamViaCode($code)
    {
        error_log("Appel de joinTeamViaCode avec code: $code");
        try {
            $this->validateMethod('POST');

            $currentUserId = $this->tokenManager->getCurrentUserId();
            if (!$currentUserId) {
                throw new Exception('Utilisateur non authentifié');
            }

            if (empty($code)) {
                throw new Exception('Code d\'invitation requis');
            }

            // Call the model to process the join request
            $teamId = $this->team->joinTeamViaCode($code, $currentUserId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Vous avez rejoint l\'équipe avec succès',
                'data' => [
                    'team_id' => $teamId
                ]
            ]);
        } catch (Exception $e) {
            error_log("Erreur dans joinTeamViaCode: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

<?php
namespace Auth\Model;

use Exception;
use PDO;
use PDOException;

class Team {
    private $db;
    private $table = 'teams';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les équipes
     * @return array Liste des équipes
     */
    public function getAll() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des équipes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère une équipe par son ID
     * @param int $id ID de l'équipe
     * @return array|bool Les données de l'équipe ou false si non trouvée
     */
    public function find($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération de l\'équipe: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée une nouvelle équipe
     * @param array $data Les données de l'équipe
     * @return int|bool L'ID de la nouvelle équipe ou false si erreur
     */
    public function create($data) {
        try {
            // Valider les données minimales requises
            if (empty($data['nom']) || empty($data['hackathon_id']) || empty($data['leader_id'])) {
                throw new Exception('Nom, hackathon et leader sont requis pour créer une équipe');
            }

            // Vérifier si l'utilisateur leader existe
            $checkUserQuery = "SELECT id FROM users WHERE id = :id LIMIT 1";
            $checkUserStmt = $this->db->prepare($checkUserQuery);
            $checkUserStmt->bindParam(':id', $data['leader_id'], PDO::PARAM_INT);
            $checkUserStmt->execute();

            if (!$checkUserStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Utilisateur leader non trouvé');
            }

            // Vérifier si le hackathon existe
            $checkHackathonQuery = "SELECT id FROM hackathons WHERE id = :id LIMIT 1";
            $checkHackathonStmt = $this->db->prepare($checkHackathonQuery);
            $checkHackathonStmt->bindParam(':id', $data['hackathon_id'], PDO::PARAM_INT);
            $checkHackathonStmt->execute();

            if (!$checkHackathonStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Hackathon non trouvé');
            }

            // Préparer les données
            $name = $data['nom'];
            $description = $data['description'] ?? null;
            $hackathonId = $data['hackathon_id'];
            $leaderId = $data['leader_id'];
            $codeInvitation = $data['code_invitation'] ?? bin2hex(random_bytes(4));
            $createdAt = date('Y-m-d H:i:s');

            // Insérer l'équipe dans la base de données
            $this->db->beginTransaction();

            try {
                $query = "INSERT INTO {$this->table} (name, description, hackathon_id, leader_id, code_invitation, created_at)
                          VALUES (:name, :description, :hackathon_id, :leader_id, :code_invitation, :created_at)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':hackathon_id', $hackathonId, PDO::PARAM_INT);
                $stmt->bindParam(':leader_id', $leaderId, PDO::PARAM_INT);
                $stmt->bindParam(':code_invitation', $codeInvitation);
                $stmt->bindParam(':created_at', $createdAt);
                $stmt->execute();

                $teamId = $this->db->lastInsertId();

                // Ajouter automatiquement le leader comme membre de l'équipe
                $memberQuery = "INSERT INTO team_members (team_id, user_id, leader_id, joined_at)
                                VALUES (:team_id, :user_id, :leader_id, :joined_at)";
                $memberStmt = $this->db->prepare($memberQuery);
                $memberStmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
                $memberStmt->bindParam(':user_id', $leaderId, PDO::PARAM_INT);
                $memberStmt->bindParam(':leader_id', $leaderId, PDO::PARAM_INT);
                $memberStmt->bindParam(':joined_at', $createdAt);
                $memberStmt->execute();

                $this->db->commit();

                return $teamId;
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            error_log('Erreur lors de la création de l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de la création de l\'équipe: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour une équipe
     * @param int $id ID de l'équipe
     * @param array $data Les données à mettre à jour
     * @return bool true si succès, sinon false
     */
    public function update($id, $data) {
        try {
            // Vérification si l'équipe existe
            $team = $this->find($id);
            if (!$team) {
                throw new Exception('Équipe non trouvée');
            }

            // Construction de la requête
            $fields = [];
            $params = [];

            // Gérer la traduction des champs français/anglais
            if (isset($data['nom'])) {
                $data['name'] = $data['nom'];
                unset($data['nom']);
            }

            // Champs à mettre à jour
            $allowedFields = ['name', 'description', 'hackathon_id', 'leader_id', 'code_invitation'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            if (empty($fields)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $params[':id'] = $id;

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour de l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour de l\'équipe: ' . $e->getMessage());
        }
    }

    /**
     * Supprime une équipe
     * @param int $id ID de l'équipe
     * @return bool true si succès, sinon false
     */
    public function delete($id) {
        try {
            // Vérification si l'équipe existe
            $team = $this->find($id);
            if (!$team) {
                throw new Exception('Équipe non trouvée');
            }

            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression de l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de la suppression de l\'équipe: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les équipes d'un hackathon
     * @param int $hackathonId ID du hackathon
     * @return array Liste des équipes
     */
    public function getByHackathon($hackathonId) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE hackathon_id = :hackathon_id ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hackathon_id', $hackathonId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des équipes par hackathon: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les équipes d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array Liste des équipes
     */
    public function getByUser($userId) {
        try {
            $query = "SELECT t.* FROM {$this->table} t
                     JOIN team_members tm ON t.id = tm.team_id
                     WHERE tm.user_id = :user_id
                     ORDER BY t.name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des équipes par utilisateur: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un utilisateur est membre d'une équipe
     * @param int $teamId ID de l'équipe
     * @param int $userId ID de l'utilisateur
     * @return bool true si l'utilisateur est membre, sinon false
     */
    public function isMember($teamId, $userId) {
        try {
            $query = "SELECT COUNT(*) FROM team_members WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification d\'appartenance à l\'équipe: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajoute un membre à une équipe
     * @param int $teamId ID de l'équipe
     * @param int $userId ID de l'utilisateur
     * @param bool $isLeader Si l'utilisateur est le leader de l'équipe
     * @return bool true si l'ajout est réussi, sinon false
     */
    public function addMember($teamId, $userId, $isLeader = false) {
        try {
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe');
            }

            // Ajouter le membre
            $query = "INSERT INTO team_members (team_id, user_id, leader_id, joined_at) VALUES (:team_id, :user_id, :leader_id, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':leader_id', $isLeader, PDO::PARAM_INT);
            $stmt->execute();

            // Si c'est le leader, mettre à jour le champ leader_id dans la table teams
            if ($isLeader) {
                $updateQuery = "UPDATE {$this->table} SET leader_id = :leader_id WHERE id = :team_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':leader_id', $userId, PDO::PARAM_INT);
                $updateStmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
                $updateStmt->execute();
            }

            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de l\'ajout du membre à l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de l\'ajout du membre à l\'équipe: ' . $e->getMessage());
        }
    }
//verifier que seul le leader accepte les demandes d'adhésion
    public function verificateTeamRequest($teamId, $userId) {
        try {
            $query = "SELECT COUNT(*) FROM teams_adhesions WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
            return false;
        }
    }

    //est le leader de l'équipe
    public function isLeader($teamId, $userId) {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE id = :team_id AND leader_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
            return false;
        }
    }   
    public function teamRequest($teamId, $userId) {
        try {
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe');
            }

            // fait une demande d'adhésion
            $query = "INSERT INTO teams_adhesions (team_id, user_id, status, type, joined_at) VALUES (:team_id, :user_id, 'pending', :type, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':type', 'pending', PDO::PARAM_STR);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
        }
    }

    public function acceptRequest($teamId, $userId) {
        try {
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe');
            }

            // accepte une demande d'adhésion
            $query = "UPDATE teams_adhesions SET status = 'validated' WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de l\'acceptation de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de l\'acceptation de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
        }
    }

    public function rejectRequest($teamId, $userId) {
        try {
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe');
            }

            // accepte une demande d'adhésion
            $query = "UPDATE teams_adhesions SET status = 'rejected' WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de la rejet de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de la rejet de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
        }
    }

    

    /**
     * Retire un membre d'une équipe
     * @param int $teamId ID de l'équipe
     * @param int $userId ID de l'utilisateur
     * @return bool true si la suppression est réussie, sinon false
     */
    public function removeMember($teamId, $userId) {
        try {
            // Vérifier si l'utilisateur est membre de l'équipe
            if (!$this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur n\'est pas membre de cette équipe');
            }

            // Vérifier si l'utilisateur est le leader de l'équipe
            $team = $this->find($teamId);
            if ($team && $team['leader_id'] == $userId) {
                throw new Exception('Le leader de l\'équipe ne peut pas être retiré. Changez d\'abord de leader.');
            }

            // Supprimer le membre
            $query = "DELETE FROM team_members WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de la suppression du membre de l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de la suppression du membre de l\'équipe: ' . $e->getMessage());
        }
    }

    /**
     * Change le leader de l'équipe
     * @param int $teamId ID de l'équipe
     * @param int $newLeaderId ID du nouvel utilisateur leader
     * @return bool true si le changement est réussi, sinon false
     */
    public function changeLeader($teamId, $newLeaderId) {
        try {
            // Vérifier si l'utilisateur est membre de l'équipe
            if (!$this->isMember($teamId, $newLeaderId)) {
                throw new Exception('Le nouvel utilisateur n\'est pas membre de cette équipe');
            }

            $this->db->beginTransaction();

            // Mettre à jour le champ leader_id dans la table team_members
            $resetQuery = "UPDATE team_members SET leader_id = NULL WHERE team_id = :team_id";
            $resetStmt = $this->db->prepare($resetQuery);
            $resetStmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $resetStmt->execute();

            $updateMemberQuery = "UPDATE team_members SET leader_id = :leader_id WHERE team_id = :team_id AND user_id = :user_id";
            $updateMemberStmt = $this->db->prepare($updateMemberQuery);
            $updateMemberStmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $updateMemberStmt->bindParam(':user_id', $newLeaderId, PDO::PARAM_INT);
            $updateMemberStmt->execute();

            // Mettre à jour le champ leader_id dans la table teams
            $updateTeamQuery = "UPDATE {$this->table} SET leader_id = :leader_id WHERE id = :team_id";
            $updateTeamStmt = $this->db->prepare($updateTeamQuery);
            $updateTeamStmt->bindParam(':leader_id', $newLeaderId, PDO::PARAM_INT);
            $updateTeamStmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $updateTeamStmt->execute();

            $this->db->commit();

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Erreur lors du changement de leader: ' . $e->getMessage());
            throw new Exception('Erreur lors du changement de leader: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les membres d'une équipe
     * @param int $teamId ID de l'équipe
     * @return array Liste des membres
     */
    public function getMembers($teamId) {
        try {
            $query = "SELECT u.*, tm.joined_at, tm.leader_id
                      FROM team_members tm
                      JOIN users u ON tm.user_id = u.id
                      WHERE tm.team_id = :team_id
                      ORDER BY tm.leader_id DESC, u.fullname";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->execute();

            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($members as &$member) {
                if (isset($member['password'])) {
                    unset($member['password']);
                }
            }

            return $members;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des membres de l\'équipe: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les équipes d'un hackathon
     * @param int $hackathonId ID du hackathon
     * @return array Liste des équipes
     */


    /**
     * Récupère les équipes d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array Liste des équipes
     */
}

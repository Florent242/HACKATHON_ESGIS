<?php

namespace Auth\Model;

use Exception;
use PDO;
use PDOException;

class Team
{
    private $db;
    private $table = 'teams';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Récupère toutes les équipes
     * @return array Liste des équipes
     */
    public function getAll()
    {
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
    public function find($id)
    {
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
    public function create($data)
    {
        try {
            // Valider les données minimales requises
            if (empty($data['name']) || empty($data['leader_id'])) {
                throw new Exception('Nom et leader sont requis pour créer une équipe');
            }

            // Vérifier si l'utilisateur leader existe
            $checkUserQuery = "SELECT id FROM users WHERE id = :id LIMIT 1";
            $checkUserStmt = $this->db->prepare($checkUserQuery);
            $checkUserStmt->bindParam(':id', $data['leader_id'], PDO::PARAM_INT);
            $checkUserStmt->execute();

            if (!$checkUserStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Utilisateur leader non trouvé');
            }

            // verifier si le leader_id est deja leader d'une equipe
            $checkLeaderQuery = "SELECT id FROM teams WHERE leader_id = :id LIMIT 1";
            $checkLeaderStmt = $this->db->prepare($checkLeaderQuery);
            $checkLeaderStmt->bindParam(':id', $data['leader_id'], PDO::PARAM_INT);
            $checkLeaderStmt->execute();

            if ($checkLeaderStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Utilisateur leader deja leader d\'une equipe');
            }

            // // Vérifier si le hackathon existe
            // $checkHackathonQuery = "SELECT id FROM hackathons WHERE id = :id LIMIT 1";
            // $checkHackathonStmt = $this->db->prepare($checkHackathonQuery);
            // $checkHackathonStmt->bindParam(':id', $data['hackathon_id'], PDO::PARAM_INT);
            // $checkHackathonStmt->execute();

            // if (!$checkHackathonStmt->fetch(PDO::FETCH_ASSOC)) {
            //     throw new Exception('Hackathon non trouvé');
            // }

            // Préparer les données
            $name = $data['name'];
            $description = $data['description'] ?? null;
            $hackathonId = $data['hackathon_id'];
            $type = $data['type'];
            $leaderId = $data['leader_id'];
            $invitationCode = $this->generateInvitationCode();
            $createdAt = date('Y-m-d H:i:s');

            // Insérer l'équipe dans la base de données
            $this->db->beginTransaction();

            try {
                $query = "INSERT INTO {$this->table} (name, description, hackathon_id, type, leader_id, invitation_code, created_at)
                          VALUES (:name, :description, :hackathon_id, :type, :leader_id, :invitation_code, :created_at)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':hackathon_id', $hackathonId, PDO::PARAM_INT);
                $stmt->bindParam(':type', $type);
                $stmt->bindParam(':leader_id', $leaderId, PDO::PARAM_INT);
                $stmt->bindParam(':invitation_code', $invitationCode);
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
            error_log(
                'Erreur lors de la création de l\'équipe: '
                    // Pour le debug
                    . $e->getMessage()
            );
            throw new Exception('Erreur lors de la création de l\'équipe: '
                // Pour le debug
                . $e->getMessage());
        }
    }

    /**
     * Met à jour une équipe
     * @param int $id ID de l'équipe
     * @param array $data Les données à mettre à jour
     * @return bool true si succès, sinon false
     */
    public function update($id, $data)
    {
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
            $allowedFields = ['name', 'description', 'hackathon_id', 'leader_id', 'invitation_code'];

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
    public function delete($id)
    {
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
    public function getByHackathon($hackathonId)
    {
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
    public function getByUser($userId)
    {
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
    public function isMember($teamId, $userId)
    {
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
    public function addMember($teamId, $userId, $isLeader = false)
    {
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
    public function verificateTeamRequest($teamId, $userId)
    {
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

    // fonction pour mettre a jour ou renouveller le code d'invitation
    public function updateTeamCode($teamId, $code)
    {
        try {
            error_log("Team::updateTeamCode appelé pour teamId: $teamId, code: $code");
            $query = "UPDATE {$this->table} SET invitation_code = :code WHERE id = :team_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                error_log("Aucune mise à jour effectuée pour teamId: $teamId");
                throw new Exception("Échec de la mise à jour du code d'invitation : équipe non trouvée");
            }
            error_log("Code d'invitation mis à jour avec succès pour teamId: $teamId");
            return true;
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour du code d\'invitation de l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour du code d\'invitation : ' . $e->getMessage());
        }
    }

    //recuperer toutes les requetes d'adhesions
    public function getAllTeamRequests($teamId)
    {
        try {
            $query = "SELECT * FROM teams_adhesions WHERE teams_id = :teams_id AND status = 'pending'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des requêtes d\'adhésion: ' . $e->getMessage());
            return [];
        }
    }
    //est le leader de l'équipe
    public function isLeader($teamId, $userId)
    {
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
    public function teamRequest($teamId, $userId)
    {
        error_log("Appel de teamRequest avec teamId: " . var_export($teamId, true) . ", userId: " . var_export($userId, true));
        try {
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe', 400);
            }

            // Vérifier s'il existe déjà une demande en attente
            $checkRequest = "SELECT COUNT(*) FROM teams_adhesions 
                           WHERE teams_id = :teams_id AND user_id = :user_id AND status = 'pending'";
            $stmtCheck = $this->db->prepare($checkRequest);
            $stmtCheck->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
            $stmtCheck->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ((int)$stmtCheck->fetchColumn() > 0) {
                throw new Exception('Une demande d\'adhésion est déjà en attente pour cette équipe', 400);
            }

            // Récupérer le leader_id de l'équipe
            $queryLeader = "SELECT id, leader_id, type, name FROM {$this->table} WHERE id = :team_id LIMIT 1";
            $stmtLeader = $this->db->prepare($queryLeader);
            $stmtLeader->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmtLeader->execute();
            $team = $stmtLeader->fetch(PDO::FETCH_ASSOC);

            error_log("Résultat de la requête SQL pour teamId $teamId: " . var_export($team, true));

            if (!$team) {
                throw new Exception('Équipe non trouvée', 404);
            }

            $leaderId = $team['leader_id'];
            $type = $team['type'];

            // Faire une demande d'adhésion
            $query = "INSERT INTO teams_adhesions (teams_id, leader_id, user_id, status, type, joined_at) 
                     VALUES (:teams_id, :leader_id, :user_id, 'pending', :type, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':leader_id', $leaderId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':type', $type, PDO::PARAM_STR);
            $stmt->execute();

            error_log("Demande d'adhésion insérée pour teamId: $teamId, userId: $userId");

            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de la demande d\'adhésion à l\'équipe: ' . $e->getMessage(), (int)($e->getCode() ?: 400));
        }
    }

    /**
     * Accepte une demande d'adhésion
     * @param int $teamId ID de l'équipe
     * @param int $userId ID de l'utilisateur
     * @return bool true si l'acceptation est réussie, sinon false
     */
    public function acceptRequest($teamId, $userId)
    {
        try {
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe');
            }

            // accepte une demande d'adhésion
            $query = "UPDATE teams_adhesions SET status = 'validated' WHERE teams_id = :teams_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
            error_log('Erreur lors de l\'acceptation de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
            throw new Exception('Erreur lors de l\'acceptation de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
        }
    }

    /**
     * Rejette une demande d'adhésion
     * @param int $teamId ID de l'équipe
     * @param int $userId ID de l'utilisateur
     * @return bool true si le rejet est réussi, sinon false
     */
    public function rejectRequest($teamId, $userId)
    {
        try {
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe');
            }

            // accepte une demande d'adhésion
            $query = "UPDATE teams_adhesions SET status = 'rejected' WHERE teams_id = :teams_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
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
    public function removeMember($teamId, $userId)
    {
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
            $query = "DELETE FROM team_members WHERE teams_id = :teams_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
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
    public function changeLeader($teamId, $newLeaderId)
    {
        try {
            // Vérifier si l'utilisateur est membre de l'équipe
            if (!$this->isMember($teamId, $newLeaderId)) {
                throw new Exception('Le nouvel utilisateur n\'est pas membre de cette équipe');
            }

            $this->db->beginTransaction();

            // Mettre à jour le champ leader_id dans la table team_members
            $resetQuery = "UPDATE team_members SET leader_id = NULL WHERE teams_id = :teams_id";
            $resetStmt = $this->db->prepare($resetQuery);
            $resetStmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
            $resetStmt->execute();

            $updateMemberQuery = "UPDATE team_members SET leader_id = :leader_id WHERE teams_id = :teams_id AND user_id = :user_id";
            $updateMemberStmt = $this->db->prepare($updateMemberQuery);
            $updateMemberStmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
            $updateMemberStmt->bindParam(':user_id', $newLeaderId, PDO::PARAM_INT);
            $updateMemberStmt->execute();

            // Mettre à jour le champ leader_id dans la table teams
            $updateTeamQuery = "UPDATE {$this->table} SET leader_id = :leader_id WHERE id = :teams_id";
            $updateTeamStmt = $this->db->prepare($updateTeamQuery);
            $updateTeamStmt->bindParam(':leader_id', $newLeaderId, PDO::PARAM_INT);
            $updateTeamStmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
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
    public function getMembers($teamId)
    {
        error_log("Appel de getMembers avec teamId: " . var_export($teamId, true));
        try {
            // Vérifier si l'équipe existe
            $queryTeam = "SELECT id FROM {$this->table} WHERE id = :team_id LIMIT 1";
            $stmtTeam = $this->db->prepare($queryTeam);
            $stmtTeam->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmtTeam->execute();
            $team = $stmtTeam->fetch(PDO::FETCH_ASSOC);

            if (!$team) {
                error_log("Équipe non trouvée pour teamId: $teamId");
                throw new Exception('Équipe non trouvée', 404);
            }

            // Récupérer les membres acceptés
            $query = "SELECT u.id, u.username, u.fullname
                      FROM team_members tm 
                      JOIN users u ON tm.user_id = u.id 
                      WHERE tm.team_id = :team_id";
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                $errorInfo = $this->db->errorInfo();
                error_log("Erreur de préparation de la requête SQL : " . json_encode($errorInfo));
                throw new Exception("Erreur de préparation de la requête SQL : " . $errorInfo[2], 500);
            }
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->execute();
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Membres récupérés pour teamId $teamId: " . json_encode($members));

            return $members;
        } catch (Exception $e) {
            error_log("Erreur dans getMembers pour teamId $teamId: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
            // Convertir explicitement le code en entier
            $errorCode = is_numeric($e->getCode()) ? (int)$e->getCode() : 500;
            throw new Exception("Une erreur est survenue lors de la récupération des membres de l'équipe : " . $e->getMessage(), $errorCode);
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
    /**
     * Génère un code d'invitation unique pour une équipe au format E8CBC-P3JO-MMAZ
     * @return string Code d'invitation
     */
    private function generateInvitationCode()
    {
        // Génère 6 octets aléatoires -> 12 caractères hex (ex : a1b2c3d4e5f6)
        $code = strtoupper(bin2hex(random_bytes(6))); // 12 caractères

        // Formater le code : XXXX-XXXX-XXXX
        $formattedCode = substr($code, 0, 4) . '-' . substr($code, 4, 4) . '-' . substr($code, 8, 4);

        // Vérifier l'unicité dans la base
        $query = "SELECT id FROM {$this->table} WHERE invitation_code = :code LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':code', $formattedCode);
        $stmt->execute();

        // Si collision, relancer la génération
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return $this->generateInvitationCode(); // Appel récursif si collision
        }

        return $formattedCode;
    }

    /**
     * Allows a user to join a team using an invitation code
     * @param string $code The invitation code
     * @param int $userId ID of the user attempting to join
     * @return int The team ID if successful
     * @throws Exception If the code is invalid or other errors occur
     */
    public function joinTeamViaCode($code, $userId)
    {
        try {
            // Vérifier si le code d'invitation existe
            $query = "SELECT id FROM {$this->table} WHERE invitation_code = :code LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();
            $team = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$team) {
                throw new Exception('Code d\'invitation invalide', 404);
            }

            $teamId = $team['id'];

            // Vérifier si l'utilisateur est déjà membre
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('Vous êtes déjà membre de cette équipe');
            }

            // Vérifier si l'utilisateur a une demande en attente
            $requestQuery = "SELECT COUNT(*) FROM teams_adhesions WHERE teams_id = :teams_id AND user_id = :user_id AND status = 'pending'";
            $requestStmt = $this->db->prepare($requestQuery);
            $requestStmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
            $requestStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $requestStmt->execute();
            if ((int)$requestStmt->fetchColumn() > 0) {
                throw new Exception('Vous avez déjà une demande d\'adhésion en attente pour cette équipe');
            }

            // Ajouter l'utilisateur comme membre
            $this->addMember($teamId, $userId);

            // Supprimer toute demande d'adhésion existante
            $deleteRequestQuery = "DELETE FROM teams_adhesions WHERE teams_id = :teams_id AND user_id = :user_id";
            $deleteRequestStmt = $this->db->prepare($deleteRequestQuery);
            $deleteRequestStmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
            $deleteRequestStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $deleteRequestStmt->execute();

            return $teamId;
        } catch (Exception $e) {
            error_log('Erreur dans joinTeamViaCode: ' . $e->getMessage());
            throw new Exception($e->getMessage(), (int)$e->getCode() ?: 400);
        }
    }
}

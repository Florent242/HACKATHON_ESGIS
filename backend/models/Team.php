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
    public function getAll($userId)
    {
        try {
            $query = "
            SELECT 
                t.id,
                t.name,
                t.description,
                t.type,
                t.leader_id,
                (
                    SELECT COUNT(*) 
                    FROM team_members tm_count 
                    WHERE tm_count.team_id = t.id
                ) AS members_count,
                EXISTS (
                    SELECT 1 
                    FROM team_members tm_check 
                    WHERE tm_check.team_id = t.id AND tm_check.user_id = :userId
                ) AS is_member
            FROM {$this->table} t
            ORDER BY t.name
        ";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir "is_member" de string "0"/"1" à booléen
            foreach ($result as &$row) {
                $row['is_member'] = (bool) $row['is_member'];
            }

            return $result;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des équipes: ' . $e->getMessage());
            throw new Exception(
                'Erreur lors de la récupération des équipes !'
                // Pour le debug
                // . $e->getMessage()
            );
        }
    }


    /**
     * Récupère une équipe par son ID
     * @param int $id ID de l'équipe
     * @return array|bool Les données de l'équipe ou false si non trouvée
     */
    public function find($id, $userId = null)
    {
        try {
            // On récupère l'équipe ainsi que son score
            $query = "SELECT t.*, (
                SELECT COUNT(*) 
                FROM team_members tm_count 
                WHERE tm_count.team_id = t.id
            ) AS members_count,
            EXISTS (
                SELECT 1 
                FROM team_members tm_check 
                WHERE tm_check.team_id = t.id AND tm_check.user_id = :userId
            ) AS is_member,
            (
                SELECT total_points 
                FROM scores s 
                WHERE s.team_id = t.id
            ) AS points
            FROM {$this->table} t 
            WHERE t.id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                return false;
            }
            if ($userId && !$this->isMember($id, $userId)) {

                unset($result['invitation_code']);
            }
            $result['points'] ??= 0;
            return $result;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération de l\'équipe: ' . $e->getMessage());
            throw new Exception(
                'Erreur lors de la récupération de l\'équipe !'
                // Pour le debug
                // . $e->getMessage()
            );
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

            // Vérifier si le nom d'equipe existe deja
            $checkTeamName = "SELECT name FROM {$this->table} WHERE name = :name LIMIT 1";
            $checkTeamNameStmt = $this->db->prepare($checkTeamName);
            $checkTeamNameStmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $checkTeamNameStmt->execute();

            if ($checkTeamNameStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Nom d\'equipe deja utilisé');
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
            $checkLeaderQuery = "SELECT id FROM {$this->table} WHERE leader_id = :id LIMIT 1";
            $checkLeaderStmt = $this->db->prepare($checkLeaderQuery);
            $checkLeaderStmt->bindParam(':id', $data['leader_id'], PDO::PARAM_INT);
            $checkLeaderStmt->execute();

            if ($checkLeaderStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Utilisateur deja leader d\'une equipe');
            }

            // Vérifier si le user est deja membre d'une equipe
            $checkMemberQuery = "SELECT id FROM team_members WHERE user_id = :id LIMIT 1";
            $checkMemberStmt = $this->db->prepare($checkMemberQuery);
            $checkMemberStmt->bindParam(':id', $data['leader_id'], PDO::PARAM_INT);
            $checkMemberStmt->execute();

            if ($checkMemberStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Utilisateur deja membre d\'une equipe ! Vous devez quitter l\'equipe actuelle pour créer une nouvelle equipe');
            }

            // Préparer les données
            $name = $data['name'];
            $description = $data['description'] ?? null;
            $type = $data['type'];
            $leaderId = $data['leader_id'];
            $invitationCode = $this->generateInvitationCode();
            $createdAt = date('Y-m-d H:i:s');

            // Insérer l'équipe dans la base de données
            $this->db->beginTransaction();

            try {
                $query = "INSERT INTO {$this->table} (name, description, type, leader_id, invitation_code, created_at)
                          VALUES (:name, :description, :type, :leader_id, :invitation_code, :created_at)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
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
                logActivity('create', 'Creation d\'une equipe', $teamId, $leaderId);

                return $teamId;
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw new Exception(
                    'Erreur lors de la création de l\'équipe !'
                    // Pour le debug
                    // .$e->getMessage()
                );
            }
        } catch (Exception $e) {
            error_log(
                'Erreur lors de la création de l\'équipe ! '
                // Pour le debug
                // . $e->getMessage()
            );
            throw new Exception($e->getMessage());
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

            // Champs à mettre à jour
            $allowedFields = ['name', 'description', 'leader_id', 'invitation_code'];

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
            error_log(
                'Erreur lors de la mise à jour de l\'équipe ! '
                // Pour le debug
                // . $e->getMessage()
            );
            throw new Exception($e->getMessage());
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
            $team = $this->find($id);
            $hackathonId = $team['hackathon_id'];

            // Verifier si l'équipe est inscrite au hackathon
            if ($hackathonId && $this->isRegisteredToHackathon($id, $hackathonId)) {
                throw new Exception("L'équipe est déjà inscrite a un hackathon, plus aucune modification n'est autorisée !");
            }
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
            throw new Exception(
                'Erreur lors de la suppression de l\'équipe !'
                // Pour le debug
                // . $e->getMessage()
            );
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

            $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($teams)) {
                throw new Exception('Aucune équipe trouvée pour ce hackathon');
            }
            return $teams;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des équipes par hackathon: ' . $e->getMessage());
            throw new Exception(
                'Erreur lors de la récupération des équipes par hackathon !'
                // Pour le debug
                // . $e->getMessage()
            );
        }
    }

    /**
     * Récupère le nombre d'équipes d'un hackathon
     * @param int $hackathonId ID du hackathon
     * @return int Nombre d'équipes
     */
    public function countByHackathon($hackathonId)
    {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE hackathon_id = :hackathon_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hackathon_id', $hackathonId, PDO::PARAM_INT);
            $stmt->execute();

            $count = $stmt->fetchColumn();
            if ($count === false) {
                throw new Exception('Aucun hackathon trouvé');
            }
            return $count;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des équipes par hackathon: ' . $e->getMessage());
            throw new Exception(
                'Erreur lors de la récupération des équipes par hackathon !'
                // Pour le debug
                // . $e->getMessage()
            );
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
            $query = "SELECT t.*, (SELECT COUNT(*) 
                    FROM team_members tm2 
                    WHERE tm2.team_id = t.id) AS members_count FROM {$this->table} t
                    JOIN team_members tm ON t.id = tm.team_id
                    WHERE tm.user_id = :user_id
                    GROUP BY t.id
                    ORDER BY t.name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($teams)) {
                throw new Exception('Aucune équipe trouvée pour cet utilisateur');
            }
            return $teams;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des équipes par utilisateur: ' . $e->getMessage());
            throw new Exception(
                'Erreur lors de la récupération des équipes par utilisateur !'
                // Pour le debug
                // . $e->getMessage()
            );
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
            throw new Exception(
                'Erreur lors de la vérification d\'appartenance à l\'équipe !'
                // Pour le debug
                // . $e->getMessage()
            );
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
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe');
            }

            // trouver le leader de l'équipe
            $team = $this->find($teamId);
            $leaderId = $team['leader_id'];

            $team = $this->find($teamId);
            $hackathonId = $team['hackathon_id'];

            // Verifier si l'équipe est inscrite au hackathon
            if ($hackathonId && $this->isRegisteredToHackathon($teamId, $hackathonId)) {
                throw new Exception("L'équipe est déjà inscrite a un hackathon, plus aucune modification n'est autorisée !");
            }

            // Ajouter le membre
            $query = "INSERT INTO team_members (team_id, user_id, leader_id, joined_at) VALUES (:team_id, :user_id, :leader_id, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':leader_id', $leaderId, PDO::PARAM_INT);
            $stmt->execute();

            // Si c'est le leader, mettre à jour le champ leader_id dans la table teams
            if ($isLeader) {
                $updateQuery = "UPDATE {$this->table} SET leader_id = :leader_id WHERE id = :team_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':leader_id', $userId, PDO::PARAM_INT);
                $updateStmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
                $updateStmt->execute();
            }

            // Mettre à jour toutes les demandes d'adhésion en cours du nouveau membre de l'équipe
            $updateQuery = "UPDATE teams_adhesions SET status = 'rejected' WHERE user_id = :user_id AND status = 'pending'";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $updateStmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erreur lors de l\'ajout du membre à l\'équipe: ' . $e->getMessage());
            throw new Exception(
                'Erreur lors de l\'ajout du membre à l\'équipe !'
                // Pour le debug
                // . $e->getMessage()
            );
        }
    }
    //verifier que seul le leader accepte les demandes d'adhésion
    public function verificateTeamRequest($teamId, $userId)
    {
        try {
            $query = "SELECT COUNT(*) FROM teams_adhesions WHERE teams_id = :teams_id AND user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification de la demande d\'adhésion à l\'équipe: ' . $e->getMessage());
            throw new Exception(
                'Erreur lors de la vérification de la demande d\'adhésion à l\'équipe !'
                // Pour le debug
                // . $e->getMessage()
            );
        }
    }

    // fonction pour mettre a jour ou renouveller le code d'invitation
    public function updateTeamCode($teamId, $code)
    {
        try {
            $team = $this->find($teamId);
            $hackathonId = $team['hackathon_id'];

            // Verifier si l'équipe est inscrite au hackathon
            if ($hackathonId && $this->isRegisteredToHackathon($teamId, $hackathonId)) {
                throw new Exception("L'équipe est déjà inscrite a un hackathon, plus aucune modification n'est autorisée !");
            }

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
            throw new Exception(
                'Erreur lors de la mise à jour du code d\'invitation .'
                // Pour le debug
                // . $e->getMessage()
            );
        }
    }

    //recuperer toutes les requetes d'adhesions
    public function getAllTeamRequests($teamId)
    {
        try {
            $query = "SELECT ta.*, u.username, u.fullname, u.email, u.special_comp 
                     FROM teams_adhesions ta 
                     JOIN users u ON ta.user_id = u.id 
                     WHERE ta.teams_id = :teams_id AND ta.status = 'pending'";
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
            throw new Exception(
                'Erreur lors de la vérification de la demande d\'adhésion à l\'équipe !'
                // Pour le debug
                // . $e->getMessage()
            );
        }
    }

    /**
     * Envoie une demande d'adhésion à une équipe
     * @param int $teamId ID de l'équipe
     * @param int $userId ID de l'utilisateur
     * @throws Exception
     * @return bool true si succès, sinon false
     */
    public function teamRequest($teamId, $userId): array
    {
        try {
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                return [
                    'success' => false,
                    'message' => 'L\'utilisateur est déjà membre de cette équipe',
                    'validated_flag_id' => null
                ];
            }
            $team = $this->find($teamId);
            $hackathonId = $team['hackathon_id'];

            // Verifier si l'équipe est inscrite au hackathon
            if ($hackathonId && $this->isRegisteredToHackathon($teamId, $hackathonId)) {
                throw new Exception("L'équipe est déjà inscrite a un hackathon, plus aucune modification n'est autorisée !");
            }

            // Vérifier s'il existe déjà une demande en attente
            try {
                $checkRequest = "SELECT COUNT(*) FROM teams_adhesions 
                           WHERE teams_id = :teams_id AND user_id = :user_id AND status = 'pending'";
                $stmtCheck = $this->db->prepare($checkRequest);
                $stmtCheck->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
                $stmtCheck->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmtCheck->execute();
            } catch (Exception $e) {
                throw new Exception(
                    'Erreur lors de la vérification de la demande d\'adhésion à l\'équipe !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }

            if ((int)$stmtCheck->fetchColumn() > 0) {
                return [
                    'success' => false,
                    'message' => 'Une demande d\'adhésion est déjà en attente pour cette équipe',
                    'validated_flag_id' => null
                ];
            }

            // Récupérer le leader_id de l'équipe
            try {
                $queryLeader = "SELECT id, leader_id, type, name FROM {$this->table} WHERE id = :team_id LIMIT 1";
                $stmtLeader = $this->db->prepare($queryLeader);
                $stmtLeader->bindParam(':team_id', $teamId, PDO::PARAM_INT);
                $stmtLeader->execute();
                $team = $stmtLeader->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                throw new Exception(
                    'Erreur lors de la récupération de l\'équipe leader !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }

            if (!$team) {
                return [
                    'success' => false,
                    'message' => 'Équipe non trouvée',
                    'validated_flag_id' => null
                ];
            }

            $leaderId = $team['leader_id'];
            $type = $team['type'];

            // Faire une demande d'adhésion
            try {
                $query = "INSERT INTO teams_adhesions (teams_id, leader_id, user_id, status, type, joined_at) 
                        VALUES (:teams_id, :leader_id, :user_id, 'pending', :type, NOW())";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
                $stmt->bindParam(':leader_id', $leaderId, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':type', $type, PDO::PARAM_STR);
                $stmt->execute();
            } catch (Exception $e) {
                throw new Exception(
                    'Erreur lors de la demande d\'adhésion à l\'équipe !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }

            if ($stmt->rowCount() > 0) {
                logActivity('join', 'Vous avez envoyé une demande d\'adhésion a l\'equipe ' . $team['name'], $teamId, $userId);
                return [
                    'success' => true,
                    'message' => 'Demande d\'adhésion envoyée avec succès'
                ];
            }
            return [
                'success' => false,
                'message' => 'Erreur lors de la demande d\'adhésion'
            ];
        } catch (Exception $e) {
            throw new Exception(
                $e->getMessage()
            );
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
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe');
            }

            $team = $this->find($teamId);
            $hackathonId = $team['hackathon_id'];

            // Verifier si l'équipe est inscrite au hackathon
            if ($hackathonId && $this->isRegisteredToHackathon($teamId, $hackathonId)) {
                throw new Exception("L'équipe est déjà inscrite a un hackathon, plus aucune modification n'est autorisée !");
            }

            // Verifier si l'utilisateur est deja membre d'une equipe
            try {
                $teamMemberShip = "SELECT COUNT(*) FROM team_members WHERE user_id = :user_id";
                $stmt = $this->db->prepare($teamMemberShip);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
            } catch (Exception $e) {
                throw new Exception(
                    'Erreur lors de la verification de la demande d\'adhésion à l\'équipe !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }

            if ((int)$stmt->fetchColumn() > 0) {
                // mettre a jour la demande d'adhésion
                try {
                    $query = "UPDATE teams_adhesions SET status = 'rejected' WHERE teams_id = :teams_id AND user_id = :user_id";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
                    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $stmt->execute();
                } catch (Exception $e) {
                    throw new Exception(
                        'Erreur lors de la mise a jour de la demande d\'adhésion !'
                        // Pour le debug
                        // . $e->getMessage()
                    );
                }
                throw new Exception('L\'utilisateur est deja membre d\'une equipe');
            }

            // accepte une demande d'adhésion
            try {
                $query = "UPDATE teams_adhesions SET status = 'validated' WHERE teams_id = :teams_id AND user_id = :user_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
            } catch (Exception $e) {
                throw new Exception(
                    'Erreur lors de l\'acceptation de la demande d\'adhésion !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }

            if ($stmt->rowCount() > 0) {
                // Insere le membre dans l'équipe
                $this->addMember($teamId, $userId);
                if ($this->db->inTransaction()) {
                    $this->db->commit();
                }
                return true;
            }
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception($e->getMessage());
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
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }
            // Vérifier si l'utilisateur est déjà membre de l'équipe
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('L\'utilisateur est déjà membre de cette équipe');
            }
            $team = $this->find($teamId);
            $hackathonId = $team['hackathon_id'];

            // Verifier si l'équipe est inscrite au hackathon
            if ($hackathonId && $this->isRegisteredToHackathon($teamId, $hackathonId)) {
                throw new Exception("L'équipe est déjà inscrite a un hackathon, plus aucune modification n'est autorisée !");
            }

            // accepte une demande d'adhésion
            try {
                $query = "UPDATE teams_adhesions SET status = 'rejected' WHERE teams_id = :teams_id AND user_id = :user_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
            } catch (Exception $e) {
                throw new Exception(
                    'Erreur lors du rejet de la demande d\'adhésion à l\'équipe !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }

            if ($stmt->rowCount() > 0) {
                if ($this->db->inTransaction()) {
                    $this->db->commit();
                }
                return true;
            }
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception($e->getMessage());
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
            $team = $this->find($teamId);
            $hackathonId = $team['hackathon_id'];

            // Verifier si l'équipe est inscrite au hackathon
            if ($hackathonId && $this->isRegisteredToHackathon($teamId, $hackathonId)) {
                throw new Exception("L'équipe est déjà inscrite a un hackathon, plus aucune modification n'est autorisée !");
            }
            // Vérifier si l'utilisateur est le leader de l'équipe
            if ($team && $team['leader_id'] == $userId) {
                throw new Exception('Le leader de l\'équipe ne peut pas être retiré. Changez d\'abord de leader.');
            }

            try {
                // Supprimer le membre
                $query = "DELETE FROM team_members WHERE team_id = :team_id AND user_id = :user_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();

                return true;
            } catch (Exception $e) {
                throw new Exception(
                    'Erreur lors de l\'operation de suppression du membre de l\'équipe !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }
        } catch (Exception $e) {
            error_log('Erreur lors de la suppression du membre de l\'équipe: ' . $e->getMessage());
            throw new Exception($e->getMessage());
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
            // obtenir l'equipe
            $team = $this->find($teamId);
            if (!$team) {
                throw new Exception('Équipe non trouvée');
            }
            $hackathonId = $team['hackathon_id'];
            // Vérifier si l'utilisateur est membre de l'équipe
            if (!$this->isMember($teamId, $newLeaderId)) {
                throw new Exception('Le nouvel utilisateur n\'est pas membre de cette équipe');
            }

            // Verifier si l'équipe est inscrite au hackathon
            if ($hackathonId && $this->isRegisteredToHackathon($teamId, $hackathonId)) {
                throw new Exception("L'équipe est déjà inscrite a un hackathon, plus aucune modification n'est autorisée !");
            }
            try {
                $this->db->beginTransaction();

                // Mettre à jour le champ leader_id dans la table team_members
                $updateMemberQuery = "UPDATE team_members SET leader_id = :leader_id WHERE team_id = :team_id";
                $updateMemberStmt = $this->db->prepare($updateMemberQuery);
                $updateMemberStmt->bindParam(':leader_id', $newLeaderId, PDO::PARAM_INT);
                $updateMemberStmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
                $updateMemberStmt->execute();

                // Mettre à jour le champ leader_id dans la table teams
                $updateTeamQuery = "UPDATE {$this->table} SET leader_id = :leader_id WHERE id = :team_id";
                $updateTeamStmt = $this->db->prepare($updateTeamQuery);
                $updateTeamStmt->bindParam(':leader_id', $newLeaderId, PDO::PARAM_INT);
                $updateTeamStmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
                $updateTeamStmt->execute();

                $this->db->commit();
                logActivity('join', 'Changement du leader de votre equipe', $teamId, $newLeaderId);

                return true;
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                error_log('Erreur lors du changement de leader: ' . $e->getMessage());
                throw new Exception(
                    'Erreur lors de l\'operation de changement de leader !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erreur lors du changement de leader: ' . $e->getMessage());
            throw new Exception($e->getMessage());
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
            $queryTeam = "SELECT id, hackathon_id FROM {$this->table} WHERE id = :team_id LIMIT 1";
            $stmtTeam = $this->db->prepare($queryTeam);
            $stmtTeam->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmtTeam->execute();
            $team = $stmtTeam->fetch(PDO::FETCH_ASSOC);

            if (!$team) {
                error_log("Équipe non trouvée pour teamId: $teamId");
                throw new Exception('Équipe non trouvée');
            }

            $query = "SELECT 
            u.id, 
            u.username, 
            u.fullname, 
            u.email, 
            u.special_comp, 
            u.study_level, 
            SUM(";

            switch ($team['hackathon_id']) {
                case 1:
                    $query .= "IFNULL(vf.points_gained, 0)";
                    break;
                case 2:
                    $query .= "IFNULL(cs.total_score, 0)";
                    break;
                default:
                    $query .= "0";
            }

            $query .= ") AS total_points
            FROM team_members tm
            JOIN users u ON tm.user_id = u.id ";

            switch ($team['hackathon_id']) {
                case 1:
                    $query .= "LEFT JOIN validated_flags vf ON u.id = vf.user_id ";
                    break;
                case 2:
                    $query .= "LEFT JOIN challenge_submissions cs ON u.id = cs.user_id AND cs.status = 'completed' ";
                    break;
            }

            $query .= "WHERE tm.team_id = :team_id 
                   GROUP BY u.id";

            // Préparation
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                $errorInfo = $this->db->errorInfo();
                error_log("Erreur de préparation de la requête SQL : " . json_encode($errorInfo));
                throw new Exception("Erreur de préparation de la requête SQL : " . $errorInfo[2]);
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
            throw new Exception(
                "Une erreur est survenue lors de la récupération des membres de l'équipe !"
                // Pour le debug
                // . $e->getMessage(),$errorCode
            );
        }
    }

    /**
     * Génère un code d'invitation unique pour une équipe au format E8CBC-P3JO-MMAZ
     * @return string Code d'invitation
     */
    private function generateInvitationCode()
    {
        try {
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
        } catch (Exception $e) {
            error_log("Erreur dans generateInvitationCode: " . $e->getMessage());
            throw new Exception(
                "Une erreur est survenue lors de la génération du code d'invitation !"
                // Pour le debug
                // . $e->getMessage()
            );
        }
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
            $query = "SELECT id, hackathon_id, name FROM {$this->table} WHERE invitation_code = :code LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();
            $team = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$team) {
                throw new Exception('Code d\'invitation invalide');
            }

            $teamId = $team['id'];
            $hackathonId = $team['hackathon_id'];

            // Verifier si l'équipe est inscrite au hackathon
            if ($this->isRegisteredToHackathon($teamId, $hackathonId)) {
                throw new Exception("L'équipe est déjà inscrite a un hackathon, plus aucune modification n'est autorisée !");
            }

            // Vérifier si l'utilisateur est déjà membre
            if ($this->isMember($teamId, $userId)) {
                throw new Exception('Vous êtes déjà membre de cette équipe');
            }

            // Vérifier si l'utilisateur est deja dans une equipe
            try {
                $query = "SELECT COUNT(*) FROM team_members WHERE user_id = :user_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();

                $stmt->fetchColumn();
            } catch (PDOException $e) {
                error_log('Erreur lors de la vérification d\'appartenance à l\'équipe: ' . $e->getMessage());
                throw new Exception(
                    'Erreur lors de la vérification d\'appartenance à l\'équipe !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }

            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Vous êtes déjà membre d\'une equipe');
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

            try {
                // Ajouter l'utilisateur comme membre
                $this->addMember($teamId, $userId);

                // Supprimer toute demande d'adhésion existante
                $deleteRequestQuery = "DELETE FROM teams_adhesions WHERE teams_id = :teams_id AND user_id = :user_id";
                $deleteRequestStmt = $this->db->prepare($deleteRequestQuery);
                $deleteRequestStmt->bindParam(':teams_id', $teamId, PDO::PARAM_INT);
                $deleteRequestStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $deleteRequestStmt->execute();
                logActivity('join', 'User ' . $userId . 'Adhesion a l\'equipe ' . $team['name'] . 'via code invitation ', $teamId, $userId);

                return $teamId;
            } catch (Exception $e) {
                error_log('Erreur lors de l\'adhésion à l\'équipe via code invitation: ' . $e->getMessage());
                throw new Exception(
                    'Erreur lors de l\'adhésion à l\'équipe via code invitation !'
                    // Pour le debug
                    // . $e->getMessage()
                );
            }
        } catch (Exception $e) {
            error_log('Erreur dans joinTeamViaCode: ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Vérifie si une équipe est inscrite à un hackathon
     * @param int $teamId ID de l'équipe
     * @return bool true si l'équipe est inscrite à au moins un hackathon, sinon false
     */
    public function isRegisteredToHackathon($teamId, $hackathonId)
    {
        try {
            // On suppose que la table teams a une colonne hackathon_id non nulle quand l'équipe est inscrite
            $query = "SELECT COUNT(*) FROM hackathon_teams WHERE team_id = :team_id AND hackathon_id = :hackathon_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->bindParam(':hackathon_id', $hackathonId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification d\'inscription au hackathon: ' . $e->getMessage());
            throw new Exception(
                'Erreur lors de la vérification d\'inscription au hackathon !'
                // Pour le debug
                // . $e->getMessage()
            );
        }
    }
}

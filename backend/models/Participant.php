<?php

namespace Auth\Model;

use Exception;
use PDOException;
use PDO;

class Participant
{
    private $db;
    private $table = 'hackathon_participants';

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Inscrire un participant à un hackathon
    public function register($data)
    {
        try {
            $this->validate($data);

            // Vérifier si le participant n'est pas déjà inscrit
            if ($this->isRegistered($data['hackathon_id'], $data['user_id'])) {
                throw new Exception("Vous êtes déjà inscrit à ce hackathon");
            }

            $sql = "INSERT INTO {$this->table} (hackathon_id, user_id, status)
                    VALUES (:hackathon_id, :user_id, :status)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $data['hackathon_id'],
                ':user_id' => $data['user_id'],
                ':status' => 'pending'
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'inscription : " . $e->getMessage());
        }
    }

    /**
     * Inscrire une équipe à un hackathon
     */
    public function registerTeam($hackathonId, $teamId, $captainId)
    {
        try {
            $this->db->beginTransaction();
            // Vérifier que c'est bien le capitaine
            $stmt = $this->db->prepare("SELECT leader_id FROM teams WHERE id = :team_id");
            $stmt->execute([':team_id' => $teamId]);
            $team = $stmt->fetch();

            if (!$team || $team['leader_id'] != $captainId) {
                throw new Exception("Seul le capitaine peut inscrire cette équipe");
            }

            // Vérifier que l’équipe n’est pas déjà inscrite
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM hackathon_teams WHERE hackathon_id = :hackathon_id AND team_id = :team_id");
            $stmt->execute([':hackathon_id' => $hackathonId, ':team_id' => $teamId]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("L’équipe est déjà inscrite à ce hackathon !");
            }

            // Verifier si la date limite d'inscription est passée
            $stmt = $this->db->prepare("SELECT registration_deadline FROM hackathons WHERE id = :hackathon_id");
            $stmt->execute([':hackathon_id' => $hackathonId]);
            $endDate = $stmt->fetchColumn();
            if (time() > strtotime($endDate)) {
                throw new Exception("La date limite d'inscription est passée !");
            }

            // Inscription dans hackathon_teams
            $stmt = $this->db->prepare("INSERT INTO hackathon_teams (hackathon_id, team_id, leader_id) VALUES (:hackathon_id, :team_id, :leader_id)");
            $stmt->execute([':hackathon_id' => $hackathonId, ':team_id' => $teamId, ':leader_id' => $captainId]);
            logActivity('Team registration' , 'Inscription d\'une équipe ', [$captainId, $teamId, $hackathonId],$captainId, 'info');

            // Récupérer tous les membres
            $stmt = $this->db->prepare("SELECT user_id FROM team_members WHERE team_id = :team_id");
            $stmt->execute([':team_id' => $teamId]);
            $members = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Inscription dans hackathon_participants
            $stmt = $this->db->prepare("INSERT INTO hackathon_participants (user_id, team_id, hackathon_id, participation_status) VALUES (:user_id, :team_id, :hackathon_id, 'pending')");
            foreach ($members as $memberId) {
                // Vérifier si le participant n'est pas déjà inscrit
                if (!$this->isRegistered($hackathonId, $memberId)) {
                    $stmt->execute([':user_id' => $memberId, ':team_id' => $teamId, ':hackathon_id' => $hackathonId]);
                    logActivity('Team registration' , 'Vous avez été automatiquement inscrit au hackathon suite a l\'inscription de votre équipe', ['memberId' => $memberId , 'teamId' => $teamId, 'hackathonId' => $hackathonId], $memberId, 'info');
                }
            }

            // mise a jour de hackathon_id de l'equipe dans la table teams
            $stmt = $this->db->prepare("UPDATE teams SET hackathon_id = :hackathon_id WHERE id = :team_id");
            $stmt->execute([':hackathon_id' => $hackathonId, ':team_id' => $teamId]);
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new Exception(
                "Erreur lors de l'inscription de l'équipe ! En cas de probleme permanant contactez le support technique sur discord ! "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }

    public function unregisterTeam($hackathonId, $teamId)
    {
        try {
            // Supprimer les membres de l'équipe de la table participants
            $sql = "DELETE FROM hackathon_participants
                WHERE hackathon_id = :hackathon_id AND team_id = :team_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $hackathonId,
                ':team_id' => $teamId
            ]);
            logActivity('Team registration' , 'Désinscription d\'une équipe ', [$teamId, $hackathonId], $teamId, 'info');
$stmt = $this->db->prepare('');
            // Supprimer l'équipe du hackathon
            $sql2 = "DELETE FROM hackathon_teams
                 WHERE hackathon_id = :hackathon_id AND team_id = :team_id";
            $stmt2 = $this->db->prepare($sql2);
            return $stmt2->execute([
                ':hackathon_id' => $hackathonId,
                ':team_id' => $teamId
            ]);
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de la désinscription de l’équipe !"
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }


    // Alias de register pour la cohérence avec les autres modèles
    // public function create($data)
    // {
    //     return $this->register($data);
    // }

    // Vérifier si un utilisateur est déjà inscrit à un hackathon
    public function isRegistered($hackathonId, $userId)
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}
                    WHERE hackathon_id = :hackathon_id
                    AND user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $hackathonId,
                ':user_id' => $userId
            ]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de la vérification : "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }

    // Trouver une inscription par son ID
    public function find($id)
    {
        try {
            $sql = "SELECT p.*, u.username, u.email,
                    h.name as hackathon_title, h.start_date, h.end_date
                    FROM {$this->table} p
                    INNER JOIN users u ON p.user_id = u.id
                    INNER JOIN hackathons h ON p.hackathon_id = h.id
                    WHERE p.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de la recherche de l'inscription ! En cas de probleme permanant contactez le support technique sur discord ! : "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }

    // Mettre à jour le statut d'une inscription
    public function updateStatus($id, $status)
    {
        try {
            if (!in_array($status, ['pending', 'approved', 'rejected'])) {
                throw new Exception("Statut invalide");
            }

            $sql = "UPDATE {$this->table}
                    SET status = :status, updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de la mise à jour du statut ! En cas de probleme permanant contactez le support technique sur discord ! : "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }

    // Annuler une inscription
    public function cancel($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de l'annulation de l'inscription ! "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }

    // Récupérer les participants d'un hackathon
    public function getByHackathon($hackathonId, $status = null)
    {
        try {
            $sql = "SELECT p.*, u.username, u.email,
                    e.id as team_id, e.name as team_name
                    FROM {$this->table} p
                    INNER JOIN users u ON p.user_id = u.id
                    LEFT JOIN team_members em ON u.id = em.user_id
                    LEFT JOIN team e ON em.team_id = e.id AND e.hackathon_id = p.hackathon_id
                    WHERE p.hackathon_id = :hackathon_id";

            if ($status) {
                $sql .= " AND p.status = :status";
            }

            $sql .= " ORDER BY p.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $params = [':hackathon_id' => $hackathonId];

            if ($status) {
                $params[':status'] = $status;
            }

            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de la récupération des participants ! "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }


    // Récupérer les hackathons d'un participant
    public function getByUser($userId, $jwt)
    {
        try {
            $sql = "SELECT p.*, h.name as hackathon_title, h.start_date, h.end_date, e.id as team_id,
             e.name as team_name 
             FROM {$this->table} p 
             INNER JOIN hackathons h ON p.hackathon_id = h.id 
             LEFT JOIN team_members em ON p.user_id = em.user_id 
             LEFT JOIN team e ON em.team_id = e.id AND e.hackathon_id = p.hackathon_id 
             WHERE p.user_id = :user_id ORDER BY h.start_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de la récupération des hackathons ! "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }

    // Compter le nombre de participants par statut
    public function countByStatus($hackathonId, $specificStatus = null)
    {
        try {
            if ($specificStatus === null) {
                // Version originale qui retourne tous les statuts
                $sql = "SELECT status, COUNT(*) as count
                        FROM {$this->table}
                        WHERE hackathon_id = :hackathon_id
                        GROUP BY status";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([':hackathon_id' => $hackathonId]);
                return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            } else {
                // Version pour compter un statut spécifique
                $sql = "SELECT COUNT(*) as count
                        FROM {$this->table}
                        WHERE hackathon_id = :hackathon_id";

                $params = [':hackathon_id' => $hackathonId];

                if ($specificStatus !== 'all') {
                    $sql .= " AND status = :status";
                    $params[':status'] = $specificStatus;
                }

                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchColumn();
            }
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors du comptage des participants ! : "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }

    // Mettre à jour un participant
    public function update($id, $data)
    {
        try {
            $fields = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'hackathon_id' && $key !== 'user_id') {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            if (empty($fields)) {
                throw new Exception("Aucune donnée à mettre à jour");
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de la mise à jour du participant ! "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }

    // Supprimer une inscription
    public function delete($id)
    {
        try {
            // Vérifier si le participant existe
            $participant = $this->find($id);
            if (!$participant) {
                throw new Exception("Participant non trouvé");
            }

            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de la suppression du participant ! "
                // pour le debugage
                //  . $e->getMessage()
            );
        }
    }

    // Validation des données
    private function validate($data)
    {
        if (empty($data['hackathon_id'])) {
            throw new Exception("Le hackathon est obligatoire");
        }

        if (empty($data['user_id'])) {
            throw new Exception("L'utilisateur est obligatoire");
        }

        // Vérifier si le hackathon est ouvert aux inscriptions
        $sql = "SELECT status, max_participants FROM hackathons
                WHERE id = :hackathon_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':hackathon_id' => $data['hackathon_id']]);
        $hackathon = $stmt->fetch();

        if (!$hackathon) {
            throw new Exception("Hackathon non trouvé");
        }

        if ($hackathon['status'] !== 'published') {
            throw new Exception("Les inscriptions ne sont pas encore ouvertes");
        }

        // Vérifier si le hackathon n'est pas complet
        if ($hackathon['max_participants']) {
            $sql = "SELECT COUNT(*) FROM {$this->table}
                    WHERE hackathon_id = :hackathon_id
                    AND status != 'rejected'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':hackathon_id' => $data['hackathon_id']]);

            if ($stmt->fetchColumn() >= $hackathon['max_participants']) {
                throw new Exception("Le hackathon est complet");
            }
        }
    }
}

<?php

class EquipeMembre {
    private $db;
    private $table = 'equipe_membres';

    public function __construct() {
        $this->db = require_once __DIR__ . '/../../database/database.php';
    }

    // Ajouter un membre à une équipe
    public function create($data) {
        try {
            $this->validate($data);

            // Vérifier si le membre n'est pas déjà dans une équipe pour ce hackathon
            if ($this->isInTeam($data['user_id'], $data['hackathon_id'])) {
                throw new Exception("L'utilisateur est déjà membre d'une équipe pour ce hackathon");
            }

            $sql = "INSERT INTO {$this->table} (equipe_id, user_id, role) 
                    VALUES (:equipe_id, :user_id, :role)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':equipe_id' => $data['equipe_id'],
                ':user_id' => $data['user_id'],
                ':role' => $data['role'] ?? 'member'
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'ajout du membre : " . $e->getMessage());
        }
    }

    // Vérifier si un utilisateur est déjà dans une équipe pour un hackathon
    public function isInTeam($userId, $hackathonId) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} em
                    INNER JOIN equipes e ON em.equipe_id = e.id
                    WHERE em.user_id = :user_id 
                    AND e.hackathon_id = :hackathon_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':hackathon_id' => $hackathonId
            ]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la vérification : " . $e->getMessage());
        }
    }

    // Trouver un membre par son ID
    public function find($id) {
        try {
            $sql = "SELECT em.*, u.username, u.email,
                    e.name as equipe_name, e.hackathon_id,
                    h.title as hackathon_title
                    FROM {$this->table} em
                    INNER JOIN users u ON em.user_id = u.id
                    INNER JOIN equipes e ON em.equipe_id = e.id
                    INNER JOIN hackathons h ON e.hackathon_id = h.id
                    WHERE em.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche du membre : " . $e->getMessage());
        }
    }

    // Mettre à jour le rôle d'un membre
    public function updateRole($id, $role) {
        try {
            if (!in_array($role, ['leader', 'member'])) {
                throw new Exception("Rôle invalide");
            }

            $sql = "UPDATE {$this->table} 
                    SET role = :role, updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':role' => $role
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour du rôle : " . $e->getMessage());
        }
    }

    // Retirer un membre d'une équipe
    public function remove($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du retrait du membre : " . $e->getMessage());
        }
    }

    // Récupérer les membres d'une équipe
    public function getByEquipe($equipeId) {
        try {
            $sql = "SELECT em.*, u.username, u.email
                    FROM {$this->table} em
                    INNER JOIN users u ON em.user_id = u.id
                    WHERE em.equipe_id = :equipe_id
                    ORDER BY em.role DESC, em.created_at ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':equipe_id' => $equipeId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des membres : " . $e->getMessage());
        }
    }

    // Récupérer les équipes d'un membre
    public function getByUser($userId) {
        try {
            $sql = "SELECT em.*, e.name as equipe_name,
                    h.id as hackathon_id, h.title as hackathon_title,
                    h.start_date, h.end_date, h.status as hackathon_status
                    FROM {$this->table} em
                    INNER JOIN equipes e ON em.equipe_id = e.id
                    INNER JOIN hackathons h ON e.hackathon_id = h.id
                    WHERE em.user_id = :user_id
                    ORDER BY h.start_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des équipes : " . $e->getMessage());
        }
    }

    // Vérifier si un utilisateur est le leader d'une équipe
    public function isLeader($userId, $equipeId) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}
                    WHERE user_id = :user_id 
                    AND equipe_id = :equipe_id 
                    AND role = 'leader'";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':equipe_id' => $equipeId
            ]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la vérification : " . $e->getMessage());
        }
    }

    // Compter le nombre de membres dans une équipe
    public function countMembers($equipeId) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}
                    WHERE equipe_id = :equipe_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':equipe_id' => $equipeId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du comptage des membres : " . $e->getMessage());
        }
    }

    // Validation des données
    private function validate($data) {
        if (empty($data['equipe_id'])) {
            throw new Exception("L'équipe est obligatoire");
        }

        if (empty($data['user_id'])) {
            throw new Exception("L'utilisateur est obligatoire");
        }

        // Vérifier si l'équipe existe et n'est pas complète
        $sql = "SELECT e.*, h.max_team_members 
                FROM equipes e
                INNER JOIN hackathons h ON e.hackathon_id = h.id
                WHERE e.id = :equipe_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':equipe_id' => $data['equipe_id']]);
        $equipe = $stmt->fetch();

        if (!$equipe) {
            throw new Exception("Équipe non trouvée");
        }

        // Vérifier la limite de membres
        if ($equipe['max_team_members']) {
            $currentMembers = $this->countMembers($data['equipe_id']);
            if ($currentMembers >= $equipe['max_team_members']) {
                throw new Exception("L'équipe est complète");
            }
        }

        // Vérifier si l'utilisateur est inscrit au hackathon
        $sql = "SELECT status FROM participants
                WHERE user_id = :user_id 
                AND hackathon_id = :hackathon_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':hackathon_id' => $equipe['hackathon_id']
        ]);
        $participant = $stmt->fetch();

        if (!$participant || $participant['status'] !== 'approved') {
            throw new Exception("L'utilisateur doit être un participant approuvé du hackathon");
        }
    }
}

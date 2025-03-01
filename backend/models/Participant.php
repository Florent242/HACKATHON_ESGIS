<?php

class Participant {
    private $db;
    private $table = 'participants';

    public function __construct() {
        $this->db = require_once __DIR__ . '/Database.php';
    }

    // Inscrire un participant à un hackathon
    public function register($data) {
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

    // Vérifier si un utilisateur est déjà inscrit à un hackathon
    public function isRegistered($hackathonId, $userId) {
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
            throw new Exception("Erreur lors de la vérification : " . $e->getMessage());
        }
    }

    // Trouver une inscription par son ID
    public function find($id) {
        try {
            $sql = "SELECT p.*, u.username, u.email,
                    h.title as hackathon_title, h.start_date, h.end_date
                    FROM {$this->table} p
                    INNER JOIN users u ON p.user_id = u.id
                    INNER JOIN hackathons h ON p.hackathon_id = h.id
                    WHERE p.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de l'inscription : " . $e->getMessage());
        }
    }

    // Mettre à jour le statut d'une inscription
    public function updateStatus($id, $status) {
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
            throw new Exception("Erreur lors de la mise à jour du statut : " . $e->getMessage());
        }
    }

    // Annuler une inscription
    public function cancel($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'annulation de l'inscription : " . $e->getMessage());
        }
    }

    // Récupérer les participants d'un hackathon
    public function getByHackathon($hackathonId, $status = null) {
        try {
            $sql = "SELECT p.*, u.username, u.email,
                    e.id as equipe_id, e.name as equipe_name
                    FROM {$this->table} p
                    INNER JOIN users u ON p.user_id = u.id
                    LEFT JOIN equipe_membres em ON u.id = em.user_id
                    LEFT JOIN equipes e ON em.equipe_id = e.id AND e.hackathon_id = p.hackathon_id
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
            throw new Exception("Erreur lors de la récupération des participants : " . $e->getMessage());
        }
    }

    // Récupérer les hackathons d'un participant
    public function getByUser($userId) {
        try {
            $sql = "SELECT p.*, h.title as hackathon_title,
                    h.start_date, h.end_date, h.status as hackathon_status,
                    e.id as equipe_id, e.name as equipe_name
                    FROM {$this->table} p
                    INNER JOIN hackathons h ON p.hackathon_id = h.id
                    LEFT JOIN equipe_membres em ON p.user_id = em.user_id
                    LEFT JOIN equipes e ON em.equipe_id = e.id AND e.hackathon_id = p.hackathon_id
                    WHERE p.user_id = :user_id
                    ORDER BY h.start_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des hackathons : " . $e->getMessage());
        }
    }

    // Compter le nombre de participants par statut
    public function countByStatus($hackathonId) {
        try {
            $sql = "SELECT status, COUNT(*) as count
                    FROM {$this->table}
                    WHERE hackathon_id = :hackathon_id
                    GROUP BY status";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':hackathon_id' => $hackathonId]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du comptage des participants : " . $e->getMessage());
        }
    }

    // Validation des données
    private function validate($data) {
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

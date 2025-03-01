<?php

class Equipe {
    private $db;
    private $table = 'equipes';

    public function __construct($db) {
        $this->db = $db;
    }

    // Créer une nouvelle équipe
    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} (name, hackathon_id, created_by) 
                    VALUES (:name, :hackathon_id, :created_by)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':hackathon_id' => $data['hackathon_id'],
                ':created_by' => $data['created_by']
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de l'équipe : " . $e->getMessage());
        }
    }

    // Ajouter un membre à l'équipe
    public function addMember($equipeId, $userId, $role = 'member') {
        try {
            $sql = "INSERT INTO membres_equipe (equipe_id, user_id, role) 
                    VALUES (:equipe_id, :user_id, :role)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':equipe_id' => $equipeId,
                ':user_id' => $userId,
                ':role' => $role
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'ajout du membre : " . $e->getMessage());
        }
    }

    // Récupérer les membres d'une équipe
    public function getMembers($equipeId) {
        try {
            $sql = "SELECT u.*, me.role as team_role 
                    FROM users u 
                    JOIN membres_equipe me ON u.id = me.user_id 
                    WHERE me.equipe_id = :equipe_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':equipe_id' => $equipeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des membres : " . $e->getMessage());
        }
    }

    // Trouver une équipe par son ID
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour une équipe
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET ";
        $params = [];
        
        foreach ($data as $key => $value) {
            $sql .= "$key = :$key, ";
            $params[":$key"] = $value;
        }
        
        $sql = rtrim($sql, ', ') . " WHERE id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Supprimer une équipe
    public function delete($id) {
        // Supprimer d'abord les membres de l'équipe
        $sql = "DELETE FROM membres_equipe WHERE equipe_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Puis supprimer l'équipe
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Récupérer les équipes par hackathon
    public function getByHackathon($hackathonId) {
        $sql = "SELECT e.*, COUNT(me.user_id) as membre_count 
                FROM {$this->table} e 
                LEFT JOIN membres_equipe me ON e.id = me.equipe_id 
                WHERE e.hackathon_id = :hackathon_id 
                GROUP BY e.id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':hackathon_id', $hackathonId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

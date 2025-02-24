<?php

class Evaluation {
    private $db;
    private $table = 'evaluations';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} (projet_id, juge_id, score, commentaire) 
                    VALUES (:projet_id, :juge_id, :score, :commentaire)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':projet_id' => $data['projet_id'],
                ':juge_id' => $data['juge_id'],
                ':score' => $data['score'],
                ':commentaire' => $data['commentaire'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de l'évaluation : " . $e->getMessage());
        }
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getByProjet($projetId) {
        $sql = "SELECT e.*, u.username as juge_nom 
                FROM {$this->table} e 
                LEFT JOIN users u ON e.juge_id = u.id 
                WHERE e.projet_id = :projet_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':projet_id', $projetId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectScores($projetId) {
        try {
            $sql = "SELECT AVG(score) as average_score,
                           COUNT(*) as evaluation_count,
                           MIN(score) as min_score,
                           MAX(score) as max_score
                    FROM {$this->table}
                    WHERE projet_id = :projet_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':projet_id' => $projetId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des scores : " . $e->getMessage());
        }
    }
}

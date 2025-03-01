<?php

class Projet {
    private $db;
    private $table = 'projets';

    public function __construct($db) {
        $this->db = $db;
    }

    private function validate($data) {
        if (empty($data['title'])) {
            throw new Exception("Le titre du projet est requis");
        }

        if (empty($data['description'])) {
            throw new Exception("La description du projet est requise");
        }

        if (empty($data['equipe_id'])) {
            throw new Exception("L'ID de l'équipe est requis");
        }

        if (empty($data['challenge_id'])) {
            throw new Exception("L'ID du challenge est requis");
        }

        if (!empty($data['repository_url']) && !filter_var($data['repository_url'], FILTER_VALIDATE_URL)) {
            throw new Exception("L'URL du dépôt n'est pas valide");
        }

        if (!empty($data['demo_url']) && !filter_var($data['demo_url'], FILTER_VALIDATE_URL)) {
            throw new Exception("L'URL de la démo n'est pas valide");
        }

        if (!empty($data['status']) && !in_array($data['status'], ['draft', 'submitted', 'evaluated'])) {
            throw new Exception("Le statut n'est pas valide");
        }
    }

    public function create($data) {
        try {
            $this->validate($data);

            $sql = "INSERT INTO {$this->table} (title, description, equipe_id, challenge_id, repository_url, demo_url, status) 
                    VALUES (:title, :description, :equipe_id, :challenge_id, :repository_url, :demo_url, :status)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':equipe_id' => $data['equipe_id'],
                ':challenge_id' => $data['challenge_id'],
                ':repository_url' => $data['repository_url'] ?? null,
                ':demo_url' => $data['demo_url'] ?? null,
                ':status' => $data['status'] ?? 'draft'
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création du projet : " . $e->getMessage());
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
        try {
            if (!empty($data)) {
                $this->validate($data);
            }

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
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour du projet : " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression du projet : " . $e->getMessage());
        }
    }

    public function getByEquipe($equipeId) {
        $sql = "SELECT * FROM {$this->table} WHERE equipe_id = :equipe_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':equipe_id', $equipeId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByChallenge($challengeId) {
        try {
            $sql = "SELECT p.*, e.name as team_name, 
                           COUNT(DISTINCT ev.id) as evaluation_count,
                           AVG(ev.score) as average_score
                    FROM {$this->table} p
                    JOIN equipes e ON p.equipe_id = e.id
                    LEFT JOIN evaluations ev ON p.id = ev.projet_id
                    WHERE p.challenge_id = :challenge_id
                    GROUP BY p.id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':challenge_id' => $challengeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des projets : " . $e->getMessage());
        }
    }

    public function submitProject($id, $repoUrl, $demoUrl = null) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET repository_url = :repository_url, 
                        demo_url = :demo_url,
                        status = 'submitted',
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':repository_url' => $repoUrl,
                ':demo_url' => $demoUrl
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la soumission du projet : " . $e->getMessage());
        }
    }
}

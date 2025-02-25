<?php

class Ressource {
    private $db;
    private $table = 'ressources';

    public function __construct() {
        $this->db = require_once __DIR__ . '/Database.php';
    }

    // Créer une nouvelle ressource
    public function create($data) {
        try {
            $this->validate($data);

            $sql = "INSERT INTO {$this->table} (hackathon_id, title, description, type, url, created_by) 
                    VALUES (:hackathon_id, :title, :description, :type, :url, :created_by)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $data['hackathon_id'],
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':type' => $data['type'],
                ':url' => $data['url'],
                ':created_by' => $data['created_by']
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de la ressource : " . $e->getMessage());
        }
    }

    // Trouver une ressource par son ID
    public function find($id) {
        try {
            $sql = "SELECT r.*, u.username as created_by_name,
                    h.title as hackathon_title
                    FROM {$this->table} r
                    INNER JOIN users u ON r.created_by = u.id
                    INNER JOIN hackathons h ON r.hackathon_id = h.id
                    WHERE r.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de la ressource : " . $e->getMessage());
        }
    }

    // Mettre à jour une ressource
    public function update($id, $data) {
        try {
            $this->validate($data);

            $sql = "UPDATE {$this->table} 
                    SET title = :title,
                        description = :description,
                        type = :type,
                        url = :url,
                        updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':type' => $data['type'],
                ':url' => $data['url']
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour de la ressource : " . $e->getMessage());
        }
    }

    // Supprimer une ressource
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de la ressource : " . $e->getMessage());
        }
    }

    // Récupérer les ressources d'un hackathon
    public function getByHackathon($hackathonId, $type = null) {
        try {
            $sql = "SELECT r.*, u.username as created_by_name
                    FROM {$this->table} r
                    INNER JOIN users u ON r.created_by = u.id
                    WHERE r.hackathon_id = :hackathon_id";

            if ($type) {
                $sql .= " AND r.type = :type";
            }

            $sql .= " ORDER BY r.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $params = [':hackathon_id' => $hackathonId];
            
            if ($type) {
                $params[':type'] = $type;
            }

            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des ressources : " . $e->getMessage());
        }
    }

    // Récupérer les ressources par créateur
    public function getByCreator($userId) {
        try {
            $sql = "SELECT r.*, h.title as hackathon_title
                    FROM {$this->table} r
                    INNER JOIN hackathons h ON r.hackathon_id = h.id
                    WHERE r.created_by = :created_by
                    ORDER BY r.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':created_by' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des ressources : " . $e->getMessage());
        }
    }

    // Rechercher des ressources
    public function search($hackathonId, $query) {
        try {
            $sql = "SELECT r.*, u.username as created_by_name
                    FROM {$this->table} r
                    INNER JOIN users u ON r.created_by = u.id
                    WHERE r.hackathon_id = :hackathon_id
                    AND (r.title LIKE :query 
                    OR r.description LIKE :query)
                    ORDER BY r.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $hackathonId,
                ':query' => "%{$query}%"
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche des ressources : " . $e->getMessage());
        }
    }

    // Validation des données
    private function validate($data) {
        if (empty($data['title'])) {
            throw new Exception("Le titre est obligatoire");
        }

        if (empty($data['type'])) {
            throw new Exception("Le type est obligatoire");
        }

        if (!in_array($data['type'], ['document', 'video', 'image', 'code', 'other'])) {
            throw new Exception("Type de ressource invalide");
        }

        if (empty($data['url'])) {
            throw new Exception("L'URL est obligatoire");
        }

        if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            throw new Exception("L'URL est invalide");
        }

        if (isset($data['hackathon_id'])) {
            // Vérifier si le hackathon existe
            $sql = "SELECT id FROM hackathons WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $data['hackathon_id']]);
            
            if (!$stmt->fetch()) {
                throw new Exception("Hackathon non trouvé");
            }
        }
    }
}

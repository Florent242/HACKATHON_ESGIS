<?php

class Ressource {
    private $db;
    private $table = 'ressources';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        try {
            $this->validate($data);

            $sql = "INSERT INTO {$this->table} (hackathon_id, titre, description, type, url, created_by, created_at) 
                    VALUES (:hackathon_id, :titre, :description, :type, :url, :created_by, :created_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $data['hackathon_id'],
                ':titre' => $data['titre'],
                ':description' => $data['description'],
                ':type' => $data['type'],
                ':url' => $data['url'],
                ':created_by' => $data['created_by'],
                ':created_at' => $data['created_at']
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de la ressource : " . $e->getMessage());
        }
    }

    public function find($id) {
        try {
            $sql = "SELECT r.*, u.nom as created_by_nom, u.prenom as created_by_prenom,
                    h.titre as hackathon_titre
                    FROM {$this->table} r
                    INNER JOIN users u ON r.created_by = u.id
                    INNER JOIN hackathons h ON r.hackathon_id = h.id
                    WHERE r.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de la ressource : " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        try {
            if (isset($data['type'])) {
                $this->validateType($data['type']);
            }

            $fields = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "{$key} = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour de la ressource : " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de la ressource : " . $e->getMessage());
        }
    }

    public function getByHackathon($hackathonId) {
        try {
            $sql = "SELECT r.*, u.nom as created_by_nom, u.prenom as created_by_prenom
                    FROM {$this->table} r
                    INNER JOIN users u ON r.created_by = u.id
                    WHERE r.hackathon_id = :hackathon_id
                    ORDER BY r.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':hackathon_id' => $hackathonId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des ressources : " . $e->getMessage());
        }
    }

    public function search($hackathonId, $query, $type = null) {
        try {
            $sql = "SELECT r.*, u.nom as created_by_nom, u.prenom as created_by_prenom
                    FROM {$this->table} r
                    INNER JOIN users u ON r.created_by = u.id
                    WHERE r.hackathon_id = :hackathon_id
                    AND (r.titre LIKE :query 
                    OR r.description LIKE :query)";

            if ($type) {
                $sql .= " AND r.type = :type";
            }

            $sql .= " ORDER BY r.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $params = [
                ':hackathon_id' => $hackathonId,
                ':query' => "%{$query}%"
            ];

            if ($type) {
                $params[':type'] = $type;
            }

            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche des ressources : " . $e->getMessage());
        }
    }

    private function validate($data) {
        if (empty($data['titre'])) {
            throw new Exception("Le titre est obligatoire");
        }

        if (empty($data['description'])) {
            throw new Exception("La description est obligatoire");
        }

        if (empty($data['type'])) {
            throw new Exception("Le type est obligatoire");
        }

        $this->validateType($data['type']);

        if (empty($data['hackathon_id'])) {
            throw new Exception("L'ID du hackathon est obligatoire");
        }

        if (!is_numeric($data['hackathon_id'])) {
            throw new Exception("L'ID du hackathon doit être un nombre");
        }

        // Vérifier si le hackathon existe
        $sql = "SELECT id FROM hackathons WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $data['hackathon_id']]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Hackathon non trouvé");
        }
    }

    private function validateType($type) {
        if (!in_array($type, ['document', 'video', 'lien'])) {
            throw new Exception("Type de ressource invalide");
        }
    }
}

<?php
namespace Auth\Model;

use Exception;
use PDO;
use PDOException;

class Challenge {
    private $db;
    private $table = 'challenges';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        try {
            $this->validate($data);

            $sql = "INSERT INTO {$this->table} (titre, description, hackathon_id, points, created_by, created_at) 
                    VALUES (:titre, :description, :hackathon_id, :points, :created_by, :created_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':titre' => $data['titre'],
                ':description' => $data['description'],
                ':hackathon_id' => $data['hackathon_id'],
                ':points' => $data['points'],
                ':created_by' => $data['created_by'],
                ':created_at' => $data['created_at']
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création du challenge : " . $e->getMessage());
        }
    }

    public function find($id) {
        try {
            $sql = "SELECT c.*, u.nom as created_by_nom, u.prenom as created_by_prenom,
                    h.titre as hackathon_titre,
                    COUNT(DISTINCT p.id) as nombre_projets
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN hackathons h ON c.hackathon_id = h.id
                    LEFT JOIN projets p ON c.id = p.challenge_id
                    WHERE c.id = :id
                    GROUP BY c.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche du challenge : " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                if ($key !== 'id' && $key !== 'hackathon_id' && $key !== 'created_by') {
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
            throw new Exception("Erreur lors de la mise à jour du challenge : " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            // Vérifier si des projets sont associés
            $sql = "SELECT COUNT(*) FROM projets WHERE challenge_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Impossible de supprimer le challenge : des projets y sont associés");
            }

            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression du challenge : " . $e->getMessage());
        }
    }

    public function getByHackathon($hackathonId) {
        try {
            $sql = "SELECT c.*, u.nom as created_by_nom, u.prenom as created_by_prenom,
                    COUNT(DISTINCT p.id) as nombre_projets
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN projets p ON c.id = p.challenge_id
                    WHERE c.hackathon_id = :hackathon_id
                    GROUP BY c.id
                    ORDER BY c.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':hackathon_id' => $hackathonId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des challenges : " . $e->getMessage());
        }
    }

    private function validate($data) {
        if (empty($data['titre'])) {
            throw new Exception("Le titre est obligatoire");
        }

        if (empty($data['description'])) {
            throw new Exception("La description est obligatoire");
        }

        if (empty($data['hackathon_id'])) {
            throw new Exception("L'ID du hackathon est obligatoire");
        }

        if (!is_numeric($data['hackathon_id'])) {
            throw new Exception("L'ID du hackathon doit être un nombre");
        }

        if (empty($data['points']) || !is_numeric($data['points']) || $data['points'] < 0) {
            throw new Exception("Le nombre de points doit être un nombre positif");
        }

        // Vérifier si le titre est unique pour ce hackathon
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE titre = :titre AND hackathon_id = :hackathon_id";

        if (isset($data['id'])) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $params = [
            ':titre' => $data['titre'],
            ':hackathon_id' => $data['hackathon_id']
        ];

        if (isset($data['id'])) {
            $params[':id'] = $data['id'];
        }

        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Un challenge avec ce titre existe déjà dans ce hackathon");
        }

        // Vérifier si le hackathon existe
        $sql = "SELECT id FROM hackathons WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $data['hackathon_id']]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Hackathon non trouvé");
        }
    }
}

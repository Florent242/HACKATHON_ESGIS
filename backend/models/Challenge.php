<?php

class Challenge {
    private $db;
    private $table = 'challenges';

    public function __construct($db) {
        $this->db = $db;
    }

    // Créer un nouveau challenge
    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} (title, description, hackathon_id, max_teams, points) 
                    VALUES (:title, :description, :hackathon_id, :max_teams, :points)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':hackathon_id' => $data['hackathon_id'],
                ':max_teams' => $data['max_teams'],
                ':points' => $data['points']
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création du challenge : " . $e->getMessage());
        }
    }

    // Trouver un challenge par son ID
    public function find($id) {
        try {
            $sql = "SELECT c.*, u.username as creator_name,
                    h.title as hackathon_title,
                    COUNT(DISTINCT p.id) as project_count
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN hackathons h ON c.hackathon_id = h.id
                    LEFT JOIN projets p ON c.id = p.challenge_id
                    WHERE c.id = :id
                    GROUP BY c.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche du challenge : " . $e->getMessage());
        }
    }

    // Mettre à jour un challenge
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

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour du challenge : " . $e->getMessage());
        }
    }

    // Supprimer un challenge
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

    // Récupérer les challenges par hackathon
    public function getByHackathon($hackathonId) {
        try {
            $sql = "SELECT c.*, u.username as creator_name,
                    COUNT(DISTINCT p.id) as project_count
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN projets p ON c.id = p.challenge_id
                    WHERE c.hackathon_id = :hackathon_id
                    GROUP BY c.id
                    ORDER BY c.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':hackathon_id' => $hackathonId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des challenges : " . $e->getMessage());
        }
    }

    // Récupérer les projets d'un challenge
    public function getProjets($challengeId) {
        try {
            $sql = "SELECT p.*, e.name as equipe_name,
                    COUNT(DISTINCT ev.id) as evaluation_count,
                    AVG(ev.score) as average_score
                    FROM projets p
                    INNER JOIN equipes e ON p.equipe_id = e.id
                    LEFT JOIN evaluations ev ON p.id = ev.projet_id
                    WHERE p.challenge_id = :challenge_id
                    GROUP BY p.id
                    ORDER BY p.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':challenge_id' => $challengeId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des projets : " . $e->getMessage());
        }
    }

    // Validation des données
    private function validate($data) {
        if (empty($data['title'])) {
            throw new Exception("Le titre est obligatoire");
        }

        if (empty($data['description'])) {
            throw new Exception("La description est obligatoire");
        }

        if (empty($data['hackathon_id'])) {
            throw new Exception("Le hackathon est obligatoire");
        }

        if (empty($data['criteres_evaluation'])) {
            throw new Exception("Les critères d'évaluation sont obligatoires");
        }

        // Vérifier si le titre est unique pour ce hackathon
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE title = :title AND hackathon_id = :hackathon_id";

        if (isset($data['id'])) {
            $sql .= " AND id != :id";
        }

        $stmt = $this->db->prepare($sql);
        $params = [
            ':title' => $data['title'],
            ':hackathon_id' => $data['hackathon_id']
        ];

        if (isset($data['id'])) {
            $params[':id'] = $data['id'];
        }

        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Un challenge avec ce titre existe déjà dans ce hackathon");
        }
    }
}

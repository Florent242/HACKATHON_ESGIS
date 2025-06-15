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

            $sql = "INSERT INTO {$this->table} (title, description, hackathon_id, points, created_by, created_at)
                    VALUES (:titre, :description, :hackathon_id, :points, :created_by, :created_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
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
            $sql = "SELECT c.*, u.username as created_by_username,
                    h.name as hackathon_titre,
                    COUNT(DISTINCT p.id) as nombre_projects
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN hackathons h ON c.hackathon_id = h.id
                    LEFT JOIN projects p ON c.id = p.challenge_id
                    WHERE c.id = :id
                    GROUP BY c.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche du challenge : " . $e->getMessage());
        }
    }

    /**
     * Récupère tous les challenges
     * @return array
     */
    public function getAll() {
        try {
            $sql = "SELECT c.*, u.username as created_by_name,
                    h.name as hackathon_titre,
                    COUNT(DISTINCT p.id) as nombre_projects
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN hackathons h ON c.hackathon_id = h.id
                    LEFT JOIN projects p ON c.id = p.challenge_id
                    GROUP BY c.id
                    ORDER BY c.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de tous les challenges : " . $e->getMessage());
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
            $sql = "SELECT COUNT(*) FROM projects WHERE challenge_id = :id";
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
            $sql = "SELECT c.*, u.username as created_by_name,
                    COUNT(DISTINCT p.id) as nombre_projects
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN projects p ON c.id = p.challenge_id
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


    public function getUserOngoingChallenges($userId, $type)
    {
        try {
            $query = "SELECT c.*
                      FROM {$this->table} c
                      INNER JOIN user_challenges uc ON c.id = uc.challenge_id
                      WHERE uc.user_id = :user_id AND c.type = :type AND uc.status = 'ongoing'"; // Adaptez 'status' si nécessaire
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            $challenges = $stmt->fetchAll();
            return $challenges;
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des défis en cours : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère le nombre total de résolutions de challenges.
     *
     * @return int Le nombre total de résolutions.
     * @throws Exception Si une erreur de base de données survient.
     */
    public function getTotalSolvesCount(): int {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM challenge_solves");
            $count = $stmt->fetchColumn();
            return (int) $count;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération du nombre total de résolutions : " . $e->getMessage());
        }
    }

    /**
     * Récupère le nombre de résolutions pour un challenge spécifique.
     *
     * @param int $challengeId L'ID du challenge.
     * @return int Le nombre de résolutions pour le challenge.
     * @throws Exception Si une erreur de base de données survient.
     */
    public function getSolvesCountByChallengeId(int $challengeId): int {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM challenge_solves WHERE challenge_id = :challenge_id");
            $stmt->bindParam(':challenge_id', $challengeId, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            return (int) $count;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération du nombre de résolutions pour le challenge {$challengeId} : " . $e->getMessage());
        }
    }
    public function getchallengeDev($hackathon_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.type,
                    c.title,
                    c.difficulty,
                    c.description,
                    c.created_at,
                    h.name as hackathon_name,
                    t.name as technology,
                    COUNT(DISTINCT cs.id) as submissions_count,
                    COUNT(DISTINCT cs.user_id) as participants_count,
                    h.status
                FROM challenges c
                INNER JOIN hackathons h ON c.hackathon_id = h.id
                INNER JOIN technologies t ON c.technology_id = t.id
                LEFT JOIN challenge_submissions cs ON c.id = cs.challenge_id
                WHERE h.id = :hackathon_id
                GROUP BY c.id
            ");
    
            $stmt->execute([':hackathon_id' => $hackathon_id]);
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            return $challenges;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des challenges : " . $e->getMessage());
        }
    }
}

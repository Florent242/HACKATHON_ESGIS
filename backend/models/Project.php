<?php
namespace Auth\Model;

use Exception;
use PDO;
use PDOException;

class Project {
    private $db;
    private $table = 'projects';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère tous les projets
     * @return array Liste des projets
     */
    public function getAll() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des projets: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère un projet par son ID
     * @param int $id ID du projet
     * @return array|bool Les données du projet ou false si non trouvé
     */
    public function find($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération du projet: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée un nouveau projet
     * @param array $data Les données du projet
     * @return int|bool L'ID du nouveau projet ou false si erreur
     */
    public function create($data) {
        try {
            // Validation des données
            if (!isset($data['name']) || empty($data['name'])) {
                throw new Exception('Le nom est requis');
            }
            if (!isset($data['description']) || empty($data['description'])) {
                throw new Exception('La description est requise');
            }
            if (!isset($data['team_id']) || empty($data['team_id'])) {
                throw new Exception('L\'ID de l\'équipe est requis');
            }
            if (!isset($data['hackathon_id']) || empty($data['hackathon_id'])) {
                throw new Exception('L\'ID du hackathon est requis');
            }

            // Vérifier si l'équipe existe
            $teamQuery = "SELECT * FROM teams WHERE id = :id LIMIT 1";
            $teamStmt = $this->db->prepare($teamQuery);
            $teamStmt->bindParam(':id', $data['team_id'], PDO::PARAM_INT);
            $teamStmt->execute();

            if (!$teamStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Équipe non trouvée');
            }

            // Vérifier si le hackathon existe
            $hackathonQuery = "SELECT * FROM hackathons WHERE id = :id LIMIT 1";
            $hackathonStmt = $this->db->prepare($hackathonQuery);
            $hackathonStmt->bindParam(':id', $data['hackathon_id'], PDO::PARAM_INT);
            $hackathonStmt->execute();

            if (!$hackathonStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('Hackathon non trouvé');
            }

            // Préparation de la requête
            $query = "INSERT INTO {$this->table} (name, description, status, repository_url, demo_url, documentation_url, team_id, hackathon_id, technologies)
                     VALUES (:name, :description, :status, :repository_url, :demo_url, :documentation_url, :team_id, :hackathon_id, :technologies)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':status', $data['status'] ?? 'ongoing');
            $stmt->bindParam(':repository_url', $data['repository_url'] ?? null);
            $stmt->bindParam(':demo_url', $data['demo_url'] ?? null);
            $stmt->bindParam(':documentation_url', $data['documentation_url'] ?? null);
            $stmt->bindParam(':team_id', $data['team_id'], PDO::PARAM_INT);
            $stmt->bindParam(':hackathon_id', $data['hackathon_id'], PDO::PARAM_INT);
            $stmt->bindParam(':technologies', $data['technologies'] ?? null);

            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Erreur lors de la création du projet: ' . $e->getMessage());
            throw new Exception('Erreur lors de la création du projet: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour un projet
     * @param int $id ID du projet
     * @param array $data Les données à mettre à jour
     * @return bool true si succès, sinon false
     */
    public function update($id, $data) {
        try {
            // Vérification si le projet existe
            $project = $this->find($id);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }

            // Construction de la requête
            $fields = [];
            $params = [];

            // Champs à mettre à jour
            $allowedFields = ['name', 'description', 'status', 'repository_url', 'demo_url', 'documentation_url',
                             'technologies', 'score', 'judges_comments', 'evaluation_criteria', 'version',
                             'rule_compliance', 'security_issues'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            if (empty($fields)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $params[':id'] = $id;

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour du projet: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour du projet: ' . $e->getMessage());
        }
    }

    /**
     * Supprime un projet
     * @param int $id ID du projet
     * @return bool true si succès, sinon false
     */
    public function delete($id) {
        try {
            // Vérification si le projet existe
            $project = $this->find($id);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }

            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression du projet: ' . $e->getMessage());
            throw new Exception('Erreur lors de la suppression du projet: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les projets d'une équipe
     * @param int $teamId ID de l'équipe
     * @return array Liste des projets
     */
    public function getByTeam($teamId) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE team_id = :team_id ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des projets par équipe: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les projets d'un hackathon
     * @param int $hackathonId ID du hackathon
     * @return array Liste des projets
     */
    public function getByHackathon($hackathonId) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE hackathon_id = :hackathon_id ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hackathon_id', $hackathonId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des projets par hackathon: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Met à jour le statut d'un projet
     * @param int $id ID du projet
     * @param string $status Nouveau statut du projet
     * @return bool true si succès, sinon false
     */
    public function updateStatus($id, $status) {
        try {
            // Vérification si le projet existe
            $project = $this->find($id);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }

            // Vérification du statut
            $validStatuses = ['ongoing', 'completed', 'validated', 'rejected'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Statut invalide');
            }

            $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour du statut du projet: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour du statut du projet: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour le score d'un projet
     * @param int $id ID du projet
     * @param int $score Nouveau score du projet
     * @param string $judgesTomments Commentaires des juges (optionnel)
     * @param string $evaluationCriteria Critères d'évaluation au format JSON (optionnel)
     * @return bool true si succès, sinon false
     */
    public function updateScore($id, $score, $judgesComments = null, $evaluationCriteria = null) {
        try {
            // Vérification si le projet existe
            $project = $this->find($id);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }

            // Vérification du score
            if ($score < 0 || $score > 100) {
                throw new Exception('Score invalide (doit être entre 0 et 100)');
            }

            // Construire la requête
            $query = "UPDATE {$this->table} SET score = :score";
            $params = [':score' => $score, ':id' => $id];

            if ($judgesComments !== null) {
                $query .= ", judges_comments = :judges_comments";
                $params[':judges_comments'] = $judgesComments;
            }

            if ($evaluationCriteria !== null) {
                $query .= ", evaluation_criteria = :evaluation_criteria";
                $params[':evaluation_criteria'] = $evaluationCriteria;
            }

            $query .= " WHERE id = :id";

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour du score du projet: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour du score du projet: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour la version d'un projet
     * @param int $id ID du projet
     * @param string $version Nouvelle version du projet
     * @return bool true si succès, sinon false
     */
    public function updateVersion($id, $version) {
        try {
            // Vérification si le projet existe
            $project = $this->find($id);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }

            $query = "UPDATE {$this->table} SET version = :version WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':version', $version);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour de la version du projet: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour de la version du projet: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les évaluations d'un projet
     * @param int $id ID du projet
     * @return array Liste des évaluations
     */
    public function getEvaluations($id) {
        try {
            $query = "SELECT e.*, u.username, u.fullname, u.school, u.email
                     FROM evaluations e
                     JOIN users u ON e.judge_id = u.id
                     WHERE e.project_id = :project_id
                     ORDER BY e.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':project_id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des évaluations du projet: ' . $e->getMessage());
            return [];
        }
    }
}

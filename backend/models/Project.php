<?php
namespace Auth\Model;

use PDO;
use PDOException;
use Exception;

class Project
{
    private $db;
    private $table = 'projects';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Crée un nouveau projet
     */
    public function create(array $data): int
    {
        try {
            $this->db->beginTransaction();

            // Préparer les données
            $projectData = [
                'team_id' => $data['team_id'],
                'hackathon_id' => $data['hackathon_id'],
                'challenge_id' => $data['challenge_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'],
                'evaluation_criteria' => $data['evaluation_criteria'] ? json_encode($data['evaluation_criteria']) : null,
                'demo_url' => $data['demo_url'] ?? null,
                'documentation_url' => $data['documentation_url'] ?? null,
                'repository_url' => $data['repository_url'] ?? null,
                'zip_path' => $data['file_path'] ?? null,
                'file_name' => $data['file_name'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? null,
                'status' => $data['status'] ?? 'ongoing',
                'rule_compliance' => $data['rule_compliance'] ?? true,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Requête d'insertion
            $fields = implode(', ', array_keys($projectData));
            $placeholders = ':' . implode(', :', array_keys($projectData));
            
            $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($projectData);
            
            $projectId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            return (int)$projectId;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la création du projet." 
            // Pour le debug
            // . $e->getMessage()
        );
        }
    }

    /**
     * Met à jour un projet existant
     */
    public function update(int $id, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // Champs autorisés à être mis à jour
            $allowedFields = [
                'name', 'description', 'repository_url', 'file_path', 'demo_url', 
                'file_name', 'status', 'rule_compliance', 'score', 'updated_at'
            ];
            
            $updates = [];
            $params = ['id' => $id];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields, true)) {
                    $updates[] = "$key = :$key";
                    $params[$key] = $value;
                }
            }
            
            if (empty($updates)) {
                throw new Exception('Aucun champ valide à mettre à jour');
            }
            
            $updates[] = 'updated_at = NOW()';
            $updateClause = implode(', ', $updates);
            
            $sql = "UPDATE {$this->table} SET $updateClause WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            $this->db->commit();
            
            return $result;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la mise à jour du projet." 
            // Pour le debug
            // . $e->getMessage()
        );
        }
    }

    /**
     * Récupère un projet par son ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT p.*, t.name as team_name, h.name as hackathon_name, 
                       c.title as challenge_title, u.username as created_by_username
                FROM {$this->table} p
                LEFT JOIN teams t ON p.team_id = t.id
                LEFT JOIN hackathons h ON p.hackathon_id = h.id
                LEFT JOIN challenges c ON p.challenge_id = c.id
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            return null;
        }
        
        // Décoder les champs JSON
        if (!empty($project['evaluation_criteria'])) {
            $project['evaluation_criteria'] = json_decode($project['evaluation_criteria'], true);
        }
        
        return $project;
    }

    /**
     * Récupère les projets d'une équipe
     */
    public function getByTeam(int $teamId): array
    {
        $sql = "SELECT p.*, h.name as hackathon_name, c.title as challenge_title
                FROM {$this->table} p
                LEFT JOIN hackathons h ON p.hackathon_id = h.id
                LEFT JOIN challenges c ON p.challenge_id = c.id
                WHERE p.team_id = :team_id
                ORDER BY p.created_at DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':team_id' => $teamId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les projets d'un hackathon
     */
    public function getByHackathon(int $hackathonId): array
    {
        $sql = "SELECT p.*, t.name as team_name, c.title as challenge_title, 
                       u.username as created_by_username
                FROM {$this->table} p
                LEFT JOIN teams t ON p.team_id = t.id
                LEFT JOIN challenges c ON p.challenge_id = c.id
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.hackathon_id = :hackathon_id
                ORDER BY p.created_at DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':hackathon_id' => $hackathonId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprime un projet
     */
    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Récupérer le chemin du fichier avant suppression
            $project = $this->find($id);
            
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);
            
            // Supprimer le fichier associé s'il existe
            if ($result && !empty($project['file_path']) && file_exists($project['file_path'])) {
                unlink($project['file_path']);
            }
            
            $this->db->commit();
            
            return $result;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la suppression du projet : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si une équipe a déjà soumis un projet pour un défi
     */
    public function hasTeamSubmittedForChallenge(int $teamId, int $challengeId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE team_id = :team_id 
                AND challenge_id = :challenge_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':team_id' => $teamId,
            ':challenge_id' => $challengeId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    /**
     * Met à jour les critères d'évaluation d'un projet
     */
    public function updateEvaluationCriteria(int $projectId, array $criteria): bool
    {
        $sql = "UPDATE {$this->table} 
                SET evaluation_criteria = :criteria,
                    updated_at = NOW()
                WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $projectId,
            ':criteria' => json_encode($criteria, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ]);
    }

    /**
     * Récupère les soumissions d'une équipe pour un hackathon
     */
    public function getTeamSubmissionsForHackathon(int $teamId, int $hackathonId): array
    {
        $sql = "SELECT p.*, c.title as challenge_title
                FROM {$this->table} p
                LEFT JOIN challenges c ON p.challenge_id = c.id
                WHERE p.team_id = :team_id
                AND p.hackathon_id = :hackathon_id
                ORDER BY p.created_at DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':team_id' => $teamId,
            ':hackathon_id' => $hackathonId
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les projets évalués par un juge
     */
    public function getProjectsEvaluatedByJudge(int $judgeId, int $hackathonId): array
    {
        $sql = "SELECT DISTINCT p.*, t.name as team_name, c.title as challenge_title
                FROM {$this->table} p
                LEFT JOIN teams t ON p.team_id = t.id
                LEFT JOIN challenges c ON p.challenge_id = c.id
                LEFT JOIN evaluations e ON p.id = e.project_id
                WHERE e.judge_id = :judge_id
                AND p.hackathon_id = :hackathon_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':judge_id' => $judgeId,
            ':hackathon_id' => $hackathonId
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les projets avec filtres optionnels
     * 
     * @param array $filters Tableau associatif des filtres (hackathon_id, team_id, challenge_id, status, user_id)
     * @return array Liste des projets correspondant aux critères
     */
    public function getAll(array $filters = []): array
    {
        try {
            $sql = "SELECT p.*, 
                           t.name as team_name, 
                           h.name as hackathon_name, 
                           c.title as challenge_title,
                           u.username as created_by_username
                    FROM {$this->table} p
                    LEFT JOIN teams t ON p.team_id = t.id
                    LEFT JOIN hackathons h ON p.hackathon_id = h.id
                    LEFT JOIN challenges c ON p.challenge_id = c.id
                    LEFT JOIN users u ON p.created_by = u.id
                    WHERE 1=1";
            
            $params = [];
            
            // Filtre par hackathon
            if (!empty($filters['hackathon_id'])) {
                $sql .= " AND p.hackathon_id = :hackathon_id";
                $params[':hackathon_id'] = (int)$filters['hackathon_id'];
            }
            
            // Filtre par équipe
            if (!empty($filters['team_id'])) {
                $sql .= " AND p.team_id = :team_id";
                $params[':team_id'] = (int)$filters['team_id'];
            }
            
            // Filtre par défi
            if (!empty($filters['challenge_id'])) {
                $sql .= " AND p.challenge_id = :challenge_id";
                $params[':challenge_id'] = (int)$filters['challenge_id'];
            }
            
            // Filtre par statut
            if (!empty($filters['status'])) {
                $sql .= " AND p.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            // Filtre par utilisateur (membre de l'équipe)
            if (!empty($filters['user_id'])) {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM team_members tm 
                    WHERE tm.team_id = p.team_id 
                    AND tm.user_id = :user_id
                )";
                $params[':user_id'] = (int)$filters['user_id'];
            }
            
            // Tri par date de création décroissante par défaut
            $sql .= " ORDER BY p.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Décoder les champs JSON
            foreach ($projects as &$project) {
                if (!empty($project['evaluation_criteria'])) {
                    $project['evaluation_criteria'] = json_decode($project['evaluation_criteria'], true);
                }
            }
            
            return $projects;
            
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des projets: ' . $e->getMessage());
            throw new Exception("Une erreur est survenue lors de la récupération des projets");
        }
    }
}

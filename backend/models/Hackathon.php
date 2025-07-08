<?php

namespace Auth\Model;

use Exception;
use PDO;
use PDOException;

class Hackathon
{
    private $db;
    private $table = 'hackathons';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Récupère tous les hackathons
     * @return array Liste des hackathons
     */
    public function getAll()
    {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY start_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des hackathons !'
            // Pour debuger
            //  . $e->getMessage()
            );
            return [];
        }
    }

    /**
     * Récupère un hackathon par son ID
     * @param int $id ID du hackathon
     * @return array|bool Les données du hackathon ou false si non trouvé
     */
    public function find($id)
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération du hackathon !'
            // Pour debuger
            //  . $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Crée un nouveau hackathon
     * @param array $data Les données du hackathon
     * @return int|bool L'ID du nouveau hackathon ou false si erreur
     */
    public function create($data)
    {
        try {
            // Validation des données
            if (!isset($data['name']) || empty($data['name'])) {
                throw new Exception('Le nom est requis');
            }
            if (!isset($data['description']) || empty($data['description'])) {
                throw new Exception('La description est requise');
            }
            if (!isset($data['start_date']) || empty($data['start_date'])) {
                throw new Exception('La date de début est requise');
            }
            if (!isset($data['end_date']) || empty($data['end_date'])) {
                throw new Exception('La date de fin est requise');
            }
            if (!isset($data['rules']) || empty($data['rules'])) {
                throw new Exception('Les règles sont requises');
            }
            if (!isset($data['created_by']) || empty($data['created_by'])) {
                throw new Exception('L\'identifiant du créateur est requis');
            }

            // Vérification des dates
            $dateDebut = strtotime($data['start_date']);
            $dateFin = strtotime($data['end_date']);

            if ($dateDebut === false || $dateFin === false) {
                throw new Exception('Format de date invalide');
            }

            if ($dateDebut > $dateFin) {
                throw new Exception('La date de début doit être antérieure à la date de fin');
            }

            // Préparation de la requête
            $query = "INSERT INTO {$this->table} (name, description, start_date, end_date, location, max_teams, max_team_members, rules, prizes, created_by)
                     VALUES (:name, :description, :start_date, :end_date, :location, :max_teams, :max_team_members, :rules, :prizes, :created_by)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':end_date', $data['end_date']);
            $stmt->bindParam(':location', $data['location'] ?? null);
            $stmt->bindParam(':max_teams', $data['max_teams'] ?? 10, PDO::PARAM_INT);
            $stmt->bindParam(':max_team_members', $data['max_team_members'] ?? 4, PDO::PARAM_INT);
            $stmt->bindParam(':rules', $data['rules']);
            $stmt->bindParam(':prizes', $data['prizes'] ?? null);
            $stmt->bindParam(':created_by', $data['created_by'], PDO::PARAM_INT);

            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Erreur lors de la création du hackathon: ' . $e->getMessage());
            throw new Exception('Erreur lors de la création du hackathon !'
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Met à jour un hackathon
     * @param int $id ID du hackathon
     * @param array $data Les données à mettre à jour
     * @return bool true si succès, sinon false
     */
    public function update($id, $data)
    {
        try {
            // Vérification si le hackathon existe
            $hackathon = $this->find($id);
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé');
            }

            // Vérification des dates
            if (isset($data['start_date']) && isset($data['end_date'])) {
                $dateDebut = strtotime($data['start_date']);
                $dateFin = strtotime($data['end_date']);

                if ($dateDebut === false || $dateFin === false) {
                    throw new Exception('Format de date invalide');
                }

                if ($dateDebut > $dateFin) {
                    throw new Exception('La date de début doit être antérieure à la date de fin');
                }
            }

            // Construction de la requête
            $fields = [];
            $params = [];

            // Champs à mettre à jour
            $allowedFields = ['name', 'description', 'start_date', 'end_date', 'location', 'max_teams', 'max_team_members', 'rules', 'prizes'];

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
            error_log('Erreur lors de la mise à jour du hackathon: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour du hackathon !' 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Supprime un hackathon
     * @param int $id ID du hackathon
     * @return bool true si succès, sinon false
     */
    public function delete($id)
    {
        try {
            // Vérification si le hackathon existe
            $hackathon = $this->find($id);
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé');
            }

            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression du hackathon: ' . $e->getMessage());
            throw new Exception('Erreur lors de la suppression du hackathon !' 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Récupère les hackathons actifs (en cours)
     * @return array Liste des hackathons actifs
     */
    public function getActive()
    {
        try {
            $now = date('Y-m-d H:i:s');
            $query = "SELECT * FROM {$this->table} WHERE start_date <= :now AND end_date >= :now ORDER BY start_date ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':now', $now);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des hackathons actifs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les hackathons passés
     * @return array Liste des hackathons passés
     */
    public function getPast()
    {
        try {
            $now = date('Y-m-d H:i:s');
            $query = "SELECT * FROM {$this->table} WHERE end_date < :now ORDER BY end_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':now', $now);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des hackathons passés: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les hackathons futurs
     * @return array Liste des hackathons futurs
     */
    public function getFuture()
    {
        try {
            $now = date('Y-m-d H:i:s');
            $query = "SELECT * FROM {$this->table} WHERE start_date > :now ORDER BY start_date ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':now', $now);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des hackathons futurs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les équipes d'un hackathon
     * @param int $id ID du hackathon
     * @return array Liste des équipes
     */
    public function getTeams($id)
    {
        try {
            $query = "SELECT t.* FROM teams t WHERE t.hackathon_id = :hackathon_id ORDER BY t.created_at";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hackathon_id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des équipes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Recuperer les equipe participantes d'un hackathon
     */
    public function getHackathonParticipants($id) {
        try {
            $query = "SELECT hp.* FROM hackathon_participants hp WHERE hp.hackathon_id = :hackathon_id AND hp.participation_status = 'accepted' ORDER BY hp.created_at";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hackathon_id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (Exception $e) {
            error_log('Erreur lors de la récupération des participants: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les projets d'un hackathon
     * @param int $id ID du hackathon
     * @return array Liste des projets
     */
    public function getProjects($id)
    {
        try {
            $query = "SELECT p.* FROM projects p WHERE p.hackathon_id = :hackathon_id ORDER BY p.created_at";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hackathon_id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des projets: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les statistiques d'un hackathon
     * @param int $id ID du hackathon
     * @return array Statistiques du hackathon
     */
    public function getStats($id)
    {
        try {
            // Vérification si le hackathon existe
            $hackathon = $this->find($id);
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé');
            }

            // Nombre d'équipes
            $teamCountQuery = "SELECT COUNT(*) FROM teams WHERE hackathon_id = :id";
            $teamStmt = $this->db->prepare($teamCountQuery);
            $teamStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $teamStmt->execute();
            $teamCount = $teamStmt->fetchColumn();

            // Nombre de participants
            $participantCountQuery = "SELECT COUNT(*) FROM hackathon_participants WHERE hackathon_id = :id AND participation_status = 'accepted'";
            $participantStmt = $this->db->prepare($participantCountQuery);
            $participantStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $participantStmt->execute();
            $participantCount = $participantStmt->fetchColumn();

            // Nombre de projets
            $projectCountQuery = "SELECT COUNT(*) FROM projects WHERE hackathon_id = :id";
            $projectStmt = $this->db->prepare($projectCountQuery);
            $projectStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $projectStmt->execute();
            $projectCount = $projectStmt->fetchColumn();

            // Projets complétés
            $completedProjectCountQuery = "SELECT COUNT(*) FROM projects WHERE hackathon_id = :id AND status = 'completed'";
            $completedProjectStmt = $this->db->prepare($completedProjectCountQuery);
            $completedProjectStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $completedProjectStmt->execute();
            $completedProjectCount = $completedProjectStmt->fetchColumn();

            // Projets validés
            $validatedProjectCountQuery = "SELECT COUNT(*) FROM projects WHERE hackathon_id = :id AND status = 'validated'";
            $validatedProjectStmt = $this->db->prepare($validatedProjectCountQuery);
            $validatedProjectStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $validatedProjectStmt->execute();
            $validatedProjectCount = $validatedProjectStmt->fetchColumn();

            return [
                'id' => $id,
                'name' => $hackathon['name'],
                'start_date' => $hackathon['start_date'],
                'end_date' => $hackathon['end_date'],
                'team_count' => $teamCount,
                'participant_count' => $participantCount,
                'project_count' => $projectCount,
                'completed_project_count' => $completedProjectCount,
                'validated_project_count' => $validatedProjectCount
            ];
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            throw new Exception('Erreur lors de la récupération des statistiques: ' 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }
}

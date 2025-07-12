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

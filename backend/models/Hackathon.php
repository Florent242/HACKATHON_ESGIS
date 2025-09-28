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
     * Vérifie si l'utilisateur fait partie d'une équipe pour ce hackathon
     * @param int $userId ID de l'utilisateur
     * @param int $hackathonId ID du hackathon
     * @return array|bool Les données de l'équipe ou false si non trouvé
     */
    public function checkParticipation($userId, $hackathonId)
    {
        try {
            // Vérifier si l'utilisateur est participant au hackathon
            $query = "SELECT COUNT(*) FROM hackathon_participants 
                     WHERE user_id = :user_id 
                     AND hackathon_id = :hackathon_id 
                     AND participation_status = 'accepted'";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':user_id' => (int)$userId,
                ':hackathon_id' => (int)$hackathonId
            ]);

            if (!$stmt->fetchColumn() > 0 && !isAdmin($userId)) {
                return [
                    'success' => false,
                    'message' => 'Accès non autorisé ! Vous devez être participant au hackathon pour accéder à cette ressource.'
                ];
            }

            // Vérifier si l'utilisateur est membre d'une equipe participant au hackathon
            $query = "SELECT COUNT(*) FROM hackathon_teams ht
                     INNER JOIN hackathon_participants hp ON ht.team_id = hp.team_id
                     WHERE ht.hackathon_id = :hackathon_id 
                     AND hp.user_id = :user_id";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':hackathon_id' => (int)$hackathonId,
                ':user_id' => (int)$userId
            ]);

            if (!$stmt->fetchColumn() > 0 && !isAdmin($userId)) {
                return [
                    'success' => false,
                    'message' => 'Accès non autorisé ! Vous devez être membre d\'une equipe participant au hackathon pour accéder à cette ressource.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Accès autorisé !'
            ];
        } catch (Exception $e) {
            throw new Exception(
                'Erreur lors de la vérification de participation: '
                    // pour debuger
                    . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Récupère tous les hackathons
     * @return array Liste des hackathons
     */
    public function getPublicAll()
    {
        try {
            $query = "SELECT h.id, h.name, h.description, h.type, h.start_date, h.end_date, h.registration_deadline, h.max_teams, h.min_team_members, h.max_team_members, h.status, h.location, h.created_at, h.updated_at 
            FROM {$this->table} h 
            ORDER BY start_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des informations publics des hackathons !'
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
            $query = "SELECT h.*, 
                     (SELECT COUNT(*) FROM hackathon_teams WHERE hackathon_id = h.id) as teams_count 
                     FROM {$this->table} h 
                     WHERE h.id = :id 
                     LIMIT 1";
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

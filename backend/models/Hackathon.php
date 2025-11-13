<?php

namespace Auth\Model;

use Exception;
use PDO;
use PDOException;
use DateTime;
use Auth\Model\Notification;

if (!class_exists('Notification')) {
    require_once __DIR__ . '/Notification.php';
}

class Hackathon
{
    private $db;
    private $table = 'hackathons';
    private $notification;

    public function __construct($db)
    {
        $this->db = $db;
        $this->notification = new Notification($db);
    }

    /**
     * Crée un nouveau hackathon
     * @param array $data Les données du hackathon
     * @return int|bool L'ID du nouveau hackathon ou false si erreur
     */
    public function create($data)
    {
        try {
            // Validation des champs obligatoires
            $requiredFields = ['name', 'description', 'start_date', 'end_date', 'type', 'status', 'visibility', 'created_by'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Le champ $field est requis");
                }
            }

            // Validation du type
            $validTypes = ['ctf', 'dev', 'mixte'];
            if (!in_array($data['type'], $validTypes)) {
                throw new Exception('Type de hackathon invalide. Les valeurs autorisées sont : ' . implode(', ', $validTypes));
            }

            // Validation du nom
            if ($this->nameExists($data['name'])) {
                throw new Exception('Le nom du hackathon est déjà utilisé');
            }

            // Validation des dates
            $startDate = new DateTime($data['start_date']);
            $endDate = new DateTime($data['end_date']);

            if ($startDate >= $endDate) {
                throw new Exception('La date de fin doit être postérieure à la date de début');
            }

            // Liste des champs autorisés
            $allowedFields = [
                'name',
                'slug',
                'theme',
                'description',
                'type',
                'status',
                'visibility',
                'start_date',
                'end_date',
                'registration_deadline',
                'location',
                'max_teams',
                'min_team_members',
                'max_team_members',
                'rules',
                'eligibility_criteria',
                'prizes',
                'created_by'
            ];

            // Préparation des données pour la requête
            $columns = [];
            $placeholders = [];
            $values = [];
            $excludeFields = ['slug']; // Champs à gérer séparément

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data) && !in_array($field, $excludeFields)) {
                    $columns[] = "`$field`";
                    $placeholders[] = ":$field";
                    $values[":$field"] = is_array($data[$field]) ? json_encode($data[$field]) : $data[$field];
                }
            }

            // Génération du slug si non fourni
            if (empty($data['slug'])) {
                $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
                $slug = $baseSlug;
                $counter = 1;

                // Vérifier si le slug existe déjà
                while ($this->slugExists($slug)) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $columns[] = '`slug`';
                $placeholders[] = ':slug';
                $values[':slug'] = $slug;
            }

            // Construction de la requête
            $query = "INSERT INTO `{$this->table}` (" . implode(', ', $columns) . ") 
                     VALUES (" . implode(', ', $placeholders) . ")";

            // Exécution de la requête
            $stmt = $this->db->prepare($query);

            // Définition des types de paramètres
            $types = [
                'max_teams' => PDO::PARAM_INT,
                'min_team_members' => PDO::PARAM_INT,
                'max_team_members' => PDO::PARAM_INT,
                'created_by' => PDO::PARAM_INT
            ];

            // Liaison des valeurs avec leur type
            foreach ($values as $key => $value) {
                $paramType = PDO::PARAM_STR;
                foreach ($types as $field => $type) {
                    if (strpos($key, $field) !== false) {
                        $paramType = $type;
                        break;
                    }
                }
                $stmt->bindValue($key, $value, $paramType);
            }

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erreur SQL: " . ($errorInfo[2] ?? 'Erreur inconnue'));
            }

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Erreur PDO lors de la création du hackathon: ' . $e->getMessage());
            throw new Exception('Erreur lors de la création du hackathon: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log('Erreur lors de la création du hackathon: ' . $e->getMessage());
            throw $e;
        }
    }

    private function slugExists($slug, $excludeId = null)
    {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
            $params = [':slug' => $slug];

            if ($excludeId !== null) {
                $query .= " AND id != :id";
                $params[':id'] = $excludeId;
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification du slug: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un hackathon existe
     * @param int $id ID du hackathon
     * @return bool true si le hackathon existe, sinon false
     */
    public function nameExists($name)
    {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE name = :name";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':name' => $name]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification de l\'existence du hackathon: ' . $e->getMessage());
            return false;
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

            // Si le nom est mis à jour, mettre à jour le slug
            if (isset($data['name'])) {
                $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
                $slug = $baseSlug;
                $counter = 1;

                while ($this->slugExists($slug, $id)) { // Passez l'ID actuel pour l'exclure
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $data['slug'] = $slug;
            }

            // Construction de la requête
            $fields = [];
            $params = [':id' => $id];

            // Liste des champs autorisés à être mis à jour
            $allowedFields = [
                'name',
                'slug',
                'theme',
                'description',
                'type',
                'status',
                'visibility',
                'start_date',
                'end_date',
                'registration_deadline',
                'location',
                'max_teams',
                'min_team_members',
                'max_team_members',
                'rules',
                'eligibility_criteria',
                'prizes'
            ];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = is_array($data[$field]) ? json_encode($data[$field]) : $data[$field];
                }
            }

            if (empty($fields)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            // Génération du slug si le nom est mis à jour
            if (isset($data['name']) && empty($data['slug'])) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
                $fields[] = "slug = :slug";
                $params[":slug"] = $slug;
            }

            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour du hackathon: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour du hackathon: ' . $e->getMessage());
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
            throw new Exception('Erreur lors de la suppression du hackathon: ' . $e->getMessage());
        }
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
            error_log(
                'Erreur lors de la récupération des hackathons !'
                // Pour debuger
                //  . $e->getMessage()
            );
            return [];
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
            error_log(
                'Erreur lors de la récupération des informations publics des hackathons !'
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
            error_log(
                'Erreur lors de la récupération du hackathon !'
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
     * Met à jour le statut d'un participant
     * @param int $participantId ID du participant
     * @param string $status Statut du participant
     * @return bool True si la mise à jour a réussi, false sinon
     */
    // public function updateStatus($participantId, $status)
    // {
    //     // Vérifier que le statut est valide
    //     $allowedStatuses = ['pending', 'accepted', 'rejected', 'disqualified'];
    //     if (!in_array($status, $allowedStatuses)) {
    //         throw new Exception('Statut invalide');
    //     }

    //     // Mettre à jour le statut dans la base de données
    //     $stmt = $this->db->prepare("
    //     UPDATE hackathon_participants 
    //     SET status = :status 
    //     WHERE id = :id
    // ");

    //     $success = $stmt->execute([
    //         ':id' => $participantId,
    //         ':status' => $status
    //     ]);

    //     if ($success) {
    //         // Envoyer une notification si nécessaire
    //         $this->notification->create([
    //             'user_id' => $participantId,
    //             'title' => 'Statut mis à jour',
    //             'message' => 'Votre statut de participation au hackathon "'. $hackathonName . '" a été mis à jour',
    //             'type' => 'success'
    //         ]);
    //     }
    // }

    /**
     * Récupère les équipes d'un hackathon
     * @param int $id ID du hackathon
     * @return array Liste des équipes
     */
    public function getTeams($hackathonId)
    {
        try {
            $query = "
                SELECT 
                    t.*,
                    u.username as leader_username,
                    u.fullname as leader_name,
                    u.profile_picture as leader_avatar,
                    COUNT(tm.user_id) as member_count
                FROM 
                    teams t
                    LEFT JOIN users u ON t.leader_id = u.id
                    LEFT JOIN team_members tm ON t.id = tm.team_id
                WHERE 
                    t.hackathon_id = :hackathon_id
                GROUP BY 
                    t.id
                ORDER BY 
                    t.created_at DESC
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['hackathon_id' => $hackathonId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des équipes : " . $e->getMessage());
        }
    }

    /**
     * Recuperer les equipe participantes d'un hackathon
     */
    public function getHackathonParticipants($id)
    {
        try {
            $query = "SELECT hp.*, u.username, u.fullname, u.email, u.school 
            FROM hackathon_participants hp 
            INNER JOIN users u ON hp.user_id = u.id
            WHERE hp.hackathon_id = :hackathon_id 
            -- AND hp.participation_status = 'accepted' 
            ORDER BY hp.joined_at";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':hackathon_id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des participants: ' . $e->getMessage());
            throw new Exception(
                "Erreur lors de la récupération des participants"
                    // Pour le debug
                    . $e->getMessage()
            );
        }
    }

    // Hackathon.php

    /**
     * Récupère tous les participants d'un hackathon
     */
    public function getParticipants($hackathonId)
    {
        $query = "
        SELECT 
            u.id, 
            u.username,
            u.fullname,
            u.email,
            u.profile_picture,
            t.name as team_name
        FROM users u
        INNER JOIN team_members tm ON u.id = tm.user_id
        INNER JOIN teams t ON tm.team_id = t.id
        WHERE t.hackathon_id = :hackathon_id
        ORDER BY t.name, u.fullname
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['hackathon_id' => $hackathonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChallenges($hackathonId)
    {
        try {
            $query = "
                SELECT 
                    c.*,
                    (
                        SELECT COUNT(DISTINCT vf.id) 
                        FROM validated_flags vf 
                        WHERE vf.challenge_id = c.id
                    ) as flag_submissions,
                    (
                        SELECT COUNT(DISTINCT cs.id) 
                        FROM challenge_submissions cs 
                        WHERE cs.challenge_id = c.id
                    ) as challenge_submissions,
                    (
                        SELECT COUNT(DISTINCT p.id) 
                        FROM projects p 
                        WHERE p.challenge_id = c.id
                    ) as project_submissions
                FROM challenges c
                WHERE c.hackathon_id = :hackathon_id
                ORDER BY c.created_at DESC
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['hackathon_id' => $hackathonId]);
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formater les données pour le frontend
            return array_map(function ($challenge) {
                // Calculer le total des soumissions
                $totalSubmissions =
                    (int)$challenge['flag_submissions'] +
                    (int)$challenge['challenge_submissions'] +
                    (int)$challenge['project_submissions'];

                return [
                    'id' => $challenge['id'],
                    'title' => $challenge['title'],
                    'description' => $challenge['description'],
                    'difficulty' => $challenge['difficulty'],
                    'type' => $challenge['type'],
                    'points' => (int)$challenge['points'],
                    'category' => $challenge['category'],
                    'submission_count' => $totalSubmissions,
                    'submissions' => [
                        'flags' => (int)$challenge['flag_submissions'],
                        'challenges' => (int)$challenge['challenge_submissions'],
                        'projects' => (int)$challenge['project_submissions']
                    ],
                    'is_active' => (bool)$challenge['is_active'],
                    'created_at' => $challenge['created_at'],
                    'updated_at' => $challenge['updated_at']
                ];
            }, $challenges);
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des défis: ' . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des défis");
        }
    }

    public function getLeaderboard($hackathonId, $phaseId = null)
    {
        try {
            $params = [':hackathon_id' => $hackathonId];
            $phaseCondition = '';

            if ($phaseId) {
                $phaseCondition = 'AND s.phase_id = :phase_id';
                $params[':phase_id'] = $phaseId;
            }

            $query = "
            SELECT 
                t.id as team_id,
                t.name as team_name,
                COALESCE(SUM(s.total_points), 0) as total_score,
                COUNT(DISTINCT s.id) as submissions_count
            FROM teams t
            LEFT JOIN scores s ON t.id = s.team_id
            WHERE t.hackathon_id = :hackathon_id AND is_active = 1
            $phaseCondition
            GROUP BY t.id, t.name
            ORDER BY total_score DESC, submissions_count DESC
        ";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            // Ajouter le rang à chaque équipe
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $rank = 1;

            return array_map(function ($row) use (&$rank) {
                $row['rank'] = $rank++;
                return $row;
            }, $results);
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération du classement: ' . $e->getMessage());
            throw new Exception("Erreur lors de la récupération du classement" . $e->getMessage());
        }
    }

    /**
     * Récupère toutes les inscriptions à un hackathon
     */
    public function getRegistrations($hackathonId)
    {
        try {
            $query = "
                SELECT 
                    ht.*,
                    t.name as team_name,
                    t.leader_id,
                    (SELECT COUNT(*) FROM team_members WHERE team_id = t.id) as members,
                    (SELECT username FROM users WHERE id = t.leader_id) as leader_username,
                    (SELECT fullname FROM users WHERE id = t.leader_id) as leader_name,
                    (SELECT email FROM users WHERE id = t.leader_id) as leader_email,
                    u.email as email 
                FROM hackathon_teams ht
                INNER JOIN teams t ON ht.team_id = t.id
                INNER JOIN users u ON t.leader_id = u.id 
                WHERE ht.hackathon_id = :hackathon_id
                ORDER BY ht.registered_at DESC, t.name
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['hackathon_id' => $hackathonId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des inscriptions: ' . $e->getMessage());
            throw new Exception(
                "Erreur lors de la récupération des inscriptions"
                // Pour le debug
                // . $e->getMessage()
            );
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
            throw new Exception(
                "Erreur lors de la récupération des projets"
                // Pour le debug
                // . $e->getMessage()
            );
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
            throw new Exception(
                'Erreur lors de la récupération des statistiques: '
                // Pour debuger
                //  . $e->getMessage()
            );
        }
    }
}

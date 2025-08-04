<?php

namespace Auth\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Auth\Model\Database;
use Auth\Model\TokenManager;
use Exception;
use PDO;
use PDOException;

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if (!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}
if (!class_exists('Auth\Model\TokenManager')) {
    require_once __DIR__ . '/../models/TokenManager.php';
}
if (!class_exists('Auth\Model\Database')) {
    require_once __DIR__ . '/../models/Database.php';
}

class AdminController extends Controller
{
    private $db;
    private $AdminUser;
    private $user;
    private $key = 'your-secret-key';

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
    }

    /**
     * Vérifie si l'utilisateur est un administrateur
     * @param int $userId ID de l'utilisateur
     * @return bool True si l'utilisateur est admin, false sinon
     */
    public function isAdmin($userId)
    {
        if (!isset($userId)) {
            return false;
        }

        $query = "SELECT role FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $userId]);
        $role = $stmt->fetchColumn();

        return $role === 'admin' || $role === 'organisateur';
    }
    public function validateToken(string $token): array
    {
        $tokenManager = new TokenManager($this->key, $this->db);
        return $tokenManager->validateToken($token);
    }
    public function getBearerToken(): ?string
    {
        // D'abord essayer le header Authorization
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        
        // Si pas dans les headers, chercher dans les cookies
        if (isset($_COOKIE['long_term_token'])) {
            return $_COOKIE['long_term_token'];
        }
        
        if (isset($_COOKIE['jwt_token'])) {
            return $_COOKIE['jwt_token'];
        }

        return null;
    }
    public function getAuthorizationHeader(): ?string
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    public function getUserIdFromJWT($jwt)
    {
        if (!$jwt) {
            return null;
        }
        $algorithm = 'HS256';
        // Vérifiez si le JWT est valide
        $decoded = JWT::decode($jwt, new Key($this->key, $algorithm));
        return $decoded->sub; // Supposons que l'ID de l'utilisateur est stocké dans le champ 'sub'
    }

    public function update($Adminid, $jwt)
    {
        try {
            $this->validateMethod('POST');

        $adminUserId = $this->getUserIdFromJWT($jwt);

        // Vérifier si l'utilisateur modifie son propre profil ou est admin
        if ($adminUserId != $Adminid && !$this->isAdmin($adminUserId)) {
            $this->jsonResponse(['success' => false, 'error' => 'Non autorisé'], 403);
            return;
        }

        $updatableFields = ['username', 'fullname', 'school', 'email'];
        $data = $this->filterData($_POST, $updatableFields);

        if (empty($data)) {
            $this->jsonResponse(['success' => false, 'error' => 'Aucune donnée à mettre à jour'], 400);
            return;
        }

        // Si l'email est modifié, vérifier qu'il n'existe pas déjà pour un autre utilisateur
        if (isset($data['email'])) {
            $existingUser = $this->AdminUser->findByEmail($data['email']);
            if ($existingUser && (int) $existingUser['id'] !== (int) $Adminid) {
                $this->jsonResponse(['success' => false, 'error' => 'Cette adresse email est déjà utilisée. Mise à jour annulée !'], 400);
                return;
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->AdminUser->update($Adminid, $data);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Profil admin mis à jour avec succès'
        ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function delete($id, $jwt)
    {
        try {
            $this->validateMethod('POST');

            if (!hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            if (!$this->user->delete($id)) {
                throw new Exception('Erreur lors de la suppression de l\'utilisateur');
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function getAdmin($id)
    {
        try {
            $this->validateMethod('GET');

            $user = $this->user->find($id);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }

            unset($user['password']); // Ne pas renvoyer le mot de passe

            $this->jsonResponse([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'UserController.php ' . $e->getMessage()
            ], 404);
        }
    }

    public function updateRole($id)
    {
        try {
            $this->validateMethod('POST');

            // Vérifier si l'utilisateur a les droits d'administrateur
            if (!hasRole('admin')) {
                throw new Exception('Non autorisé - Réservé aux administrateurs');
            }

            if (empty($_POST['role'])) {
                throw new Exception('Le rôle est requis');
            }

            $role = $_POST['role'];
            $allowedRoles = ['participant', 'organisateur', 'jury', 'admin'];

            if (!in_array($role, $allowedRoles)) {
                throw new Exception('Rôle non valide');
            }

            $this->user->update($id, [
                'role' => $role,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Rôle mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function requireAdmin()
    {
        if (!$this->isAdmin($_SESSION['user_id'])) {
            header('Location: /auth_admin');
            exit;
        }
    }

    public function getJuryList()
    {
        try {
            $this->validateMethod('GET');

            $jurys = $this->user->getByRole('jury');

            $this->jsonResponse([
                'success' => true,
                'data' => $jurys
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    /**
     * Récupère les statistiques pour le tableau de bord admin
     */
    public function getStats()
    {
        try {
            $this->validateMethod('GET');
            
            // Nombre de hackathons
            $hackathonsQuery = "SELECT COUNT(*) FROM hackathons";
            $stmt = $this->db->prepare($hackathonsQuery);
            $stmt->execute();
            $hackathonsCount = $stmt->fetchColumn();
            
            // Nombre de challenges
            $challengesQuery = "SELECT COUNT(*) FROM challenges";
            $stmt = $this->db->prepare($challengesQuery);
            $stmt->execute();
            $challengesCount = $stmt->fetchColumn();
            
            // Nombre d'utilisateurs
            $usersQuery = "SELECT COUNT(*) FROM users";
            $stmt = $this->db->prepare($usersQuery);
            $stmt->execute();
            $usersCount = $stmt->fetchColumn();
            
            // Nombre d'équipes
            $teamsQuery = "SELECT COUNT(*) FROM teams";
            $stmt = $this->db->prepare($teamsQuery);
            $stmt->execute();
            $teamsCount = $stmt->fetchColumn();
            
            $stats = [
                'hackathons_count' => $hackathonsCount,
                'challenges_count' => $challengesCount,
                'users_count' => $usersCount,
                'teams_count' => $teamsCount
            ];
            
            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les activités récentes
     */
    public function getActivity()
    {
        try {
            $this->validateMethod('GET');
            
            $query = "SELECT al.*, u.username 
                     FROM activity_logs al
                     LEFT JOIN users u ON al.user_id = u.id
                     ORDER BY al.created_at DESC
                     LIMIT 10";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $activities
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les hackathons à venir
     */
    public function getUpcomingHackathons()
    {
        try {
            $this->validateMethod('GET');
            
            // Modifié pour utiliser hackathon_participants au lieu de participants
            $query = "SELECT h.*, 
                     (SELECT COUNT(*) FROM hackathon_participants hp WHERE hp.hackathon_id = h.id) as participants
                     FROM hackathons h
                     WHERE h.start_date > NOW()
                     ORDER BY h.start_date ASC
                     LIMIT 5";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $hackathons
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les défis populaires
     */
    public function getPopularChallenges()
    {
        try {
            $this->validateMethod('GET');
            
            $query = "SELECT c.*, 
                        (SELECT COUNT(*) FROM challenge_submissions cs WHERE cs.challenge_id = c.id) as participants,
                        CASE 
                            WHEN h.end_date < NOW() THEN 'completed'
                            WHEN h.start_date > NOW() THEN 'upcoming'
                            ELSE 'active'
                        END as status
                    FROM challenges c
                    JOIN hackathons h ON c.hackathon_id = h.id
                    ORDER BY participants DESC
                    LIMIT 5";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $challenges
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les notifications pour l'administrateur
     */
    public function getAdminNotifications()
    {
        try {
            $this->validateMethod('GET');
            
            $query = "SELECT 'system' as type, al.id, al.action as title, al.description as message,
                    al.level as notification_type, al.created_at, al.user_id, u.username
                    FROM activity_logs al
                    LEFT JOIN users u ON al.user_id = u.id
                    WHERE al.level IN ('warning', 'error')
                    
                    UNION
                    
                    SELECT 'user' as type, n.id, n.message as title, n.message, 
                    'info' as notification_type, n.created_at, n.user_id, u.username
                    FROM notifications n
                    JOIN users u ON n.user_id = u.id
                    
                    ORDER BY created_at DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques d'un hackathon spécifique
     * @param int $hackathonId ID du hackathon
     */
    public function getHackathonStats($hackathonId)
    {
        try {
            $this->validateMethod('GET');
            
            // Récupérer le hackathon
            $hackathonQuery = "SELECT * FROM hackathons WHERE id = :id";
            $stmt = $this->db->prepare($hackathonQuery);
            $stmt->bindValue(':id', $hackathonId);
            $stmt->execute();
            $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé');
            }
            
            // Nombre de participants - modifié pour utiliser hackathon_participants
            $participantsQuery = "SELECT COUNT(*) FROM hackathon_participants WHERE hackathon_id = :id";
            $stmt = $this->db->prepare($participantsQuery);
            $stmt->bindValue(':id', $hackathonId);
            $stmt->execute();
            $participantsCount = $stmt->fetchColumn();
            
            // Nombre d'équipes
            $teamsQuery = "SELECT COUNT(*) FROM teams WHERE hackathon_id = :id";
            $stmt = $this->db->prepare($teamsQuery);
            $stmt->bindValue(':id', $hackathonId);
            $stmt->execute();
            $teamsCount = $stmt->fetchColumn();
            
            // Nombre de challenges
            $challengesQuery = "SELECT COUNT(*) FROM challenges WHERE hackathon_id = :id";
            $stmt = $this->db->prepare($challengesQuery);
            $stmt->bindValue(':id', $hackathonId);
            $stmt->execute();
            $challengesCount = $stmt->fetchColumn();
            
            // Nombre de soumissions
            $submissionsQuery = "SELECT COUNT(*) FROM challenge_submissions cs
                              JOIN challenges c ON cs.challenge_id = c.id
                              WHERE c.hackathon_id = :id";
            $stmt = $this->db->prepare($submissionsQuery);
            $stmt->bindValue(':id', $hackathonId);
            $stmt->execute();
            $submissionsCount = $stmt->fetchColumn();
            
            $stats = [
                'hackathon' => $hackathon,
                'participants_count' => $participantsCount,
                'teams_count' => $teamsCount,
                'challenges_count' => $challengesCount,
                'submissions_count' => $submissionsCount
            ];
            
            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques d'un challenge spécifique
     * @param int $challengeId ID du challenge
     */
    public function getChallengeStats($challengeId)
    {
        try {
            $this->validateMethod('GET');
            
            // Récupérer le challenge
            $challengeQuery = "SELECT c.*, h.name as hackathon_name
                              FROM challenges c
                              JOIN hackathons h ON c.hackathon_id = h.id
                              WHERE c.id = :id";
            $stmt = $this->db->prepare($challengeQuery);
            $stmt->bindValue(':id', $challengeId);
            $stmt->execute();
            $challenge = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$challenge) {
                throw new Exception('Challenge non trouvé');
            }
            
            // Nombre de soumissions
            $submissionsQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE challenge_id = :id";
            $stmt = $this->db->prepare($submissionsQuery);
            $stmt->bindValue(':id', $challengeId);
            $stmt->execute();
            $submissionsCount = $stmt->fetchColumn();
            
            // Nombre de résolutions
            $solvedQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE challenge_id = :id AND status = 'accepted'";
            $stmt = $this->db->prepare($solvedQuery);
            $stmt->bindValue(':id', $challengeId);
            $stmt->execute();
            $solvedCount = $stmt->fetchColumn();
            
            // Taux de réussite
            $successRate = ($submissionsCount > 0) ? ($solvedCount / $submissionsCount) * 100 : 0;
            
            $stats = [
                'challenge' => $challenge,
                'submissions_count' => $submissionsCount,
                'solved_count' => $solvedCount,
                'success_rate' => $successRate
            ];
            
            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère tous les utilisateurs
     */
    public function getAllUsers()
    {
        try {
            $this->validateMethod('GET');
            
            // Modifié pour utiliser hackathon_participants
            $query = "SELECT u.*,
                    (SELECT COUNT(*) FROM hackathon_participants hp WHERE hp.user_id = u.id) as participations,
                    (SELECT COUNT(*) FROM team_members tm WHERE tm.user_id = u.id) as teams
                    FROM users u
                    ORDER BY u.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $users
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère tous les hackathons
     */
    public function getAllHackathons()
    {
        try {
            $this->validateMethod('GET');
            
            // Modifié pour utiliser hackathon_participants
            $query = "SELECT h.*,
                    u.username as organizer,
                    (SELECT COUNT(*) FROM hackathon_participants hp WHERE hp.hackathon_id = h.id) as participants_count,
                    (SELECT COUNT(*) FROM challenges c WHERE c.hackathon_id = h.id) as challenges_count
                    FROM hackathons h
                    JOIN users u ON h.created_by = u.id
                    ORDER BY h.start_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $hackathons
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère tous les challenges
     */
    public function getAllChallenges()
    {
        try {
            $this->validateMethod('GET');
            
            $query = "SELECT c.*,
                    h.name as hackathon_title,
                    (SELECT COUNT(*) FROM challenge_submissions cs WHERE cs.challenge_id = c.id) as submissions_count,
                    (SELECT COUNT(*) FROM challenge_submissions cs WHERE cs.challenge_id = c.id AND cs.status = 'accepted') as solved_count
                    FROM challenges c
                    JOIN hackathons h ON c.hackathon_id = h.id
                    ORDER BY c.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $challenges
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère toutes les équipes
     */
    public function getAllTeams()
    {
        try {
            $this->validateMethod('GET');
            
            $query = "SELECT t.*,
                    h.name as hackathon_title,
                    (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.id) as members_count,
                    (SELECT SUM(points) FROM challenge_submissions cs
                     JOIN team_members tm ON cs.user_id = tm.user_id
                     WHERE tm.team_id = t.id AND cs.status = 'accepted') as total_points
                    FROM teams t
                    JOIN hackathons h ON t.hackathon_id = h.id
                    ORDER BY t.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $teams
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour le statut d'un hackathon
     *
     * @param int $hackathonId ID du hackathon
     * @param string $status Nouveau statut
     * @return bool Succès ou échec
     */
    public function updateHackathonStatus($hackathonId, $status)
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $query = "UPDATE hackathons SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $hackathonId);
        $stmt->bindValue(':status', $status);

        echo json_encode($stmt->execute()) !== false ? $stmt->execute() : [];
        exit;
    }

    /**
     * Format une date pour l'affichage
     *
     * @param string $date Date au format SQL
     * @param string $format Format de sortie
     * @return string Date formatée
     */
    public function formatDate($date, $format = 'd/m/Y H:i')
    {
        if (!$date) return 'N/A';
        return date($format, strtotime($date));
    }

    /**
     * Récupère toutes les soumissions
     */
    public function getAllSubmissions()
    {
        try {
            $this->validateMethod('GET');
            
            $query = "SELECT cs.*,
                    u.username,
                    c.title as challenge_title,
                    h.name as hackathon_title
                    FROM challenge_submissions cs
                    JOIN users u ON cs.user_id = u.id
                    JOIN challenges c ON cs.challenge_id = c.id
                    JOIN hackathons h ON c.hackathon_id = h.id
                    ORDER BY cs.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $submissions
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des soumissions
     */
    public function getSubmissionStats()
    {
        try {
            $this->validateMethod('GET');
            
            // Total des soumissions
            $totalQuery = "SELECT COUNT(*) FROM challenge_submissions";
            $stmt = $this->db->prepare($totalQuery);
            $stmt->execute();
            $total = $stmt->fetchColumn();
            
            // Points attribués
            $pointsQuery = "SELECT SUM(points) FROM challenge_submissions WHERE status = 'approved'";
            $stmt = $this->db->prepare($pointsQuery);
            $stmt->execute();
            $pointsAwarded = $stmt->fetchColumn();
            
            // Soumissions en attente
            $pendingQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE status = 'pending'";
            $stmt = $this->db->prepare($pendingQuery);
            $stmt->execute();
            $pending = $stmt->fetchColumn();
            
            // Taux d'approbation
            $approvedQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE status = 'approved'";
            $stmt = $this->db->prepare($approvedQuery);
            $stmt->execute();
            $approved = $stmt->fetchColumn();
            
            $approvalRate = ($total > 0) ? round(($approved / $total) * 100) : 0;
            
            $stats = [
                'total' => $total,
                'points_awarded' => $pointsAwarded,
                'pending' => $pending,
                'approval_rate' => $approvalRate
            ];
            
            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des équipes
     */
    public function getTeamStats()
    {
        try {
            $this->validateMethod('GET');
            
            // Total des équipes
            $teamsQuery = "SELECT COUNT(*) FROM teams";
            $stmt = $this->db->prepare($teamsQuery);
            $stmt->execute();
            $teamsCount = $stmt->fetchColumn();
            
            // Total des membres
            $membersQuery = "SELECT COUNT(*) FROM team_members";
            $stmt = $this->db->prepare($membersQuery);
            $stmt->execute();
            $membersCount = $stmt->fetchColumn();
            
            // Total des participations
            $participationsQuery = "SELECT COUNT(*) FROM teams t JOIN hackathons h ON t.hackathon_id = h.id";
            $stmt = $this->db->prepare($participationsQuery);
            $stmt->execute();
            $participationsCount = $stmt->fetchColumn();
            
            // Total des défis réalisés
            $challengesQuery = "SELECT COUNT(DISTINCT cs.challenge_id) 
                              FROM challenge_submissions cs
                              JOIN team_members tm ON cs.user_id = tm.user_id
                              WHERE cs.status = 'approved'";
            $stmt = $this->db->prepare($challengesQuery);
            $stmt->execute();
            $challengesCount = $stmt->fetchColumn();
            
            $stats = [
                'teams_count' => $teamsCount,
                'members_count' => $membersCount,
                'participations_count' => $participationsCount,
                'challenges_count' => $challengesCount
            ];
            
            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getDashboardStats()
    {
        $database = Database::getInstance();
        $db = $database->getConnection();

        // Nombre total d'utilisateurs
        $usersQuery = "SELECT COUNT(*) FROM users";
        $stmt = $db->prepare($usersQuery);
        $stmt->execute();
        $usersCount = $stmt->fetchColumn();

        // Nombre d'administrateurs
        $adminsQuery = "SELECT COUNT(*) FROM users WHERE role IN ('admin', 'organisateur')";
        $stmt = $db->prepare($adminsQuery);
        $stmt->execute();
        $adminsCount = $stmt->fetchColumn();

        // Nombre de hackathons
        $hackathonsQuery = "SELECT COUNT(*) FROM hackathons";
        $stmt = $db->prepare($hackathonsQuery);
        $stmt->execute();
        $hackathonsCount = $stmt->fetchColumn();

        // Nombre de challenges
        $challengesQuery = "SELECT COUNT(*) FROM challenges";
        $stmt = $db->prepare($challengesQuery);
        $stmt->execute();
        $challengesCount = $stmt->fetchColumn();

        // Nombre d'équipes
        $teamsQuery = "SELECT COUNT(*) FROM teams";
        $stmt = $db->prepare($teamsQuery);
        $stmt->execute();
        $teamsCount = $stmt->fetchColumn();

        // Nombre de participants - modifié pour utiliser hackathon_participants
        $participantsQuery = "SELECT COUNT(*) FROM hackathon_participants";
        $stmt = $db->prepare($participantsQuery);
        $stmt->execute();
        $participantsCount = $stmt->fetchColumn();

        // Nombre de soumissions
        $submissionsQuery = "SELECT COUNT(*) FROM challenge_submissions";
        $stmt = $db->prepare($submissionsQuery);
        $stmt->execute();
        $submissionsCount = $stmt->fetchColumn();

        $data = [
            'users' => $usersCount,
            'admins' => $adminsCount,
            'participants' => $participantsCount,
            'hackathons' => $hackathonsCount,
            'challenges' => $challengesCount,
            'teams' => $teamsCount,
            'submissions' => $submissionsCount
        ];

        echo json_encode($data);
        exit;
    }

}
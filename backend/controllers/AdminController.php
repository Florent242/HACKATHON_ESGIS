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
    private $TokenManager;
    private $key = 'your-secret-key';

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->TokenManager = $tokenManager;
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
            $submissionsQuery = "SELECT COUNT(*) 
                              FROM challenge_submissions cs
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
                    (SELECT COUNT(*) FROM hackathon_participants hp WHERE hp.hackathon_id = h.id) as participants_count,
                    (SELECT COUNT(*) FROM challenges c WHERE c.hackathon_id = h.id) as challenges_count
                    FROM hackathons h
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
                    (SELECT SUM(total_score) FROM challenge_submissions cs
                     JOIN team_members tm ON cs.user_id = tm.user_id
                     WHERE tm.team_id = t.id AND cs.status = 'completed') as total_points
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

    /**
     * Gestion des challenges - Liste avec filtres et pagination
     */
    public function getChallenges()
    {
        try {
            $this->validateMethod('GET');

            // Paramètres de filtrage et pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            $difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
            $status = isset($_GET['status']) ? $_GET['status'] : '';

            $offset = ($page - 1) * $limit;

            // Construire la requête avec filtres
            $sql = "SELECT c.*, 
                           u.username as created_by_name,
                           h.name as hackathon_name,
                           COUNT(DISTINCT p.id) as participants_count
                    FROM challenges c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN hackathons h ON c.hackathon_id = h.id
                    LEFT JOIN projects p ON c.id = p.challenge_id";

            $whereConditions = [];
            $params = [];

            if ($search) {
                $whereConditions[] = "(c.title LIKE :search OR c.description LIKE :search OR c.category LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if ($type) {
                $whereConditions[] = "c.type = :type";
                $params[':type'] = $type;
            }

            if ($difficulty) {
                $whereConditions[] = "c.difficulty = :difficulty";
                $params[':difficulty'] = $difficulty;
            }

            if ($status !== '') {
                $whereConditions[] = "c.is_active = :status";
                $params[':status'] = $status;
            }

            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(' AND ', $whereConditions);
            }

            $sql .= " GROUP BY c.id ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compter le total pour la pagination
            $countSql = "SELECT COUNT(DISTINCT c.id) as total FROM challenges c";
            if (!empty($whereConditions)) {
                $countSql .= " WHERE " . implode(' AND ', $whereConditions);
            }

            $countStmt = $this->db->prepare($countSql);
            $countParams = array_diff_key($params, [':limit' => '', ':offset' => '']);
            $countStmt->execute($countParams);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $this->jsonResponse([
                'success' => true,
                'data' => $challenges,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_items' => $total,
                    'per_page' => $limit
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Statistiques des challenges
     */
    public function getChallengesStats()
    {
        try {
            $this->validateMethod('GET');

            // Statistiques générales
            $statsSql = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                            SUM(points) as total_points,
                            COUNT(DISTINCT created_by) as creators
                         FROM challenges";

            $stmt = $this->db->prepare($statsSql);
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Compter les participants
            $participantsSql = "SELECT COUNT(DISTINCT p.team_id) as team_participants
                               FROM projects p
                               INNER JOIN challenges c ON p.challenge_id = c.id";

            $stmt = $this->db->prepare($participantsSql);
            $stmt->execute();
            $participants = $stmt->fetch(PDO::FETCH_ASSOC);

            $stats['team_participants'] = $participants['team_participants'] ?? 0;

            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Créer un nouveau challenge
     */
    public function createChallenge()
    {
        try {
            $this->validateMethod('POST');

            if (!hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            $input = json_decode(file_get_contents('php://input'), true);

            // Validation des champs requis
            $requiredFields = ['title', 'type', 'difficulty', 'hackathon_id', 'points', 'description'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Le champ '$field' est requis");
                }
            }

            // Validation des points
            if (!is_numeric($input['points']) || $input['points'] < 1 || $input['points'] > 1000) {
                throw new Exception('Les points doivent être entre 1 et 1000');
            }

            // Validation du hackathon
            $hackathonStmt = $this->db->prepare("SELECT id FROM hackathons WHERE id = ?");
            $hackathonStmt->execute([$input['hackathon_id']]);
            if (!$hackathonStmt->fetch()) {
                throw new Exception('Hackathon invalide');
            }

            // Validation JSON pour hint et algo_config
            if (!empty($input['hint'])) {
                if (!isValidJSON($input['hint'])) {
                    throw new Exception('Format JSON invalide pour les indices');
                }
            }

            if (!empty($input['algo_config'])) {
                if (!isValidJSON($input['algo_config'])) {
                    throw new Exception('Format JSON invalide pour la configuration algo');
                }
            }

            // Insérer le challenge
            $sql = "INSERT INTO challenges (
                        title, type, category, description, hint, difficulty,
                        url_path, resource_link, instructions, points, is_active,
                        is_dynamic, created_by, hackathon_id, phase_id, algo_config
                    ) VALUES (
                        :title, :type, :category, :description, :hint, :difficulty,
                        :url_path, :resource_link, :instructions, :points, :is_active,
                        :is_dynamic, :created_by, :hackathon_id, :phase_id, :algo_config
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $input['title'],
                ':type' => $input['type'],
                ':category' => $input['category'] ?? null,
                ':description' => $input['description'],
                ':hint' => $input['hint'] ?? null,
                ':difficulty' => $input['difficulty'],
                ':url_path' => $input['url_path'] ?? null,
                ':resource_link' => $input['resource_link'] ?? null,
                ':instructions' => $input['instructions'] ?? null,
                ':points' => $input['points'],
                ':is_active' => $input['is_active'] ?? 1,
                ':is_dynamic' => $input['is_dynamic'] ?? 0,
                ':created_by' => $_SESSION['user_id'],
                ':hackathon_id' => $input['hackathon_id'],
                ':phase_id' => $input['phase_id'] ?? null,
                ':algo_config' => $input['algo_config'] ?? null
            ]);

            $challengeId = $this->db->lastInsertId();

            // Traiter les flags (CTF)
            if (!empty($input['flags']) && is_array($input['flags'])) {
                $this->createFlags($challengeId, $input['flags']);
            }

            // Traiter les snippets (Algo)
            if (!empty($input['snippets']) && is_array($input['snippets'])) {
                $this->createSnippets($challengeId, $input['snippets']);
            }

            // Traiter les tests (Algo)
            if (!empty($input['tests']) && is_array($input['tests'])) {
                $this->createTests($challengeId, $input['tests']);
            }

            // Traiter les technologies (Dev)
            if (!empty($input['technologies']) && is_array($input['technologies'])) {
                $this->createTechnologies($challengeId, $input['technologies']);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Challenge créé avec succès',
                'data' => ['id' => $challengeId]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupérer un challenge par ID
     */
    public function getChallenge($id)
    {
        try {
            $this->validateMethod('GET');

            $sql = "SELECT c.*, 
                           u.username as created_by_name,
                           h.name as hackathon_name,
                           p.name as phase_name
                    FROM challenges c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN hackathons h ON c.hackathon_id = h.id
                    LEFT JOIN phases p ON c.phase_id = p.id
                    WHERE c.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$challenge) {
                throw new Exception('Challenge non trouvé');
            }

            // Charger les données liées
            $challenge['flags'] = $this->getFlags($id);
            $challenge['snippets'] = $this->getSnippets($id);
            $challenge['tests'] = $this->getTests($id);
            $challenge['technologies'] = $this->getTechnologies($id);

            $this->jsonResponse([
                'success' => true,
                'data' => $challenge
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mettre à jour un challenge
     */
    public function updateChallenge($id)
    {
        // Démarrer la transaction
        $this->db->beginTransaction();

        try {
            $this->validateMethod('PUT');
            $userId = $this->TokenManager->getCurrentUserId();

            if (!$this->isAdmin($userId)) {
                throw new Exception('Non autorisé');
            }

            $input = json_decode(file_get_contents('php://input'), true);

            // Vérifier que le challenge existe et est verrouillé
            $checkStmt = $this->db->prepare("
                SELECT id, is_active 
                FROM challenges 
                WHERE id = ? 
                FOR UPDATE
            ");
            $checkStmt->execute([$id]);
            $challenge = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$challenge) {
                throw new Exception('Challenge non trouvé');
            }

            if ($challenge['is_active'] == 0) {
                throw new Exception('Ce challenge est verrouillé et ne peut pas être modifié');
            }

            // Validation des champs requis
            $requiredFields = ['title', 'type', 'difficulty', 'hackathon_id', 'points', 'description'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Le champ '$field' est requis");
                }
            }

            // Validation des points
            if (!is_numeric($input['points']) || $input['points'] < 1 || $input['points'] > 1000) {
                throw new Exception('Les points doivent être entre 1 et 1000');
            }

            // Validation JSON
            if (!empty($input['hint']) && !isValidJSON($input['hint'])) {
                throw new Exception('Format JSON invalide pour les indices');
            }

            if (!empty($input['algo_config']) && !isValidJSON($input['algo_config'])) {
                throw new Exception('Format JSON invalide pour la configuration algo');
            }

            // Validation du hackathon
            $hackathonStmt = $this->db->prepare("
                SELECT id FROM hackathons 
                WHERE id = ? AND (end_date > NOW() OR status = 'active')
            ");
            $hackathonStmt->execute([$input['hackathon_id']]);
            if (!$hackathonStmt->fetch()) {
                throw new Exception('Hackathon invalide ou terminé');
            }

            // Validation du phase
            if (empty($input['phase_id'])) {
                throw new Exception('ID de phase requis');
            }
            $phaseStmt = $this->db->prepare("SELECT id FROM phases WHERE id = ?");
            $phaseStmt->execute([$input['phase_id']]);
            if (!$phaseStmt->fetch()) {
                throw new Exception('Phase invalide');
            }

            // Mise à jour du challenge
            $sql = "UPDATE challenges SET 
                        title = :title, 
                        type = :type, 
                        category = :category,
                        description = :description, 
                        hint = :hint, 
                        difficulty = :difficulty,
                        url_path = :url_path, 
                        resource_link = :resource_link,
                        instructions = :instructions, 
                        points = :points, 
                        is_active = :is_active,
                        is_dynamic = :is_dynamic, 
                        hackathon_id = :hackathon_id,
                        phase_id = :phase_id, 
                        algo_config = :algo_config,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':id' => $id,
                ':title' => $input['title'],
                ':type' => $input['type'],
                ':category' => $input['category'] ?? null,
                ':description' => $input['description'],
                ':hint' => isset($input['hint']) && $input['hint'] !== ''
                    ? json_encode($input['hint'])
                    : null,
                ':difficulty' => $input['difficulty'],
                ':url_path' => $input['url_path'] ?? null,
                ':resource_link' => $input['resource_link'] ?? null,
                ':instructions' => isset($input['instructions']) ? (string) $input['instructions'] : '',
                ':points' => $input['points'],
                ':is_active' => (int) $input['is_active'] ?? 1,
                ':is_dynamic' => (int) $input['is_dynamic'] ?? 0,
                ':hackathon_id' => (int) $input['hackathon_id'],
                ':phase_id' => !empty($input['phase_id']) ? (int)$input['phase_id'] : null,
                ':algo_config' => isset($input['algo_config']) && $input['algo_config'] !== ''
                    ? json_encode($input['algo_config'])
                    : null,
            ]);

            if (!$result) {
                throw new Exception('Erreur lors de la mise à jour du challenge');
            }

            // Suppression et recréation des données liées
            $this->deleteFlags($id);
            $this->deleteSnippets($id);
            $this->deleteTests($id);
            $this->deleteTechnologies($id);

            // Validation et création des flags
            if (!empty($input['flags']) && is_array($input['flags'])) {
                $this->validateFlags($input['flags']); // Nouvelle méthode à implémenter
                $this->createFlags($id, $input['flags']);
            }

            // Validation et création des snippets
            if (!empty($input['snippets']) && is_array($input['snippets'])) {
                $this->validateSnippets($input['snippets']); // Nouvelle méthode à implémenter
                $this->createSnippets($id, $input['snippets']);
            }

            // Validation et création des tests
            if (!empty($input['tests']) && is_array($input['tests'])) {
                $this->validateTests($input['tests']);
                $this->createTests($id, $input['tests']);
            }

            // Validation et création des technologies
            if (!empty($input['technologies']) && is_array($input['technologies'])) {
                $this->validateTechnologies($input['technologies']); // Nouvelle méthode à implémenter
                $this->createTechnologies($id, $input['technologies']);
            }

            // Journalisation de l'action
            logSecurity($userId, 'update_challenge', [
                'challenge_id' => $id,
                'user_id' => $userId,
                'action' => 'update_challenge',
                'details' => "Mise à jour du challenge #$id"
            ]);

            // Validation finale
            $this->validateChallengeConsistency($id, $input);

            // Tout s'est bien passé, on valide la transaction
            if ($this->db->inTransaction()) $this->db->commit();

            $this->jsonResponse([
                'success' => true,
                'message' => 'Challenge mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            // En cas d'erreur, on annule la transaction
            if ($this->db->inTransaction()) $this->db->rollBack();

            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // À désactiver en production
            ], 400);
        }
    }

    /**
     * Supprimer un challenge
     */
    public function deleteChallenge($id)
    {
        try {
            $this->validateMethod('DELETE');

            $user_id = $this->TokenManager->getCurrentUserId();
            if (!$this->isAdmin($user_id)) {
                throw new Exception('Non autorisé ' . $user_id);
            }

            // Démarrer la transaction
            $this->db->beginTransaction();

            try {
                // Vérifier que le challenge existe
                $checkStmt = $this->db->prepare("SELECT id FROM challenges WHERE id = ?");
                $checkStmt->execute([$id]);
                if (!$checkStmt->fetch()) {
                    throw new Exception('Challenge non trouvé');
                }

                // Vérifier s'il y a des soumissions
                $submissionsStmt = $this->db->prepare("SELECT COUNT(*) as count FROM projects WHERE challenge_id = ?");
                $submissionsStmt->execute([$id]);
                $submissions = $submissionsStmt->fetch(PDO::FETCH_ASSOC);

                if ($submissions['count'] > 0) {
                    throw new Exception('Impossible de supprimer ce challenge car il a des soumissions associées');
                }

                // Désactiver temporairement la vérification des clés étrangères
                $this->db->exec('SET FOREIGN_KEY_CHECKS=0');

                // Supprimer les données liées
                $this->deleteFlags($id);
                $this->deleteSnippets($id);
                $this->deleteTests($id);
                $this->deleteTechnologies($id);

                // Supprimer les flags liés à ce challenge
                $deleteFlagsStmt = $this->db->prepare("DELETE FROM flags WHERE challenge_id = ?");
                $deleteFlagsStmt->execute([$id]);

                // Supprimer le challenge
                $stmt = $this->db->prepare("DELETE FROM challenges WHERE id = ?");
                $stmt->execute([$id]);

                // Réactiver la vérification des clés étrangères
                $this->db->exec('SET FOREIGN_KEY_CHECKS=1');

                // Valider la transaction
                $this->db->commit();

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Challenge supprimé avec succès'
                ]);
            } catch (Exception $e) {
                // Annuler la transaction en cas d'erreur
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                // Réactiver la vérification des clés étrangères en cas d'erreur
                $this->db->exec('SET FOREIGN_KEY_CHECKS=1');

                throw $e; // Relancer l'exception pour le catch externe
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Créer les flags pour un challenge
     */
    private function createFlags($challengeId, $flags)
    {
        $sql = "INSERT INTO flags (challenge_id, name, value, points, initial_points, min_points, decay, is_dynamic)
                VALUES (:challenge_id, :name, :value, :points, :initial_points, :min_points, :decay, :is_dynamic)";

        $stmt = $this->db->prepare($sql);

        foreach ($flags as $flag) {
            // Vérifie si la valeur est déjà un hash SHA-256 (64 caractères hexadécimaux)
            if (!preg_match('/^[a-f0-9]{64}$/i', $flag['value'])) {
                $flag['value'] = hash('sha256', $flag['value']);
            }
            $stmt->execute([
                ':challenge_id' => $challengeId,
                ':name' => $flag['name'] ?? null,
                ':value' => $flag['value'],
                ':points' => $flag['points'] ?? 100,
                ':initial_points' => $flag['points'] ?? 100,
                ':min_points' => $flag['min_points'] ?? 50,
                ':decay' => $flag['decay'] ?? 10,
                ':is_dynamic' => $flag['is_dynamic'] ?? 0
            ]);
        }
    }

    /**
     * Créer les snippets pour un challenge
     */
    private function createSnippets($challengeId, $snippets)
    {
        $sql = "INSERT INTO snippets (challenge_id, python, bash, javascript, cpp, c, csharp, php, ruby, typescript, pascal)
                VALUES (:challenge_id, :python, :bash, :javascript, :cpp, :c, :csharp, :php, :ruby, :typescript, :pascal)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':challenge_id' => $challengeId,
            ':python' => isset($snippets['python']) && $snippets['python'] !== ''
                ? $snippets['python']
                : null,
            'bash' => isset($snippets['bash']) && $snippets['bash'] !== ''
                ? $snippets['bash']
                : null,
            ':javascript' => isset($snippets['javascript']) && $snippets['javascript'] !== ''
                ? $snippets['javascript']
                : null,
            ':cpp' => isset($snippets['cpp']) && $snippets['cpp'] !== ''
                ? $snippets['cpp']
                : null,
            ':c' => isset($snippets['c']) && $snippets['c'] !== ''
                ? $snippets['c']
                : null,
            ':csharp' => isset($snippets['csharp']) && $snippets['csharp'] !== ''
                ? $snippets['csharp']
                : null,
            ':php' => isset($snippets['php']) && $snippets['php'] !== ''
                ? $snippets['php']
                : null,
            ':ruby' => isset($snippets['ruby']) && $snippets['ruby'] !== ''
                ? $snippets['ruby']
                : null,
            ':typescript' => isset($snippets['typescript']) && $snippets['typescript'] !== ''
                ? $snippets['typescript']
                : null,
            ':pascal' => isset($snippets['pascal']) && $snippets['pascal'] !== ''
                ? $snippets['pascal']
                : null,

        ]);
    }

    /**
     * Créer les tests pour un challenge
     */
    private function createTests($challengeId, $tests)
    {
        $sql = "INSERT INTO challenge_algo_tests (challenge_id, input_data, expected_output, is_public, weight, timeout_seconds, memory_limit_mb, test_order)
                VALUES (:challenge_id, :input_data, :expected_output, :is_public, :weight, :timeout_seconds, :memory_limit_mb, :test_order)";

        $stmt = $this->db->prepare($sql);

        foreach ($tests as $index => $test) {
            $stmt->execute([
                ':challenge_id' => $challengeId,
                ':input_data' => $test['input_data'],
                ':expected_output' => $test['expected_output'],
                ':is_public' => (isset($test['is_public']) && $test['is_public'] == true) ? 1 : 0 ?? 1,
                ':weight' => $test['weight'] ?? 10,
                ':timeout_seconds' => $test['timeout_seconds'] ?? 2,
                ':memory_limit_mb' => $test['memory_limit_mb'] ?? 128,
                ':test_order' => $index + 1
            ]);
        }
    }

    /**
     * Créer les technologies pour un challenge
     */
    private function createTechnologies($challengeId, $technologies)
    {
        $sql = "INSERT INTO challenge_technologies (challenge_id, technology_id) VALUES (:challenge_id, :technology_id)";
        $stmt = $this->db->prepare($sql);

        foreach ($technologies as $technologyId) {
            $stmt->execute([
                ':challenge_id' => $challengeId,
                ':technology_id' => $technologyId
            ]);
        }
    }

    /**
     * Récupérer les flags d'un challenge
     */
    private function getFlags($challengeId)
    {
        $sql = "SELECT * FROM flags WHERE challenge_id = ? ORDER BY id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$challengeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les snippets d'un challenge
     */
    private function getSnippets($challengeId)
    {
        $sql = "SELECT * FROM snippets WHERE challenge_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$challengeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les tests d'un challenge
     */
    private function getTests($challengeId)
    {
        $sql = "SELECT * FROM challenge_algo_tests WHERE challenge_id = ? ORDER BY test_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$challengeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les technologies d'un challenge
     */
    private function getTechnologies($challengeId)
    {
        $sql = "SELECT t.* FROM technologies t
                INNER JOIN challenge_technologies ct ON t.id = ct.technology_id
                WHERE ct.challenge_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$challengeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprimer les flags d'un challenge
     */
    private function deleteFlags($challengeId)
    {
        $stmt = $this->db->prepare("DELETE FROM flags WHERE challenge_id = ?");
        $stmt->execute([$challengeId]);
    }

    /**
     * Supprimer les snippets d'un challenge
     */
    private function deleteSnippets($challengeId)
    {
        $stmt = $this->db->prepare("DELETE FROM snippets WHERE challenge_id = ?");
        $stmt->execute([$challengeId]);
    }

    /**
     * Supprimer les tests d'un challenge
     */
    private function deleteTests($challengeId)
    {
        $stmt = $this->db->prepare("DELETE FROM challenge_algo_tests WHERE challenge_id = ?");
        $stmt->execute([$challengeId]);
    }

    /**
     * Supprimer les technologies d'un challenge
     */
    private function deleteTechnologies($challengeId)
    {
        $stmt = $this->db->prepare("DELETE FROM challenge_technologies WHERE challenge_id = ?");
        $stmt->execute([$challengeId]);
    }

    /**
     * Récupérer la liste des technologies
     */
    public function getAllTechnologies()
    {
        try {
            $this->validateMethod('GET');

            $sql = "SELECT * FROM technologies ORDER BY name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $technologies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse([
                'success' => true,
                'data' => $technologies
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupérer les phases d'un hackathon
     */
    public function getHackathonPhases($hackathonId)
    {
        try {
            $this->validateMethod('GET');

            $sql = "SELECT * FROM phases WHERE hackathon_id = ? ORDER BY start";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$hackathonId]);
            $phases = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse([
                'success' => true,
                'data' => $phases
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Valide les flags d'un challenge
     */
    private function validateFlags(array $flags)
    {
        if (count($flags) === 0) {
            throw new Exception('Au moins un flag est requis');
        }

        foreach ($flags as $flag) {
            if (empty($flag['flag'])) {
                throw new Exception('Le contenu du flag ne peut pas être vide');
            }

            if (strlen($flag['flag']) > 255) {
                throw new Exception('Le flag ne peut pas dépasser 255 caractères');
            }
        }
    }

    /**
     * Valide les snippets de code
     */
    private function validateSnippets(array $snippets)
    {
        $allowedLanguages = ['python', 'javascript', 'java', 'cpp', 'c', 'csharp', 'php', 'ruby', 'pascal', 'typescript', 'bash'];

        foreach ($snippets as $language => $code) {
            if (!in_array($language, $allowedLanguages)) {
                throw new Exception("Langage non supporté: $language");
            }

            if (!is_string($code)) {
                throw new Exception("Le code pour $language doit être une chaîne de caractères");
            }
        }
    }

    /**
     * Valide les tests du challenge
     */
    private function validateTests(array $tests)
    {
        if (count($tests) === 0) {
            throw new Exception('Au moins un test est requis');
        }

        foreach ($tests as $test) {
            if (empty($test['input_data'])) {
                throw new Exception("L'entrée du test ne peut pas être vide");
            }

            if (!isset($test['expected_output'])) {
                throw new Exception("La sortie attendue est requise");
            }

            if (!isset($test['weight']) || !is_numeric($test['weight']) || $test['weight'] <= 0) {
                throw new Exception("Le poids du test doit être un nombre positif");
            }

            if (isset($test['is_public']) && !is_bool($test['is_public'])) {
                throw new Exception("Le champ is_public doit être un booléen");
            }
        }
    }

    /**
     * Valide les technologies
     */
    private function validateTechnologies(array $technologies)
    {
        if (empty($technologies)) {
            return; // Les technologies sont optionnelles
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM technologies WHERE id = ?");

        foreach ($technologies as $techId) {
            if (!is_numeric($techId)) {
                throw new Exception("ID de technologie invalide: $techId");
            }

            $stmt->execute([$techId]);
            if ($stmt->fetchColumn() === 0) {
                throw new Exception("Technologie non trouvée: $techId");
            }
        }
    }

    /**
     * Valide la cohérence globale du challenge
     */
    private function validateChallengeConsistency($challengeId, array $input)
    {
        // Vérifier que le nombre de points est cohérent avec les tests
        if (!empty($input['tests'])) {
            $totalWeight = array_sum(array_column($input['tests'], 'weight'));
            if ($totalWeight <= 0) {
                throw new Exception("La somme des poids des tests doit être supérieure à 0");
            }
        }

        // Vérifier que le type de challenge est valide
        $validTypes = ['dev', 'ctf'];
        if (!in_array($input['type'], $validTypes)) {
            throw new Exception("Type de challenge invalide");
        }

        // Vérifier la difficulté
        $validDifficulties = ['easy', 'medium', 'hard', 'expert'];
        if (!in_array($input['difficulty'], $validDifficulties)) {
            throw new Exception("Niveau de difficulté invalide");
        }
    }

    /**
     * Journalise une action
     */
    private function logAction($userId, $action, $details = '')
    {
        try {
            $stmt = $this->db->prepare("
            INSERT INTO audit_log (user_id, action, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");

            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $stmt->execute([
                $userId,
                $action,
                $details,
                $ip,
                $userAgent
            ]);
        } catch (Exception $e) {
            // Ne pas faire échouer l'opération principale en cas d'échec de journalisation
            error_log("Échec de la journalisation: " . $e->getMessage());
        }
    }
}

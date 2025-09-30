<?php

namespace Auth\Controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Auth\Model\Database;
use Auth\Model\TokenManager;
use Exception;
use PDO;
use PDOException;
use Auth\Controller\Controller;
use DateTime;
use DateInterval;

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

        // Vérification du rôle global
        $query = "SELECT role FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $userId]);
        $role = $stmt->fetchColumn();

        if (!in_array($role, ['admin', 'organisateur'])) {
            return false;
        }

        // Vérification dans la whitelist
        $query = "SELECT 1 FROM admin_whitelist WHERE user_id = :id AND (expires_at > NOW() OR expires_at IS NULL) LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $userId]);

        return (bool) $stmt->fetchColumn();
    }

    public function validateToken(string $token): array
    {
        $tokenManager = new TokenManager($this->key, $this->db);
        return $tokenManager->validateToken($token);
    }
    /**
     * Bannir un utilisateur
     */
    public function banUser($userId, $reason = '', $isbulk = false)
    {
        try {
            $this->validateMethod('POST');

            $userId = (int)$userId;
            $reason = trim($reason);

            // Vérifier si l'utilisateur existe
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé', 404);
            }

            // Mettre à jour le statut
            $stmt = $this->db->prepare("
            UPDATE users 
            SET status = 'inactive',
                suspended_until = NULL,
                suspension_reason = :reason
            WHERE id = :id
        ");

            $stmt->execute([
                ':id' => $userId,
                ':reason' => $reason ?: 'Compte banni par un administrateur'
            ]);

            // Journalisation
            $this->logActivity(
                'user_banned',
                "Utilisateur #$userId banni" . ($reason ? " (Raison: $reason)" : ''),
                $userId,
                $user['role'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );

            $this->jsonResponse(['success' => true, 'message' => 'Utilisateur banni avec succès']);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Suspendre un utilisateur
     */
    public function suspendUser($userId, $duration = 24, $reason = '', $isbulk = false)
    {
        try {
            $this->validateMethod('POST');

            $userId = (int)$userId;
            $duration = max(1, (int)$duration); // En heures
            $reason = trim($reason);

            // Vérifier si l'utilisateur existe
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé', 404);
            }

            // Calculer la date de fin de suspension
            $suspendedUntil = (new DateTime())
                ->add(new DateInterval("PT{$duration}H"))
                ->format('Y-m-d H:i:s');

            // Mettre à jour le statut
            $stmt = $this->db->prepare("
            UPDATE users 
            SET status = 'inactive',
                suspended_until = :suspended_until,
                suspension_reason = :reason
            WHERE id = :id
        ");

            $stmt->execute([
                ':id' => $userId,
                ':suspended_until' => $suspendedUntil,
                ':reason' => $reason ?: 'Compte suspendu par un administrateur'
            ]);

            // Journalisation
            $this->logActivity(
                'user_suspended',
                "Utilisateur #$userId suspendu pour $duration heures" . ($reason ? " (Raison: $reason)" : ''),
                $userId,
                $user['role'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );

            if ($isbulk) {
                return;
            }
            $this->jsonResponse([
                'success' => true,
                'message' => 'Utilisateur suspendu avec succès',
                'suspended_until' => $suspendedUntil
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Réactiver un utilisateur
     */
    public function unsuspendUser($userId)
    {
        try {
            $this->validateMethod('POST');

            $userId = (int)$userId;

            // Vérifier si l'utilisateur existe
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé', 404);
            }

            // Réactiver l'utilisateur
            $stmt = $this->db->prepare("
            UPDATE users 
            SET status = 'active',
                suspended_until = NULL,
                suspension_reason = NULL,
                failed_attempts = 0,
                locked_until = NULL
            WHERE id = :id
        ");

            $stmt->execute([':id' => $userId]);

            // Journalisation
            $this->logActivity('user_unsuspended', "Utilisateur #$userId réactivé", $userId, $user['role'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

            $this->jsonResponse(['success' => true, 'message' => 'Utilisateur réactivé avec succès']);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur
     */
    public function resetUserPassword($userId, $newPassword = null)
    {
        try {
            $this->validateMethod('POST');

            $userId = (int)$userId;

            // Vérifier si l'utilisateur existe
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé', 404);
            }

            // Générer un mot de passe aléatoire si aucun n'est fourni
            if (empty($newPassword)) {
                $newPassword = bin2hex(random_bytes(8)); // Mot de passe de 16 caractères
            }

            // Hacher le nouveau mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe
            $stmt = $this->db->prepare("
            UPDATE users 
            SET password = :password,
                password_changed_at = NOW(),
                failed_attempts = 0,
                locked_until = NULL
            WHERE id = :id
        ");

            $stmt->execute([
                ':id' => $userId,
                ':password' => $hashedPassword
            ]);

            // Journalisation
            $this->logActivity('password_reset', "Reset du mot de passe de l'utilisateur #$userId", $userId, $user['role'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès',
                'new_password' => $newPassword // À supprimer en production, à utiliser avec précaution
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Débloquer un compte verrouillé
     */
    public function unlockUserAccount($userId, $isbulk = false)
    {
        try {
            $this->validateMethod('POST');

            $userId = (int)$userId;

            // Vérifier si l'utilisateur existe
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé', 404);
            }

            // Débloquer le compte
            $stmt = $this->db->prepare("
            UPDATE users 
            SET failed_attempts = 0,
                locked_until = NULL
            WHERE id = :id
        ");

            $stmt->execute([':id' => $userId]);

            // Journalisation
            $this->logActivity('account_unlocked', 
            "Compte utilisateur #$userId débloqué",
            $userId,
            $user['role'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']);

            if ($isbulk) {
                return;
            }
            $this->jsonResponse(['success' => true, 'message' => 'Compte débloqué avec succès']);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Méthode utilitaire pour récupérer un utilisateur par son ID
     */
    private function getUserById($userId)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM users WHERE id = :id
    ");

        $stmt->execute([':id' => (int)$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

            $this->logActivity('update_admin', 'Mise à jour d\'un admin par ' . $this->TokenManager->getCurrentUserId(), $data, 'admin_update', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

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

            logActivity('delete_user', 'Utilisateur supprimé', [
                'user_id' => $id,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'identifier' => $_SESSION['user'] ? ['identifier'] : null,
                'email' => $_SESSION['user'] ? ['email'] : null,
                'role' => $_SESSION['user'] ? ['role'] : null,
                'logged_in' => $_SESSION['user'] ? ['logged_in'] : null,
                'last_activity' => $_SESSION['user'] ? ['last_activity'] : null,
            ], $_SESSION['user_id'], 'info');

            $this->logActivity('delete_user', 'Utilisateur supprimé', [
                'user_id' => $id,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'identifier' => $_SESSION['user'] ? ['identifier'] : null,
                'email' => $_SESSION['user'] ? ['email'] : null,
                'role' => $_SESSION['user'] ? ['role'] : null,
                'logged_in' => $_SESSION['user'] ? ['logged_in'] : null,
                'last_activity' => $_SESSION['user'] ? ['last_activity'] : null,
            ], 'info_deletion', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

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
     * Récupère les utilisateurs avec pagination
     * 
     * @param int $page Numéro de page
     * @param int $perPage Nombre d'éléments par page
     * @param string $search Terme de recherche
     * @param string $status Filtre par statut
     * @param string $role Filtre par rôle
     * @param string $team Filtre par équipe
     */
    public function getUsersPaginated()
    {
        try {
            // Récupérer les paramètres de la requête
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = min(50, max(5, (int)($_GET['per_page'] ?? 10))); // Entre 5 et 50 par page
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $role = $_GET['role'] ?? '';
            $team = $_GET['team'] ?? '';

            // Construction de la requête
            $query = "SELECT SQL_CALC_FOUND_ROWS 
                 u.*,
                 (SELECT name FROM teams t JOIN team_members tm ON t.id = tm.team_id WHERE tm.user_id = u.id LIMIT 1) as team_name
                 FROM users u 
                 WHERE 1=1";

            $params = [];

            // Filtres
            if (!empty($search)) {
                $query .= " AND (u.username LIKE :search OR u.email LIKE :search1 OR u.fullname LIKE :search2)";
                $params[':search'] = "%$search%";
                $params[':search1'] = "%$search%";
                $params[':search2'] = "%$search%";
            }

            if (!empty($status)) {
                $query .= " AND u.status = :status";
                $params[':status'] = $status;
            }

            if (!empty($role)) {
                $query .= " AND u.role = :role";
                $params[':role'] = $role;
            }

            if (!empty($team)) {
                $query .= " AND EXISTS (SELECT 1 FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = u.id AND t.id = :team)";
                $params[':team'] = $team;
            }

            // Tri
            $sortField = in_array($_GET['sort'] ?? 'created_at', ['username', 'email', 'role', 'status', 'created_at'])
                ? $_GET['sort']
                : 'created_at';

            $sortOrder = strtoupper($_GET['order'] ?? 'desc') === 'ASC' ? 'ASC' : 'DESC';
            $query .= " ORDER BY $sortField $sortOrder";

            // Pagination
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT :offset, :perPage";
            $params[':offset'] = $offset;
            $params[':perPage'] = $perPage;

            // Exécution de la requête
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }

            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Nombre total d'utilisateurs correspondant aux filtres
            $total = (int)$this->db->query("SELECT FOUND_ROWS()")->fetchColumn();
            $lastPage = max(1, ceil($total / $perPage));

            // Réponse
            $this->jsonResponse([
                'success' => true,
                'data' => $users,
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => $lastPage,
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $total)
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les détails d'un utilisateur
     */
    public function getUser($userId)
    {
        try {
            $query = "SELECT u.*, 
                 (SELECT name FROM teams t JOIN team_members tm ON t.id = tm.team_id WHERE tm.user_id = u.id LIMIT 1) as team_name
                 FROM users u 
                 WHERE u.id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('Utilisateur non trouvé', 404);
            }

            // Ne pas renvoyer le mot de passe
            unset($user['password']);

            return $user;
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Récupère les statistiques d'un utilisateur
     */
    public function getUserStats($userId)
    {
        header('Content-Type: application/json');

        try {
            $database = Database::getInstance();
            $db = $database->getConnection();

            // Vérifier l'existence de l'utilisateur
            $userCheck = $db->prepare("SELECT id FROM users WHERE id = ?");
            $userCheck->execute([$userId]);
            $user = $userCheck->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                throw new Exception("Utilisateur non trouvé");
            }

            // Initialiser la structure de réponse
            $response = [
                'success' => true,
                'data' => [
                    'stats' => [
                        'number-dev-challenges' => 0,
                        'number-dev-challenges-on' => 0,
                        'number-hacking-challenges' => 0,
                        'number-hacking-challenges-validate' => 0,
                        'number-submitted-projects' => 0,
                        'total-points' => 0,
                        'total-points-stat' => 0,
                        'hacking-stat' => 0,
                        'points-change' => 0,
                        'points-change-percent' => 0
                    ]
                ]
            ];

            // Défis de développement (basé sur user_progress et snippets)
            try {
                $devQuery = $db->prepare("
                    SELECT 
                        COUNT(DISTINCT up.challenge_id) as total,
                        COALESCE(SUM(CASE WHEN up.status = 'ongoing' THEN 1 ELSE 0 END), 0) as in_progress
                    FROM user_progress up
                    LEFT JOIN snippets s ON up.challenge_id = s.challenge_id
                    WHERE up.user_id = ?
                    AND (s.language IS NULL OR s.language IN ('java', 'python', 'js', 'bash'))
                ");
                $devQuery->execute([$userId]);
                $devData = $devQuery->fetch(PDO::FETCH_ASSOC);

                if ($devData) {
                    $response['data']['stats']['number-dev-challenges'] = (int)$devData['total'];
                    $response['data']['stats']['number-dev-challenges-on'] = (int)$devData['in_progress'];
                }
            } catch (Exception $e) {
                error_log("Erreur dans la requête des défis de développement: " . $e->getMessage());
            }

            // Défis de hacking (basé sur validated_flags)
            try {
                $hackingQuery = $db->prepare("
                    SELECT 
                        COUNT(*) as total,
                        COALESCE(SUM(CASE WHEN vf.is_valid = 1 THEN 1 ELSE 0 END), 0) as validated
                    FROM validated_flags vf
                    WHERE vf.user_id = ?
                ");
                $hackingQuery->execute([$userId]);
                $hackingData = $hackingQuery->fetch(PDO::FETCH_ASSOC);

                if ($hackingData) {
                    $response['data']['stats']['number-hacking-challenges'] = (int)$hackingData['total'];
                    $response['data']['stats']['number-hacking-challenges-validate'] = (int)$hackingData['validated'];

                    if ($response['data']['stats']['number-hacking-challenges'] > 0) {
                        $response['data']['stats']['hacking-stat'] = round(
                            ($response['data']['stats']['number-hacking-challenges-validate'] /
                                $response['data']['stats']['number-hacking-challenges']) * 100
                        );
                    }
                }
            } catch (Exception $e) {
                error_log("Erreur dans la requête des défis de hacking: " . $e->getMessage());
            }

            // Projets soumis (basé sur team_members et teams)
            try {
                $projectsQuery = $db->prepare("
                    SELECT * 
                    FROM projects p
                    WHERE status = 'submitted' AND team_id IN (
                        SELECT team_id 
                        FROM team_members 
                        WHERE user_id = ?
                    )
                ");
                $projectsQuery->execute([$userId]);
                $projectsData = $projectsQuery->fetch(PDO::FETCH_ASSOC);

                if ($projectsData) {
                    $response['data']['stats']['number-submitted-projects'] = (int)$projectsData['submitted'];
                }
            } catch (Exception $e) {
                error_log("Erreur dans la requête des projets soumis: " . $e->getMessage());
            }

            // Points totaux (basé sur validated_flags)
            try {
                $pointsQuery = $db->prepare("
                    SELECT COALESCE(SUM(points_gained), 0) as total
                    FROM validated_flags
                    WHERE user_id = ? AND is_valid = 1
                ");
                $pointsQuery->execute([$userId]);
                $pointsData = $pointsQuery->fetch(PDO::FETCH_ASSOC);

                if ($pointsData) {
                    $response['data']['stats']['total-points'] = (int)$pointsData['total'];
                }

                // Points gagnés depuis la dernière connexion
                $pointsChangeQuery = $db->prepare("
                    SELECT COALESCE(SUM(points_gained), 0) as total
                    FROM validated_flags vf
                    JOIN users u ON vf.user_id = u.id
                    WHERE vf.user_id = ? AND vf.created_at > u.last_login
                ");
                $pointsChangeQuery->execute([$userId]);
                $pointsChangeData = $pointsChangeQuery->fetch(PDO::FETCH_ASSOC);

                if ($pointsChangeData) {
                    $response['data']['stats']['points-change'] = (int)$pointsChangeData['total'];
                    $response['data']['stats']['points-change-percent'] = $response['data']['stats']['total-points'] > 0
                        ? round(($response['data']['stats']['points-change'] / $response['data']['stats']['total-points']) * 100)
                        : 0;
                }
            } catch (Exception $e) {
                error_log("Erreur dans la requête des points: " . $e->getMessage());
            }

            // Pourcentage de progression
            $response['data']['stats']['total-points-stat'] = min(
                100,
                round(($response['data']['stats']['total-points'] / 1000) * 100)
            );

            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        } catch (PDOException $e) {
            error_log("Erreur de base de données: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques',
                'code' => 500,
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            error_log("Erreur générale: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques',
                'code' => 500,
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function getNotifications($userId)
    {
        header('Content-Type: application/json');

        try {
            $currentUserId = $this->TokenManager->getCurrentUserId();
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }

            $database = Database::getInstance();
            $db = $database->getConnection();

            // Récupérer les notifications de l'utilisateur
            $stmt = $db->prepare("
                SELECT
                    id,
                    message,
                    read_status,
                    created_at
                FROM notifications
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $notificationsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compter le nombre de notifications non lues
            $unreadStmt = $db->prepare("
                SELECT COUNT(*)
                FROM notifications
                WHERE user_id = :user_id AND read_status = 0
            ");
            $unreadStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $unreadStmt->execute();
            $unreadCount = (int) $unreadStmt->fetchColumn();

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'list' => $notificationsList,
                    'unread_count' => $unreadCount
                ]
            ]);
        } catch (PDOException $e) {
            error_log("Erreur de base de données dans getNotifications: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la récupération des notifications',
                'code' => 500,
                'details' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            error_log("Erreur générale dans getNotifications: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les équipes de l'utilisateur et renvoie un JSON
     */
    public function getUserTeams($userId)
    {
        $db = $this->db;

        $stmt = $db->prepare("SELECT e.* FROM team e JOIN team_members em ON e.id = em.team_id WHERE em.user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    public function getAllActivities($userId, $isbulk = false)
    {
        try {
            $currentUserId = $this->TokenManager->getCurrentUserId();
            if ($currentUserId != $userId && !$this->isAdmin($currentUserId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                return;
            }

            $database = Database::getInstance();
            $db = $database->getConnection();

            try {
                // Récupérer les activités récentes de l'utilisateur
                $stmt = $db->prepare("
                SELECT 
                    user_id,
                    action,
                    description,
                    data,
                    level,
                    ip_address,
                    user_agent,
                    created_at as timestamp
                FROM activity_logs
                WHERE user_id = :userId
                ORDER BY created_at DESC
            ");
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->execute();

                $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => "Une erreur est survenue lors de la récupération des activités récentes !"
                    // pour debug
                    // . $e->getMessage()
                ], 500);
            }
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
     * Crée un nouvel utilisateur
     */
    public function createUser()
    {
        try {
            $this->db->beginTransaction();
            $this->validateMethod('POST');
            $data = json_decode(file_get_contents('php://input'), true);

            // Validation des données
            $required = ['username', 'email', 'role', 'password', 'fullname', 'school', 'study_level', 'number', 'password_confirmation'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Le champ $field est obligatoire", 400);
                }
            }

            // Vérifier si l'email existe déjà
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                throw new Exception("Un utilisateur avec cet email existe déjà", 409);
            }

            // Verifier la confirmation de mot de passe
            if ($data['password'] !== $data['password_confirmation']) {
                throw new Exception("Les mots de passe ne correspondent pas", 400);
            }

            // Hachage du mot de passe si fourni
            $password = !empty($data['password'])
                ? password_hash($data['password'], PASSWORD_BCRYPT)
                : password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT); // Générer un mot de passe aléatoire si non fourni

            // Insertion de l'utilisateur
            $query = "INSERT INTO users (username, email, password, fullname, school, special_comp, study_level, number, role, bio, two_factor_enabled)
                 VALUES (:username, :email, :password, :fullname, :school, :special_comp, :study_level, :number, :role, :bio, :two_factor_enabled)";

            $stmt = $this->db->prepare($query);

            $stmt->execute([
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => $password ?? null,
                ':fullname' => $data['fullname'] ?? '',
                ':school' => $data['school'] ?? '',
                ':special_comp' => $data['special_comp'] ?? '',
                ':study_level' => $data['study_level'] ?? '',
                ':number' => $data['number'] ?? '',
                ':role' => $data['role'] ?? 'participant',
                ':bio' => $data['bio'] ?? '',
                ':two_factor_enabled' => $data['two_factor_enabled'] === '1' ? 1 : 0,
            ]);
            $userId = $this->db->lastInsertId();

            $this->logActivity('create_user', 'Création d\'un utilisateur par ' . $this->TokenManager->getCurrentUserId(), $data, 'admin_create', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            // Récupérer l'utilisateur créé
            if ($this->db->inTransaction()) {
                $this->db->commit();
            }
            $data = $this->getUser($userId);

            jsonResponse([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->jsonResponse(['success' => false, 'error' => 'Une erreur est survenue lors de la création de l\'utilisateur : ' . $e->getMessage()], $e->getCode() ?: 500);
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->jsonResponse(['success' => false, 'error' => 'Une erreur est survenue lors de la création de l\'utilisateur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Met à jour le statut d'un utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @param string $status Nouveau statut
     * @return bool Succès ou échec
     */
    public function updateUserStatus($userId, $status, $isbulk = false): void
    {
        try {
            $query = "UPDATE users SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $userId);
            $stmt->bindValue(':status', $status);

            $stmt->execute();
            $data = [
                'id' => $userId,
                'status' => $status,
            ];

            if ($isbulk) {
                return;
            }
            $this->logActivity('update_user_status', 'Mise à jour du statut d\'un utilisateur par ' . $this->TokenManager->getCurrentUserId(), $data, 'admin_update', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            $this->jsonResponse(['success' => true, 'message' => 'Statut mis à jour avec succès',
            'data' => [
                'updated_count' => $stmt->rowCount(),
            ]]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    /**
     * Récupère les statistiques globales des utilisateurs
     */
    public function getAllUserStats()
    {
        try {
            $this->validateMethod('GET');

            // Initialisation de la réponse
            $stats = [
                'total_users' => 0,
                'active_users' => 0,
                'inactive_users' => 0,
                'users_by_role' => [],
                'users_by_status' => [],
                'recent_users' => [],
                'users_activity' => []
            ];

            // 1. Nombre total d'utilisateurs
            $query = "SELECT COUNT(*) as total FROM users";
            $stmt = $this->db->query($query);
            $stats['total_users'] = (int)$stmt->fetchColumn();

            // 2. Utilisateurs actifs/inactifs (basé sur last_login ou status) mais etant donne que last_login n'existe pas on ira sur status
            $query = "SELECT 
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                    COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive
                  FROM users";
            $stmt = $this->db->query($query);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['active_users'] = (int)($activity['active'] ?? 0);
            $stats['inactive_users'] = (int)($activity['inactive'] ?? 0);

            // 3. Répartition par rôle
            $query = "SELECT 
                    role, 
                    COUNT(*) as count 
                  FROM users 
                  GROUP BY role";
            $stmt = $this->db->query($query);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats['users_by_role'][$row['role']] = (int)$row['count'];
            }

            // 4. Répartition par statut
            $query = "SELECT 
                    status, 
                    COUNT(*) as count 
                  FROM users 
                  GROUP BY status";
            $stmt = $this->db->query($query);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats['users_by_status'][$row['status']] = (int)$row['count'];
            }

            // 5. Derniers utilisateurs inscrits (30 derniers jours)
            $query = "SELECT 
                    id, 
                    username, 
                    email, 
                    role, 
                    status, 
                    created_at
                  FROM users 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  ORDER BY created_at DESC
                  LIMIT 10";
            $stmt = $this->db->query($query);
            $stats['recent_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. Activité des utilisateurs (30 derniers jours)
            $query = "SELECT 
                    DATE(created_at) as date, 
                    COUNT(*) as signups
                    -- ,(SELECT COUNT(*) FROM user_sessions 
                    --  WHERE DATE(created_at) = DATE(users.created_at)) as logins
                  FROM users 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date DESC
                  LIMIT 30";
            $stmt = $this->db->query($query);
            $stats['users_activity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Met à jour un utilisateur existant
     */
    public function updateUser($userId, $isbulk = false)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Vérifier si l'utilisateur existe
            $user = $this->db->query("SELECT * FROM users WHERE id = " . (int)$userId)->fetch();
            if (!$user) {
                throw new Exception('Utilisateur non trouvé', 404);
            }

            // Mise à jour des champs
            $updates = [];
            $params = [':id' => $userId];

            $allowedFields = ['username', 'email', 'first_name', 'last_name', 'role', 'status', 'bio'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            // Mise à jour du mot de passe si fourni
            if (!empty($data['password'])) {
                $updates[] = "password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            if (empty($updates)) {
                throw new Exception('Aucune donnée à mettre à jour', 400);
            }

            $query = "UPDATE users SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            // Journalisation de l'action
            $this->logActivity('update_user', 'Mise à jour d\'un utilisateur par ' . $this->TokenManager->getCurrentUserId(), $data, 'admin_update', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            
            // Récupérer l'utilisateur mis à jour
            $data = $this->getUser($userId);

            if ($isbulk) {
                return;
            }
            $this->jsonResponse([
                'success' => true,
                'message' => 'Utilisateur mis à jour avec succès',
                'data' => $data
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Met à jour le rôle d'un utilisateur
     */
    public function updateUserRole($userId, $role, $isbulk = false)
    {
        try {
            // Vérifier si l'utilisateur existe
            $user = $this->db->query("SELECT * FROM users WHERE id = " . (int)$userId)->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé', 404);
            }

            // Mettre à jour le rôle
            $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute([':role' => $role, ':id' => $userId]);

            // Journalisation de l'action
            $this->logActivity('update_user_role', 'Mise à jour du rôle d\'un utilisateur par ' . $userId . ' (' . $user['username'] . ' - ' . $user['role'] . ')', ['role' => $role], 'admin_update', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            
            // Récupérer l'utilisateur mis à jour
            $data = $this->getUser($userId);

            if ($isbulk) {
                return;
            }
            $this->jsonResponse([
                'success' => true,
                'message' => 'Rôle de l\'utilisateur mis à jour avec succès',
                'data' => $data
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Supprime un utilisateur et l'archive
     * 
     * @param int $userId ID de l'utilisateur à supprimer
     * @return void
     */
    public function deleteUser($userId, $isbulk = false)
    {
        try {
            // Vérifier si l'utilisateur existe
            $user = $this->db->query("SELECT * FROM users WHERE id = " . (int)$userId)->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé', 404);
            }

            // Ne pas permettre la suppression de l'utilisateur actuel
            $currentUserId = $this->TokenManager->getCurrentUserId();
            if ($currentUserId && $currentUserId == $userId) {
                throw new Exception('Vous ne pouvez pas supprimer votre propre compte', 403);
            }

            // Démarrer une transaction
            $this->db->beginTransaction();

            try {
                // 1. Archiver l'utilisateur
                $stmt = $this->db->prepare("
                INSERT INTO users_archive (
                    id, username, email, role, status, bio, 
                    created_at, updated_at, deleted_at, deleted_by, original_data
                ) VALUES (
                    :id, :username, :email, :role, :status, :bio,
                    :created_at, :updated_at, NOW(), :deleted_by, :original_data
                )
            ");

                $stmt->execute([
                    ':id' => $user['id'],
                    ':username' => $user['username'],
                    ':email' => $user['email'],
                    ':role' => $user['role'],
                    ':status' => $user['status'],
                    ':bio' => $user['bio'] ?? null,
                    ':created_at' => $user['created_at'],
                    ':updated_at' => $user['updated_at'] ?? null,
                    ':deleted_by' => $currentUserId,
                    ':original_data' => json_encode($user) // Sauvegarde complète des données
                ]);

                // 2. Supprimer les relations de l'utilisateur
                $tables = [
                    'team_members',
                    'hackathon_participants',
                    'hackathon_qualifications',
                    'challenge_submissions',
                    'notifications',
                    'teams_adhesions',
                    'user_tokens',
                    'validated_flags'
                ];

                foreach ($tables as $table) {
                    $stmt = $this->db->prepare("DELETE FROM $table WHERE user_id = :user_id");
                    $stmt->execute([':user_id' => $userId]);
                }

                // 3. Supprimer l'utilisateur
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);

                // Valider la transaction
                $this->db->commit();

                // Journalisation de l'action
                $logData = array_intersect_key($user, array_flip(['id', 'username', 'email', 'role']));
                $this->logActivity(
                    'delete_user',
                    "Utilisateur supprimé: {$user['username']} (ID: $userId)",
                    $logData,
                    'admin_delete',
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT']
                );

                if ($isbulk) {
                    return;
                }
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Utilisateur supprimé et archivé avec succès'
                ]);
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], (int) $e->getCode() ?: 500);
        }
    }

    /**
     * Effectue une action sur plusieurs utilisateurs
     */
    public function bulkUserAction($input)
    {
        try {
            $this->validateMethod('POST');

            $data = $input;
            $action = $data['action'] ?? '';
            $userIds = $data['user_ids'] ?? [];

            if (empty($userIds)) {
                throw new Exception('Aucun utilisateur sélectionné', 400);
            }

            switch ($action) {
                case 'delete':
                    foreach ($userIds as $userId) {
                        $this->deleteUser($userId, true);
                    }
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Utilisateurs supprimés et archivés avec succès'
                    ]);
                    break;
                case 'activate':
                    foreach ($userIds as $userId) {
                        $this->updateUserStatus($userId, 'active', true);
                    }
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Utilisateurs activés avec succès'
                    ]);
                    break;
                case 'deactivate':
                    foreach ($userIds as $userId) {
                        $this->updateUserStatus($userId, 'inactive', true);
                    }
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Utilisateurs desactivés avec succès'
                    ]);
                    break;
                case 'ban':
                    foreach ($userIds as $userId) {
                        $this->banUser($userId, true);
                    }
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Action non pris en charge pour le moment. Veuillez contacter le support ou tout simplement desactiver le compte'
                    ]);
                    break;
                case 'unlock':
                    foreach ($userIds as $userId) {
                        $this->unlockUserAccount($userId, true);
                    }
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Comptes débloqués avec succès'
                    ]);
                    break;
                case 'suspend':
                    foreach ($userIds as $userId) {
                        $this->suspendUser($userId, $input['duration'] ?? 24, $input['reason'] ?? 'Suspension par un Administrateur', isbulk: true);
                    }
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Comptes suspendus avec succès'
                    ]);
                    break;
                case 'change_role':
                    foreach ($userIds as $userId) {
                        $this->updateUserRole($userId, $data['role'], true);
                    }
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Rôles des utilisateurs modifiés avec succès'
                    ]);
                    break;
                default:
                    throw new Exception('Action non reconnue ' . print_r($action, true), 400);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Action effectuée sur les utilisateurs sélectionnés'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], (int) $e->getCode() ?: 500);
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
            ], (int) $e->getCode() ?: 500);
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

        $stmt->execute();

        // Journalisation de l'action
        $this->logActivity('update_hackathon_status', 'Mise à jour du statut d\'un hackathon par ' . $this->TokenManager->getCurrentUserId(), $hackathonId, 'admin_update', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

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
     * Récupère toutes les soumissions avec filtres, tri et pagination
     */
    public function getAllSubmissions()
    {
        try {
            $this->validateMethod('GET');

            // Récupération des paramètres de requête
            $status = $_GET['status'] ?? null;
            $hackathonId = isset($_GET['hackathon_id']) ? (int)$_GET['hackathon_id'] : null;
            $difficulty = $_GET['difficulty'] ?? null;
            $search = $_GET['search'] ?? null;
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
            $sort = $_GET['sort'] ?? 'submitted_at';
            $order = strtoupper($_GET['order'] ?? 'DESC');
            $order = in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';

            // Validation du champ de tri
            $allowedSorts = [
                'submitted_at',
                'username',
                'challenge_title',
                'difficulty',
                'points',
                'status',
                'execution_time_ms',
                'memory_used_bytes'
            ];
            $sort = in_array($sort, $allowedSorts) ? $sort : 'submitted_at';

            // Construction de la requête de base
            $baseQuery = "FROM challenge_submissions cs
                        LEFT JOIN users u ON cs.user_id = u.id
                        LEFT JOIN challenges c ON cs.challenge_id = c.id
                        LEFT JOIN hackathons h ON c.hackathon_id = h.id
                        LEFT JOIN team_members tm ON u.id = tm.user_id
                        LEFT JOIN teams t ON tm.team_id = t.id
                        WHERE 1=1";

            $params = [];
            $types = '';

            // Filtres
            if ($status) {
                $baseQuery .= " AND cs.status = ?";
                $params[] = $status;
                $types .= 's';
            }

            if ($hackathonId) {
                $baseQuery .= " AND h.id = ?";
                $params[] = $hackathonId;
                $types .= 'i';
            }

            if ($difficulty) {
                $baseQuery .= " AND c.difficulty = ?";
                $params[] = $difficulty;
                $types .= 's';
            }
            if ($search) {
                $searchTerm = "%$search%";
                $baseQuery .= " AND (
                    u.username LIKE ? OR 
                    u.email LIKE ? OR 
                    c.title LIKE ? OR 
                    h.name LIKE ? OR
                    t.name LIKE ?
                )";
                $params = array_merge($params, array_fill(0, 5, $searchTerm));
                $types .= str_repeat('s', 5);
            }

            // Compte total des résultats (pour la pagination)
            $countQuery = "SELECT COUNT(*) as total $baseQuery";
            error_log("Requête de comptage: $countQuery");
            error_log("Params: " . print_r($params, true));

            try {
                $stmt = $this->db->prepare($countQuery);

                // Exécution avec les paramètres
                if (!empty($params)) {
                    $stmt->execute($params);
                } else {
                    $stmt->execute();
                }

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalItems = $row ? (int)$row['total'] : 0;
                error_log("Total items: $totalItems");

                $totalPages = ceil($totalItems / $limit);
                $offset = ($page - 1) * $limit;
            } catch (PDOException $e) {
                error_log("Erreur PDO: " . $e->getMessage());
                throw new Exception("Erreur lors de l'exécution de la requête de comptage: " . $e->getMessage());
            }

            try {
                // Requête des données avec pagination et tri
                $dataQuery = "SELECT 
                            cs.id,
                            cs.user_id,
                            cs.challenge_id,
                            cs.status,
                            cs.total_score,
                            cs.tests_passed,
                            cs.total_tests,
                            cs.execution_time_ms,
                            cs.memory_used_bytes,
                            cs.submitted_at,
                            u.username,
                            u.email,
                            t.name as team_name,
                            c.title as challenge_title,
                            c.difficulty,
                            h.name as hackathon_title,
                            h.id as hackathon_id,
                            (SELECT COUNT(*) FROM challenge_submissions cs2 
                             WHERE cs2.challenge_id = cs.challenge_id AND cs2.user_id = cs.user_id) as submission_count
                            $baseQuery
                            ORDER BY $sort $order
                            LIMIT ? OFFSET ?";

                // Ajout des paramètres de pagination au tableau de paramètres
                $params[] = (int)$limit;
                $params[] = (int)$offset;

                // Préparation et exécution de la requête
                $stmt = $this->db->prepare($dataQuery);
                $stmt->execute($params);
                $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Erreur PDO (requête de données): " . $e->getMessage());
                throw new Exception("Erreur lors de la récupération des données: " . $e->getMessage());
            }

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'items' => $submissions,
                    'pagination' => [
                        'total_items' => (int)$totalItems,
                        'total_pages' => $totalPages,
                        'current_page' => $page,
                        'items_per_page' => $limit
                    ]
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère la liste des hackathons pour les filtres
     */
    public function getHackathons()
    {
        try {
            $this->validateMethod('GET');

            $query = "SELECT id, name, start_date, end_date 
                     FROM hackathons 
                     ORDER BY start_date DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            $hackathons = [];

            while ($row = $result->fetch_assoc()) {
                $hackathons[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date']
                ];
            }

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
     * Récupère les statistiques des soumissions
     */
    public function getSubmissionStats()
    {
        try {
            $this->validateMethod('GET');

            // Total des soumissions
            $query = "SELECT COUNT(*) FROM challenge_submissions";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $total = (int)$stmt->fetchColumn();

            // Points attribués (somme des scores totaux des soumissions complétées)
            $pointsQuery = "SELECT COALESCE(SUM(total_score), 0) FROM challenge_submissions WHERE status = 'completed'";
            $stmt = $this->db->prepare($pointsQuery);
            $stmt->execute();
            $pointsAwarded = (int)$stmt->fetchColumn();

            // Soumissions en attente
            $pendingQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE status = 'pending'";
            $stmt = $this->db->prepare($pendingQuery);
            $stmt->execute();
            $pending = (int)$stmt->fetchColumn();

            // Taux de complétion (plutôt que d'approbation)
            $completedQuery = "SELECT COUNT(*) FROM challenge_submissions WHERE status = 'completed'";
            $stmt = $this->db->prepare($completedQuery);
            $stmt->execute();
            $completed = (int)$stmt->fetchColumn();

            // Calcul du taux de réussite basé sur les tests passés
            $successRate = $total > 0 ? round(($completed / $total) * 100) : 0;

            $stats = [
                'total_submissions' => $total,
                'points_awarded' => $pointsAwarded,
                'pending_submissions' => $pending,
                'success_rate' => $successRate,
                'completed_submissions' => $completed
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

            // Total des défis réalisés avec succès (au moins une soumission complétée)
            $challengesQuery = "SELECT COUNT(DISTINCT cs.challenge_id) 
                              FROM challenge_submissions cs
                              WHERE cs.status = 'completed'";
            $stmt = $this->db->prepare($challengesQuery);
            $stmt->execute();
            $challengesCount = (int)$stmt->fetchColumn();

            // Statistiques d'exécution moyennes
            $executionStatsQuery = "SELECT 
                                    ROUND(AVG(execution_time_ms), 2) as avg_execution_time,
                                    ROUND(AVG(memory_used_bytes) / 1024, 2) as avg_memory_kb,
                                    ROUND(AVG(tests_passed * 100.0 / NULLIF(total_tests, 0)), 2) as avg_test_success_rate
                                  FROM challenge_submissions 
                                  WHERE status = 'completed' AND total_tests > 0";
            $stmt = $this->db->prepare($executionStatsQuery);
            $stmt->execute();
            $executionStats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Répartition par statut
            $statusDistributionQuery = "SELECT 
                                        status, 
                                        COUNT(*) as count,
                                        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM challenge_submissions), 2) as percentage
                                      FROM challenge_submissions 
                                      GROUP BY status";
            $stmt = $this->db->prepare($statusDistributionQuery);
            $stmt->execute();
            $statusDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stats = [
                'teams_count' => (int)$teamsCount,
                'members_count' => (int)$membersCount,
                'participations_count' => (int)$participationsCount,
                'challenges_count' => $challengesCount,
                'execution_stats' => $executionStats ?: [
                    'avg_execution_time' => 0,
                    'avg_memory_kb' => 0,
                    'avg_test_success_rate' => 0
                ],
                'status_distribution' => $statusDistribution ?: []
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

            $userId = $this->TokenManager->getCurrentUserId();
            if (!$this->isAdmin($userId)) {
                throw new Exception('Non autorisé');
            }

            $this->db->beginTransaction();
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
                        code_name, title, type, category, description, hint, difficulty,
                        url_path, resource_link, instructions, points, is_active,
                        is_dynamic, created_by, hackathon_id, phase_id, algo_config
                    ) VALUES (
                        :code_name, :title, :type, :category, :description, :hint, :difficulty,
                        :url_path, :resource_link, :instructions, :points, :is_active,
                        :is_dynamic, :created_by, :hackathon_id, :phase_id, :algo_config
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':code_name' => $input['code_name'] ?? null,
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
                ':instructions' => $input['instructions'] ?? null,
                ':points' => $input['points'],
                ':is_active' => $input['is_active'] ?? 1,
                ':is_dynamic' => $input['is_dynamic'] ?? 0,
                ':created_by' => $input['created_by'] ?? null,
                ':hackathon_id' => $input['hackathon_id'],
                ':phase_id' => $input['phase_id'] ?? null,
                ':algo_config' => isset($input['algo_config']) && $input['algo_config'] !== ''
                    ? json_encode($input['algo_config'])
                    : null
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

            $this->db->commit();

            // Journalisation de l'action
            logSecurity($userId, 'create_challenge', [
                'challenge_id' => $challengeId,
                'user_id' => $userId,
                'action' => 'L\'admin ' . $userId . ' a créé un challenge : ' . $input['title'],
                'details' => "Création du challenge #$challengeId"
            ]);

            $this->logActivity('create_challenge', 'Création d\'un challenge par ' . $this->TokenManager->getCurrentUserId(), $input, 'admin_create', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Challenge créé avec succès',
                'data' => ['id' => $challengeId]
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();
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

            // Journalisation de l'action
            logSecurity($userId, 'update_challenge', [
                'challenge_id' => $id,
                'user_id' => $userId,
                'action' => 'update_challenge',
                'details' => "Mise à jour du challenge #$id"
            ]);

            $this->logActivity('update_challenge', 'Mise à jour d\'un challenge par ' . $this->TokenManager->getCurrentUserId(), $input, 'admin_update', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

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
    public function deleteChallenge($id, $userId = null)
    {
        try {
            $this->validateMethod('DELETE');

            $user_id = $userId ?? $this->TokenManager->getCurrentUserId();
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

                // Journalisation de l'action
                $userId = $userId ?? $this->TokenManager->getCurrentUserId();
                logSecurity($userId, 'delete_challenge', [
                    'challenge_id' => $id,
                    'user_id' => $userId,
                    'action' => 'delete_challenge',
                    'details' => "Suppression du challenge #$id"
                ]);

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
                $userId = $userId ?? $this->TokenManager->getCurrentUserId();
                // Journalisation de l'action
                logSecurity($userId, 'delete_challenge', [
                    'challenge_id' => $id,
                    'user_id' => $userId,
                    'action' => 'delete_challenge',
                    'details' => "Suppression du challenge #$id"
                ]);

                $this->logActivity('delete_challenge', 'Suppression d\'un challenge par ' . $this->TokenManager->getCurrentUserId(), $id, 'admin_delete', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

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
                ':is_dynamic' => $flag['is_dynamic'] ? 1 : 0
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
            if (empty($flag['value'])) {
                throw new Exception('Le contenu du flag ne peut pas être vide');
            }

            if (strlen($flag['value']) > 255) {
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
}

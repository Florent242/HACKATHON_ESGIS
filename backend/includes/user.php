<?php
/**
 * Fichier de fonctions pour récupérer les données au format JSON
 * Ces fonctions récupèrent uniquement des données réelles de la base de données
 */

/**
 * Récupère les données du leaderboard et les renvoie au format JSON
 * @return string Données du leaderboard au format JSON
 */
function getLeaderboardJSON() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();
    
    try {
        // Requête pour récupérer les utilisateurs classés par points
        $stmt = $db->prepare("
            SELECT 
                u.id, 
                u.username, 
                u.fullname, 
                u.school, 
                u.profile_picture,
                u.bio,
                u.email,
                (
                    SELECT COUNT(*) 
                    FROM team_members tm 
                    WHERE tm.user_id = u.id
                ) as teams_count,
                (
                    SELECT COUNT(*) 
                    FROM activity_logs al 
                    WHERE al.user_id = u.id AND al.action = 'login_success'
                ) as login_count
            FROM users u
            ORDER BY login_count DESC, teams_count DESC
            LIMIT 50
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ajouter le rang à chaque utilisateur
        $rank = 1;
        foreach ($users as &$user) {
            $user['rank'] = $rank++;
            
            // Récupérer les activités récentes de l'utilisateur
            $activityStmt = $conn->prepare("
                SELECT action, description, level, created_at
                FROM activity_logs
                WHERE user_id = :userId
                ORDER BY created_at DESC
                LIMIT 5
            ");
            $activityStmt->bindParam(':userId', $user['id'], PDO::PARAM_INT);
            $activityStmt->execute();
            $user['recent_activities'] = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return json_encode([
            "status" => "success",
            "data" => $users
        ]);
    } catch(PDOException $e) {
        return json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}

/**
 * Récupère les données du profil d'un utilisateur et les renvoie au format JSON
 * @param int $userId ID de l'utilisateur
 * @return string Données du profil au format JSON
 */
function getProfileJSON($userId) {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();
    
    try {
        // Récupérer les informations de base de l'utilisateur
        $stmt = $db->prepare("
            SELECT 
                id, 
                username, 
                fullname, 
                email, 
                school, 
                bio, 
                profile_picture, 
                github_url, 
                linkedin_url, 
                role,
                created_at,
                updated_at,
                status
            FROM users 
            WHERE id = :userId
        ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return json_encode(["status" => "error", "message" => "User not found"]);
        }
        
        // Récupérer les équipes de l'utilisateur
        $teamsStmt = $conn->prepare("
            SELECT 
                t.id, 
                t.name, 
                t.created_at,
                h.name as hackathon_name,
                h.id as hackathon_id,
                (t.leader_id = :userId) as is_leader
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            JOIN hackathons h ON t.hackathon_id = h.id
            WHERE tm.user_id = :userId
        ");
        $teamsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $teamsStmt->execute();
        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les hackathons auxquels l'utilisateur participe
        $hackathonsStmt = $conn->prepare("
            SELECT 
                h.id, 
                h.name, 
                h.description, 
                h.start_date, 
                h.end_date, 
                h.location,
                hp.participation_status
            FROM hackathons h
            JOIN hackathon_participants hp ON h.id = hp.hackathon_id
            WHERE hp.user_id = :userId
        ");
        $hackathonsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $hackathonsStmt->execute();
        $hackathons = $hackathonsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les activités récentes de l'utilisateur
        $activityStmt = $conn->prepare("
            SELECT 
                id,
                action, 
                description, 
                data, 
                level, 
                created_at
            FROM activity_logs
            WHERE user_id = :userId
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $activityStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $activityStmt->execute();
        $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Traiter les données JSON dans la colonne 'data'
        foreach ($activities as &$activity) {
            if (!empty($activity['data'])) {
                $activity['data'] = json_decode($activity['data'], true);
            }
        }
        
        // Récupérer les notifications de l'utilisateur
        $notificationsStmt = $conn->prepare("
            SELECT 
                id, 
                message, 
                read_status, 
                created_at
            FROM notifications
            WHERE user_id = :userId
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $notificationsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $notificationsStmt->execute();
        $notifications = $notificationsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculer les statistiques de l'utilisateur
        $statsStmt = $conn->prepare("
            SELECT 
                COUNT(DISTINCT hp.hackathon_id) as hackathons_count,
                COUNT(DISTINCT tm.team_id) as teams_count,
                COUNT(DISTINCT al.id) as activities_count
            FROM users u
            LEFT JOIN hackathon_participants hp ON u.id = hp.user_id
            LEFT JOIN team_members tm ON u.id = tm.user_id
            LEFT JOIN activity_logs al ON u.id = al.user_id
            WHERE u.id = :userId
        ");
        $statsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $statsStmt->execute();
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Ajouter des statistiques supplémentaires
        $stats['login_count'] = $conn->query("SELECT COUNT(*) FROM activity_logs WHERE user_id = $userId AND action = 'login_success'")->fetchColumn();
        $stats['last_login'] = $conn->query("SELECT created_at FROM activity_logs WHERE user_id = $userId AND action = 'login_success' ORDER BY created_at DESC LIMIT 1")->fetchColumn();
        
        return json_encode([
            "status" => "success",
            "data" => [
                "user" => $user,
                "teams" => $teams,
                "hackathons" => $hackathons,
                "activities" => $activities,
                "notifications" => $notifications,
                "stats" => $stats
            ]
        ]);
    } catch(PDOException $e) {
        return json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}

/**
 * Récupère les données des défis et les renvoie au format JSON
 * @return string Données des défis au format JSON
 */
function getChallengesJSON() {
    require_once __DIR__ . '/../backend/models/Database.php';
    $database = \Auth\Model\Database::getInstance();
    $db = $database->getConnection();
    
    try {
        // Récupérer tous les défis
        $stmt = $db->prepare("
            SELECT 
                c.id, 
                c.title, 
                c.description, 
                c.difficulty, 
                c.hackathon_id,
                c.created_at,
                c.updated_at,
                h.name as hackathon_name
            FROM challenges c
            JOIN hackathons h ON c.hackathon_id = h.id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
        $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Pour chaque défi, récupérer les technologies associées
        foreach ($challenges as &$challenge) {
            $techStmt = $conn->prepare("
                SELECT 
                    t.id, 
                    t.name
                FROM technologies t
                JOIN challenge_technologies ct ON t.id = ct.technology_id
                WHERE ct.challenge_id = :challengeId
            ");
            $techStmt->bindParam(':challengeId', $challenge['id'], PDO::PARAM_INT);
            $techStmt->execute();
            $challenge['technologies'] = $techStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Récupérer les meilleurs utilisateurs
        $topUsersStmt = $conn->prepare("
            SELECT 
                u.id, 
                u.username, 
                u.fullname,
                u.profile_picture,
                COUNT(DISTINCT al.id) as activity_count
            FROM users u
            LEFT JOIN activity_logs al ON u.id = al.user_id
            GROUP BY u.id
            ORDER BY activity_count DESC
            LIMIT 10
        ");
        $topUsersStmt->execute();
        $topUsers = $topUsersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer toutes les technologies disponibles
        $technologiesStmt = $conn->prepare("
            SELECT id, name
            FROM technologies
            ORDER BY name
        ");
        $technologiesStmt->execute();
        $technologies = $technologiesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        return json_encode([
            "status" => "success",
            "data" => [
                "challenges" => $challenges,
                "top_users" => $topUsers,
                "technologies" => $technologies,
                "filters" => [
                    "difficulties" => ["easy", "medium", "hard"],
                    "technologies" => array_column($technologies, 'name')
                ]
            ]
        ]);
    } catch(PDOException $e) {
        return json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}

/**
 * Fonction utilitaire pour convertir un objet JSON en tableau associatif
 * Utile pour traiter les données JSON stockées dans la base de données
 * @param string $json Chaîne JSON à convertir
 * @return array Tableau associatif
 */
function jsonToRecord($json) {
    if (empty($json)) {
        return [];
    }
    
    try {
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Fonction utilitaire pour renvoyer des données au format JSON avec les en-têtes appropriés
 * @param string $jsonData Données au format JSON
 */
function outputJSON($jsonData) {
    // Définir les en-têtes pour le JSON et CORS
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    // Afficher les données JSON
    echo $jsonData;
    exit;
}

/**
 * Fonction pour récupérer toutes les données nécessaires pour les trois pages
 * et les renvoyer dans un seul objet JSON
 * @param int $userId ID de l'utilisateur (optionnel)
 * @return string Toutes les données au format JSON
 */
function getAllDataJSON($userId = null) {
    try {
        $data = [
            "status" => "success",
            "timestamp" => date('Y-m-d H:i:s'),
            "data" => []
        ];
        
        // Récupérer les données du leaderboard
        $leaderboardData = json_decode(getLeaderboardJSON(), true);
        if (isset($leaderboardData['status']) && $leaderboardData['status'] === 'success') {
            $data['data']['leaderboard'] = $leaderboardData['data'];
        } else {
            $data['data']['leaderboard'] = ["error" => "Failed to retrieve leaderboard data"];
        }
        
        // Récupérer les données des défis
        $challengesData = json_decode(getChallengesJSON(), true);
        if (isset($challengesData['status']) && $challengesData['status'] === 'success') {
            $data['data']['challenges'] = $challengesData['data'];
        } else {
            $data['data']['challenges'] = ["error" => "Failed to retrieve challenges data"];
        }
        
        // Récupérer les données du profil si un ID utilisateur est fourni
        if ($userId) {
            $profileData = json_decode(getProfileJSON($userId), true);
            if (isset($profileData['status']) && $profileData['status'] === 'success') {
                $data['data']['profile'] = $profileData['data'];
            } else {
                $data['data']['profile'] = ["error" => "Failed to retrieve profile data"];
            }
        }
        
        return json_encode($data);
    } catch (Exception $e) {
        return json_encode([
            "status" => "error",
            "message" => "Error retrieving data: " . $e->getMessage()
        ]);
    }
}

/**
 * Exemple d'utilisation:
 * 
 * // Pour récupérer et afficher le leaderboard
 * $leaderboardData = getLeaderboardJSON();
 * outputJSON($leaderboardData);
 * 
 * // Pour récupérer et afficher le profil d'un utilisateur
 * $profileData = getProfileJSON(1);
 * outputJSON($profileData);
 * 
 * // Pour récupérer et afficher les défis
 * $challengesData = getChallengesJSON();
 * outputJSON($challengesData);
 * 
 * // Pour récupérer toutes les données en une seule fois
 * $allData = getAllDataJSON(1); // Avec ID utilisateur
 * outputJSON($allData);
 */
?>
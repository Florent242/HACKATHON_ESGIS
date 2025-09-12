<?php
if (!defined('FUNCTIONS_INCLUDED')) {
    define('FUNCTIONS_INCLUDED', true);
}

function sendResponse($statusCode, $data = [], $headers = [])
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    foreach ($headers as $key => $value) {
        header("$key: $value");
    }
    echo json_encode($data);
    exit;
}

function recalculateCTFScores(PDO $db, int $hackathonId, ?int $phaseId = null): void
{
    try {
        // Récupérer les équipes concernées
        $teamsStmt = $db->prepare("
            SELECT id, team_id 
            FROM hackathon_teams 
            WHERE hackathon_id = :hackathon_id
        ");
        $teamsStmt->execute([':hackathon_id' => $hackathonId]);
        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$teams) {
            echo "=== /!\ Operation de mise a jour des CTF de la table score echoue pour le hackathon $hackathonId ! === \n";
            return;
        }

        $phaseId ??= 1;

        foreach ($teams as $team) {
            $teamId = $team['team_id'];

            // Total des points pour cette équipe via les flags validés
            $scoreStmt = $db->prepare("
                SELECT SUM(vf.points_gained) AS total_points
                FROM validated_flags vf
                JOIN team_members tm ON tm.user_id = vf.user_id
                WHERE tm.team_id = :team_id AND vf.is_valid = 1
            ");
            $scoreStmt->execute([':team_id' => $teamId]);
            $result = $scoreStmt->fetch(PDO::FETCH_ASSOC);
            $totalPoints = (int)($result['total_points'] ?? 0);

            // Mise à jour ou insertion dans la table scores
            $updateStmt = $db->prepare("
                INSERT INTO scores (team_id, hackathon_id, phase_id, total_points)
                VALUES (:team_id, :hackathon_id, :phase_id, :total_points)
                ON DUPLICATE KEY UPDATE total_points = :update_points, last_update = NOW()
            ");
            $updateStmt->execute([
                ':team_id' => $teamId,
                ':hackathon_id' => $hackathonId,
                ':phase_id' => $phaseId,
                ':total_points' => $totalPoints,
                ':update_points' => $totalPoints
            ]);

            echo "→ Équipe $teamId : points recalculés = $totalPoints\n";

            if ($updateStmt) {
                echo "=== Operation de mise a jour de la table score reussi avec success pour l'equipe $teamId ! === \n";
            } else {
                echo "=== /!\ Operation de mise a jour de la table score echoue pour l'equipe $teamId ! === \n";
            }
        }
    } catch (PDOException $e) {
        error_log("Erreur CTF recalculation : " . $e->getMessage());
    }
}

function recalculateChallengeScores(PDO $db, int $hackathonId, ?int $phaseId = null): void
{
    try {
        // Récupérer les équipes du hackathon
        $teamsStmt = $db->prepare("
            SELECT id, team_id 
            FROM hackathon_teams 
            WHERE hackathon_id = :hackathon_id
        ");
        $teamsStmt->execute([':hackathon_id' => $hackathonId]);
        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$teams) {
            echo "=== /!\ Operation de mise a jour des challenges de la table score echoue pour le hackathon $hackathonId ! === \n";
            return;
        }

        $phaseId ??= 2;

        foreach ($teams as $team) {
            $teamId = $team['team_id'];

            // Total des points cumulés depuis les soumissions
            $scoreStmt = $db->prepare("
                SELECT SUM(cs.total_score) AS total_points
                FROM challenge_submissions cs
                JOIN team_members tm ON tm.user_id = cs.user_id
                WHERE tm.team_id = :team_id AND cs.status = 'completed'
            ");
            $scoreStmt->execute([':team_id' => $teamId]);
            $result = $scoreStmt->fetch(PDO::FETCH_ASSOC);
            $totalPoints = (int)($result['total_points'] ?? 0);

            // Mise à jour dans scores
            $updateStmt = $db->prepare("
                INSERT INTO scores (team_id, hackathon_id, phase_id, total_points)
                VALUES (:team_id, :hackathon_id, :phase_id, :total_points)
                ON DUPLICATE KEY UPDATE total_points = :update_points, last_update = NOW()
            ");
            $updateStmt->execute([
                ':team_id' => $teamId,
                ':hackathon_id' => $hackathonId,
                ':phase_id' => $phaseId,
                ':total_points' => $totalPoints,
                ':update_points' => $totalPoints
            ]);

            if ($updateStmt) {
                echo "=== Operation de mise a jour des challenges de la table score reussi avec succes pour l'equipe $teamId ! === \n";
            } else {
                echo "=== /!\ Operation de mise a jour des challenges de la table score echoue pour l'equipe $teamId ! === \n";
            }
        }
    } catch (PDOException $e) {
        error_log("Erreur CHALLENGE recalculation : " . $e->getMessage());
    }
}

function deactivateOrphanScores(PDO $db, int $hackathonId, ?int $phaseId = null): void
{
    try {
        $sql = "
            UPDATE scores s
            LEFT JOIN hackathon_teams ht
              ON s.team_id = ht.team_id AND s.hackathon_id = ht.hackathon_id
            SET s.is_active = 0
            WHERE ht.id IS NULL
              AND s.hackathon_id = :hackathon_id
              AND (:phase_id IS NULL OR s.phase_id = :score_phase_id)
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':hackathon_id' => $hackathonId,
            ':phase_id' => $phaseId,
            ':score_phase_id' => $phaseId
        ]);

        echo "[INFO] Désactivation des scores orphelins terminée pour le hackathon $hackathonId\n";
    } catch (PDOException $e) {
        error_log("Erreur lors de la désactivation des scores orphelins : " . $e->getMessage());
    }
}

function updateFlagSolves(PDO $db): void
{
    try {
        // Récupérer le nombre de validations valides par flag
        $stmt = $db->prepare("
            SELECT flag_id, COUNT(*) AS solve_count
            FROM validated_flags
            WHERE is_valid = 1
            GROUP BY flag_id
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Réinitialiser tous les counts à 0 au préalable
        $resetStmt = $db->prepare("UPDATE flags SET solves = 0");
        $resetStmt->execute();

        // Mettre à jour chaque flag avec le nombre de solves
        $updateStmt = $db->prepare("UPDATE flags SET solves = :solve_count WHERE id = :flag_id");

        foreach ($results as $row) {
            $updateStmt->execute([
                ':solve_count' => $row['solve_count'],
                ':flag_id' => $row['flag_id']
            ]);
            echo "→ Flag #{$row['flag_id']} mis à jour avec {$row['solve_count']} solves\n";
        }

        echo "[OK] Tous les flags ont été mis à jour avec les solves\n";
    } catch (PDOException $e) {
        error_log("Erreur updateFlagSolves : " . $e->getMessage());
        echo "[ERROR] Erreur updateFlagSolves : " . $e->getMessage();
    }
}

function recalculateAllHackathonScores(PDO $db): void
{
    // Hackathon 1 = CTF
    recalculateCTFScores($db, 1, 1); // tu peux passer null si phase pas gérée

    // Désactivation des scores orphelins
    deactivateOrphanScores($db, 1, 1);

    updateFlagSolves($db);

    // Hackathon 2 = Challenge Dev
    recalculateChallengeScores($db, 2, 2);

    // Désactivation des scores orphelins
    deactivateOrphanScores($db, 2, 2);
}

// Fonction pour valider une adresse email
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction pour valider une URL
function validateUrl($url)
{
    return filter_var($url, FILTER_VALIDATE_URL);
}

// Fonction pour nettoyer une chaîne de caractères
function sanitizeString($string)
{
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

// Fonction pour générer un slug
function generateSlug($string)
{
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Fonction pour formater une date
function formatDate($date, $format = 'd/m/Y H:i')
{
    return date($format, strtotime($date));
}

// Fonction pour générer un mot de passe aléatoire
function generateRandomPassword($length = 12)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Fonction pour valider une date
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Fonction pour calculer le temps écoulé
function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Il y a quelques secondes';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return 'Il y a ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return 'Il y a ' . $hours . ' heure' . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return 'Il y a ' . $days . ' jour' . ($days > 1 ? 's' : '');
    } else {
        return formatDate($datetime);
    }
}

// Fonction pour valider un numéro de téléphone
function validatePhone($phone)
{
    return preg_match('/^[+]?[0-9]{8,15}$/', $phone);
}

// Fonction pour formater un nombre
function formatNumber($number, $decimals = 0)
{
    return number_format($number, $decimals, ',', ' ');
}

// Fonction pour tronquer un texte
function truncateText($text, $length = 100, $ending = '...')
{
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length - strlen($ending)) . $ending;
}

// Fonction pour générer un identifiant unique
function generateUniqueId($prefix = '')
{
    return uniqid($prefix, true);
}

// Fonction pour valider un fichier
function validateFile($file, $allowedTypes = ['image/jpeg', 'image/png'], $maxSize = 5242880)
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    if ($file['size'] > $maxSize) {
        return false;
    }

    return true;
}

// Fonction pour uploader un fichier
function uploadFile($file, $destination, $newName = null)
{
    if (!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }

    $filename = $newName ?? generateUniqueId() . '_' . basename($file['name']);
    $filepath = $destination . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }

    return false;
}

// Fonction pour supprimer un fichier
function deleteFile($filepath)
{
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// Fonction pour envoyer un email
function sendEmail($to, $subject, $message, $headers = [])
{
    $defaultHeaders = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . APP_NAME . ' <' . $_ENV['EMAIL_FROM'] ?? 'morelvlto93@gmail.com' . '>'
    ];

    $headers = array_merge($defaultHeaders, $headers);
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

// Fonction pour afficher un message flash
function setFlashMessage($type, $message, $details = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['notification'] = [
        'message' => $message,
        'details' => $details,
        'type' => $type
    ];
}

// Fonction pour récupérer et effacer le message flash
function getFlashMessage()
{
    if (!isset($_SESSION) || !is_array($_SESSION)) {
        session_start();
    }
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']);
        return $notification;
    }
    return null;
}

// Fonction pour enregistrer les activités
function logActivity($action, $description, $data = [], $userId = null, $level = 'info')
{
    // Vérifier si la table existe
    global $db;

    // Si aucune connexion à la base de données n'est disponible, essayer d'en créer une
    if (!isset($db)) {
        try {
            require_once __DIR__ . '/../models/Database.php';
            $database = \Auth\Model\Database::getInstance();
            $db = $database->getConnection();
        } catch (Exception $e) {
            error_log("Erreur de connexion à la base de données pour logActivity: " . $e->getMessage());
            return false;
        }
    }

    // Vérifier si l'utilisateur est connecté

    // Données utilisateur
    $userId = isset($_SESSION['user']) && isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : $userId ?? $data['identifier'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    // Données sérialisées
    $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

    try {
        // Vérifier si la table activity_logs existe
        $stmt = $db->prepare("SHOW TABLES LIKE 'activity_logs'");
        $stmt->execute();
        $tableExists = $stmt->rowCount() > 0;

        // Si la table n'existe pas, on la crée
        if (!$tableExists) {
            $createTable = "CREATE TABLE activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                action VARCHAR(255) NOT NULL,
                description TEXT,
                data TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                level VARCHAR(20) DEFAULT 'info',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->exec($createTable);
        }

        // Insérer le log
        $query = "INSERT INTO activity_logs (user_id, action, description, data, ip_address, user_agent, level)
                  VALUES (:user_id, :action, :description, :data, :ip_address, :user_agent, :level)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':data', $dataJson);
        $stmt->bindParam(':ip_address', $ipAddress);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->bindParam(':level', $level);

        $result = $stmt->execute();

        // Également, enregistrer dans le fichier de log
        $logMessage = date('Y-m-d H:i:s') . " [$level] - $action - $description - " .
            "User: $userId - IP: $ipAddress - Data: $dataJson";
        error_log($logMessage);

        return $result;
    } catch (Exception $e) {
        error_log("Erreur lors de l'enregistrement de l'activité: " . $e->getMessage());
        return false;
    }
}

function logSecurity($action, $description, $data = [], $userId = null, $level = 'info')
{
    global $db;
    
    try {
        // Récupérer l'adresse IP du client
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Récupérer le user-agent du navigateur
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Convertir les données en JSON pour le stockage
        $dataJson = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        
        // Préparer et exécuter la requête d'insertion
        $query = "INSERT INTO security_logs 
                 (user_id, event_type, ip_address, user_agent, details) 
                 VALUES (:user_id, :event_type, :ip_address, :user_agent, :details)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':event_type', $action);
        $stmt->bindParam(':ip_address', $ipAddress);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->bindParam(':details', $description);

        $result = $stmt->execute();

        // Également, enregistrer dans le fichier de log système
        $logMessage = date('Y-m-d H:i:s') . " [SECURITY][$level] - $action - $description - " .
            "User: " . ($userId ?? 'guest') . " - IP: $ipAddress";
        if (!empty($data)) {
            $logMessage .= " - Data: " . json_encode($data);
        }
        error_log($logMessage);

        return $result;
    } catch (Exception $e) {
        error_log("Erreur lors de l'enregistrement du log de sécurité: " . $e->getMessage());
        return false;
    }
}

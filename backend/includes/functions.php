<?php
if (!defined('FUNCTIONS_INCLUDED')) {
    define('FUNCTIONS_INCLUDED', true);
}

function sendResponse($statusCode, $data = [], $headers = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    foreach ($headers as $key => $value) {
        header("$key: $value");
    }
    echo json_encode($data);
    exit;
}
 
// Fonction pour valider une adresse email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction pour valider une URL
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

// Fonction pour nettoyer une chaîne de caractères
function sanitizeString($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

// Fonction pour générer un slug
function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Fonction pour formater une date
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

// Fonction pour générer un mot de passe aléatoire
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Fonction pour valider une date
function validateDate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Fonction pour calculer le temps écoulé
function timeAgo($datetime) {
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
function validatePhone($phone) {
    return preg_match('/^[+]?[0-9]{8,15}$/', $phone);
}

// Fonction pour formater un nombre
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, ',', ' ');
}

// Fonction pour tronquer un texte
function truncateText($text, $length = 100, $ending = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length - strlen($ending)) . $ending;
}

// Fonction pour générer un identifiant unique
function generateUniqueId($prefix = '') {
    return uniqid($prefix, true);
}

// Fonction pour valider un fichier
function validateFile($file, $allowedTypes = ['image/jpeg', 'image/png'], $maxSize = 5242880) {
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
function uploadFile($file, $destination, $newName = null) {
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
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// Fonction pour envoyer un email
function sendEmail($to, $subject, $message, $headers = []) {
    $defaultHeaders = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . APP_NAME . ' <noreply@example.com>'
    ];

    $headers = array_merge($defaultHeaders, $headers);
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

// Fonction pour générer un token JWT
function generateJwtToken($data, $expiration = 3600) {
    $header = base64_encode(json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256'
    ]));

    $payload = base64_encode(json_encode([
        'data' => $data,
        'exp' => time() + $expiration
    ]));

    $signature = hash_hmac('sha256', "$header.$payload", 'your-secret-key');

    return "$header.$payload.$signature";
}

// Fonction pour vérifier un token JWT
function verifyJwtToken($token) {
    list($header, $payload, $signature) = explode('.', $token);

    $validSignature = hash_hmac('sha256', "$header.$payload", 'your-secret-key');

    if ($signature !== $validSignature) {
        return false;
    }

    $payload = json_decode(base64_decode($payload), true);

    if ($payload['exp'] < time()) {
        return false;
    }

    return $payload['data'];
}

// Fonction pour afficher un message flash
function setFlashMessage($type, $message,$details = null) {
    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['notification'] = [
        'message' => $message,
        'details' => $details,
        'type' => $type
    ];
}

// Fonction pour récupérer et effacer le message flash
function getFlashMessage() {
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
function logActivity($action, $description, $data = [], $userId = null, $level = 'info') {
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

function logSecurity(int $userId, string $eventType, array $details = [])
{
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
    
    try {
        $stmt = $db->prepare(
            "INSERT INTO security_logs 
        (user_id, event_type, ip_address, user_agent, details, created_at) 
        VALUES (:user_id, :event_type, :ip, :ua, :details, NOW())"
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':event_type' => $eventType,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            ':details' => json_encode($details)
        ]);
    } catch (Exception $e) {
        error_log('Failed to log security event: ' . $e->getMessage());
    }
}


/**
 * Valide si une chaîne est un JSON valide
 */
function isValidJSON($string) {
    if (empty($string)) {
        return false;
    }
    
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Valide les champs requis dans un tableau
 */
function validateRequiredFields($data, $requiredFields) {
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        throw new Exception('Champs manquants: ' . implode(', ', $missingFields));
    }
    
    return true;
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 */

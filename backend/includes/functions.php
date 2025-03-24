<?php

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
    if (!isset($_SESSION) || !is_array($_SESSION)) {
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
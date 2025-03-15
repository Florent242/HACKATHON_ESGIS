<?php

// Fonction pour nettoyer les entrées utilisateur
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour générer une réponse JSON
/*
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}*/

// Fonction pour vérifier si l'utilisateur est connecté
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Fonction pour obtenir l'utilisateur connecté
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    require_once MODELS_PATH . '/User.php';
    require_once MODELS_PATH . '/Database.php';
    
    $database = Database::getInstance();
    $db = $database->getConnection();
    $userModel = new User($db);
    return $userModel->find($_SESSION['user_id']);
}

// Fonction pour vérifier le rôle de l'utilisateur
function hasRole($role) {
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    return isset($user['role']) && $user['role'] === $role;
}

// Fonction pour générer un token CSRF
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier le token CSRF
function verifyCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Fonction pour rediriger
function redirect($path) {
    if (php_sapi_name() === 'cli') {
        return; // Ne pas rediriger en mode CLI
    }
    header("Location: " . APP_URL . $path);
    exit;
}

// Fonction pour afficher un message flash
function setFlashMessage($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Fonction pour récupérer et effacer le message flash
function getFlashMessage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Fonction pour valider une date
function validateDate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Fonction pour formater une date
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

// Fonction pour générer un slug
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text;
}

// Fonction pour uploader un fichier
function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']) {
    try {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Paramètres invalides.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('Aucun fichier envoyé.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Fichier trop volumineux.');
            default:
                throw new Exception('Erreur inconnue.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $fileType = $finfo->file($file['tmp_name']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('Extension de fichier non autorisée.');
        }

        $fileName = generateSlug(pathinfo($file['name'], PATHINFO_FILENAME));
        $fileName = $fileName . '_' . uniqid() . '.' . $extension;
        $filePath = $destination . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Échec du déplacement du fichier.');
        }

        return $fileName;
    } catch (Exception $e) {
        throw new Exception('Erreur lors de l\'upload : ' . $e->getMessage());
    }
}
<?php

// Définition des constantes de l'application
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('MODELS_PATH', BASE_PATH . '/backend/models');
define('CONTROLLERS_PATH', BASE_PATH . '/backend/controllers');
define('VIEWS_PATH', BASE_PATH . '/frontend');

// Configuration de l'application
define('APP_NAME', 'Plateforme de Hackathon');
define('APP_URL', 'http://localhost/HACKATHON_ESGIS/public');

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'hackathon_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration des sessions
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Fonction d'autoload des classes
spl_autoload_register(function ($class) {
    // Conversion du namespace en chemin de fichier
    $prefix = 'Auth\\';
    $base_dir = BASE_PATH . '/backend/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    error_log("Tentative de chargement de la classe: {$class}");
    error_log("Chemin du fichier: {$file}");

    if (file_exists($file)) {
        require $file;
    }
});

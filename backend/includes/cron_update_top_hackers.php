<?php
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
try {
    $db = \Auth\Model\Database::getInstance()->getConnection();
    updateTopHackers($db);
} catch (Exception $e) {
    // Gérer l'erreur
    error_log("Erreur lors de la mise à jour des meilleurs hackers : " . $e->getMessage());
    echo "Erreur lors de la mise à jour des meilleurs hackers : " . $e->getMessage();
}

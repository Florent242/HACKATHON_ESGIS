<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../backend/models/Database.php';

use Auth\Model\Database;

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    echo "Liste des utilisateurs:\n";
    echo "--------------------\n";
    
    $query = "SELECT id, nom, email, role, date_inscription FROM users";
    $stmt = $db->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "\nID: {$user['id']}\n";
        echo "Nom: {$user['nom']}\n";
        echo "Email: {$user['email']}\n";
        echo "Rôle: {$user['role']}\n";
        echo "Date d'inscription: {$user['date_inscription']}\n";
        echo "--------------------\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

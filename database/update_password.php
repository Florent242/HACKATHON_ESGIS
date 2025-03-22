<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../backend/models/Database.php';

use Auth\Model\Database;

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    // Hasher le mot de passe avec PASSWORD_DEFAULT (comme spécifié dans les MEMORIES)
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Mettre à jour le mot de passe de l'administrateur
    $sql = "UPDATE users SET mot_de_passe = ? WHERE email = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$hashedPassword, 'admin@test.com']);
    
    if ($result) {
        echo "✅ Mot de passe mis à jour avec succès pour admin@test.com\n";
        echo "Nouveau hash: {$hashedPassword}\n";
    } else {
        echo "❌ Erreur lors de la mise à jour du mot de passe\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

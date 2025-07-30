<?php
require_once __DIR__ . '/backend/includes/config.php';

use Auth\Model\Database;

if (!class_exists('Database')) {
    require_once __DIR__ . '/backend/models/Database.php';
}

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Vérification des défis algorithmiques dans la base\n";
    echo "==============================================\n\n";
    
    // Chercher les défis de type 'dev' avec catégorie 'algo'
    $stmt = $db->prepare("SELECT id, title, type, category, difficulty FROM challenges WHERE type = 'dev' AND category = 'algo' LIMIT 10");
    $stmt->execute();
    $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($challenges)) {
        echo "Aucun défi algorithmique trouvé (type=dev, category=algo)\n\n";
        
        // Chercher tous les défis pour voir la structure
        echo "Tous les défis dans la base :\n";
        $stmt = $db->prepare("SELECT id, title, type, category, difficulty FROM challenges LIMIT 10");
        $stmt->execute();
        $allChallenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($allChallenges as $challenge) {
            echo "ID: {$challenge['id']}, Title: {$challenge['title']}, Type: {$challenge['type']}, Category: {$challenge['category']}\n";
        }
    } else {
        echo "Défis algorithmiques trouvés :\n";
        foreach ($challenges as $challenge) {
            echo "ID: {$challenge['id']}, Title: {$challenge['title']}, Type: {$challenge['type']}, Category: {$challenge['category']}, Difficulté: {$challenge['difficulty']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>

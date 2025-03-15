<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../backend/models/Database.php';

use Auth\Model\Database;

try {
    // Obtenir la connexion à la base de données
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    echo "✅ Connexion à la base de données établie avec succès!\n\n";
    
    // Lister toutes les tables
    $query = "SHOW TABLES";
    $stmt = $db->query($query);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables dans la base de données:\n";
    echo "-----------------------------\n";
    foreach ($tables as $table) {
        echo "📋 {$table}\n";
        
        // Afficher la structure de la table
        $query = "DESCRIBE {$table}";
        $stmt = $db->query($query);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   Colonnes:\n";
        foreach ($columns as $column) {
            echo "   - {$column['Field']} ({$column['Type']})\n";
        }
        echo "\n";
        
        // Compter le nombre d'enregistrements
        $query = "SELECT COUNT(*) as count FROM {$table}";
        $stmt = $db->query($query);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "   Nombre d'enregistrements: {$count}\n\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données:\n";
    echo $e->getMessage() . "\n";
    
    // Vérifier si la base de données existe
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST,
            DB_USER,
            DB_PASS
        );
        
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([DB_NAME]);
        
        if (!$stmt->fetch()) {
            echo "\n⚠️ La base de données '" . DB_NAME . "' n'existe pas!\n";
            echo "Pour créer la base de données et les tables, exécutez:\n";
            echo "mysql -u " . DB_USER . " < " . __DIR__ . "/schema.sql\n";
        }
    } catch (PDOException $e) {
        echo "\n❌ Impossible de se connecter au serveur MySQL:\n";
        echo $e->getMessage() . "\n";
    }
}

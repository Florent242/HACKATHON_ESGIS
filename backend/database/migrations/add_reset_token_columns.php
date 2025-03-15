<?php

try {
    $db = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=hackathon_esgis',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Vérifier si les colonnes existent déjà
    $checkColumns = $db->query("SHOW COLUMNS FROM users WHERE Field IN ('reset_token', 'reset_token_expiry')");
    $existingColumns = $checkColumns->fetchAll(PDO::FETCH_COLUMN);
    
    // Ajouter les colonnes si elles n'existent pas
    if (!in_array('reset_token', $existingColumns)) {
        $db->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL");
        echo "Colonne reset_token ajoutée avec succès\n";
    } else {
        echo "La colonne reset_token existe déjà\n";
    }
    
    if (!in_array('reset_token_expiry', $existingColumns)) {
        $db->exec("ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL");
        echo "Colonne reset_token_expiry ajoutée avec succès\n";
    } else {
        echo "La colonne reset_token_expiry existe déjà\n";
    }
    
    // Ajouter des index pour optimiser les recherches
    $db->exec("CREATE INDEX IF NOT EXISTS idx_reset_token ON users(reset_token)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_reset_token_expiry ON users(reset_token_expiry)");
    
    echo "Migration terminée avec succès\n";
    
} catch (PDOException $e) {
    die("Erreur lors de la migration : " . $e->getMessage() . "\n");
}

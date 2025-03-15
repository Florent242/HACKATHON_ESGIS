<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../backend/models/Database.php';
require_once __DIR__ . '/../backend/models/User.php';

use Auth\Model\Database;
use Auth\Model\User;

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    $userModel = new User($db);
    
    // Test avec l'utilisateur admin
    $email = 'admin@test.com';
    $password = 'admin123';
    
    echo "Test d'authentification pour {$email}\n";
    echo "--------------------------------\n";
    
    // Vérifier si l'utilisateur existe
    $user = $userModel->findByEmail($email);
    if ($user) {
        echo "✅ Utilisateur trouvé:\n";
        echo "ID: {$user['id']}\n";
        echo "Nom: {$user['nom']}\n";
        echo "Email: {$user['email']}\n";
        echo "Rôle: {$user['role']}\n";
        echo "Mot de passe hashé: {$user['mot_de_passe']}\n\n";
        
        // Tester l'authentification
        $authenticated = $userModel->authenticate($email, $password);
        if ($authenticated) {
            echo "✅ Authentification réussie!\n";
            echo "Données de session qui seraient définies:\n";
            echo "- user_id: {$authenticated['id']}\n";
            echo "- user_role: {$authenticated['role']}\n";
            echo "- user_email: {$authenticated['email']}\n";
        } else {
            echo "❌ Échec de l'authentification: mot de passe incorrect\n";
        }
    } else {
        echo "❌ Utilisateur non trouvé avec l'email: {$email}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

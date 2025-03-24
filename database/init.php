<?php
/**
 * Script d'initialisation de la base de données
 *
 * Ce script exécute le fichier new_schema.sql pour créer ou mettre à jour la structure de la base de données
 * Il crée également un utilisateur admin par défaut si aucun n'existe
 */

require_once __DIR__ . '/../backend/includes/config.php';
require_once __DIR__ . '/../backend/models/Database.php';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=localhost;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connexion à MySQL réussie\n";

    // Vérifie si la base de données existe
    $dbExists = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'hackathon_db'")->fetchColumn();

    if (!$dbExists) {
        echo "Création de la base de données 'hackathon_db'...\n";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS hackathon_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } else {
        echo "La base de données 'hackathon_db' existe déjà\n";
    }

    // Sélectionne la base de données
    $pdo->exec("USE hackathon_db");

    // Lecture du fichier SQL
    echo "Exécution du script SQL pour créer les tables...\n";
    $sql = file_get_contents(__DIR__ . '/new_schema.sql');

    // Exécution du script SQL
    $pdo->exec($sql);

    echo "Tables créées avec succès\n";

    // Vérifie s'il y a des utilisateurs admin
    $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

    if (!$adminExists) {
        echo "Création d'un utilisateur admin par défaut...\n";

        // Hashage du mot de passe
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);

        // Insertion de l'utilisateur admin
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, bio) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Admin', 'User', 'admin@example.com', $hashedPassword, 'admin', 'Administrateur du système']);

        echo "Utilisateur admin créé avec succès :\n";
        echo "Email: admin@example.com\n";
        echo "Mot de passe: admin123\n";
    } else {
        echo "Un utilisateur admin existe déjà\n";
    }

    echo "Initialisation de la base de données terminée\n";

} catch (PDOException $e) {
    die("Erreur lors de l'initialisation de la base de données : " . $e->getMessage() . "\n");
}

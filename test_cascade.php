<?php
require 'c:\Users\flore\Documents\ESGIS20ans\HACKATHON_ESGIS\backend\models\Database.php';
$db = Database::getInstance()->getConnection();

// Création d'un projet
$projectId = 0;
$stmt = $db->prepare('INSERT INTO projets (title, description, equipe_id, challenge_id) VALUES (:title, :description, :equipe_id, :challenge_id)');
$stmt->execute([
    ':title' => 'Test Project',
    ':description' => 'This is a test project',
    ':equipe_id' => 1,
    ':challenge_id' => 1
]);
$projectId = $db->lastInsertId();

// Création d'une évaluation associée
$stmt = $db->prepare('INSERT INTO evaluations (projet_id, juge_id, score, commentaire) VALUES (:projet_id, :juge_id, :score, :commentaire)');
$stmt->execute([
    ':projet_id' => $projectId,
    ':juge_id' => 4,
    ':score' => 10,
    ':commentaire' => 'Great project!'
]);

// Suppression du projet
$stmt = $db->prepare('DELETE FROM projets WHERE id = :id');
$stmt->bindParam(':id', $projectId);
$stmt->execute();

// Vérification des évaluations restantes
$stmt = $db->prepare('SELECT COUNT(*) FROM evaluations WHERE projet_id = :projet_id');
$stmt->bindParam(':projet_id', $projectId);
$stmt->execute();
$evaluationCount = $stmt->fetchColumn();

echo "Nombre d'évaluations restantes pour le projet supprimé : " . $evaluationCount;
?>

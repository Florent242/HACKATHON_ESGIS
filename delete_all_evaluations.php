<?php
require 'c:\Users\flore\Documents\ESGIS20ans\HACKATHON_ESGIS\backend\models\Database.php';
$db = Database::getInstance()->getConnection();
$projectId = 68; // ID du projet dont on veut supprimer les évaluations
$stmt = $db->prepare('DELETE FROM evaluations WHERE projet_id = :projet_id');
$stmt->bindParam(':projet_id', $projectId);
$stmt->execute();
echo 'Toutes les évaluations pour le projet supprimées';
?>

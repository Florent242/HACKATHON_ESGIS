<?php
require 'c:\Users\flore\Documents\ESGIS20ans\HACKATHON_ESGIS\backend\models\Database.php';
$db = Database::getInstance()->getConnection();
$projectId = 68; // ID du projet dont on veut vérifier les évaluations
$stmt = $db->prepare('SELECT COUNT(*) FROM evaluations WHERE projet_id = :projet_id');
$stmt->bindParam(':projet_id', $projectId);
$stmt->execute();
echo $stmt->fetchColumn();
?>

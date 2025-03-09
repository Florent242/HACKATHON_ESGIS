<?php
require 'HACKATHON_ESGIS\backend\models\Database.php';
$db = Database::getInstance()->getConnection();
$projectId = 68; // ID du projet à supprimer
$stmt = $db->prepare('DELETE FROM projets WHERE id = :id');
$stmt->bindParam(':id', $projectId);
$stmt->execute();
echo 'Projet supprimé';
?>

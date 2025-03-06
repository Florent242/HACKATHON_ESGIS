<?php
require 'HACKATHON_ESGIS\backend\models\Database.php';
$db = Database::getInstance()->getConnection();
$evaluationId = 1; // ID de l'évaluation à supprimer
$stmt = $db->prepare('DELETE FROM evaluations WHERE id = :id');
$stmt->bindParam(':id', $evaluationId);
$stmt->execute();
echo 'Évaluation supprimée';
?>

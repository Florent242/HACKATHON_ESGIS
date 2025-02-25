<?php
require 'c:\Users\flore\Documents\ESGIS20ans\HACKATHON_ESGIS\backend\models\Database.php';
$db = Database::getInstance()->getConnection();
$projetId = 68;
$stmt = $db->prepare('SELECT COUNT(*) FROM evaluations WHERE projet_id = :projet_id');
$stmt->bindParam(':projet_id', $projetId);
$stmt->execute();
echo $stmt->fetchColumn();
?>

<?php
require 'c:\Users\flore\Documents\ESGIS20ans\HACKATHON_ESGIS\backend\models\Database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->query('SELECT * FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "ID: " . $user['id'] . " - Username: " . $user['username'] . "\n";
}
?>

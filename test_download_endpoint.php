<?php
/**
 * Script de test pour vérifier l'endpoint de téléchargement des projets
 */

// Test de l'endpoint /api/projects/12/download
$baseUrl = 'http://localhost';
$projectId = 12; // ID du projet test
$token = 'YOUR_JWT_TOKEN_HERE'; // Remplacer par un token valide

echo "Test de l'endpoint de téléchargement\n";
echo "===================================\n\n";

// Test 1: Vérifier que l'endpoint existe et retourne une réponse appropriée
$url = $baseUrl . "/api/projects/{$projectId}/download";
echo "URL testée: $url\n";

$headers = [
    'Authorization: Bearer ' . $token,
    'X-Requested-With: XMLHttpRequest'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

curl_close($ch);

echo "Code de réponse HTTP: $httpCode\n";
echo "Type de contenu: $contentType\n";

if ($httpCode == 200) {
    echo "✅ L'endpoint répond correctement\n";
} else {
    echo "❌ L'endpoint ne répond pas correctement\n";
    echo "Headers de réponse:\n$response\n";
}

echo "\nPour tester complètement:\n";
echo "1. Remplacez YOUR_JWT_TOKEN_HERE par un token valide\n";
echo "2. Vérifiez que le projet ID 12 existe et a un fichier ZIP\n";
echo "3. Testez depuis un navigateur avec les outils de développement\n";
?>
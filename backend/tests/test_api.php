<?php

// Configuration
$baseUrl = 'http://localhost/api.php';

// Fonction pour faire une requête HTTP
function makeRequest($endpoint, $method = 'GET', $data = null) {
    global $baseUrl;
    $url = $baseUrl . '/' . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Tests des routes
echo "Test des routes de l'API...\n\n";

// Test Hackathons
echo "1. Test des Hackathons\n";
echo "GET /hackathons\n";
print_r(makeRequest('hackathons'));

// Test Équipes
echo "\n2. Test des Équipes\n";
echo "GET /equipes\n";
print_r(makeRequest('equipes'));

// Test Participants
echo "\n3. Test des Participants\n";
echo "GET /participants\n";
print_r(makeRequest('participants'));

// Test Projets
echo "\n4. Test des Projets\n";
echo "GET /projets\n";
print_r(makeRequest('projets'));

// Test Évaluations
echo "\n5. Test des Évaluations\n";
echo "GET /evaluations\n";
print_r(makeRequest('evaluations'));

// Test Users
echo "\n6. Test des Users\n";
echo "GET /users\n";
print_r(makeRequest('users'));

// Test Notifications
echo "\n7. Test des Notifications\n";
echo "GET /notifications\n";
print_r(makeRequest('notifications'));

// Test Ressources
echo "\n8. Test des Ressources\n";
echo "GET /ressources\n";
print_r(makeRequest('ressources'));

// Test Commentaires
echo "\n9. Test des Commentaires\n";
echo "GET /commentaires\n";
print_r(makeRequest('commentaires'));

// Test Équipe Membres
echo "\n10. Test des Équipe Membres\n";
echo "GET /equipe-membres\n";
print_r(makeRequest('equipe-membres'));

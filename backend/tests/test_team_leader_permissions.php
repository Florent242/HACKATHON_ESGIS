<?php

// Configuration
$baseUrl = 'https://hackathon.esgis.bj/api.php';

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

// Tests des permissions du leader d'équipe
echo "\nTest des permissions du leader d'équipe\n";

// Créer un hackathon de test
$hackathonTest = makeRequest('hackathons', 'POST', [
    'name' => 'Hackathon Test',
    'description' => 'Hackathon de test pour les permissions',
    'start_date' => date('Y-m-d H:i:s'),
    'end_date' => date('Y-m-d H:i:s', strtotime('+1 week')),
    'rules' => 'Règles de test'
]);

if ($hackathonTest['status'] !== 200) {
    echo "Erreur lors de la création du hackathon de test\n";
    exit(1);
}

$hackathonId = $hackathonTest['response']['data']['id'];

// Créer un utilisateur leader
echo "\n1. Création d'un utilisateur leader\n";
$leader = makeRequest('users', 'POST', [
    'email' => 'leader@test.com',
    'password' => 'password123',
    'fullname' => 'Leader Test'
]);

if ($leader['status'] !== 200) {
    echo "Erreur lors de la création du leader\n";
    exit(1);
}

$leaderId = $leader['response']['data']['id'];

// Créer un utilisateur membre
echo "\n2. Création d'un utilisateur membre\n";
$member = makeRequest('users', 'POST', [
    'email' => 'member@test.com',
    'password' => 'password123',
    'fullname' => 'Member Test'
]);

if ($member['status'] !== 200) {
    echo "Erreur lors de la création du membre\n";
    exit(1);
}

$memberId = $member['response']['data']['id'];

// Créer une équipe avec le leader
echo "\n3. Création d'une équipe avec le leader\n";
$team = makeRequest('teams', 'POST', [
    'nom' => 'Équipe Test',
    'hackathon_id' => $hackathonId,
    'leader_id' => $leaderId
]);

if ($team['status'] !== 200) {
    echo "Erreur lors de la création de l'équipe\n";
    exit(1);
}

$teamId = $team['response']['data'];

// Test 1: Le leader peut accepter une demande d'adhésion
echo "\n4. Test: Le leader peut accepter une demande d'adhésion\n";
$memberRequest = makeRequest("teams/{$teamId}/requests", 'POST', [
    'user_id' => $memberId
]);

if ($memberRequest['status'] !== 200) {
    echo "Erreur lors de la création de la demande d'adhésion\n";
    exit(1);
}

$acceptRequest = makeRequest("teams/{$teamId}/leader/accept/{$memberId}", 'POST');
if ($acceptRequest['status'] === 200) {
    echo "✅ Test réussi: Le leader peut accepter une demande d'adhésion\n";
} else {
    echo "❌ Test échoué: Le leader ne peut pas accepter une demande d'adhésion\n";
    exit(1);
}

// Test 2: Un membre non-leader ne peut pas accepter une demande
echo "\n5. Test: Un membre non-leader ne peut pas accepter une demande\n";
$member2 = makeRequest('users', 'POST', [
    'email' => 'member2@test.com',
    'password' => 'password123',
    'fullname' => 'Member 2 Test'
]);

if ($member2['status'] !== 200) {
    echo "Erreur lors de la création du membre 2\n";
    exit(1);
}

$member2Id = $member2['response']['data']['id'];

$member2Request = makeRequest("teams/{$teamId}/requests", 'POST', [
    'user_id' => $member2Id
]);

if ($member2Request['status'] !== 200) {
    echo "Erreur lors de la création de la demande d'adhésion\n";
    exit(1);
}

$memberAcceptRequest = makeRequest("teams/{$teamId}/leader/accept/{$member2Id}", 'POST');
if ($memberAcceptRequest['status'] === 403) {
    echo "✅ Test réussi: Un membre non-leader ne peut pas accepter une demande\n";
} else {
    echo "❌ Test échoué: Un membre non-leader peut accepter une demande\n";
    exit(1);
}

// Test 3: Le leader peut ajouter des membres
echo "\n6. Test: Le leader peut ajouter des membres\n";
$addMember = makeRequest("teams/{$teamId}/members/add", 'POST', [
    'user_id' => $member2Id
]);

if ($addMember['status'] === 200) {
    echo "✅ Test réussi: Le leader peut ajouter des membres\n";
} else {
    echo "❌ Test échoué: Le leader ne peut pas ajouter des membres\n";
    exit(1);
}

// Test 4: Un membre non-leader ne peut pas ajouter de membres
echo "\n7. Test: Un membre non-leader ne peut pas ajouter de membres\n";
$memberAddMember = makeRequest("teams/{$teamId}/members/add", 'POST', [
    'user_id' => $memberId
]);

if ($memberAddMember['status'] === 403) {
    echo "✅ Test réussi: Un membre non-leader ne peut pas ajouter de membres\n";
} else {
    echo "❌ Test échoué: Un membre non-leader peut ajouter des membres\n";
    exit(1);
}

// Test 5: Le leader peut changer de leader
echo "\n8. Test: Le leader peut changer de leader\n";
$changeLeader = makeRequest("teams/{$teamId}/leader/change", 'POST', [
    'new_leader_id' => $member2Id
]);

if ($changeLeader['status'] === 200) {
    echo "✅ Test réussi: Le leader peut changer de leader\n";
} else {
    echo "❌ Test échoué: Le leader ne peut pas changer de leader\n";
    exit(1);
}

// Test 6: Un membre non-leader ne peut pas changer de leader
echo "\n9. Test: Un membre non-leader ne peut pas changer de leader\n";
$memberChangeLeader = makeRequest("teams/{$teamId}/leader/change", 'POST', [
    'new_leader_id' => $memberId
]);

if ($memberChangeLeader['status'] === 403) {
    echo "✅ Test réussi: Un membre non-leader ne peut pas changer de leader\n";
} else {
    echo "❌ Test échoué: Un membre non-leader peut changer de leader\n";
    exit(1);
}

// Nettoyage des données de test
echo "\nNettoyage des données de test...\n";
makeRequest("users/{$leaderId}", 'DELETE');
makeRequest("users/{$memberId}", 'DELETE');
makeRequest("users/{$member2Id}", 'DELETE');
makeRequest("hackathons/{$hackathonId}", 'DELETE');
makeRequest("teams/{$teamId}", 'DELETE');

echo "\nTous les tests ont été exécutés avec succès !\n";

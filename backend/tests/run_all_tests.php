<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../database/database.php';

// Configuration pour les tests
$config = [
    'db_file' => __DIR__ . '/test_database.json',
    'base_url' => 'http://localhost:8000'
];

// Classe de base pour les tests
class ApiTest {
    protected $baseUrl;
    protected $dbFile;

    public function __construct($config) {
        $this->baseUrl = $config['base_url'];
        $this->dbFile = $config['db_file'];
        
        // S'assurer que le fichier de base de données de test existe
        if (!file_exists($this->dbFile)) {
            file_put_contents($this->dbFile, json_encode([
                'hackathons' => [],
                'equipes' => [],
                'participants' => [],
                'projets' => [],
                'evaluations' => [],
                'users' => []
            ]));
        }
    }

    protected function makeRequest($endpoint, $method = 'GET', $data = null) {
        $curl = curl_init();
        
        $url = $this->baseUrl . $endpoint;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            echo "❌ CURL Error: $error\n";
        }
        
        return [
            'status' => $statusCode,
            'response' => json_decode($response, true),
            'raw_response' => $response,
            'error' => $error
        ];
    }

    protected function assert($condition, $message) {
        if ($condition) {
            echo "✅ PASS - $message\n";
        } else {
            echo "❌ FAIL - $message\n";
        }
    }

    protected function assertResponse($response, $expectedStatus, $message) {
        if ($response['status'] === $expectedStatus) {
            echo "✅ PASS - $message\n";
        } else {
            echo "❌ FAIL - $message\n";
            echo "Status attendu: $expectedStatus, reçu: {$response['status']}\n";
            if (isset($response['response']['error'])) {
                echo "Erreur: {$response['response']['error']}\n";
            }
            if (!empty($response['raw_response'])) {
                echo "Réponse brute: {$response['raw_response']}\n";
            }
        }
    }

    public function runTests() {
        echo "\n=== Tests des Hackathons ===\n";
        
        // Test GET /hackathons
        $response = $this->makeRequest('/hackathons');
        $this->assertResponse($response, 200, "GET /hackathons devrait retourner 200");
        
        // Test création d'un hackathon
        $hackathon = [
            'titre' => 'Test Hackathon',
            'description' => 'Description du test',
            'date_debut' => '2025-03-15',
            'date_fin' => '2025-03-16'
        ];
        $response = $this->makeRequest('/hackathons', 'POST', $hackathon);
        $this->assertResponse($response, 201, "POST /hackathons devrait retourner 201");
        
        if (isset($response['response']['id'])) {
            $id = $response['response']['id'];
            
            // Test GET /hackathons/{id}
            $response = $this->makeRequest("/hackathons/$id");
            $this->assertResponse($response, 200, "GET /hackathons/$id devrait retourner 200");
            
            // Test PUT /hackathons/{id}
            $update = ['titre' => 'Test Hackathon Updated'];
            $response = $this->makeRequest("/hackathons/$id", 'PUT', $update);
            $this->assertResponse($response, 200, "PUT /hackathons/$id devrait retourner 200");
            
            // Test DELETE /hackathons/{id}
            $response = $this->makeRequest("/hackathons/$id", 'DELETE');
            $this->assertResponse($response, 200, "DELETE /hackathons/$id devrait retourner 200");
        }
        
        // Tests des Équipes
        echo "\n=== Tests des Équipes ===\n";
        
        // Test GET /equipes
        $response = $this->makeRequest('/equipes');
        $this->assertResponse($response, 200, "GET /equipes devrait retourner 200");
        
        // Test création d'une équipe
        $equipe = [
            'name' => 'Test Team',
            'hackathon_id' => 1
        ];
        $response = $this->makeRequest('/equipes', 'POST', $equipe);
        $this->assertResponse($response, 201, "POST /equipes devrait retourner 201");
        
        if (isset($response['response']['id'])) {
            $id = $response['response']['id'];
            
            // Test GET /equipes/{id}
            $response = $this->makeRequest("/equipes/$id");
            $this->assertResponse($response, 200, "GET /equipes/$id devrait retourner 200");
            
            // Test PUT /equipes/{id}
            $update = ['name' => 'Test Team Updated'];
            $response = $this->makeRequest("/equipes/$id", 'PUT', $update);
            $this->assertResponse($response, 200, "PUT /equipes/$id devrait retourner 200");
            
            // Test DELETE /equipes/{id}
            $response = $this->makeRequest("/equipes/$id", 'DELETE');
            $this->assertResponse($response, 200, "DELETE /equipes/$id devrait retourner 200");
        }
        
        // Tests des Projets
        echo "\n=== Tests des Projets ===\n";
        
        // Test GET /projets
        $response = $this->makeRequest('/projets');
        $this->assertResponse($response, 200, "GET /projets devrait retourner 200");
        
        // Test création d'un projet
        $projet = [
            'titre' => 'Test Project',
            'description' => 'Test Description',
            'equipe_id' => 1
        ];
        $response = $this->makeRequest('/projets', 'POST', $projet);
        $this->assertResponse($response, 201, "POST /projets devrait retourner 201");
        
        if (isset($response['response']['id'])) {
            $id = $response['response']['id'];
            
            // Test GET /projets/{id}
            $response = $this->makeRequest("/projets/$id");
            $this->assertResponse($response, 200, "GET /projets/$id devrait retourner 200");
            
            // Test PUT /projets/{id}
            $update = ['description' => 'Updated Description'];
            $response = $this->makeRequest("/projets/$id", 'PUT', $update);
            $this->assertResponse($response, 200, "PUT /projets/$id devrait retourner 200");
            
            // Test soumission du projet
            $submit = [
                'repository_url' => 'https://github.com/test/project',
                'demo_url' => 'https://demo.test.com'
            ];
            $response = $this->makeRequest("/projets/$id/submit", 'POST', $submit);
            $this->assertResponse($response, 200, "POST /projets/$id/submit devrait retourner 200");
            
            // Test DELETE /projets/{id}
            $response = $this->makeRequest("/projets/$id", 'DELETE');
            $this->assertResponse($response, 200, "DELETE /projets/$id devrait retourner 200");
        }
        
        // Tests des Évaluations
        echo "\n=== Tests des Évaluations ===\n";
        
        // Test GET /evaluations
        $response = $this->makeRequest('/evaluations');
        $this->assertResponse($response, 200, "GET /evaluations devrait retourner 200");
        
        // Test création d'une évaluation
        $evaluation = [
            'projet_id' => 1,
            'juge_id' => 1,
            'score' => 85,
            'commentaire' => 'Bon projet'
        ];
        $response = $this->makeRequest('/evaluations', 'POST', $evaluation);
        $this->assertResponse($response, 201, "POST /evaluations devrait retourner 201");
        
        if (isset($response['response']['id'])) {
            $id = $response['response']['id'];
            
            // Test GET /evaluations/{id}
            $response = $this->makeRequest("/evaluations/$id");
            $this->assertResponse($response, 200, "GET /evaluations/$id devrait retourner 200");
            
            // Test PUT /evaluations/{id}
            $update = ['score' => 90];
            $response = $this->makeRequest("/evaluations/$id", 'PUT', $update);
            $this->assertResponse($response, 200, "PUT /evaluations/$id devrait retourner 200");
            
            // Test moyenne du projet
            $response = $this->makeRequest("/evaluations/projet/1/moyenne");
            $this->assertResponse($response, 200, "GET /evaluations/projet/1/moyenne devrait retourner 200");
            
            // Test DELETE /evaluations/{id}
            $response = $this->makeRequest("/evaluations/$id", 'DELETE');
            $this->assertResponse($response, 200, "DELETE /evaluations/$id devrait retourner 200");
        }
    }
}

// Exécution des tests
echo "=== Démarrage des tests du backend ===\n";
$test = new ApiTest($config);
$test->runTests();
echo "\n=== Tests terminés ===\n";

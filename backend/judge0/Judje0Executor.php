<?php

namespace Judge0;

require_once __DIR__ . '/Judge0Request.php';
require_once __DIR__ . '/Judge0Response.php';

/**
 * Exécuteur pour l'API Judge0 - Gère l'exécution sécurisée de code
 */
class Judge0Executor
{
    private $apiUrl;
    private $apiKey;
    private $timeout;
    private $retryAttempts;
    private $defaultHeaders;

    public function __construct($apiUrl = 'https://ce.judge0.com', $apiKey = null, $timeout = 30)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
        $this->retryAttempts = 2;
        
        $this->defaultHeaders = [
            'Content-Type: application/json',
        ];
        
        if ($this->apiKey) {
            $this->defaultHeaders[] = 'X-RapidAPI-Key: ' . $this->apiKey;
        }
    }

    /**
     * Exécute une requête Judge0
     */
    public function execute(Judge0Request $request, $wait = true, $base64 = true)
    {
        // Validation de sécurité
        try {
            $request->validateSecurity();
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('Code non autorisé: ' . $e->getMessage());
        }

        $attempts = 0;
        $lastError = null;

        while ($attempts < $this->retryAttempts) {
            try {
                // Soumettre le code
                $submission = $this->createSubmission($request->toArray(), $wait);
                
                // Si on ne veut pas attendre, on retourne juste le token
                if (!$wait) {
                    return ['token' => $submission['token']];
                }
                
                // Sinon, on récupère le résultat
                $response = $this->getSubmission($submission['token'], $base64);
                $response['other'] = $request->toArray();
                
                return new Judge0Response($response);
                
            } catch (\Exception $e) {
                $lastError = $e;
                $attempts++;
                
                if ($attempts < $this->retryAttempts) {
                    // Attendre avant de réessayer
                    sleep(1);
                }
            }
        }

        throw new \Exception('Échec après ' . $this->retryAttempts . ' tentatives: ' . $lastError->getMessage());
    }

    /**
     * Crée une nouvelle soumission
     */
    private function createSubmission($data, $wait = true)
    {
        $url = $this->apiUrl . '/submissions' . ($wait ? '?base64_encoded=false&wait=true' : '');
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $this->defaultHeaders,
                'body' => json_encode($data),
                'timeout' => $this->timeout,
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            throw new \Exception('Erreur de connexion à l\'API Judge0: ' . ($error['message'] ?? 'Erreur inconnue'));
        }
        
        $statusCode = $this->getHttpStatusCode($http_response_header);
        $decodedResponse = json_decode($response, true);
        
        if ($statusCode >= 400) {
            throw new \Exception($decodedResponse['error'] ?? 'Erreur inconnue de l\'API Judge0.', $statusCode);
        }
        
        return $decodedResponse;
    }
    
    /**
     * Récupère le résultat d'une soumission
     */
    public function getSubmission($token, $base64 = true)
    {
        $url = $this->apiUrl . '/submissions/' . $token . '?base64_encoded=' . ($base64 ? 'true' : 'false');
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $this->defaultHeaders,
                'timeout' => $this->timeout,
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            throw new \Exception('Erreur de connexion à l\'API Judge0: ' . ($error['message'] ?? 'Erreur inconnue'));
        }
        
        $statusCode = $this->getHttpStatusCode($http_response_header);
        $decodedResponse = json_decode($response, true);
        
        if ($statusCode >= 400) {
            throw new \Exception($decodedResponse['error'] ?? 'Erreur inconnue de l\'API Judge0', $statusCode);
        }
        
        return $decodedResponse;
    }
    
    /**
     * Extrait le code HTTP de la réponse
     */
    private function getHttpStatusCode($headers)
    {
        $statusLine = $headers[0];
        preg_match('{HTTP\/\S*\s(\d{3})}', $statusLine, $match);
        return (int)($match[1] ?? 500);
    }

    /**
     * Exécute du code contre un cas de test spécifique
     */
    public function executeTestCase($language, $sourceCode, $input, $expectedOutput)
    {
        $request = new Judge0Request($language, $sourceCode, $input);
        $response = $this->execute($request);

        return [
            'passed' => $response->compareOutput($expectedOutput),
            'actual_output' => $response->getOutput(),
            'expected_output' => $expectedOutput,
            'execution_time_ms' => $response->getExecutionTime(),
            'memory_used_bytes' => $response->getMemoryUsed(),
            'error' => $response->getError(),
            'is_timeout' => $response->isTimeout(),
            'is_memory_limit' => $response->isMemoryLimit()
            //, 'other' => $response->getOther(),
        ];
    }

    /**
     * Exécute du code contre plusieurs cas de test
     */
    public function executeAllTestCases($language, $sourceCode, $testCases)
    {
        $results = [];
        $totalPassed = 0;
        $totalTime = 0;
        $maxMemory = 0;

        foreach ($testCases as $index => $testCase) {
            try {
                $result = $this->executeTestCase(
                    $language,
                    $sourceCode,
                    $testCase['input_data'],
                    $testCase['expected_output']
                );

                $result['test_case_id'] = $testCase['id'] ?? $index;
                $result['is_public'] = $testCase['is_public'] ?? true;
                $result['weight'] = $testCase['weight'] ?? 1.0;
                $result['description'] = $testCase['description'] ?? "Test " . ($index + 1);

                if ($result['passed']) {
                    $totalPassed++;
                }

                $totalTime += $result['execution_time_ms'] ?? 0;
                $maxMemory = max($maxMemory, $result['memory_used_bytes'] ?? 0);

                $results[] = $result;

            } catch (\Exception $e) {
                $results[] = [
                    'test_case_id' => $testCase['id'] ?? $index,
                    'passed' => false,
                    'actual_output' => '',
                    'expected_output' => $testCase['expected_output'],
                    'execution_time_ms' => 0,
                    'memory_used_bytes' => 0,
                    'error' => 'Erreur d\'exécution: ' . $e->getMessage(),
                    'is_timeout' => false,
                    'is_memory_limit' => false,
                    'is_public' => $testCase['is_public'] ?? true,
                    'weight' => $testCase['weight'] ?? 1.0,
                    'description' => $testCase['description'] ?? "Test " . ($index + 1)
                ];
            }
        }

        // Calculer le score basé sur les poids
        $totalWeight = array_sum(array_column($testCases, 'weight'));
        $earnedWeight = 0;

        foreach ($results as $result) {
            if ($result['passed']) {
                $earnedWeight += $result['weight'];
            }
        }

        $score = $totalWeight > 0 ? ($earnedWeight / $totalWeight) * 100 : 0;

        return [
            'results' => $results,
            'summary' => [
                'total_tests' => count($testCases),
                'passed_tests' => $totalPassed,
                'score' => round($score, 2),
                'total_execution_time_ms' => $totalTime,
                'max_memory_used_bytes' => $maxMemory
            ]
        ];
    }

    /**
     * Teste la connectivité à l'API Judge0
     */
    public function testConnection()
    {
        try {
            $request = new Judge0Request('python', 'print("Hello, World!")');
            $response = $this->execute($request);
            return $response->getOutput() === 'Hello, World!';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Récupère les langages supportés
     */
    public function getSupportedLanguages()
    {
        try {
            $url = $this->apiUrl . '/languages';
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => $this->defaultHeaders,
                    'timeout' => $this->timeout,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                throw new \Exception('Impossible de récupérer les langages supportés');
            }
            
            $languages = json_decode($response, true);
            
            // Formater la réponse pour correspondre au format attendu
            return array_map(function($lang) {
                return [
                    'id' => $lang['id'],
                    'name' => $lang['name'],
                    'version' => $lang['version'] ?? '',
                    'aliases' => $lang['aliases'] ?? []
                ];
            }, $languages);
            
        } catch (\Exception $e) {
            // Retourner une liste par défaut si l'API n'est pas accessible
            return [
                ['id' => 71, 'name' => 'Python', 'version' => '3.10.0', 'aliases' => ['python3', 'py']],
                ['id' => 63, 'name' => 'JavaScript', 'version' => '18.15.0', 'aliases' => ['javascript', 'nodejs']],
                ['id' => 62, 'name' => 'Java', 'version' => '15.0.2', 'aliases' => ['java']],
                ['id' => 54, 'name' => 'C++', 'version' => '10.2.0', 'aliases' => ['cpp', 'c++']],
                ['id' => 50, 'name' => 'C', 'version' => '10.2.0', 'aliases' => ['c']]
            ];
        }
    }
}

<?php

namespace Piston;

require_once __DIR__ . '/PistonRequest.php';
require_once __DIR__ . '/PistonResponse.php';

/**
 * Exécuteur pour l'API Piston - Gère l'exécution sécurisée de code
 */
class PistonExecutor
{
    private $apiUrl;
    private $timeout;
    private $retryAttempts;

    public function __construct($apiUrl = 'https://emkc.org/api/v2/piston', $timeout = 30)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->timeout = $timeout;
        $this->retryAttempts = 2;
    }

    /**
     * Exécute une requête Piston
     */
    public function execute(PistonRequest $request)
    {
        // Validation de sécurité
        try {
            $request->validateSecurity();
        } catch (\InvalidArgumentException $e) { // Changer SecurityException en InvalidArgumentException
            throw new \Exception('Code non autorisé: ' . $e->getMessage());
        }

        $attempts = 0;
        $lastError = null;

        while ($attempts < $this->retryAttempts) {
            try {
                $response = $this->makeRequest($request);
                $response['other'] = $request->toArray();
                return new PistonResponse($response);
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
     * Exécute du code contre un cas de test spécifique
     */
    public function executeTestCase($language, $sourceCode, $input, $expectedOutput)
    {
        $request = new PistonRequest($language, $sourceCode, $input);
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
    public function executeAllTestCases($language, $sourceCode, $testCases, $challengeMaxScore)
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
                'max_memory_used_bytes' => $maxMemory,
                'total_weight' => $totalWeight,
                'earned_weight' => $earnedWeight,
                'challenge_max_score' => $challengeMaxScore
            ]
        ];
    }

    /**
     * Effectue la requête HTTP vers l'API Piston
     */
    private function makeRequest(PistonRequest $request)
    {
        $url = $this->apiUrl . '/execute';
        $data = json_encode($request->toArray());

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'User-Agent: HackathonPlatform/1.0'
                ],
                'content' => $data,
                'timeout' => $this->timeout
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last();
            throw new \Exception('Erreur de connexion à l\'API Piston: ' . ($error['message'] ?? 'Erreur inconnue'));
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Réponse JSON invalide de l\'API Piston: ' . json_last_error_msg());
        }

        return $decodedResponse;
    }

    /**
     * Teste la connectivité à l'API Piston
     */
    public function testConnection()
    {
        try {
            $request = new PistonRequest('python', 'print("Hello, World!")');
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
            $url = $this->apiUrl . '/runtimes';
            $response = @file_get_contents($url);
            
            if ($response === false) {
                throw new \Exception('Impossible de récupérer les langages supportés');
            }

            return json_decode($response, true);
        } catch (\Exception $e) {
            // Retourner une liste par défaut si l'API n'est pas accessible
            return [
                ['language' => 'python', 'version' => '3.10.0'],
                ['language' => 'javascript', 'version' => '18.15.0'],
                ['language' => 'java', 'version' => '15.0.2'],
                ['language' => 'cpp', 'version' => '10.2.0'],
                ['language' => 'c', 'version' => '10.2.0']
            ];
        }
    }
}

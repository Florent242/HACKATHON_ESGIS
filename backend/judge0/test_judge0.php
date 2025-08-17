<?php

require_once __DIR__ . '/Judge0Request.php';
require_once __DIR__ . '/Judje0Executor.php';

// Configuration
$apiUrl = 'https://ce.judge0.com';
$apiKey = null; // Mettez votre clé API si nécessaire

// Initialisation de l'exécuteur
$executor = new Judge0\Judge0Executor($apiUrl, $apiKey);

// Test 1: Vérification de la connexion
echo "=== Test de connexion à Judge0 ===\n";
try {
    $languages = $executor->getSupportedLanguages();
    echo "✓ Connexion réussie. " . count($languages) . " langages supportés.\n";
    
    // Afficher les 5 premiers langages disponibles
    echo "\nQuelques langages disponibles :\n";
    foreach (array_slice($languages, 0, 5) as $lang) {
        echo "- {$lang['name']} (ID: {$lang['id']}, Version: {$lang['version']})\n";
    }
} catch (Exception $e) {
    die("✗ Erreur de connexion: " . $e->getMessage() . "\n");
}

// Test 2: Exécution d'un code Python simple
$testCases = [
    [
        'input_data' => '',
        'expected_output' => 'Hello, World!',
        'description' => 'Test basique de sortie standard'
    ],
    [
        'input_data' => '42',
        'expected_output' => '42',
        'description' => 'Test avec entrée utilisateur'
    ]
];

// Création de la requête pour Python (ID 71)
$request = new Judge0\Judge0Request('python', 'print(input() or "Hello, World!")');

// Exécution des tests
echo "\n=== Exécution des tests ===\n";

foreach ($testCases as $i => $testCase) {
    echo "\nTest #" . ($i + 1) . ": {$testCase['description']}\n";
    echo "Entrée: " . (empty($testCase['input_data']) ? '(vide)' : $testCase['input_data']) . "\n";
    
    try {
        // Configuration de l'entrée utilisateur
        $request->setStdin($testCase['input_data']);
        
        // Exécution
        $startTime = microtime(true);
        $response = $executor->execute($request);
        $executionTime = round((microtime(true) - $startTime) * 1000); // en ms
        
        // Affichage des résultats
        echo "Sortie: " . $response->getOutput() . "\n";
        echo "Statut: " . ($response->isSuccess() ? '✓ Succès' : '✗ Échec') . "\n";
        
        if (!$response->isSuccess()) {
            echo "Erreur: " . $response->getError() . "\n";
        }
        
        echo "Temps d'exécution: {$executionTime}ms\n";
        echo "Mémoire utilisée: " . $response->getMemoryUsed() . " octets\n";
        
    } catch (Exception $e) {
        echo "✗ Erreur lors de l'exécution: " . $e->getMessage() . "\n";
    }
}

// Test 3: Test d'erreur de compilation
echo "\n=== Test d'erreur de compilation ===\n";
try {
    $request = new Judge0\Judge0Request('python', 'print("Hello, World!"'); // SyntaxError
    $response = $executor->execute($request);
    
    if (!$response->isSuccess()) {
        echo "✗ Test d'erreur réussi !\n";
        echo "Message d'erreur: " . $response->getError() . "\n";
    } else {
        echo "⚠ Le test d'erreur a réussi alors qu'il aurait dû échouer\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur lors du test d'erreur: " . $e->getMessage() . "\n";
}

// Test 4: Test de timeout
echo "\n=== Test de timeout ===\n";
try {
    $request = new Judge0\Judge0Request('python', 'import time\nwhile True: pass');
    $request->setLimits(1); // 1 seconde de limite
    
    $startTime = microtime(true);
    $response = $executor->execute($request);
    $executionTime = round((microtime(true) - $startTime) * 1000);
    
    if ($response->isTimeout()) {
        echo "✓ Timeout détecté comme prévu\n";
    } else {
        echo "⚠ Le timeout n'a pas été détecté\n";
    }
    
    echo "Temps d'exécution: {$executionTime}ms\n";
    
} catch (Exception $e) {
    echo "✗ Erreur lors du test de timeout: " . $e->getMessage() . "\n";
}

echo "\n=== Tests terminés ===\n";

<?php

// Activer l'affichage des erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        throw new RuntimeException('Invalid JSON input');
    }

    require_once __DIR__ . '/CodeExecutionController.php';
    $controller = new CodeExecutionController();
    $result = $controller->handleExecution($input);
    
    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'trace' => $e->getTrace() // À désactiver en production
    ]);
}
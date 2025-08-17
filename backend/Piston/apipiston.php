<?php
// Toujours au tout début du fichier
header('Content-Type: application/json; charset=utf-8');

// Désactiver la mise en cache
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Vérifier que rien n'a été envoyé avant
if (headers_sent()) {
    die(json_encode([
        'error' => 'Headers already sent',
        'details' => 'Check for spaces or echo before header()'
    ]));
}

// Activer le debug (à désactiver en production)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);


try {
    // Vérifier la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Méthode non autorisée', 405);
    }

    // Lire l'input JSON
    $jsonInput = file_get_contents('php://input');
    if ($jsonInput === false) {
        throw new RuntimeException('Impossible de lire les données d\'entrée');
    }

    $input = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('JSON invalide: ' . json_last_error_msg());
    }

    // Valider les données requises
    $required = ['language', 'code', 'version'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new InvalidArgumentException("Champ requis manquant: $field");
        }
    }

    // Inclure les dépendances (avec vérification des chemins)
    $baseDir = __DIR__;
    require_once $baseDir . '/PistonRequest.php';
    require_once $baseDir . '/PistonExecutor.php';
    require_once $baseDir . '/PistonResponse.php';

    // Créer et exécuter la requête
    $request = new Piston\PistonRequest(
        $input['language'],
        $input['code'],
        $input['version']
    );

    $executor = new Piston\PistonExecutor();
    $response = $executor->execute($request);

    // Renvoyer la réponse formatée
    echo json_encode([
        'success' => true,
        'result' => $response->toArray()
    ]);

} catch (Throwable $e) {
    // Gestion centralisée des erreurs
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration et fonctions utilitaires
require_once __DIR__ . '/includes/config.php';
<<<<<<< HEAD
require_once __DIR__ . '/database/database.php';
require_once __DIR__ . '/models/Database.php';
=======

// Contrôleurs
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/SignupController.php';
require_once __DIR__ . '/controllers/ResetPasswordController.php';

// Modèles
require_once __DIR__ . '/models/User.php';
>>>>>>> d07363345c399c7a5b7c589f546ca407f849d0d3
require_once __DIR__ . '/models/Hackathon.php';
require_once __DIR__ . '/models/Equipe.php';
require_once __DIR__ . '/models/Projet.php';
require_once __DIR__ . '/models/Evaluation.php';
require_once __DIR__ . '/models/User.php';

use Auth\Controller\AuthController;
use Auth\Controller\SignupController;
use Auth\Controller\ResetPasswordController;
use Auth\Model\Hackathon;

// Initialisation de la base de données
<<<<<<< HEAD
$db = Database::getInstance()->getConnection();
=======
$db = new PDO('sqlite:' . DB_FILE, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
>>>>>>> d07363345c399c7a5b7c589f546ca407f849d0d3

// Pour les requêtes OPTIONS, renvoyer directement une réponse
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Récupération de la méthode HTTP et de l'URL
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/HACKATHON_ESGIS/public/api/', '/', $uri); // Nettoyer l'URI
$request = explode('/', trim($uri, '/'));

// Extraction des composants de l'URL
$endpoint = $request[0] ?? '';
$id = $request[1] ?? null;
$action = $request[2] ?? null;

// Lecture des données du corps de la requête
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST;
}

// Validation des données d'entrée JSON
if ($input === null && in_array($method, ['POST', 'PUT']) && !empty(file_get_contents('php://input'))) {
    sendResponse(400, [
        'success' => false,
        'error' => 'Format JSON invalide',
        'details' => json_last_error_msg()
    ]);
}

try {
    switch ($endpoint) {
        case 'hackathons':
            $controller = new Hackathon($db);
            handleRequest($method, $controller, $id, $input);
            break;

        case 'equipes':
            $controller = new Equipe($db);
            handleRequest($method, $controller, $id, $input);
            break;

        case 'users':
            $controller = new User($db);
            handleRequest($method, $controller, $id, $input);
            break;

        case 'projets':
            $controller = new Projet($db);
            if ($id && $action === 'submit') {
                $result = $controller->submitProject($id, $input['repository_url'], $input['demo_url'] ?? null);
                sendResponse(200, $result);
            } else {
                handleRequest($method, $controller, $id, $input);
            }
            break;

        case 'evaluations':
            $controller = new Evaluation($db);
            if ($id === 'projet' && isset($request[2])) {
                $projetId = $request[2];
                $result = $controller->getMoyenneProjet($projetId);
                sendResponse(200, $result);
            } else {
                handleRequest($method, $controller, $id, $input);
            }
            break;

<<<<<<< HEAD
        default:
            sendResponse(404, ['error' => 'Endpoint non trouvé']);
=======
        case 'auth':
            // Initialisation des contrôleurs
            $authController = new AuthController($db);
            $signupController = new SignupController($authController);

            // Déterminer l'action en fonction de l'ID ou de l'action
            $authAction = $id ?? $action;

            switch ($authAction) {
                case 'login':
                    if ($method === 'POST') {
                        $authController->login();
                    } else {
                        throw new Exception('Méthode non autorisée');
                    }
                    break;
                case 'signup':
                    if ($method === 'POST') {
                        $signupController->handleSignup();
                    } else {
                        throw new Exception('Méthode non autorisée');
                    }
                    break;
                case 'logout':
                    if ($method === 'POST') {
                        $authController->logout();
                    } else {
                        throw new Exception('Méthode non autorisée');
                    }
                    break;
                case 'forgot-password':
                    if ($method === 'POST') {
                        $resetController = new ResetPasswordController($db);
                        $resetController->requestReset();
                    } else {
                        throw new Exception('Méthode non autorisée');
                    }
                    break;
                case 'reset-password':
                    if ($method === 'POST') {
                        $resetController = new ResetPasswordController($db);
                        $resetController->resetPassword();
                    } else {
                        throw new Exception('Méthode non autorisée');
                    }
                    break;
                default:
                    throw new Exception('Action non valide');
            }
            break;
            
        default:
            if ($endpoint === '') {
                sendResponse(200, [
                    'success' => true,
                    'message' => 'API is running',
                    'version' => '1.0'
                ]);
            } else {
                sendResponse(404, [
                    'success' => false,
                    'error' => 'Endpoint non trouvé'
                ]);
            }
>>>>>>> d07363345c399c7a5b7c589f546ca407f849d0d3
    }
} catch (Exception $e) {
    sendResponse(500, ['error' => $e->getMessage()]);
}

<<<<<<< HEAD
function handleRequest($method, $controller, $id, $input = null)
{
    switch ($method) {
        case 'GET':
            if ($id) {
                $result = $controller->find($id);
                if (!$result) {
                    sendResponse(404, ['error' => 'Ressource non trouvée']);
                }
            } else {
                $result = $controller->getAll();
            }
            sendResponse(200, $result);
            break;

        case 'POST':
            try {
                $result = $controller->create($input);
                sendResponse(201, ['id' => $result]);
            } catch (Exception $e) {
                sendResponse(400, ['error' => $e->getMessage()]);
            }
            break;

        case 'PUT':
            if (!$id) {
                sendResponse(400, ['error' => 'ID requis pour la mise à jour']);
            }
            try {
                $result = $controller->update($id, $input);
                sendResponse(200, $result);
            } catch (Exception $e) {
                sendResponse(400, ['error' => $e->getMessage()]);
            }
            break;

        case 'DELETE':
            if (!$id) {
                sendResponse(400, ['error' => 'ID requis pour la suppression']);
            }
            try {
                $result = $controller->delete($id);
                sendResponse(200, $result);
            } catch (Exception $e) {
                sendResponse(400, ['error' => $e->getMessage()]);
            }
            break;

        default:
            sendResponse(405, ['error' => 'Méthode non autorisée']);
            break;
=======
function handleRequest($method, $controller, $id, $input = null) {
    try {
        $response = ['success' => true];

        switch ($method) {
            case 'GET':
                if ($id) {
                    $result = $controller->find($id);
                    if (!$result) {
                        throw new Exception('Ressource non trouvée', 404);
                    }
                    $response['data'] = $result;
                } else {
                    $response['data'] = $controller->getAll();
                }
                sendResponse(200, $response);
                break;
                
            case 'POST':
                $result = $controller->create($input);
                $response['message'] = 'Ressource créée avec succès';
                $response['data'] = ['id' => $result];
                sendResponse(201, $response);
                break;
                
            case 'PUT':
                if (!$id) {
                    throw new Exception('ID requis pour la mise à jour', 400);
                }
                $result = $controller->update($id, $input);
                $response['message'] = 'Ressource mise à jour avec succès';
                $response['data'] = $result;
                sendResponse(200, $response);
                break;
                
            case 'DELETE':
                if (!$id) {
                    throw new Exception('ID requis pour la suppression', 400);
                }
                $result = $controller->delete($id);
                $response['message'] = 'Ressource supprimée avec succès';
                sendResponse(200, $response);
                break;
                
            default:
                throw new Exception('Méthode non autorisée', 405);
        }
    } catch (Exception $e) {
        $status = $e->getCode() ?: 400;
        sendResponse($status, [
            'success' => false,
            'error' => $e->getMessage()
        ]);
>>>>>>> d07363345c399c7a5b7c589f546ca407f849d0d3
    }
}

function sendResponse($status, $data)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    
    // S'assurer que les données sont correctement encodées
    $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    if ($jsonData === false) {
        // En cas d'erreur d'encodage JSON
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de l\'encodage des données',
            'details' => json_last_error_msg()
        ]);
    } else {
        echo $jsonData;
    }
    exit;
}
?>
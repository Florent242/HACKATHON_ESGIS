<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

use Auth\Model\Database;
use Auth\Model\Hackathon;
use Auth\Model\Equipe;
use Auth\Model\Projet;
use Auth\Model\Evaluation;
use Auth\Model\User;
use Auth\Controller\AuthController;
use Auth\Controller\HackathonController;
use Auth\Controller\EquipeController;
use Auth\Controller\NotificationController;
use Auth\Controller\ParticipantController;
use Auth\Controller\EquipeMembreController;

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/database/database.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/Hackathon.php';
require_once __DIR__ . '/models/Equipe.php';
require_once __DIR__ . '/models/Projet.php';
require_once __DIR__ . '/models/Evaluation.php';
require_once __DIR__ . '/models/User.php';

// Configurer CORS pour toutes les requêtes API
configureCors();

// Initialisation de la base de données
$db = Database::getInstance()->getConnection();

// Pour les requêtes OPTIONS, renvoyer directement une réponse
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 
const BASE_URL = '/HACKATHON_ESGIS/public';

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
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Si c'est une requête POST et que le JSON n'est pas valide, utiliser $_POST
if ($input === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST;
}

try {
    switch ($endpoint) {
        case 'auth':
            require_once __DIR__ . '/controllers/AuthController.php';
            if (!file_exists(__DIR__ . '/controllers/AuthController.php')) {
                die(json_encode(["error" => true, "message" => "Fichier AuthController introuvable"]));
            }
            
            $controller = new AuthController();
            switch ($request[1]) {
                case 'check-email':
                    if ($method === 'POST') {
                        $email = $input['email'] ?? '';
                        $controller->checkEmail($email);
                    }
                    break;

                case 'check-username':
                    if ($method === 'POST') {
                        $username = $input['username'] ?? '';
                        $controller->checkUsername($username);
                    }
                    break;
                    
                case 'login':
                    if ($method === 'POST') {
                        try {
                            $controller->login();
                            // La redirection est gérée dans le contrôleur
                            exit();
                        } catch (Exception $e) {
                            // Gérer l'erreur, par exemple en affichant un message d'erreur
                            error_log($e->getMessage());
                            // setFlashMessage('error', $e->getMessage());
                            // Rediriger vers la page de connexion avec un message d'erreur
                            header("Location: " . BASE_URL . "/auth");
                            exit();
                        }
                    }
                    break;

                case 'register':
                    if ($method === 'POST') {
                        try {
                            $controller->register();
                            // Rediriger vers le profil après l'inscription
                            header("Location: " . BASE_URL . "/user");
                            exit();
                        } catch (Exception $e) {
                            error_log($e->getMessage());
                            // setFlashMessage('error', $e->getMessage());
                            // Rediriger vers la page d'inscription avec un message d'erreur
                            header("Location: " . BASE_URL . "/auth");
                            exit();
                        }
                    }
                    break;

                case 'logout':
                    if ($method === 'POST') {
                        $controller->logout();
                        setFlashMessage('success', 'Vous avez été déconnecté avec succès');
                        // Rediriger vers la page de connexion après la déconnexion
                        header("Location: " . BASE_URL);
                        exit();
                    }
                    break;

                default:
                    throw new Exception('Endpoint non trouvé. - api ' . var_dump($uri), 404);
            }
            break;

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

        default:
            sendResponse(404, ['error' => 'Endpoint non trouvé. - api.php.']);
    }
} catch (Exception $e) {
    sendResponse(500, ['error' => $e->getMessage()]);
}

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
    }
}

function sendResponse($status, $data)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}
?>

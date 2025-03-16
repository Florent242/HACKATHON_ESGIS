<?php
/*
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');



require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/database/database.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/Hackathon.php';
require_once __DIR__ . '/models/Equipe.php';
require_once __DIR__ . '/models/Projet.php';
require_once __DIR__ . '/models/Evaluation.php';
require_once __DIR__ . '/models/User.php';
/*
// Initialisation de la base de données
$db = Database::getInstance()->getConnection();

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

        default:
            sendResponse(404, ['error' => 'Endpoint non trouvé']);
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
*/
use Auth\Controller\ParticipantController;
use Auth\Controller\AuthController;
use Auth\Controller\HackathonController;
use Auth\Controller\EquipeController;
use Auth\Controller\NotificationController;
use Auth\Controller\EquipeMembreController;

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cors.php';

// Configurer CORS pour toutes les requêtes API
configureCors();

// Définir la base URL de l'application (à adapter selon la structure de votre projet)
define('BASE_URL', '/HACKATHON_ESGIS/public'); // Modifiez en fonction du chemin d'accès à votre application

// Récupérer la méthode HTTP et l'URL
$method = $_SERVER['REQUEST_METHOD']; // Récupère la méthode HTTP (GET, POST, PUT, DELETE)
$uri = $_SERVER['REQUEST_URI']; // Récupère l'URI complète
$scriptName = dirname($_SERVER['SCRIPT_NAME']); // Chemin du script

// Nettoyage de l'URI pour enlever le chemin du script
$uri = str_replace($scriptName, '', $uri);
$uri = trim($uri, '/');

// Découpage en segments
$segments = explode('/', $uri);

// Debug pour voir les segments
// var_dump($segments);

// Router API
try {
    switch ($segments[0]) {
        case 'auth':
            require_once __DIR__ . '/../../backend/controllers/AuthController.php';
            if (!file_exists(__DIR__ . '/../../backend/controllers/AuthController.php')) {
                die(json_encode(["error" => true, "message" => "Fichier AuthController introuvable"]));
            }
            
            $controller = new AuthController();

            switch ($segments[1] ?? '') {
                case 'login':
                    if ($method === 'POST') {
                        try {
                            $controller->login();
                            // La redirection est gérée dans le contrôleur
                            exit();
                        } catch (Exception $e) {
                            // Gérer l'erreur, par exemple en affichant un message d'erreur
                            error_log($e->getMessage());
                            // Rediriger vers la page de connexion avec un message d'erreur
                            header("Location: " . BASE_URL . "/auth?error=" . urlencode($e->getMessage()));
                            exit();
                        }
                    }
                    break;

                case 'register':
                    if ($method === 'POST') {
                        try {
                            $controller->register();
                            // Rediriger vers le profil après l'inscription
                            header("Location: " . BASE_URL . "/profile");
                            exit();
                        } catch (Exception $e) {
                            error_log($e->getMessage());
                            // Rediriger vers la page d'inscription avec un message d'erreur
                            header("Location: " . BASE_URL . "/register?error=" . urlencode($e->getMessage()));
                            exit();
                        }
                    }
                    break;

                case 'logout':
                    if ($method === 'POST') {
                        $controller->logout();
                        // Rediriger vers la page de connexion après la déconnexion
                        header("Location: " . BASE_URL . "/login");
                        exit();
                    }
                    break;

                default:
                    throw new Exception('Endpoint non trouvé', 404);
            }
            break;

        case 'hackathons':
            require_once __DIR__ . '/../../backend/controllers/HackathonController.php';
            $controller = new HackathonController($db);

            if (empty($segments[1])) {
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                }
            } else {
                $id = $segments[1];
                switch ($segments[2] ?? '') {
                    case '':
                        if ($method === 'GET') {
                            $controller->get($id);
                        } elseif ($method === 'PUT') {
                            $controller->update($id);
                        } elseif ($method === 'DELETE') {
                            $controller->delete($id);
                        }
                        break;

                    case 'participants':
                        require_once __DIR__ . '/../../backend/controllers/ParticipantController.php';
                        $participantController = new ParticipantController($db);
                        
                        if ($method === 'GET') {
                            $participantController->index($id);
                        } elseif ($method === 'POST') {
                            $participantController->register($id);
                        }
                        break;

                    default:
                        throw new Exception('Endpoint non trouvé', 404);
                }
            }
            break;

        case 'equipes':
            require_once __DIR__ . '/../../backend/controllers/EquipeController.php';
            $controller = new EquipeController($db);

            if (empty($segments[1])) {
                if ($method === 'GET') {
                    $controller->index();
                } elseif ($method === 'POST') {
                    $controller->create();
                }
            } else {
                $id = $segments[1];
                switch ($segments[2] ?? '') {
                    case '':
                        if ($method === 'GET') {
                            $controller->get($id);
                        } elseif ($method === 'PUT') {
                            $controller->update($id);
                        } elseif ($method === 'DELETE') {
                            $controller->delete($id);
                        }
                        break;

                    case 'membres':
                        require_once __DIR__ . '/../../backend/controllers/EquipeMembreController.php';
                        $membreController = new EquipeMembreController($db);
                        
                        if ($method === 'GET') {
                            $membreController->index($id);
                        } elseif ($method === 'POST') {
                            $membreController->add($id);
                        }
                        break;

                    default:
                        throw new Exception('Endpoint non trouvé', 404);
                }
            }
            break;

        case 'notifications':
            require_once __DIR__ . '/../../backend/controllers/NotificationController.php';
            $controller = new NotificationController($db);

            if (empty($segments[1])) {
                if ($method === 'GET') {
                    $controller->getByUser($userId);
                }
            } else {
                switch ($segments[1]) {
                    case 'unread-count':
                        if ($method === 'GET') {
                            $controller->getUnreadCount($_SESSION['user_id']);
                        }
                        break;

                    case 'mark-all-read':
                        if ($method === 'POST') {
                            $controller->markAllAsRead($_SESSION['user_id']);
                        }
                        break;

                    default:
                        $id = $segments[1];
                        if ($method === 'POST') {
                            switch ($segments[2] ?? '') {
                                case 'read':
                                    $controller->markAsRead($id);
                                    break;
                                case 'delete':
                                    $controller->delete($id);
                                    break;
                                default:
                                    throw new Exception('Action non trouvée', 404);
                            }
                        }
                }
            }
            break;

        default:
            throw new Exception('Endpoint non trouvé', 404);
    }
} catch (Exception $e) {
    handleApiError($e, $e->getCode() ?: 500);
}

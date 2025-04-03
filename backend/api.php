<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

use Auth\Model\Database;
use Auth\Controller\AuthController;
use Auth\Controller\HackathonController;
use Auth\Controller\TeamController;
use Auth\Controller\ProjectController;
use Auth\Controller\UserController;
use Auth\Controller\NotificationController;
use Auth\Controller\ChallengeController;
use Auth\Controller\EvaluationController;

// ✅ Inclure une seule fois le fichier de configuration
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/includes/config.php';
}

// ✅ Inclure les fichiers contenant des fonctions
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/includes/functions.php';
}


// ✅ Inclure les classes seulement si elles n'existent pas déjà
$files = [
    'Database'            => '/models/Database.php',
    'Controller'          => '/controllers/Controller.php',
    'AuthController'      => '/controllers/AuthController.php',
    'UserController'      => '/controllers/UserController.php',
    'HackathonController' => '/controllers/HackathonController.php',
    'TeamController'      => '/controllers/TeamController.php',
    'ProjectController'   => '/controllers/ProjectController.php',
    'ChallengeController' => '/controllers/ChallengeController.php',
    'EvaluationController'=> '/controllers/EvaluationController.php',
];

foreach ($files as $class => $path) {
    if (!class_exists($class)) {
        require_once __DIR__ . $path;
    }
}


// require_once __DIR__ . '/models/Database.php';
// require_once __DIR__ . '/controllers/Controller.php';
// require_once __DIR__ . '/controllers/AuthController.php';
// require_once __DIR__ . '/controllers/UserController.php';
// require_once __DIR__ . '/controllers/HackathonController.php';
// require_once __DIR__ . '/controllers/TeamController.php';
// require_once __DIR__ . '/controllers/ProjectController.php';
// require_once __DIR__ . '/controllers/ChallengeController.php';
// require_once __DIR__ . '/controllers/EvaluationController.php';

// chemin de base
const BASE_URL = '/HACKATHON_ESGIS/public';

// Configurer CORS pour toutes les requêtes API
configureCors();

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
$uri = str_replace(BASE_URL . '/api/', '/', $uri); // Nettoyer l'URI
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
            $controller = new AuthController($db);
            switch ($request[1] ?? '') {
                case 'login':
                    try {
                        $controller->login();
                    } catch (Exception $e) {
                        setFlashMessage('error', 'Connexion echouée', $e->getMessage());

                        // un echo pour les requetes frontend
                        echo json_encode(['error' => $e->getMessage()]);
                        //redirection vers la page de connexion
                        header('Location: ' . BASE_URL . '/auth');
                        exit();  
                        }
                    break;
                
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
                      
                case 'register':
                    try {
                        $controller->register();
                    } catch (Exception $e) {
                        setFlashMessage('error', 'Inscription echouée', $e->getMessage());
                        
                        // un echo pour les requetes frontend
                        echo json_encode(['error' => $e->getMessage()]);
                        //redirection vers la page d'inscription
                        header('Location: ' . BASE_URL . '/auth');
                        exit();
                    }
                    break;
                case 'logout':
                    $controller->logout();
                    break;
                case 'forgot-password':
                    $controller->forgotPassword();
                    break;
                case 'reset-password':
                    $controller->resetPassword();
                    break;
                default:
                    throw new Exception('Endpoint non trouvé. - api ' . var_dump($uri), 404);
            }
            break;

        case 'users':
            $controller = new UserController($db);
            if ($id === null) {
                // Route /api/users
                if ($method === 'GET') {
                    $controller->get($id);
                } elseif ($method === 'POST') {
                    $controller->register();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/users/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } else {
                    switch ($action) {
                        case 'role':
                            $controller->updateRole($id);
                            break;
                        default:
                            throw new Exception('Action non reconnue', 404);
                    }
                }
            } else {
                throw new Exception('ID non valide', 400);
            }
            break;

        case 'hackathons':
            $controller = new HackathonController($db);
            if ($id === null) {
                // Route /api/hackathons
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/hackathons/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } else {
                    switch ($action) {
                        case 'teams':
                            $controller->getTeams($id);
                            break;
                        case 'projects':
                            $controller->getProjects($id);
                            break;
                        case 'stats':
                            $controller->getStats($id);
                            break;
                        default:
                            throw new Exception('Action non reconnue', 404);
                    }
                }
            } elseif ($id === 'active') {
                $controller->getActive();
            } elseif ($id === 'past') {
                $controller->getPast();
            } elseif ($id === 'future') {
                $controller->getFuture();
            } else {
                throw new Exception('ID non valide', 400);
            }
            break;

        case 'teams':
            $controller = new TeamController($db);
            if ($id === null) {
                // Route /api/teams
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/teams/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } else {
                    switch ($action) {
                        case 'members':
                            if ($method === 'GET') {
                                $controller->getMembers($id);
                            } elseif (isset($request[3]) && $request[3] === 'add') {
                                $controller->addMember($id);
                            } elseif (isset($request[3]) && $request[3] === 'remove') {
                                $controller->removeMember($id);
                            } else {
                                throw new Exception('Action non reconnue', 404);
                            }
                            break;
                        case 'leader':
                            if (isset($request[3]) && $request[3] === 'change') {
                                $controller->changeLeader($id);
                            } else {
                                throw new Exception('Action non reconnue', 404);
                            }
                            break;
                        default:
                            throw new Exception('Action non reconnue', 404);
                    }
                }
            } elseif ($id === 'hackathon' && is_numeric($action)) {
                $controller->getByHackathon($action);
            } elseif ($id === 'user' && is_numeric($action)) {
                $controller->getByUser($action);
            } else {
                throw new Exception('ID non valide', 400);
            }
            break;

        case 'projects':
            $controller = new ProjectController($db);
            if ($id === null) {
                // Route /api/projects
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/projects/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } else {
                    switch ($action) {
                        case 'status':
                            $controller->updateStatus($id);
                            break;
                        case 'score':
                            $controller->updateScore($id);
                            break;
                        case 'version':
                            $controller->updateVersion($id);
                            break;
                        case 'evaluations':
                            $controller->getEvaluations($id);
                            break;
                        default:
                            throw new Exception('Action non reconnue', 404);
                    }
                }
            } elseif ($id === 'team' && is_numeric($action)) {
                $controller->getByTeam($action);
            } elseif ($id === 'hackathon' && is_numeric($action)) {
                $controller->getByHackathon($action);
            } else {
                throw new Exception('ID non valide', 400);
            }
            break;

        case 'challenges':
            $controller = new ChallengeController($db);
            if ($id === null) {
                // Route /api/challenges
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/challenges/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                }
            } elseif ($id === 'hackathon' && is_numeric($action)) {
                $controller->getByHackathon($action);
            } else {
                throw new Exception('ID non valide', 400);
            }
            break;

        case 'evaluations':
            $controller = new EvaluationController($db);
            if ($id === null) {
                // Route /api/evaluations
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/evaluations/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                }
            } elseif ($id === 'project' && is_numeric($action)) {
                $controller->getByProject($action);
            } elseif ($id === 'judge' && is_numeric($action)) {
                $controller->getByJudge($action);
            } else {
                throw new Exception('ID non valide', 400);
            }
            break;

        case 'notifications':
            $controller = new NotificationController($db);
            if ($id === null) {
                // Route /api/notifications
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/notifications/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } elseif ($action === 'markAsRead') {
                    $controller->markAsRead($id);
                }
            } elseif ($id === 'user' && is_numeric($action)) {
                $controller->getByUser($action);
            } else {
                throw new Exception('ID non valide', 400);
            }
            break;

        default:
            throw new Exception('Endpoint non trouvé', 404);
    }
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => print_r($request, true)
    ]);
}
